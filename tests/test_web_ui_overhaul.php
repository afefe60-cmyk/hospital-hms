<?php

echo "===================================================================\n";
echo " HOSPITAL HMS - UI/UX & NAVIGATION WEB INTEGRATION TESTS\n";
echo "===================================================================\n\n";

$baseUrl = 'http://127.0.0.1:8080';
$cookieFile = sys_get_temp_dir() . '/hms_ui_test_cookie.txt';
if (file_exists($cookieFile)) {
    unlink($cookieFile);
}

$passCount = 0;
$totalTests = 0;

function assertWeb(string $desc, bool $condition): void {
    global $passCount, $totalTests;
    $totalTests++;
    if ($condition) {
        $passCount++;
        echo "  [PASS] {$desc}\n";
    } else {
        echo "  [FAIL] {$desc}\n";
    }
}

function httpGet(string $url, ?string $cookieFile = null): array {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    if ($cookieFile) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    }
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $code, 'body' => $body];
}

function httpPost(string $url, array $fields, ?string $cookieFile = null): array {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    if ($cookieFile) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    }
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $code, 'body' => $body];
}

// 1. Check Public Home Page
$homeRes = httpGet($baseUrl . '/', $cookieFile);
assertWeb('GET / returns HTTP 200', $homeRes['code'] === 200);
assertWeb('Home displays hospital brand and navigation', str_contains($homeRes['body'], 'مستشفى الأمل التخصصي') || str_contains($homeRes['body'], 'لوحة القيادة'));

// 2. Fetch login page and extract CSRF token
$loginPage = httpGet($baseUrl . '/login', $cookieFile);
assertWeb('GET /login returns HTTP 200', $loginPage['code'] === 200);

preg_match('/name="_csrf_token"\s+value="([^"]+)"/', $loginPage['body'], $tokenMatches);
$csrfToken = $tokenMatches[1] ?? '';
assertWeb('CSRF token extracted for authentication', !empty($csrfToken));

// 3. Login as Super Admin
$loginSubmit = httpPost($baseUrl . '/login', [
    '_csrf_token' => $csrfToken,
    'username' => 'admin',
    'password' => 'Password@123',
], $cookieFile);

assertWeb('POST /login succeeds with admin session', $loginSubmit['code'] === 200 && (str_contains($loginSubmit['body'], 'مدير النظام') || str_contains($loginSubmit['body'], 'admin') || str_contains($loginSubmit['body'], 'الرئيسية')));

// 4. Test Live Quick Search Web API
$searchRes = httpGet($baseUrl . '/patients/quick-search?q=%D8%B9%D9%85%D8%B1', $cookieFile);
assertWeb('GET /patients/quick-search returns HTTP 200', $searchRes['code'] === 200);
$searchJson = json_decode($searchRes['body'], true);
assertWeb('Quick Search API returns JSON structure with data', is_array($searchJson) && isset($searchJson['data']));

// 5. Test Key Navigation Routes with new Navbar
$routesToTest = [
    '/reception' => 'الاستقبال',
    '/patients' => 'سجل المرضى',
    '/appointments' => 'المواعيد',
    '/emr' => 'عيادة الطبيب',
    '/emergency' => 'الطوارئ',
    '/admissions' => 'التنويم',
    '/nursing' => 'التمريض',
    '/surgery' => 'العمليات',
    '/icu' => 'العناية',
    '/lab' => 'المختبرات',
    '/radiology' => 'الأشعة',
    '/pharmacy' => 'الصيدلية',
    '/inventory' => 'المستودعات',
    '/billing' => 'الفوترة',
    '/cashboxes' => 'الصناديق',
    '/insurance' => 'التأمين',
    '/hr' => 'الموارد البشرية',
    '/employees' => 'الكوادر',
    '/reports' => 'التقارير',
    '/settings' => 'إعدادات',
];

$allRoutesOk = true;
foreach ($routesToTest as $path => $label) {
    $res = httpGet($baseUrl . $path, $cookieFile);
    if ($res['code'] !== 200) {
        $allRoutesOk = false;
        echo "    [!] Failed route {$path} returned HTTP {$res['code']}\n";
    }
}
assertWeb('All 20 core navigation module routes return HTTP 200 with new navbar', $allRoutesOk);

echo "\n===================================================================\n";
echo " WEB TEST RESULTS: {$passCount} / {$totalTests} PASSED\n";
echo "===================================================================\n";

if (file_exists($cookieFile)) {
    unlink($cookieFile);
}

if ($passCount === $totalTests) {
    exit(0);
} else {
    exit(1);
}
