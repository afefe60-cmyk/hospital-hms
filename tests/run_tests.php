<?php

/**
 * HOSPITAL HMS - Test Suite for PHASE 1
 */

$basePath = dirname(__DIR__);
require_once $basePath . '/vendor/autoload.php';

\App\Core\Env::load($basePath . '/.env');
\App\Core\Config::load($basePath . '/config');

echo "==========================================\n";
echo " HOSPITAL HMS - PHASE 1 VERIFICATION TESTS\n";
echo "==========================================\n\n";

$passed = 0;
$failed = 0;

function assertTest(string $name, bool $condition, string $detail = '') {
    global $passed, $failed;
    if ($condition) {
        echo " [PASS] {$name}\n";
        $passed++;
    } else {
        echo " [FAIL] {$name} - {$detail}\n";
        $failed++;
    }
}

// 1. Config Test
assertTest("Config loader", config('app.timezone') === 'Africa/Khartoum', "Expected Africa/Khartoum");
assertTest("Currency is SDG", config('app.currency') === 'SDG', "Expected SDG");

// 2. Database Test
try {
    $db = \App\Core\Database::getInstance();
    $result = $db->fetchColumn("SELECT 1 + 1");
    assertTest("Database Connection & PDO Query", (int)$result === 2, "Expected 2");
} catch (\Throwable $e) {
    assertTest("Database Connection", false, $e->getMessage());
}

// 3. Router Test
$router = new \App\Core\Router();
$router->get('/test/{id}', function (\App\Core\Request $req, \App\Core\Response $res, $id) {
    return $res->json(['received_id' => $id]);
});

$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/test/123';
$request = new \App\Core\Request();
$response = new \App\Core\Response();

$result = $router->dispatch($request, $response);
assertTest("Router Parameter Extraction", $request->param('id') === '123', "Expected '123'");

// 4. Session & CSRF Test
\App\Core\Session::start();
$token = \App\Core\Session::token();
assertTest("CSRF Token Generation", !empty($token) && strlen($token) === 64, "Token length not 64");
assertTest("CSRF Token Validation", \App\Core\Session::validateToken($token), "Token validation failed");
assertTest("CSRF Token Rejection on Mismatch", !\App\Core\Session::validateToken('invalid_token'), "Invalid token accepted");

// 5. Localization & Formatting Test
$arPrice = format_currency(25000);
assertTest("Sudanese Currency Formatting", str_contains($arPrice, '25,000.00'), "Currency string mismatch: {$arPrice}");

echo "\n------------------------------------------\n";
echo "Tests Completed: {$passed} Passed, {$failed} Failed\n";
echo "==========================================\n";

exit($failed > 0 ? 1 : 0);
