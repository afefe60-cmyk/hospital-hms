<?php

$testFiles = glob(__DIR__ . '/test_*.php');
$phpExe = 'C:\\wamp64\\bin\\php\\php8.3.28\\php.exe';

$passedCount = 0;
$failedCount = 0;
$failedTests = [];

echo "===================================================================\n";
echo " RUNNING ALL HOSPITAL HMS TEST SUITES (REGRESSION)\n";
echo "===================================================================\n\n";

foreach ($testFiles as $file) {
    if (basename($file) === 'run_all_tests.php') {
        continue;
    }

    $cmd = "\"{$phpExe}\" \"{$file}\" 2>&1";
    exec($cmd, $output, $returnCode);

    if ($returnCode === 0) {
        $passedCount++;
        echo " [✓] " . basename($file) . " -> PASSED\n";
    } else {
        $failedCount++;
        $failedTests[] = basename($file);
        echo " [✗] " . basename($file) . " -> FAILED (Code: {$returnCode})\n";
        echo "     Output preview: " . implode("\n     ", array_slice($output, -5)) . "\n";
    }
    unset($output);
}

echo "\n===================================================================\n";
echo " SUMMARY: {$passedCount} Passed, {$failedCount} Failed (Total: " . ($passedCount + $failedCount) . ")\n";
echo "===================================================================\n";

if ($failedCount > 0) {
    echo "Failed Suites:\n - " . implode("\n - ", $failedTests) . "\n";
    exit(1);
} else {
    echo "ALL TEST SUITES PASSED CLEANLY!\n";
    exit(0);
}
