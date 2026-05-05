<?php

class SessionSecurity {
    private const IDLE_TIMEOUT_SECONDS = 1800;
    private const ABSOLUTE_TIMEOUT_SECONDS = 28800;
    private const ROTATE_INTERVAL_SECONDS = 900;
    private const OAUTH_STATE_TTL_SECONDS = 600;

    public static function initialize($sessionDir = null) {
        $sessionPath = $sessionDir ?: (__DIR__ . '/../storage/sessions');
        if (!is_dir($sessionPath)) {
            @mkdir($sessionPath, 0750, true);
        }

        // P3: Dùng Redis session handler khi Redis khả dụng — tránh file-lock tranh chấp
        $redisHost = defined('REDIS_HOST') ? (string)REDIS_HOST : '';
        if ($redisHost !== '' && extension_loaded('redis')) {
            $redisPort = defined('REDIS_PORT') ? (int)REDIS_PORT : 6379;
            $redisPass = defined('REDIS_PASS') ? (string)REDIS_PASS : '';
            $redisDb   = defined('REDIS_DB')   ? (int)REDIS_DB   : 0;
            $prefix    = defined('REDIS_PREFIX') ? (string)REDIS_PREFIX : 'qdl:';

            $dsn = 'tcp://' . $redisHost . ':' . $redisPort
                 . '?persistent=1'
                 . '&database=' . $redisDb
                 . '&prefix=' . urlencode($prefix . 'sess:');
            if ($redisPass !== '') {
                $dsn .= '&auth=' . urlencode($redisPass);
            }

            ini_set('session.save_handler', 'redis');
            ini_set('session.save_path', $dsn);
            // Khi dùng Redis, tắt PHP GC file (không cần quét file nữa)
            ini_set('session.gc_probability', '0');
        } else {
            // File-based fallback
            if (is_dir($sessionPath) && is_writable($sessionPath)) {
                session_save_path($sessionPath);
            }
            // Giảm GC probability để ít chạy hơn — dùng cron để dọn session cũ
            ini_set('session.gc_probability', '1');
            ini_set('session.gc_divisor', '1000');
        }

        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.gc_maxlifetime', (string)self::ABSOLUTE_TIMEOUT_SECONDS);

        $isSecure = self::isHttpsRequest();
        if (PHP_VERSION_ID >= 70300) {
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => $isSecure,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        } else {
            session_set_cookie_params(0, '/; samesite=Lax', '', $isSecure, true);
        }
    }

    public static function start() {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public static function enforce() {
        self::start();

        $now = time();
        if (!isset($_SESSION['_session']) || !is_array($_SESSION['_session'])) {
            self::initializeMetadata($now);
            return ['invalidated' => false, 'reason' => null];
        }

        $meta = $_SESSION['_session'];
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['_session']['last_activity'] = $now;
            return ['invalidated' => false, 'reason' => null];
        }

        $expectedUserAgentHash = (string)($meta['user_agent_hash'] ?? '');
        $currentUserAgentHash = self::fingerprintUserAgent();
        if ($expectedUserAgentHash !== '' && $expectedUserAgentHash !== $currentUserAgentHash) {
            return self::invalidateAuthenticatedSession('session_user_agent_mismatch', 'Phien dang nhap khong hop le. Vui long dang nhap lai.');
        }

        $lastActivity = (int)($meta['last_activity'] ?? $now);
        if (($now - $lastActivity) > self::IDLE_TIMEOUT_SECONDS) {
            return self::invalidateAuthenticatedSession('session_idle_timeout', 'Phien dang nhap da het han do khong hoat dong. Vui long dang nhap lai.');
        }

        $authTime = (int)($meta['auth_time'] ?? $now);
        if (($now - $authTime) > self::ABSOLUTE_TIMEOUT_SECONDS) {
            return self::invalidateAuthenticatedSession('session_absolute_timeout', 'Phien dang nhap da het han. Vui long dang nhap lai.');
        }

        $lastRegenerated = (int)($meta['last_regenerated'] ?? $authTime);
        if (($now - $lastRegenerated) > self::ROTATE_INTERVAL_SECONDS) {
            session_regenerate_id(true);
            $_SESSION['_session']['last_regenerated'] = $now;
            self::logSecurityEvent('session_id_rotated', [
                'user_id' => (int)($_SESSION['user_id'] ?? 0),
            ]);
        }

        $_SESSION['_session']['last_activity'] = $now;
        return ['invalidated' => false, 'reason' => null];
    }

    public static function completeLogin(int $userId, string $role, string $userName, array $extraSessionData = []): void {
        self::start();

        session_regenerate_id(true);
        $_SESSION = [];

        $now = time();
        $_SESSION['user_id'] = (int)$userId;
        $_SESSION['role'] = (string)$role;
        $_SESSION['user_name'] = (string)$userName;
        foreach ($extraSessionData as $key => $value) {
            $_SESSION[$key] = $value;
        }

        self::initializeMetadata($now, true);
        self::clearSecurityTokens();
        self::logSecurityEvent('login_success', [
            'user_id' => (int)$userId,
            'role' => (string)$role,
        ]);
    }

