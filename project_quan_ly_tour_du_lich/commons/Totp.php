<?php
/**
 * Pure-PHP TOTP (RFC 6238) implementation — no external dependencies.
 * Compatible with Google Authenticator, Authy, Microsoft Authenticator.
 */
class Totp {
    private const BASE32_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    private const DIGITS          = 6;
    private const PERIOD          = 30;   // seconds per time-step
    private const WINDOW          = 1;    // accept codes ±1 period (clock skew)

    // ──────────────────────────────────────────────────────────────────────
    // Public API
    // ──────────────────────────────────────────────────────────────────────

    /** Generate a cryptographically secure 160-bit secret (32-char Base32 string). */
    public static function generateSecret(): string {
        return self::base32Encode(random_bytes(20));
    }

    /**
     * Verify a 6-digit code against the secret.
     * Accepts codes from current ±WINDOW time-steps (default ±30 s).
     */
    public static function verify(string $secret, string $code): bool {
        $code = preg_replace('/\s+/', '', $code);
        if (!ctype_digit($code) || strlen($code) !== self::DIGITS) {
            return false;
        }

        $timeStep = (int)floor(time() / self::PERIOD);
        for ($i = -self::WINDOW; $i <= self::WINDOW; $i++) {
            if (hash_equals(self::computeCode($secret, $timeStep + $i), $code)) {
                return true;
            }
        }
        return false;
    }

    /** Build the otpauth:// URI used for QR code generation. */
    public static function otpauthUri(string $secret, string $accountLabel, string $issuer = 'QuanLyTour'): string {
        return 'otpauth://totp/'
            . rawurlencode($issuer) . ':' . rawurlencode($accountLabel)
            . '?secret=' . rawurlencode($secret)
            . '&issuer=' . rawurlencode($issuer)
            . '&algorithm=SHA1&digits=' . self::DIGITS . '&period=' . self::PERIOD;
    }

    /** Return a URL that renders a QR code PNG via api.qrserver.com (no API key required). */
    public static function qrCodeUrl(string $otpauthUri, int $size = 220): string {
        return 'https://api.qrserver.com/v1/create-qr-code/?size=' . $size . 'x' . $size
            . '&ecc=M&data=' . rawurlencode($otpauthUri);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Internal helpers
    // ──────────────────────────────────────────────────────────────────────

    private static function computeCode(string $secret, int $timeStep): string {
        $key  = self::base32Decode($secret);
        $msg  = pack('N2', 0, $timeStep);           // 8-byte big-endian counter
        $hash = hash_hmac('sha1', $msg, $key, true); // 20-byte HMAC-SHA1

        // Dynamic truncation (RFC 4226 §5.4)
        $offset = ord($hash[19]) & 0x0F;
        $code   = (
            ((ord($hash[$offset])     & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) << 8)  |
            ( ord($hash[$offset + 3]) & 0xFF)
        ) % (10 ** self::DIGITS);

        return str_pad((string)$code, self::DIGITS, '0', STR_PAD_LEFT);
    }

    public static function base32Encode(string $data): string {
        $alphabet = self::BASE32_ALPHABET;
        $out      = '';
        $buf      = 0;
        $bits     = 0;

        foreach (str_split($data) as $byte) {
            $buf   = ($buf << 8) | ord($byte);
            $bits += 8;
            while ($bits >= 5) {
                $bits -= 5;
                $out  .= $alphabet[($buf >> $bits) & 0x1F];
            }
        }
        if ($bits > 0) {
            $out .= $alphabet[($buf << (5 - $bits)) & 0x1F];
        }
        return $out;
    }

    public static function base32Decode(string $data): string {
        $alphabet = self::BASE32_ALPHABET;
        $data     = strtoupper(preg_replace('/[^A-Z2-7=]/', '', $data));
        $out      = '';
        $buf      = 0;
        $bits     = 0;

        foreach (str_split($data) as $char) {
            if ($char === '=') break;
            $val = strpos($alphabet, $char);
            if ($val === false) continue;
            $buf   = ($buf << 5) | $val;
            $bits += 5;
            if ($bits >= 8) {
                $bits -= 8;
                $out  .= chr(($buf >> $bits) & 0xFF);
            }
        }
        return $out;
    }
}
