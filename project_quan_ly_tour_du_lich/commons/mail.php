<?php

if (!class_exists('PHPMailer\\PHPMailer\\PHPMailer', false)) {
    $autoloadPath = __DIR__ . '/../vendor/autoload.php';
    if (is_file($autoloadPath)) {
        require_once $autoloadPath;
    }
}

function mailLogEvent($status, array $payload = []) {
    $logFile = __DIR__ . '/../storage/mail_log.txt';
    $payload['status'] = $status;
    $payload['timestamp'] = date('c');

    @file_put_contents(
        $logFile,
        json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL,
        FILE_APPEND
    );
}

function normalizeMailAddress($email) {
    $email = trim((string)$email);
    if ($email === '') {
        return '';
    }

    if (function_exists('validateEmail')) {
        $validated = validateEmail($email);
        return $validated !== null ? $validated : '';
    }

    return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : '';
}

function encodeMailHeader($value) {
    $value = trim((string)$value);
    if ($value === '') {
        return '';
    }

    if (preg_match('/^[\x20-\x7E]+$/', $value) === 1) {
        return $value;
    }

    return '=?UTF-8?B?' . base64_encode($value) . '?=';
}

function buildMailAddressHeader($email, $name = '') {
    $email = normalizeMailAddress($email);
    if ($email === '') {
        return '';
    }

    $name = trim((string)$name);
    return $name === '' ? $email : encodeMailHeader($name) . ' <' . $email . '>';
}

function getDefaultMailFromAddress() {
    $configured = normalizeMailAddress(MAIL_FROM_ADDRESS);
    if ($configured !== '') {
        return $configured;
    }

    $host = parse_url((string)BASE_URL, PHP_URL_HOST);
    if (!is_string($host) || trim($host) === '') {
        $host = 'localhost';
    }

    return 'no-reply@' . preg_replace('/[^A-Za-z0-9.-]/', '', $host);
}

function getSmtpSecurity() {
    $encryption = strtolower(trim((string)SMTP_ENCRYPTION));
    if ($encryption === 'ssl') {
        return 'ssl';
    }

    if ($encryption === 'tls' || $encryption === 'starttls') {
        return 'tls';
    }

    return '';
}

function isSmtpMailConfigured() {
    if (!class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
        return false;
    }

    if (SMTP_HOST === '' || SMTP_PORT <= 0) {
        return false;
    }

    return !SMTP_AUTH || SMTP_USERNAME !== '';
}

