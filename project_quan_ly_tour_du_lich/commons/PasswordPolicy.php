<?php

class PasswordPolicy {
    public const MIN_LENGTH = 8;

    public static function generateTemporaryPassword($length = 14) {
        $length = max(12, (int)$length);

        $uppercase = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        $lowercase = 'abcdefghijkmnopqrstuvwxyz';
        $digits = '23456789';
        $specials = '!@#$%^&*()-_=+[]{}';

        $required = [
            $uppercase[random_int(0, strlen($uppercase) - 1)],
            $lowercase[random_int(0, strlen($lowercase) - 1)],
            $digits[random_int(0, strlen($digits) - 1)],
            $specials[random_int(0, strlen($specials) - 1)],
        ];

        $allChars = $uppercase . $lowercase . $digits . $specials;
        while (count($required) < $length) {
            $required[] = $allChars[random_int(0, strlen($allChars) - 1)];
        }

        shuffle($required);
        return implode('', $required);
    }

    public static function validate($password) {
        $password = (string)$password;
        $errors = [];

        if (strlen($password) < self::MIN_LENGTH) {
            $errors[] = 'min_length';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'missing_uppercase';
        }
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'missing_lowercase';
        }
        if (!preg_match('/\d/', $password)) {
            $errors[] = 'missing_digit';
        }
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'missing_special';
        }

        return [
            'ok' => empty($errors),
            'errors' => $errors,
        ];
    }

    public static function isSecureHash($storedPassword) {
        $storedPassword = (string)$storedPassword;
        if ($storedPassword === '') {
            return false;
        }

        $info = password_get_info($storedPassword);
        return !empty($info['algo']);
    }

    public static function needsForceChange($storedHash) {
        $storedHash = (string)$storedHash;
        if ($storedHash === '') {
            return false;
        }

        // Accounts left with default password must change immediately after login.
        return password_verify('123456', $storedHash);
    }
}