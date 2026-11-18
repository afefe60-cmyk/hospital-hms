<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فاتورة علاجية - <?= htmlspecialchars($invoice['invoice_number']) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #fff;
            color: #212529;
            font-size: 13px;
        }
        .header-box {
            border-bottom: 2px solid #198754;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }
        .table-custom th, .table-custom td {
            padding: 6px 10px;
            border: 1px solid #dee2e6;
        }
        .stamp-box {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }
        .sig-block {
            text-align: center;
            width: 200px;
            border-top: 1px solid #666;
            padding-top: 5px;
        }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body class="p-4">

<div class="no-print mb-3 text-end">
    <button onclick="window.print()" class="btn btn-success btn-sm px-4">
        <i class="bi bi-printer"></i> طباعة الفاتورة
    </button>
    <button onclick="window.close()" class="btn btn-outline-secondary btn-sm px-3">إغلاق</button>
</div>

<!-- Header -->
<div class="header-box d-flex justify-content-between align-items-center">
    <div>
        <h4 class="fw-bold mb-1 text-success">مستشفى الأمل التخصصي - الخرطوم</h4>
        <div class="text-muted small">قسم الحسابات والفوترة الطبية (Patient Financial Services)</div>
    </div>
    <div class="text-start">
        <div class="fw-bold fs-5 text-dark">فاتورة علاجية رسمية</div>
        <div class="font-monospace text-muted small">رقم الفاتورة: <?= htmlspecialchars($invoice['invoice_number']) ?></div>
        <div class="small">التاريخ: <?= date('Y-m-d H:i', strtotime($invoice['created_at'])) ?></div>
    </div>
</div>

<!-- Patient Info -->
<table class="w-100 table-custom mb-3">
    <tr>
        <th width="15%" class="bg-light">اسم المريض:</th>
        <td width="35%"><strong><?= htmlspecialchars($invoice['patient_name']) ?></strong></td>
        <th width="15%" class="bg-light">الرقم الطبي (MRN):</th>
        <td width="35%"><span class="font-monospace"><?= htmlspecialchars($invoice['mrn']) ?></span></td>
    </tr>
    <tr>
        <th class="bg-light">العمر والنوع:</th>
        <td><?= $invoice['patient_age'] ?> سنة / <?= $invoice['patient_gender'] === 'male' ? 'ذكر' : 'أنثى' ?></td>
        <th class="bg-light">الهاتف:</th>
        <td><?= htmlspecialchars($invoice['phone_primary']) ?></td>
    </tr>
    <?php if (!empty($invoice['notes'])): ?>
    <tr>
        <th class="bg-light">البيان / ملاحظات:</th>
        <td colspan="3"><?= htmlspecialchars($invoice['notes']) ?></td>
    </tr>
    <?php endif; ?>
</table>

<!-- Line Items -->
<h6 class="fw-bold text-success mb-2">تفاصيل الخدمات الطبية</h6>
<table class="w-100 table-custom mb-3">
    <thead class="bg-light">
        <tr>
            <th width="5%">#</th>
            <th width="50%">الخدمة / الإجراء الطبي</th>
            <th width="10%" class="text-center">الكمية</th>
            <th width="15%" class="text-end">السعر (SDG)</th>
            <th width="20%" class="text-end">الإجمالي (SDG)</th>
        </tr>
    </thead>
    <tbody>
        <?php $idx = 1; foreach ($items as $it): ?>
            <tr>
                <td><?= $idx++ ?></td>
                <td><?= htmlspecialchars($it['description']) ?></td>
                <td class="text-center"><?= (float)$it['quantity'] ?></td>
                <td class="text-end font-monospace"><?= number_format($it['unit_price'], 2) ?></td>
                <td class="text-end font-monospace"><?= number_format($it['total_amount'], 2) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <th colspan="4" class="text-end bg-light">إجمالي الخدمات قبل الخصم:</th>
            <td class="text-end font-monospace fw-bold"><?= number_format($invoice['total_gross'], 2) ?> SDG</td>
        </tr>
        <?php if ($invoice['total_discount'] > 0): ?>
        <tr>
            <th colspan="4" class="text-end bg-light text-danger">الخصم المعتمد:</th>
            <td class="text-end font-monospace text-danger">- <?= number_format($invoice['total_discount'], 2) ?> SDG</td>
        </tr>
        <?php endif; ?>
        <tr class="table-light">
            <th colspan="4" class="text-end fs-6 fw-bold">الصافي المطلوب من المريض (Net Due):</th>
            <td class="text-end font-monospace fs-6 fw-bold text-success"><?= number_format($invoice['total_patient_due'], 2) ?> SDG</td>
        </tr>
        <tr>
            <th colspan="4" class="text-end bg-light">المدفوع نقداً / بنكك:</th>
            <td class="text-end font-monospace fw-bold text-primary"><?= number_format($invoice['paid_amount'], 2) ?> SDG</td>
        </tr>
        <tr>
            <th colspan="4" class="text-end bg-light">المتبقي المطلوب:</th>
            <td class="text-end font-monospace fw-bold text-danger"><?= number_format(max(0.00, $invoice['total_patient_due'] - $invoice['paid_amount']), 2) ?> SDG</td>
        </tr>
    </tfoot>
</table>

<!-- Signatures -->
<div class="stamp-box">
    <div class="sig-block">
        <div class="small fw-bold">توقيع المستلم / المريض</div>
    </div>
    <div class="sig-block">
        <div class="small fw-bold">المحاسب / الصراف</div>
        <div class="text-muted small"><?= htmlspecialchars($invoice['creator_name'] ?? 'الصراف') ?></div>
    </div>
    <div class="sig-block">
        <div class="small fw-bold">ختم الإدارة المالية للمستشفى</div>
    </div>
</div>

</body>
</html>
