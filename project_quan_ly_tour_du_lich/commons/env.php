<?php 

// Biến môi trường, dùng chung toàn hệ thống
// Khai báo dưới dạng HẰNG SỐ để không phải dùng $GLOBALS

// Load .env file if exists
if (file_exists(__DIR__ . '/../.env')) {
    $envFile = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($envFile as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }

        $eqPos = strpos($line, '=');
        if ($eqPos === false) {
            continue;
        }

        $name = trim(substr($line, 0, $eqPos));
        $value = trim(substr($line, $eqPos + 1));
        if ($name === '') {
            continue;
        }

        // Support quoted env values in .env files.
        if ((strlen($value) >= 2) && (($value[0] === '"' && $value[strlen($value) - 1] === '"') || ($value[0] === '\'' && $value[strlen($value) - 1] === '\''))) {
            $value = substr($value, 1, -1);
        }

        $_ENV[$name] = $value;
    }
}

date_default_timezone_set('Asia/Ho_Chi_Minh');

define('APP_ENV', strtolower((string)($_ENV['APP_ENV'] ?? 'local')));
define('ASSET_VERSION', (string)($_ENV['ASSET_VERSION'] ?? '1'));

// Performance/profiling (opt-in via .env)
define('PERF_LOG_ENABLED', (($_ENV['PERF_LOG_ENABLED'] ?? '0') === '1'));
define('PERF_HEADER_ENABLED', (($_ENV['PERF_HEADER_ENABLED'] ?? '0') === '1'));
define('PERF_SLOW_MS', (int)($_ENV['PERF_SLOW_MS'] ?? 800));
define('PERF_DB_PROFILE', (($_ENV['PERF_DB_PROFILE'] ?? '0') === '1'));
define('PERF_DB_SLOW_MS', (int)($_ENV['PERF_DB_SLOW_MS'] ?? 120));
define('PERF_DB_SLOW_MAX', (int)($_ENV['PERF_DB_SLOW_MAX'] ?? 5));

if (!class_exists('ProfiledPDOStatement', false)) {
    class ProfiledPDOStatement extends PDOStatement {
        public static int $queryCount = 0;
        public static float $queryTimeMs = 0.0;
        public static array $slowQueries = [];

        protected function __construct() {}

        public function execute($params = null): bool {
            $start = microtime(true);
            try {
                if ($params === null) {
                    $result = parent::execute();
                } else {
                    $result = parent::execute($params);
                }
                return $result;
            } finally {
                $elapsedMs = (microtime(true) - $start) * 1000.0;
                self::$queryCount++;
                self::$queryTimeMs += $elapsedMs;

                $slowMs = defined('PERF_DB_SLOW_MS') ? (int)PERF_DB_SLOW_MS : 120;
                $maxItems = defined('PERF_DB_SLOW_MAX') ? (int)PERF_DB_SLOW_MAX : 5;
                if ($elapsedMs >= $slowMs && count(self::$slowQueries) < $maxItems) {
                    self::$slowQueries[] = [
                        'ms' => round($elapsedMs, 1),
                        'sql' => $this->queryString,
                    ];
                }
            }
        }
    }
}

