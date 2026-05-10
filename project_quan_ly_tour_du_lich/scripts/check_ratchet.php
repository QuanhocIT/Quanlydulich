<?php
require __DIR__ . '/../vendor/autoload.php';
echo class_exists('Ratchet\Server\IoServer') ? "Ratchet OK\n" : "Ratchet MISSING\n";