    public static function logout($reason = 'logout') {
        self::start();
        self::logSecurityEvent($reason, [
            'user_id' => (int)($_SESSION['user_id'] ?? 0),
            'role' => (string)($_SESSION['role'] ?? ''),
        ]);

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool)$params['secure'], (bool)$params['httponly']);
        }

        session_destroy();
    }

    public static function recordFailedLogin(string $identifier, string $reason = 'invalid_credentials'): void {
        self::logSecurityEvent('login_failed', [
            'identifier' => self::sanitize($identifier),
            'reason' => self::sanitize($reason),
        ]);
    }

    public static function generateOAuthState(string $provider): string {
        self::start();

        $provider = self::sanitize($provider);
        $state = bin2hex(random_bytes(24));
        $_SESSION['_oauth_state'][$provider] = [
            'value' => $state,
            'expires_at' => time() + self::OAUTH_STATE_TTL_SECONDS,
        ];

        return $state;
    }

    public static function verifyOAuthState(string $provider, string $state): bool {
        self::start();

        $provider = self::sanitize($provider);
        $payload = $_SESSION['_oauth_state'][$provider] ?? null;
        unset($_SESSION['_oauth_state'][$provider]);

        if (!is_array($payload)) {
            self::logSecurityEvent('oauth_state_missing', ['provider' => $provider]);
            return false;
        }

        $expectedValue = (string)($payload['value'] ?? '');
        $expiresAt = (int)($payload['expires_at'] ?? 0);
        if ($expectedValue === '' || $expiresAt < time()) {
            self::logSecurityEvent('oauth_state_expired', ['provider' => $provider]);
            return false;
        }

        $state = (string)$state;
        if ($state === '' || !hash_equals($expectedValue, $state)) {
            self::logSecurityEvent('oauth_state_invalid', ['provider' => $provider]);
            return false;
        }

        return true;
    }

    public static function logSecurityEvent(string $event, array $context = []): void {
        $logDir = __DIR__ . '/../storage';
        $logFile = $logDir . '/security.log';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0750, true);
        }

        $normalizedContext = [];
        foreach ($context as $key => $value) {
            if (is_scalar($value) || $value === null) {
                $normalizedContext[$key] = self::sanitize($value);
            } else {
                $normalizedContext[$key] = $value;
            }
        }

        $payload = [
            'time' => date('c'),
            'event' => self::sanitize($event),
            'ip' => self::sanitize($_SERVER['REMOTE_ADDR'] ?? ''),
            'method' => self::sanitize($_SERVER['REQUEST_METHOD'] ?? ''),
            'uri' => self::sanitize($_SERVER['REQUEST_URI'] ?? ''),
            'user_id' => (int)($_SESSION['user_id'] ?? 0),
            'context' => $normalizedContext,
        ];

        @file_put_contents($logFile, json_encode($payload, JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND);
    }

    private static function initializeMetadata(int $now, bool $authenticated = false): void {
        $_SESSION['_session'] = [
            'created_at' => isset($_SESSION['_session']['created_at']) ? (int)$_SESSION['_session']['created_at'] : $now,
            'last_activity' => $now,
            'last_regenerated' => $now,
            'user_agent_hash' => self::fingerprintUserAgent(),
            'auth_time' => $authenticated ? $now : (int)($_SESSION['_session']['auth_time'] ?? 0),
        ];
    }

    private static function invalidateAuthenticatedSession(string $reason, string $message): array {
        self::logSecurityEvent($reason, [
            'user_id' => (int)($_SESSION['user_id'] ?? 0),
        ]);

        $_SESSION = [];
        session_regenerate_id(true);
        self::initializeMetadata(time());
        $_SESSION['error'] = $message;

        return ['invalidated' => true, 'reason' => $reason];
    }

    private static function clearSecurityTokens() {
        foreach (array_keys($_SESSION) as $key) {
            if (strpos((string)$key, 'csrf_') === 0 || $key === '_oauth_state') {
                unset($_SESSION[$key]);
            }
        }
    }

    private static function fingerprintUserAgent() {
        $userAgent = (string)($_SERVER['HTTP_USER_AGENT'] ?? 'unknown');
        return hash('sha256', $userAgent);
    }

    private static function isHttpsRequest() {
        if (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') {
            return true;
        }

        $forwardedProto = strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));
        return $forwardedProto === 'https';
    }

    private static function sanitize(mixed $value): ?string {
        if ($value === null) {
            return null;
        }

        return trim(strip_tags((string)$value));
    }
}