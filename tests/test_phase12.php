<?php

$basePath = dirname(__DIR__);
require_once $basePath . '/vendor/autoload.php';

\App\Core\Env::load($basePath . '/.env');
\App\Core\Config::load($basePath . '/config');
$db = \App\Core\Database::getInstance();

echo "===================================================================\n";
echo " HOSPITAL HMS - PHASE 12 HR, ROSTERS & PAYROLL UNIT TESTS\n";
echo "===================================================================\n\n";

$testsPassed = 0;
$testsTotal = 0;

function assertTest(string $description, bool $condition): void {
    global $testsPassed, $testsTotal;
    $testsTotal++;
    if ($condition) {
        $testsPassed++;
        echo "  [PASS] {$description}\n";
    } else {
        echo "  [FAIL] {$description}\n";
    }
}

// 1. Employee Roster Model Tests
$rosterModel = new \App\Models\EmployeeRoster();

// Check seeded rosters
$today = date('Y-m-d');
$todayRosters = $rosterModel->getByDate($today);
assertTest("Seeded rosters for today exist", count($todayRosters) > 0);

$onDutyCount = $rosterModel->countOnDutyToday();
assertTest("countOnDutyToday returns positive count", $onDutyCount >= 1);

// Test range retrieval
$rangeRosters = $rosterModel->getRosterRange(date('Y-m-d', strtotime('-3 days')), date('Y-m-d', strtotime('+5 days')));
assertTest("getRosterRange returns weekly schedule", count($rangeRosters) >= 7);

// Test assignShift logic
$testDate = '2026-12-01';
$assignId = $rosterModel->assignShift(1, 1, 2, $testDate, 'طبيب باطنية مناوب', 'اختبار الجدولة');
assertTest("assignShift successfully creates or updates roster", $assignId > 0);

$checkRoster = $rosterModel->find($assignId);
assertTest("Roster attributes match assigned values", 
    $checkRoster !== null && (int)$checkRoster['employee_id'] === 1 && $checkRoster['duty_date'] === $testDate);

// Test updating status
$rosterModel->update($assignId, ['status' => 'attended']);
$updatedRoster = $rosterModel->find($assignId);
assertTest("Roster status updated to attended", $updatedRoster['status'] === 'attended');

// 2. Employee Leave Model Tests
$leaveModel = new \App\Models\EmployeeLeave();
$allLeaves = $leaveModel->allDetailed();
assertTest("Seeded employee leaves exist", count($allLeaves) >= 2);

$pendingLeaves = $leaveModel->allDetailed('pending');
assertTest("Pending leaves filter works", count($pendingLeaves) >= 1);

// Test creating new leave request
$newLeaveId = $leaveModel->create([
    'employee_id' => 3, // Dr. Ahmed
    'leave_type'  => 'sick',
    'start_date'  => '2026-12-10',
    'end_date'    => '2026-12-12',
    'total_days'  => 3,
    'reason'      => 'تقرير طبي - راحة سريرية',
    'status'      => 'pending',
    'approved_by' => null,
]);
assertTest("New leave request created", $newLeaveId > 0);

$findLeave = $leaveModel->findDetailed((int)$newLeaveId);
assertTest("findDetailed returns employee and department info", 
    $findLeave !== null && !empty($findLeave['employee_name']) && !empty($findLeave['department_name_ar']));

// Test approving leave
$leaveModel->update((int)$newLeaveId, [
    'status'      => 'approved',
    'approved_by' => 1,
]);
$approvedLeave = $leaveModel->find((int)$newLeaveId);
assertTest("Leave request approved with approver user ID", 
    $approvedLeave['status'] === 'approved' && (int)$approvedLeave['approved_by'] === 1);

// 3. Payroll Calculation & Breakdown Tests
$calc = \App\Models\Payroll::calculateBreakdown(
    800000.00,
    [
        ['title' => 'بدل سكن', 'amount' => 120000.00],
        ['title' => 'حافز عيادة', 'amount' => 80000.00],
    ],
    [
        ['title' => 'تأمين اجتماعي', 'amount' => 64000.00],
        ['title' => 'ضريبة كسب عمل', 'amount' => 40000.00],
    ]
);
assertTest("calculateBreakdown: Basic 800,000 + Allowances 200,000 - Deductions 104,000 = Net 896,000 SDG",
    $calc['basic_salary'] === 800000.00 &&
    $calc['total_allowances'] === 200000.00 &&
    $calc['total_deductions'] === 104000.00 &&
    $calc['net_salary'] === 896000.00
);

// 4. Payroll Model & Generator Tests
$payrollModel = new \App\Models\Payroll();
$generatedNum = $payrollModel->generatePayrollNumber(2026, 12);
assertTest("generatePayrollNumber format PAY-YYYYMM-XXXXX", str_starts_with($generatedNum, 'PAY-202612-'));

// Test November 2026 seeded payroll
$novPayroll = $payrollModel->allDetailed(2026, 11);
assertTest("November 2026 payroll sheets exist for active employees", count($novPayroll) >= 7);

$firstSheet = $novPayroll[0];
$detailedPayroll = $payrollModel->findDetailed((int)$firstSheet['id']);
assertTest("findDetailed includes items line items", 
    $detailedPayroll !== null && isset($detailedPayroll['items']) && count($detailedPayroll['items']) >= 4);

// Test summary stats in SDG
$stats = $payrollModel->getSummaryStats(2026, 11);
assertTest("getSummaryStats returns positive totals in SDG", 
    (float)$stats['sum_basic'] > 0 && (float)$stats['sum_net'] > 0 && (int)$stats['total_sheets'] >= 7);

// Test markPaid functionality
$unpaidSheet = null;
foreach ($novPayroll as $np) {
    if (!$np['is_paid']) {
        $unpaidSheet = $np;
        break;
    }
}

// Ensure test idempotency across multiple runs
if (!$unpaidSheet && !empty($novPayroll)) {
    $db->query("UPDATE `payroll` SET `is_paid` = 0, `payment_date` = NULL, `transaction_reference` = NULL WHERE `id` = :id", ['id' => $novPayroll[0]['id']]);
    $novPayroll = $payrollModel->allDetailed(2026, 11);
    foreach ($novPayroll as $np) {
        if (!$np['is_paid']) {
            $unpaidSheet = $np;
            break;
        }
    }
}

assertTest("Unpaid payroll sheet exists for payment testing", $unpaidSheet !== null);

if ($unpaidSheet) {
    $paidSuccess = $payrollModel->markPaid((int)$unpaidSheet['id'], 'bankak', 'BNK-TEST-998811', 'تم صرف الراتب للاختبار');
    assertTest("markPaid successfully marks salary as paid via Bankak", $paidSuccess === true);

    $reloaded = $payrollModel->findDetailed((int)$unpaidSheet['id']);
    assertTest("Reloaded payroll sheet shows is_paid = 1 and reference BNK-TEST-998811", 
        (int)$reloaded['is_paid'] === 1 && $reloaded['transaction_reference'] === 'BNK-TEST-998811');
}

// Clean up test data
$db->execute("DELETE FROM employee_rosters WHERE duty_date = '2026-12-01'");
$db->execute("DELETE FROM employee_leaves WHERE reason LIKE '%تقرير طبي - راحة سريرية%'");

echo "\n===================================================================\n";
echo " TEST RESULTS: {$testsPassed} / {$testsTotal} PASSED\n";
echo "===================================================================\n";

exit($testsPassed === $testsTotal ? 0 : 1);