// Base URL
$envBaseUrl = trim((string)($_ENV['BASE_URL'] ?? ''));
if ($envBaseUrl === '' || strtoupper($envBaseUrl) === 'AUTO') {
    $defaultPath = '/du_an_1-main/project_quan_ly_tour_du_lich/';
    if (!empty($_SERVER['HTTP_HOST'])) {
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || ((int)($_SERVER['SERVER_PORT'] ?? 0) === 443)
            || (strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https');
        $scheme = $isHttps ? 'https' : 'http';
        $envBaseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . $defaultPath;
    } else {
        // CLI fallback
        $envBaseUrl = 'http://localhost' . $defaultPath;
    }
}
define('BASE_URL', rtrim($envBaseUrl, '/') . '/');

define('DB_HOST'    , $_ENV['DB_HOST'] ?? 'localhost');
define('DB_PORT'    , (int)($_ENV['DB_PORT'] ?? 3306));
define('DB_USERNAME', $_ENV['DB_USERNAME'] ?? 'root');
define('DB_PASSWORD', $_ENV['DB_PASSWORD'] ?? '');
define('DB_NAME'    , $_ENV['DB_NAME'] ?? 'quan_ly_tour_du_lich');  // Tên database

// Path Configuration
define('PATH_ROOT', __DIR__ . '/../');
define('PATH_UPLOADS', PATH_ROOT . 'uploads/');
define('PATH_VIEWS', PATH_ROOT . 'views/');

// Google OAuth
// Lưu ý: Nếu dùng file .env thì nên thêm vào .env, nếu không thì thêm ở đây
define('GOOGLE_CLIENT_ID', $_ENV['GOOGLE_CLIENT_ID'] ?? '');
define('GOOGLE_CLIENT_SECRET', $_ENV['GOOGLE_CLIENT_SECRET'] ?? '');
define('GOOGLE_REDIRECT_URI', $_ENV['GOOGLE_REDIRECT_URI'] ?? (BASE_URL . 'google_callback.php'));

// Payment gateway configuration
define('PAYMENT_MODE', $_ENV['PAYMENT_MODE'] ?? 'manual_qr'); // mock | vnpay | manual_qr
define('VNPAY_TMN_CODE', $_ENV['VNPAY_TMN_CODE'] ?? '');
define('VNPAY_HASH_SECRET', $_ENV['VNPAY_HASH_SECRET'] ?? '');
define('VNPAY_URL', $_ENV['VNPAY_URL'] ?? 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html');
define('VNPAY_RETURN_URL', $_ENV['VNPAY_RETURN_URL'] ?? (BASE_URL . 'index.php?act=payment/callback'));
define('VNPAY_IPN_URL', $_ENV['VNPAY_IPN_URL'] ?? (BASE_URL . 'index.php?act=payment/vnpayIpn'));
define('QR_PAYMENT_IMAGE_URL', $_ENV['QR_PAYMENT_IMAGE_URL'] ?? '/public/uploads/qr/image.png');
define('QR_PAYMENT_ACCOUNT_NAME', $_ENV['QR_PAYMENT_ACCOUNT_NAME'] ?? 'CAP NHAT TEN CHU TK');
define('QR_PAYMENT_ACCOUNT_NUMBER', $_ENV['QR_PAYMENT_ACCOUNT_NUMBER'] ?? 'CAP NHAT SO TAI KHOAN');
define('QR_PAYMENT_BANK_NAME', $_ENV['QR_PAYMENT_BANK_NAME'] ?? 'MB Bank');
define('QR_PAYMENT_TRANSFER_NOTE_HINT', $_ENV['QR_PAYMENT_TRANSFER_NOTE_HINT'] ?? 'TEN + SDT + BOOKING_ID');

// Bank transfer webhook (Casso/SePay/custom)
define('BANK_WEBHOOK_ENABLED', (($_ENV['BANK_WEBHOOK_ENABLED'] ?? '0') === '1'));
define('BANK_WEBHOOK_PROVIDER', $_ENV['BANK_WEBHOOK_PROVIDER'] ?? 'custom'); // custom | casso | sepay
define('BANK_WEBHOOK_SECRET', $_ENV['BANK_WEBHOOK_SECRET'] ?? '');
define('BANK_WEBHOOK_ALLOW_OVERPAY', (($_ENV['BANK_WEBHOOK_ALLOW_OVERPAY'] ?? '1') === '1'));

// Mail delivery configuration
define('MAIL_ENABLED', (($_ENV['MAIL_ENABLED'] ?? '1') === '1'));
define('MAIL_FROM_ADDRESS', trim((string)($_ENV['MAIL_FROM_ADDRESS'] ?? '')));
define('MAIL_FROM_NAME', trim((string)($_ENV['MAIL_FROM_NAME'] ?? 'AVENTURA')));
define('MAIL_REPLY_TO', trim((string)($_ENV['MAIL_REPLY_TO'] ?? '')));
define('MAIL_REPLY_TO_NAME', trim((string)($_ENV['MAIL_REPLY_TO_NAME'] ?? MAIL_FROM_NAME)));
define('SMTP_HOST', trim((string)($_ENV['SMTP_HOST'] ?? '')));
define('SMTP_PORT', (int)($_ENV['SMTP_PORT'] ?? 587));
define('SMTP_USERNAME', trim((string)($_ENV['SMTP_USERNAME'] ?? '')));
define('SMTP_PASSWORD', (string)($_ENV['SMTP_PASSWORD'] ?? ''));
define('SMTP_ENCRYPTION', strtolower(trim((string)($_ENV['SMTP_ENCRYPTION'] ?? 'tls'))));
define('SMTP_AUTH', (($_ENV['SMTP_AUTH'] ?? '1') === '1'));
define('SMTP_TIMEOUT', (int)($_ENV['SMTP_TIMEOUT'] ?? 20));

// Hàm tạo kết nối PDO
function getPDOConnection() {
    static $sharedConn = null;

    if ($sharedConn instanceof PDO) {
        return $sharedConn;
    }

    try {
        $pdoOptions = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ];

        if (defined('PERF_DB_PROFILE') && PERF_DB_PROFILE) {
            $pdoOptions[PDO::ATTR_STATEMENT_CLASS] = [ProfiledPDOStatement::class, []];
        }

        $sharedConn = new PDO(
            "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USERNAME,
            DB_PASSWORD,
            $pdoOptions
        );
        // Thiết lập timezone
        $sharedConn->exec("SET time_zone = '+07:00'");
        return $sharedConn;
    } catch (PDOException $e) {
        die("Kết nối thất bại: " . $e->getMessage());
    }
}

