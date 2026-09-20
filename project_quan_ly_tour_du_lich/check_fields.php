<?php
require_once __DIR__ . '/commons/env.php';
require_once __DIR__ . '/commons/function.php';
require_once __DIR__ . '/models/LichKhoiHanh.php';

$m = new LichKhoiHanh();
$list = $m->getAllFiltered([]);
if (!empty($list)) {
    print_r(array_keys($list[0]));
    echo "\nSample record:\n";
    print_r($list[0]);
}
