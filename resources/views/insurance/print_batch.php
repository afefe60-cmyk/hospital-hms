<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>كشف مطالبات تأمينية مجمعة - <?= htmlspecialchars($company['name_ar'] ?? 'كافة الشركات') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #fff;
            color: #212529;
            font-size: 11px;
        }
        .header-box {
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 10px;
            margin-bottom: 12px;
        }
        .table-custom th, .table-custom td {
            padding: 4px 6px;
            border: 1px solid #dee2e6;
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
        <i class="bi bi-printer"></i> طباعة الكشف
    </button>
    <button onclick="window.close()" class="btn btn-outline-secondary btn-sm px-3">إغلاق</button>
</div>

<!-- Header -->
<div class="header-box d-flex justify-content-between align-items-center">
    <div>
        <h4 class="fw-bold mb-1 text-primary">مستشفى الأمل التخصصي - الخرطوم</h4>
        <div class="text-muted small">إدارة الحسابات الطبية والمطالبات التأمينية (Insurance Claims Department)</div>
    </div>
    <div class="text-start">
        <div class="fw-bold fs-5 text-dark">كشف مطالبات تأمين صحي مجمع</div>
        <div class="text-muted small">الشركة: <strong><?= htmlspecialchars($company['name_ar'] ?? 'جميع الشركات المتعاقدة') ?></strong></div>
        <div class="small">تاريخ الإصدار: <?= date('Y-m-d') ?></div>
    </div>
</div>

<!-- Claims Table -->
<table class="w-100 table-custom text-center mb-3">
    <thead class="bg-light">
        <tr>
            <th>#</th>
            <th>رقم المطالبة</th>
            <th>شركة التأمين</th>
            <th>اسم المريض</th>
            <th>الرقم الطبي</th>
            <th>رقم الوثيقة</th>
            <th>تاريخ الرفع</th>
            <th class="text-end">مبلغ المطالبة (SDG)</th>
            <th class="text-end">المعتمد (SDG)</th>
            <th>الحالة</th>
        </tr>
    </thead>
    <tbody>
        <?php
            $totalClaimed = 0;
            $totalApproved = 0;
            $i = 1;
        ?>
        <?php if (empty($claims)): ?>
            <tr>
                <td colspan="10" class="py-3 text-muted">لا توجد مطالبات في هذا الكشف.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($claims as $c): ?>
                <?php
                    $totalClaimed += (float)$c['claim_amount'];
                    $totalApproved += (float)($c['approved_amount'] ?? 0);
                    $statusLabel = match($c['status']) {
                        'approved' => 'معتمدة',
                        'settled' => 'مسددة',
                        'partially_approved' => 'اعتماد جزئي',
                        'under_review' => 'قيد التدقيق',
                        'rejected' => 'مرفوضة',
                        default => 'مرفوعة'
                    };
                ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td class="font-monospace fw-bold text-primary"><?= htmlspecialchars($c['claim_number']) ?></td>
                    <td><?= htmlspecialchars($c['company_name']) ?></td>
                    <td class="text-start"><?= htmlspecialchars($c['patient_name']) ?></td>
                    <td class="font-monospace"><?= htmlspecialchars($c['mrn']) ?></td>
                    <td class="font-monospace"><?= htmlspecialchars($c['policy_number']) ?></td>
                    <td><?= date('Y-m-d', strtotime($c['submission_date'])) ?></td>
                    <td class="text-end font-monospace fw-bold"><?= number_format($c['claim_amount'], 2) ?></td>
                    <td class="text-end font-monospace"><?= $c['approved_amount'] ? number_format($c['approved_amount'], 2) : '-' ?></td>
                    <td><?= $statusLabel ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
    <tfoot class="bg-light fw-bold font-monospace">
        <tr>
            <th colspan="7" class="text-end">الإجمالي الكلي:</th>
            <td class="text-end text-primary"><?= number_format($totalClaimed, 2) ?> SDG</td>
            <td class="text-end text-success"><?= number_format($totalApproved, 2) ?> SDG</td>
            <td></td>
        </tr>
    </tfoot>
</table>

<!-- Signatures -->
<div class="d-flex justify-content-between mt-5 pt-3">
    <div class="text-center" style="width: 200px; border-top: 1px solid #666;">
        <div class="small fw-bold">مسؤول المطالبات التأمينية</div>
    </div>
    <div class="text-center" style="width: 200px; border-top: 1px solid #666;">
        <div class="small fw-bold">المدير المالي للمستشفى</div>
    </div>
    <div class="text-center" style="width: 200px; border-top: 1px solid #666;">
        <div class="small fw-bold">استلام وتدقيق شركة التأمين</div>
    </div>
</div>

</body>
</html>
