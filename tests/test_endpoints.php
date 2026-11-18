<?php

$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/health';
$_SERVER['HTTP_ACCEPT'] = 'application/json';

ob_start();
require __DIR__ . '/../public/index.php';
$output = ob_get_clean();

echo "HEALTH_ENDPOINT_OUTPUT:\n" . $output . "\n";

$data = json_decode($output, true);
if (isset($data['status']) && $data['status'] === 'healthy' && $data['database'] === true) {
    echo "HEALTH_CHECK_VERIFIED_SUCCESSFULLY\n";
} else {
    echo "HEALTH_CHECK_FAILED\n";
    exit(1);
}