function buildPlainTextFromHtml($html) {
    $html = (string)$html;
    if ($html === '') {
        return '';
    }

    $text = preg_replace('/<\s*br\s*\/?>/i', "\n", $html);
    $text = preg_replace('/<\/p>/i', "\n\n", (string)$text);
    $text = strip_tags((string)$text);
    $decoded = html_entity_decode((string)$text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return trim(preg_replace("/\n{3,}/", "\n\n", (string)$decoded) ?? '');
}

function sendHtmlEmail($to, $subject, $htmlBody, $textBody = '', array $options = []) {
    $recipient = normalizeMailAddress($to);
    $subject = trim((string)$subject);

    if ($recipient === '' || $subject === '') {
        mailLogEvent('invalid_mail_request', [
            'to' => (string)$to,
            'subject' => $subject,
        ]);
        return false;
    }

    if (!MAIL_ENABLED) {
        mailLogEvent('mail_disabled', [
            'to' => $recipient,
            'subject' => $subject,
        ]);
        return false;
    }

    $fromAddress = getDefaultMailFromAddress();
    $fromName = trim((string)($options['from_name'] ?? MAIL_FROM_NAME));
    $replyToAddress = normalizeMailAddress($options['reply_to'] ?? MAIL_REPLY_TO);
    $replyToName = trim((string)($options['reply_to_name'] ?? MAIL_REPLY_TO_NAME));
    $attachmentPath = isset($options['attachment_path']) ? trim((string)$options['attachment_path']) : '';
    $attachmentName = isset($options['attachment_name']) ? trim((string)$options['attachment_name']) : '';

    $htmlBody = (string)$htmlBody;
    $textBody = trim((string)$textBody);
    if ($textBody === '') {
        $textBody = buildPlainTextFromHtml($htmlBody);
    }

    $transport = isSmtpMailConfigured() ? 'smtp' : 'mail';

    if ($transport === 'smtp') {
        try {
            $mailer = new PHPMailer\PHPMailer\PHPMailer(true);
            $mailer->isSMTP();
            $mailer->Host = SMTP_HOST;
            $mailer->Port = SMTP_PORT;
            $mailer->SMTPAuth = SMTP_AUTH;
            $mailer->Username = SMTP_USERNAME;
            $mailer->Password = SMTP_PASSWORD;
            $mailer->Timeout = SMTP_TIMEOUT;
            $mailer->CharSet = 'UTF-8';
            $mailer->Encoding = 'base64';
            $mailer->isHTML(true);
            $mailer->setFrom($fromAddress, $fromName);
            $mailer->addAddress($recipient);
            $mailer->Subject = $subject;
            $mailer->Body = $htmlBody;
            $mailer->AltBody = $textBody;

            $smtpSecurity = getSmtpSecurity();
            if ($smtpSecurity !== '') {
                $mailer->SMTPSecure = $smtpSecurity;
            }

            if ($replyToAddress !== '') {
                $mailer->addReplyTo($replyToAddress, $replyToName);
            }

            if ($attachmentPath !== '' && is_file($attachmentPath) && is_readable($attachmentPath)) {
                if ($attachmentName === '') {
                    $attachmentName = basename($attachmentPath);
                }
                $mailer->addAttachment($attachmentPath, $attachmentName);
            }

            $sent = $mailer->send();
            mailLogEvent($sent ? 'mail_sent' : 'mail_failed', [
                'transport' => 'smtp',
                'to' => $recipient,
                'subject' => $subject,
                'from' => $fromAddress,
                'attachment' => $attachmentPath,
            ]);

            return $sent;
        } catch (Throwable $e) {
            mailLogEvent('mail_failed', [
                'transport' => 'smtp',
                'to' => $recipient,
                'subject' => $subject,
                'from' => $fromAddress,
                'attachment' => $attachmentPath,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    $headers = [
        'MIME-Version: 1.0',
        'From: ' . buildMailAddressHeader($fromAddress, $fromName),
        'X-Mailer: PHP/' . phpversion(),
    ];

    if ($replyToAddress !== '') {
        $headers[] = 'Reply-To: ' . buildMailAddressHeader($replyToAddress, $replyToName);
    }

    $body = '';
    if ($attachmentPath !== '' && is_file($attachmentPath) && is_readable($attachmentPath)) {
        $mixedBoundary = 'mixed_' . bin2hex(random_bytes(12));
        $altBoundary = 'alt_' . bin2hex(random_bytes(12));
        $attachmentContent = chunk_split(base64_encode((string)file_get_contents($attachmentPath)));
        if ($attachmentName === '') {
            $attachmentName = basename($attachmentPath);
        }

        $headers[] = 'Content-Type: multipart/mixed; boundary="' . $mixedBoundary . '"';
        $body = '--' . $mixedBoundary . "\r\n"
            . 'Content-Type: multipart/alternative; boundary="' . $altBoundary . '"' . "\r\n\r\n"
            . '--' . $altBoundary . "\r\n"
            . "Content-Type: text/plain; charset=UTF-8\r\n"
            . "Content-Transfer-Encoding: 8bit\r\n\r\n"
            . $textBody . "\r\n\r\n"
            . '--' . $altBoundary . "\r\n"
            . "Content-Type: text/html; charset=UTF-8\r\n"
            . "Content-Transfer-Encoding: 8bit\r\n\r\n"
            . $htmlBody . "\r\n\r\n"
            . '--' . $altBoundary . "--\r\n"
            . '--' . $mixedBoundary . "\r\n"
            . 'Content-Type: application/octet-stream; name="' . addslashes($attachmentName) . '"' . "\r\n"
            . 'Content-Transfer-Encoding: base64' . "\r\n"
            . 'Content-Disposition: attachment; filename="' . addslashes($attachmentName) . '"' . "\r\n\r\n"
            . $attachmentContent . "\r\n"
            . '--' . $mixedBoundary . "--";
    } else {
        $altBoundary = 'alt_' . bin2hex(random_bytes(12));
        $headers[] = 'Content-Type: multipart/alternative; boundary="' . $altBoundary . '"';
        $body = '--' . $altBoundary . "\r\n"
            . "Content-Type: text/plain; charset=UTF-8\r\n"
            . "Content-Transfer-Encoding: 8bit\r\n\r\n"
            . $textBody . "\r\n\r\n"
            . '--' . $altBoundary . "\r\n"
            . "Content-Type: text/html; charset=UTF-8\r\n"
            . "Content-Transfer-Encoding: 8bit\r\n\r\n"
            . $htmlBody . "\r\n\r\n"
            . '--' . $altBoundary . '--';
    }

    if (PHP_OS_FAMILY === 'Windows') {
        @ini_set('sendmail_from', $fromAddress);
    }

    $encodedSubject = encodeMailHeader($subject);
    $sent = @mail($recipient, $encodedSubject, $body, implode("\r\n", $headers));

    mailLogEvent($sent ? 'mail_sent' : 'mail_failed', [
        'transport' => 'mail',
        'to' => $recipient,
        'subject' => $subject,
        'from' => $fromAddress,
        'attachment' => $attachmentPath,
    ]);

    return $sent;
}

function sendInvoiceEmail($to, $subject, $body, $attachmentPath = null) {
    $plainTextBody = trim((string)$body);
    $htmlBody = '<!DOCTYPE html><html lang="vi"><head><meta charset="UTF-8"></head><body style="font-family:Arial,sans-serif;line-height:1.6;color:#1f2937;">'
        . '<div style="max-width:720px;margin:0 auto;padding:24px;background:#ffffff;border:1px solid #e5e7eb;border-radius:12px;">'
        . '<h2 style="margin:0 0 16px;color:#111827;">AVENTURA</h2>'
        . '<div style="white-space:pre-line;">' . nl2br(htmlspecialchars($plainTextBody, ENT_QUOTES, 'UTF-8')) . '</div>'
        . '</div></body></html>';

    return sendHtmlEmail($to, $subject, $htmlBody, $plainTextBody, [
        'attachment_path' => $attachmentPath,
    ]);
}
