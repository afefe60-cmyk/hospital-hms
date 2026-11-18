<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مسير الرواتب المجمع لشهر <?= $selectedMonth ?>/<?= $selectedYear ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #fff;
            color: #212529;
            font-size: 12px;
        }
        .header-box {
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .table-custom th, .table-custom td {
            padding: 6px 8px;
            border: 1px solid #dee2e6;
            vertical-align: middle;
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
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
            @page {
                size: A4 landscape;
                margin: 1cm;
            }
        }
    </style>
</head>
<body class="p-4">

<div class="no-print mb-3 text-end">
    <button onclick="window.print()" class="btn btn-primary btn-sm px-4">
        طباعة المسير المجمع (A4 Landscape)
    </button>
    <button onclick="window.close()" class="btn btn-outline-secondary btn-sm px-3">إغلاق</button>
</div>

<!-- Header -->
<div class="header-box d-flex justify-content-between align-items-center">
    <div>
        <h4 class="fw-bold mb-1 text-primary">مستشفى الأمل التخصصي - الخرطوم</h4>
        <div class="text-muted small">الإدارة العامة &bull; الإدارة المالية والموارد البشرية</div>
        <div class="text-muted small">كشف مسير رواتب الكادر الطبي والإداري المجمع (Monthly Master Payroll)</div>
    </div>
    <div class="text-start">
        <div class="fw-bold fs-5 text-dark">مسير مرتبات شهر: <?= $selectedMonth ?> / <?= $selectedYear ?></div>
        <div class="small text-muted">القسم / الإدارة: <strong><?= htmlspecialchars($deptName) ?></strong></div>
        <div class="small text-muted">تاريخ الطباعة: <?= date('Y-m-d H:i') ?></div>
    </div>
</div>

<!-- Master Table -->
<table class="table table-custom table-striped mb-4">
    <thead class="table-light text-center">
        <tr>
            <th style="width: 4%;">#</th>
            <th style="width: 12%;">رقم الكشف</th>
            <th style="width: 18%;">اسم الموظف</th>
            <th style="width: 12%;">القسم والوظيفة</th>
            <th style="width: 11%;">الأساسي (SDG)</th>
            <th style="width: 10%;">البدلات (SDG)</th>
            <th style="width: 10%;">الاستقطاعات (SDG)</th>
            <th style="width: 12%;">صافي الراتب (SDG)</th>
            <th style="width: 11%;">حالة الصرف والتحويل</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $i = 1;
        $sumBasic = 0;
        $sumAllow = 0;
        $sumDeduct = 0;
        $sumNet = 0;
        foreach ($payrollList as $row): 
            $sumBasic += (float)$row['basic_salary'];
            $sumAllow += (float)$row['allowances'];
            $sumDeduct += (float)$row['deductions'];
            $sumNet += (float)$row['net_salary'];
        ?>
            <tr>
                <td class="text-center"><?= $i++ ?></td>
                <td class="font-monospace text-center"><?= htmlspecialchars($row['payroll_number']) ?></td>
                <td>
                    <div class="fw-bold"><?= htmlspecialchars($row['employee_name']) ?></div>
                    <small class="text-muted"><?= htmlspecialchars($row['employee_code']) ?></small>
                </td>
                <td>
                    <div><?= htmlspecialchars($row['job_title']) ?></div>
                    <small class="text-muted"><?= htmlspecialchars($row['department_name_ar']) ?></small>
                </td>
                <td class="text-end"><?= number_format((float)$row['basic_salary'], 2) ?></td>
                <td class="text-end text-success">+<?= number_format((float)$row['allowances'], 2) ?></td>
                <td class="text-end text-danger">-<?= number_format((float)$row['deductions'], 2) ?></td>
                <td class="text-end fw-bold fs-6 text-primary"><?= number_format((float)$row['net_salary'], 2) ?></td>
                <td class="text-center">
                    <?php if ($row['is_paid']): ?>
                        <span class="badge bg-success">مسدد (<?= $row['payment_method'] === 'bankak' ? 'بنكك' : 'نقدي' ?>)</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">بانتظار الصرف</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot class="table-light fw-bold">
        <tr>
            <td colspan="4" class="text-center">الإجمالي العام لمسير الرواتب (SDG):</td>
            <td class="text-end"><?= number_format($sumBasic, 2) ?></td>
            <td class="text-end text-success">+<?= number_format($sumAllow, 2) ?></td>
            <td class="text-end text-danger">-<?= number_format($sumDeduct, 2) ?></td>
            <td class="text-end text-primary fs-6"><?= number_format($sumNet, 2) ?> SDG</td>
            <td class="text-center"><?= count($payrollList) ?> موظف</td>
        </tr>
    </tfoot>
</table>

<!-- Signatures -->
<div class="signature-box">
    <div class="sig-block">
        <div>إعداد ومراجعة الموارد البشرية</div>
        <small class="text-muted d-block mt-4">التوقيع / التاريخ</small>
    </div>
    <div class="sig-block">
        <div>المدير المالي والتدقيق الداخلي</div>
        <small class="text-muted d-block mt-4">التوقيع / التاريخ</small>
    </div>
    <div class="sig-block">
        <div>اعتماد المدير العام للمستشفى</div>
        <small class="text-muted d-block mt-4">التوقيع / الختم الرسمي</small>
    </div>
</div>

</body>
</html>
