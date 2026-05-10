<?php
require __DIR__ . '/../commons/env.php';
require __DIR__ . '/../commons/function.php';

if (!function_exists('curl_init')) {
    fwrite(STDERR, "cURL extension is required.\n");
    exit(1);
}

$_SERVER['HTTP_USER_AGENT'] = 'PerfBenchUA/1.0';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

initializeSecureSession();

$conn = connectDB();
$stmt = $conn->query("SELECT id, ho_ten FROM nguoi_dung WHERE vai_tro = 'Admin' ORDER BY id ASC LIMIT 1");
$admin = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
if (!$admin) {
    fwrite(STDERR, "No Admin account found in nguoi_dung.\n");
    exit(2);
}

completeUserLoginSession((int)$admin['id'], 'Admin', (string)$admin['ho_ten']);

$sessionCookie = session_name() . '=' . session_id();
$userAgent = 'PerfBenchUA/1.0';
$baseUrl = rtrim((string)(getenv('PERF_BASE_URL') ?: 'http://localhost/Quanlydulich-main/project_quan_ly_tour_du_lich'), '/');
$targets = [
    'admin_dashboard' => $baseUrl . '/index.php?act=admin/dashboard',
    'dashboard_kpi_snapshot' => $baseUrl . '/index.php?act=admin/dashboardKpiSnapshot',
];

function request_once(string $url, string $cookie, string $userAgent, int $timeoutSeconds): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => $timeoutSeconds,
        CURLOPT_HTTPHEADER => [
            'Cookie: ' . $cookie,
            'User-Agent: ' . $userAgent,
            'Accept: text/html,application/json,*/*',
        ],
    ]);

    $start = microtime(true);
    $body = curl_exec($ch);
    $elapsedMs = (microtime(true) - $start) * 1000;

    if ($body === false) {
        $error = curl_error($ch);
        curl_close($ch);
        return [
            'ok' => false,
            'error' => $error,
            'elapsed_ms' => round($elapsedMs, 2),
        ];
    }

    $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $effectiveUrl = (string)curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);

    return [
        'ok' => true,
        'http_code' => $httpCode,
        'effective_url' => $effectiveUrl,
        'elapsed_ms' => round($elapsedMs, 2),
        'is_login_redirect' => stripos($effectiveUrl, 'act=auth/login') !== false,
    ];
}

function percentile(array $sorted, float $p): float {
    $count = count($sorted);
    if ($count === 0) {
        return 0.0;
    }
    $idx = (int)floor(($count - 1) * $p);
    return (float)$sorted[$idx];
}

$iterations = max(1, (int)(getenv('PERF_ITERATIONS') ?: 30));
$warmup = max(0, (int)(getenv('PERF_WARMUP') ?: 3));
$requestTimeoutSeconds = max(2, (int)(getenv('PERF_REQUEST_TIMEOUT') ?: 10));
$result = [
    'base_url' => $baseUrl,
    'iterations' => $iterations,
    'warmup' => $warmup,
    'session' => [
        'cookie_name' => session_name(),
        'user_id' => (int)$admin['id'],
        'user_name' => (string)$admin['ho_ten'],
    ],
    'request_timeout_seconds' => $requestTimeoutSeconds,
    'metrics' => [],
];

foreach ($targets as $name => $url) {
    for ($i = 0; $i < $warmup; $i++) {
        request_once($url, $sessionCookie, $userAgent, $requestTimeoutSeconds);
    }

    $samples = [];
    $probe = null;
    for ($i = 0; $i < $iterations; $i++) {
        $r = request_once($url, $sessionCookie, $userAgent, $requestTimeoutSeconds);
        if (!$r['ok']) {
            $result['metrics'][$name] = [
                'url' => $url,
                'error' => $r['error'],
            ];
            continue 2;
        }
        $samples[] = (float)$r['elapsed_ms'];
        $probe = $r;
    }

    sort($samples);
    $avg = array_sum($samples) / max(count($samples), 1);
    $result['metrics'][$name] = [
        'url' => $url,
        'http_code' => $probe['http_code'],
        'effective_url' => $probe['effective_url'],
        'is_login_redirect' => $probe['is_login_redirect'],
        'p50_ms' => round(percentile($samples, 0.50), 2),
        'p95_ms' => round(percentile($samples, 0.95), 2),
        'avg_ms' => round($avg, 2),
        'min_ms' => round((float)$samples[0], 2),
        'max_ms' => round((float)$samples[count($samples) - 1], 2),
    ];
}

echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), PHP_EOL;
