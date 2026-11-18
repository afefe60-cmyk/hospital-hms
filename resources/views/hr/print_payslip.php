<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>كشف مفردات مرتب - <?= htmlspecialchars($payroll['payroll_number']) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #fff;
            color: #212529;
            font-size: 13px;
        }
        .header-box {
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .table-custom th, .table-custom td {
            padding: 7px 12px;
            border: 1px solid #dee2e6;
        }
        .signature-box {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }
        .sig-block {
            text-align: center;
            width: 220px;
            border-top: 1px solid #444;
            padding-top: 6px;
        }
        .payslip-summary {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 15px;
        }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body class="p-4">

<div class="no-print mb-3 text-end">
    <button onclick="window.print()" class="btn btn-primary btn-sm px-4">
        طباعة كشف المفردات (A4)
    </button>
    <button onclick="window.close()" class="btn btn-outline-secondary btn-sm px-3">إغلاق</button>
</div>

<!-- Header -->
<div class="header-box d-flex justify-content-between align-items-center">
    <div>
        <h4 class="fw-bold mb-1 text-primary">مستشفى الأمل التخصصي - الخرطوم</h4>
        <div class="text-muted small">إدارة الموارد البشرية والشؤون المالية (HR & Payroll Department)</div>
        <div class="text-muted small">الخرطوم، السودان &bull; هاتف: 249183000000+</div>
    </div>
    <div class="text-start">
        <div class="fw-bold fs-5 text-dark">كشف مفردات راتب شهري (Payslip)</div>
        <div class="font-monospace text-muted small">رقم الكشف: <?= htmlspecialchars($payroll['payroll_number']) ?></div>
        <div class="small">عن شهر: <strong><?= $payroll['salary_month'] ?> / <?= $payroll['salary_year'] ?></strong></div>
        <div class="small">تاريخ التحرير: <?= date('Y-m-d') ?></div>
    </div>
</div>

<!-- Employee Metadata Table -->
<table class="table table-custom mb-4">
    <tbody>
        <tr>
            <th class="bg-light" style="width: 15%;">اسم الموظف:</th>
            <td style="width: 35%;" class="fw-bold"><?= htmlspecialchars($payroll['employee_name']) ?></td>
            <th class="bg-light" style="width: 15%;">الرقم الوظيفي:</th>
            <td style="width: 35%;" class="font-monospace"><?= htmlspecialchars($payroll['employee_code']) ?></td>
        </tr>
        <tr>
            <th class="bg-light">القسم / الإدارة:</th>
            <td><?= htmlspecialchars($payroll['department_name_ar']) ?></td>
            <th class="bg-light">المسمى الوظيفي:</th>
            <td><?= htmlspecialchars($payroll['job_title']) ?></td>
        </tr>
        <tr>
            <th class="bg-light">الرقم الوطني:</th>
            <td class="font-monospace"><?= htmlspecialchars($payroll['national_id']) ?></td>
            <th class="bg-light">تاريخ التعيين:</th>
            <td><?= $payroll['hire_date'] ?></td>
        </tr>
    </tbody>
</table>

<!-- Breakdown Tables -->
<div class="row g-3 mb-4">
    <div class="col-6">
        <h6 class="fw-bold text-success border-bottom pb-2">أولاً: الاستحقاقات والبدلات (Allowances)</h6>
        <table class="table table-custom">
            <thead class="table-light">
                <tr>
                    <th>البند</th>
                    <th class="text-end">المبلغ (SDG)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>الراتب الأساسي التعاقدي (Basic Salary)</td>
                    <td class="text-end fw-bold"><?= number_format((float)$payroll['basic_salary'], 2) ?></td>
                </tr>
                <?php foreach ($payroll['items'] as $item): ?>
                    <?php if ($item['type'] === 'allowance'): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['title']) ?></td>
                            <td class="text-end text-success">+<?= number_format((float)$item['amount'], 2) ?></td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
                <tr class="table-light fw-bold">
                    <td>إجمالي الاستحقاقات الشاملة:</td>
                    <td class="text-end text-success"><?= number_format((float)$payroll['basic_salary'] + (float)$payroll['allowances'], 2) ?> SDG</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="col-6">
        <h6 class="fw-bold text-danger border-bottom pb-2">ثانياً: الاستقطاعات والخصومات (Deductions)</h6>
        <table class="table table-custom">
            <thead class="table-light">
                <tr>
                    <th>البند</th>
                    <th class="text-end">المبلغ (SDG)</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $dedCount = 0;
                foreach ($payroll['items'] as $item): 
                    if ($item['type'] === 'deduction'):
                        $dedCount++;
                ?>
                    <tr>
                        <td><?= htmlspecialchars($item['title']) ?></td>
                        <td class="text-end text-danger">-<?= number_format((float)$item['amount'], 2) ?></td>
                    </tr>
                <?php 
                    endif;
                endforeach; 
                if ($dedCount === 0):
                ?>
                    <tr><td colspan="2" class="text-center text-muted">لا توجد خصومات</td></tr>
                <?php endif; ?>
                <tr class="table-light fw-bold">
                    <td>إجمالي الاستقطاعات والضرائب:</td>
                    <td class="text-end text-danger">-<?= number_format((float)$payroll['deductions'], 2) ?> SDG</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Net Salary Summary Box -->
<div class="payslip-summary mb-4">
    <div class="row align-items-center">
        <div class="col-8">
            <span class="text-muted small d-block">صافي الراتب المستحق للصرف بالجنيه السوداني (Net Pay in SDG):</span>
            <h3 class="fw-bold text-success mb-0"><?= number_format((float)$payroll['net_salary'], 2) ?> جنيه سوداني</h3>
        </div>
        <div class="col-4 text-start">
            <div class="small">
                <strong>حالة الصرف:</strong> 
                <?= $payroll['is_paid'] ? '<span class="text-success fw-bold">تم الصرف</span>' : '<span class="text-warning fw-bold">قيد الانتظار</span>' ?>
            </div>
            <?php if ($payroll['is_paid']): ?>
                <div class="small text-muted">طريقة التحويل: <?= $payroll['payment_method'] === 'bankak' ? 'تطبيق بنكك' : 'نقدي' ?></div>
                <div class="small font-monospace text-muted">المرجع: <?= htmlspecialchars($payroll['transaction_reference'] ?? '-') ?></div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Signatures -->
<div class="signature-box">
    <div class="sig-block">
        <div>إعداد أخصائي الموارد البشرية</div>
        <small class="text-muted d-block mt-4">التوقيع / الختم</small>
    </div>
    <div class="sig-block">
        <div>المراجعة والتدقيق المالي</div>
        <small class="text-muted d-block mt-4">التوقيع / الختم</small>
    </div>
    <div class="sig-block">
        <div>توقيع واستلام الموظف</div>
        <small class="text-muted d-block mt-4">التوقيع / التاريخ</small>
    </div>
</div>

</body>
</html>
