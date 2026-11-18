<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير إغلاق وتسليم وردية - <?= htmlspecialchars($session['session_number']) ?></title>
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
            padding-bottom: 10px;
            margin-bottom: 12px;
        }
        .table-custom th, .table-custom td {
            padding: 5px 8px;
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
        <i class="bi bi-printer"></i> طباعة تقرير الوردية
    </button>
    <button onclick="window.close()" class="btn btn-outline-secondary btn-sm px-3">إغلاق</button>
</div>

<!-- Header -->
<div class="header-box d-flex justify-content-between align-items-center">
    <div>
        <h4 class="fw-bold mb-1 text-primary">مستشفى الأمل التخصصي - الخرطوم</h4>
        <div class="text-muted small">قسم الخزينة والمتحصلات المالية (Cashier & Treasury)</div>
    </div>
    <div class="text-start">
        <div class="fw-bold fs-5 text-dark">محضر إغلاق ومطابقة وردية صندوق</div>
        <div class="font-monospace text-muted small">رقم الوردية: <?= htmlspecialchars($session['session_number']) ?></div>
        <div class="small">التاريخ: <?= date('Y-m-d H:i') ?></div>
    </div>
</div>

<!-- Shift Info -->
<table class="w-100 table-custom mb-3">
    <tr>
        <th width="15%" class="bg-light">اسم الصندوق:</th>
        <td width="35%"><strong><?= htmlspecialchars($session['cashbox_name']) ?></strong></td>
        <th width="15%" class="bg-light">الصراف المسؤول:</th>
        <td width="35%"><strong><?= htmlspecialchars($session['cashier_name']) ?></strong></td>
    </tr>
    <tr>
        <th class="bg-light">توقيت الفتح:</th>
        <td><?= date('Y-m-d h:i A', strtotime($session['opened_at'])) ?></td>
        <th class="bg-light">توقيت الإغلاق:</th>
        <td><?= $session['closed_at'] ? date('Y-m-d h:i A', strtotime($session['closed_at'])) : 'ما زالت مفتوحة' ?></td>
    </tr>
</table>

<!-- Cash & Methods Summary -->
<h6 class="fw-bold text-primary mb-2">ملخص النقدية والمتحصلات بالجنيه السوداني (SDG)</h6>
<table class="w-100 table-custom mb-3 text-center">
    <thead class="bg-light">
        <tr>
            <th>عهدة البداية</th>
            <th>متحصلات نقداً (كاش)</th>
            <th>متحصلات بنكك (Bankak)</th>
            <th>متحصلات بطاقات (POS)</th>
            <th>إجمالي المقبوضات</th>
        </tr>
    </thead>
    <tbody>
        <tr class="fw-bold font-monospace fs-6">
            <td><?= number_format($session['opening_balance'], 2) ?></td>
            <td class="text-success"><?= number_format($breakdown['cash'], 2) ?></td>
            <td class="text-info"><?= number_format($breakdown['bankak'], 2) ?></td>
            <td><?= number_format($breakdown['card'], 2) ?></td>
            <td class="text-primary"><?= number_format($breakdown['total'], 2) ?></td>
        </tr>
    </tbody>
</table>

<!-- Reconciliation Box -->
<div class="p-3 border rounded mb-3 bg-light">
    <div class="row text-center">
        <div class="col-4">
            <span class="text-muted d-block small">المفترض نقداً بالدرج (عهدة + كاش)</span>
            <strong class="font-monospace fs-5"><?= number_format($session['expected_closing'] ?? ($session['opening_balance'] + $breakdown['cash']), 2) ?> SDG</strong>
        </div>
        <div class="col-4">
            <span class="text-muted d-block small">النقدية الفعلية بعد الجرد</span>
            <strong class="font-monospace fs-5 text-primary"><?= number_format($session['actual_closing'] ?? 0, 2) ?> SDG</strong>
        </div>
        <div class="col-4">
            <span class="text-muted d-block small">الفارق (عجز / زيادة)</span>
            <?php $d = (float)($session['difference'] ?? 0); ?>
            <strong class="font-monospace fs-5 <?= $d < 0 ? 'text-danger' : ($d > 0 ? 'text-success' : 'text-muted') ?>">
                <?= ($d > 0 ? '+' : '') . number_format($d, 2) ?> SDG
            </strong>
        </div>
    </div>
</div>

<!-- Signatures -->
<div class="d-flex justify-content-between mt-5 pt-3">
    <div class="text-center" style="width: 200px; border-top: 1px solid #666;">
        <div class="small fw-bold">الصراف المسلم للوردية</div>
        <div class="text-muted small"><?= htmlspecialchars($session['cashier_name']) ?></div>
    </div>
    <div class="text-center" style="width: 200px; border-top: 1px solid #666;">
        <div class="small fw-bold">مشرف الخزينة / المستلم</div>
    </div>
    <div class="text-center" style="width: 200px; border-top: 1px solid #666;">
        <div class="small fw-bold">اعتماد الإدارة المالية</div>
    </div>
</div>

</body>
</html>
