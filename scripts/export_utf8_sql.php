<?php
$cmd = '"C:\wamp64\bin\mysql\mysql8.4.7\bin\mysqldump.exe" -u root --default-character-set=utf8mb4 --single-transaction --routines --triggers hospital_hms';
$output = shell_exec($cmd);
if (!empty($output)) {
    // Save as pure UTF-8 without BOM
    file_put_contents(__DIR__ . '/../database/hospital_hms_production.sql', $output);
    echo "SUCCESS: Exported " . strlen($output) . " bytes in pure UTF-8 format.\n";
} else {
    echo "FAILED to generate dump\n";
}
