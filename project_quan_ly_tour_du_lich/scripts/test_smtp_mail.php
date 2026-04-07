<?php
/**
 * Test SMTP mail delivery via PHPMailer
 * Usage: php scripts/test_smtp_mail.php
 */

require_once __DIR__ . '/../commons/env.php';
require_once __DIR__ . '/../commons/mail.php';

echo "============================================\n";
echo "SMTP Mail Configuration Test\n";
echo "============================================\n\n";

// Display current SMTP configuration
echo "Current Configuration:\n";
echo "MAIL_ENABLED: " . (MAIL_ENABLED ? "Yes" : "No") . "\n";
echo "SMTP_HOST: " . SMTP_HOST . "\n";
echo "SMTP_PORT: " . SMTP_PORT . "\n";
echo "SMTP_USERNAME: " . SMTP_USERNAME . "\n";
echo "SMTP_ENCRYPTION: " . SMTP_ENCRYPTION . "\n";
echo "MAIL_FROM_ADDRESS: " . MAIL_FROM_ADDRESS . "\n";
echo "MAIL_FROM_NAME: " . MAIL_FROM_NAME . "\n\n";

// Check if SMTP is configured
if (!isSmtpMailConfigured()) {
    echo "❌ SMTP is NOT properly configured. Falling back to PHP mail().\n";
    echo "Make sure these are set in .env:\n";
    echo "  - SMTP_HOST\n";
    echo "  - SMTP_PORT\n";
    echo "  - SMTP_USERNAME\n";
    echo "  - SMTP_PASSWORD\n\n";
} else {
    echo "✅ SMTP is properly configured.\n\n";
}

// Send test email
echo "Attempting to send test email...\n\n";

$testEmail = SMTP_USERNAME;  // Send to the SMTP account itself
$subject = "Test Email from AVENTURA Tour System";
$htmlBody = <<<HTML
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; line-height: 1.6; }
        .container { max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
        .header { color: #2ecc71; font-size: 24px; margin-bottom: 20px; }
        .content { margin: 15px 0; }
        .footer { color: #999; font-size: 12px; margin-top: 30px; border-top: 1px solid #ddd; padding-top: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">✓ AVENTURA Mail Test Successful</div>
        <div class="content">
            <p>Hello,</p>
            <p>This is a test email from your AVENTURA Tour Management system.</p>
            <p><strong>Test Details:</strong></p>
            <ul>
                <li>SMTP Host: {SMTP_HOST}</li>
                <li>SMTP Port: {SMTP_PORT}</li>
                <li>From: {FROM}</li>
                <li>Sent at: {TIME}</li>
            </ul>
            <p>If you received this email, your SMTP configuration is working correctly!</p>
        </div>
        <div class="footer">
            <p>AVENTURA Tour Management System</p>
            <p>This is an automated test email - do not reply.</p>
        </div>
    </div>
</body>
</html>
HTML;

$htmlBody = str_replace(['{SMTP_HOST}', '{SMTP_PORT}', '{FROM}', '{TIME}'], [
    SMTP_HOST,
    SMTP_PORT,
    MAIL_FROM_ADDRESS,
    date('Y-m-d H:i:s'),
], $htmlBody);

$plainTextBody = <<<TEXT
AVENTURA Mail Test Successful

This is a test email from your AVENTURA Tour Management system.

Test Details:
- SMTP Host: {SMTP_HOST}
- SMTP Port: {SMTP_PORT}
- From: {FROM}
- Sent at: {TIME}

If you received this email, your SMTP configuration is working correctly!

--
AVENTURA Tour Management System
This is an automated test email - do not reply.
TEXT;

$plainTextBody = str_replace(['{SMTP_HOST}', '{SMTP_PORT}', '{FROM}', '{TIME}'], [
    SMTP_HOST,
    SMTP_PORT,
    MAIL_FROM_ADDRESS,
    date('Y-m-d H:i:s'),
], $plainTextBody);

$sent = sendHtmlEmail(
    $testEmail,
    $subject,
    $htmlBody,
    $plainTextBody
);

if ($sent) {
    echo "✅ SUCCESS!\n";
    echo "Test email sent to: $testEmail\n";
    echo "\nSMTP Configuration is working correctly!\n";
    echo "\nYour mail system can now:\n";
    echo "  • Send booking confirmations\n";
    echo "  • Send invoice emails\n";
    echo "  • Send payment notifications\n";
} else {
    echo "❌ FAILED!\n";
    echo "Could not send test email to: $testEmail\n";
    echo "\nPlease check:\n";
    echo "  1. SMTP credentials are correct\n";
    echo "  2. .env file has been updated\n";
    echo "  3. Network connection is available\n";
    echo "  4. Brevo account is active\n";
}

echo "\nCheck storage/mail_log.txt for detailed logs.\n";
echo "\n============================================\n";
?>
