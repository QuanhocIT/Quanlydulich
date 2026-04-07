<?php

class RequestValidator {
    public static function sanitizeText($value) {
        if (is_array($value) || is_object($value)) {
            return '';
        }

        $value = trim((string)$value);
        $value = strip_tags($value);
        return preg_replace('/\s+/', ' ', $value) ?? '';
    }

    public static function sanitizeValue($value) {
        if (is_array($value)) {
            $clean = [];
            foreach ($value as $key => $item) {
                $clean[$key] = self::sanitizeValue($item);
            }
            return $clean;
        }

        if (is_object($value)) {
            return '';
        }

        return self::sanitizeText($value);
    }

    public static function sanitizeSuperglobals() {
        $_GET = self::sanitizeValue($_GET);
        $_POST = self::sanitizeValue($_POST);
        $_REQUEST = self::sanitizeValue($_REQUEST);
    }

    public static function whitelistParams(array $input, array $allowedKeys) {
        $filtered = [];
        foreach ($allowedKeys as $key) {
            if (array_key_exists($key, $input)) {
                $filtered[$key] = $input[$key];
            }
        }
        return $filtered;
    }

    public static function isValidRouteFormat($act) {
        return is_string($act)
            && preg_match('/^[A-Za-z0-9_]+(?:\/[A-Za-z0-9_]+)+$/', $act) === 1;
    }

    public static function sourceFromMethod($method = 'REQUEST') {
        $method = strtoupper((string)$method);
        if ($method === 'GET') {
            return $_GET;
        }
        if ($method === 'POST') {
            return $_POST;
        }
        return $_REQUEST;
    }

    public static function getString($key, $default = '', $method = 'REQUEST') {
        $source = self::sourceFromMethod($method);
        if (!array_key_exists($key, $source)) {
            return $default;
        }

        return self::sanitizeText($source[$key]);
    }

    public static function getId($key, $default = null, $method = 'REQUEST') {
        $source = self::sourceFromMethod($method);
        if (!array_key_exists($key, $source)) {
            return $default;
        }

        return self::validateId($source[$key]) ?? $default;
    }

    public static function getEmail($key, $default = null, $method = 'REQUEST') {
        $source = self::sourceFromMethod($method);
        if (!array_key_exists($key, $source)) {
            return $default;
        }

        return self::validateEmail($source[$key]) ?? $default;
    }

    public static function getPhone($key, $default = null, $method = 'REQUEST') {
        $source = self::sourceFromMethod($method);
        if (!array_key_exists($key, $source)) {
            return $default;
        }

        return self::validatePhone($source[$key]) ?? $default;
    }

    public static function getMoney($key, $default = null, $method = 'REQUEST', $min = 0.0, $max = 999999999999.0) {
        $source = self::sourceFromMethod($method);
        if (!array_key_exists($key, $source)) {
            return $default;
        }

        return self::validateMoney($source[$key], $min, $max) ?? $default;
    }

    public static function validateEmail($email) {
        $email = self::sanitizeText($email);
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    public static function validatePhone($phone) {
        $phone = preg_replace('/\D+/', '', (string)$phone) ?? '';
        if ($phone === '') {
            return null;
        }

        if (preg_match('/^(0|84)?[3-9][0-9]{8}$/', $phone) !== 1) {
            return null;
        }

        if (strpos($phone, '84') === 0 && strlen($phone) === 11) {
            $phone = '0' . substr($phone, 2);
        }

        return $phone;
    }

    public static function validateId($value) {
        if (!is_numeric($value)) {
            return null;
        }

        $id = (int)$value;
        return $id > 0 ? $id : null;
    }

    public static function validateMoney($value, $min = 0.0, $max = 999999999999.0) {
        $numeric = is_string($value) ? str_replace(',', '', $value) : $value;
        if (!is_numeric($numeric)) {
            return null;
        }

        $money = (float)$numeric;
        if ($money < $min || $money > $max) {
            return null;
        }

        return $money;
    }

    public static function validatePayload(array $source, array $rules) {
        $clean = [];
        $errors = [];

        foreach ($rules as $field => $rule) {
            $fieldRules = is_array($rule) ? $rule : [];
            $type = isset($fieldRules['type']) ? (string)$fieldRules['type'] : 'string';
            $required = !empty($fieldRules['required']);
            $min = isset($fieldRules['min']) ? $fieldRules['min'] : null;
            $max = isset($fieldRules['max']) ? $fieldRules['max'] : null;
            $enum = isset($fieldRules['enum']) && is_array($fieldRules['enum']) ? $fieldRules['enum'] : null;

            $hasValue = array_key_exists($field, $source);
            $raw = $hasValue ? $source[$field] : null;

            if ($required && (!$hasValue || $raw === null || $raw === '')) {
                $errors[$field] = 'required';
                continue;
            }

            if (!$hasValue || $raw === null || $raw === '') {
                $clean[$field] = null;
                continue;
            }

            $normalized = null;
            $valid = true;

            switch ($type) {
                case 'id':
                    $normalized = self::validateId($raw);
                    $valid = ($normalized !== null);
                    break;
                case 'email':
                    $normalized = self::validateEmail($raw);
                    $valid = ($normalized !== null);
                    break;
                case 'phone':
                    $normalized = self::validatePhone($raw);
                    $valid = ($normalized !== null);
                    break;
                case 'money':
                    $minMoney = $min !== null ? (float)$min : 0.0;
                    $maxMoney = $max !== null ? (float)$max : 999999999999.0;
                    $normalized = self::validateMoney($raw, $minMoney, $maxMoney);
                    $valid = ($normalized !== null);
                    break;
                case 'date':
                    $normalized = self::sanitizeText($raw);
                    $date = DateTime::createFromFormat('Y-m-d', $normalized);
                    $valid = ($date && $date->format('Y-m-d') === $normalized);
                    break;
                default:
                    $normalized = self::sanitizeText($raw);
                    break;
            }

            if (!$valid) {
                $errors[$field] = 'invalid_' . $type;
                continue;
            }

            if (is_string($normalized)) {
                $length = mb_strlen($normalized);
                if ($min !== null && $length < (int)$min) {
                    $errors[$field] = 'min';
                    continue;
                }
                if ($max !== null && $length > (int)$max) {
                    $errors[$field] = 'max';
                    continue;
                }
            }

            if ($enum !== null && !in_array($normalized, $enum, true)) {
                $errors[$field] = 'enum';
                continue;
            }

            $clean[$field] = $normalized;
        }

        return [
            'ok' => empty($errors),
            'data' => $clean,
            'errors' => $errors,
        ];
    }
}