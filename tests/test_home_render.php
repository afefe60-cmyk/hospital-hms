<?php

$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['HTTP_ACCEPT'] = 'text/html';

ob_start();
require __DIR__ . '/../public/index.php';
$output = ob_get_clean();

$hasHms = str_contains($output, 'HOSPITAL HMS');
$hasBootstrap = str_contains($output, 'bootstrap.rtl.min.css');
$hasMeta = str_contains($output, 'csrf-token');

if ($hasHms && $hasBootstrap && $hasMeta) {
    echo "HOME_PAGE_RENDER_VERIFIED_SUCCESSFULLY (Size: " . strlen($output) . " bytes)\n";
    exit(0);
} else {
    echo "HOME_PAGE_RENDER_FAILED\n";
    exit(1);
}
