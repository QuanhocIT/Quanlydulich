<?php
$base = __DIR__ . '/../';
$files = [
    'views/nha_cung_cap/chi_tiet_dich_vu.php',
    'views/nha_cung_cap/bao_gia.php',
    'views/nha_cung_cap/dich_vu.php',
    'views/nha_cung_cap/cong_no.php',
    'views/nha_cung_cap/dashboard.php',
    'views/nha_cung_cap/hop_dong.php',
    'views/hdv/nhat_ky.php',
    'views/hdv/checkin.php',
    'views/hdv/yeu_cau_dac_biet.php',
    'views/hdv/profile.php',
    'views/hdv/phan_hoi.php',
    'views/admin/chi_tiet_dich_vu.php',
];
$from = [
    "echo \$_SESSION['success'];",
    "echo \$_SESSION['error'];",
];
$to = [
    "echo htmlspecialchars((string)(\$_SESSION['success'] ?? ''), ENT_QUOTES, 'UTF-8');",
    "echo htmlspecialchars((string)(\$_SESSION['error'] ?? ''), ENT_QUOTES, 'UTF-8');",
];
foreach ($files as $f) {
    $path = realpath($base . $f);
    if (!$path) { echo "NOT FOUND: $f\n"; continue; }
    $c = file_get_contents($path);
    $new = str_replace($from, $to, $c, $count);
    file_put_contents($path, $new);
    echo "Fixed ($count replacements): " . basename($f) . "\n";
}
echo "Done.\n";
