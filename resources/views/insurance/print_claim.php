<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نموذج مطالبة تأمين صحي - <?= htmlspecialchars($claim['claim_number']) ?></title>
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
            margin-bottom: 15px;
        }
        .table-custom th, .table-custom td {
            padding: 6px 10px;
            border: 1px solid #dee2e6;
        }
        .signature-box {
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
    <button onclick="window.print()" class="btn btn-primary btn-sm px-4">
        <i class="bi bi-printer"></i> طباعة نموذج المطالبة
    </button>
    <button onclick="window.close()" class="btn btn-outline-secondary btn-sm px-3">إغلاق</button>
</div>

<!-- Header -->
<div class="header-box d-flex justify-content-between align-items-center">
    <div>
        <h4 class="fw-bold mb-1 text-primary">مستشفى الأمل التخصصي - الخرطوم</h4>
        <div class="text-muted small">إدارة التأمين الطبي والمطالبات (Medical Insurance Department)</div>
    </div>
    <div class="text-start">
        <div class="fw-bold fs-5 text-dark">استمارة مطالبة تأمين صحي</div>
        <div class="font-monospace text-muted small">رقم المطالبة: <?= htmlspecialchars($claim['claim_number']) ?></div>
        <div class="small">تاريخ التقديم: <?= date('Y-m-d', strtotime($claim['submission_date'])) ?></div>
    </div>
</div>

<!-- Insurance Company & Patient Details -->
<table class="w-100 table-custom mb-3">
    <tr>
        <th width="15%" class="bg-light">شركة التأمين:</th>
        <td width="35%"><strong><?= htmlspecialchars($claim['company_name_ar']) ?></strong></td>
        <th width="15%" class="bg-light">رقم الوثيقة / البطاقة:</th>
        <td width="35%"><span class="font-monospace fw-bold text-primary"><?= htmlspecialchars($claim['policy_number']) ?></span></td>
    </tr>
    <tr>
        <th class="bg-light">اسم المريض المؤمَّن:</th>
        <td><strong><?= htmlspecialchars($claim['patient_name']) ?></strong></td>
        <th class="bg-light">الرقم الطبي (MRN):</th>
        <td><span class="font-monospace"><?= htmlspecialchars($claim['mrn']) ?></span></td>
    </tr>
    <tr>
        <th class="bg-light">العمر والنوع:</th>
        <td><?= $claim['patient_age'] ?> سنة / <?= $claim['patient_gender'] === 'male' ? 'ذكر' : 'أنثى' ?></td>
        <th class="bg-light">رقم الفاتورة:</th>
        <td><span class="font-monospace"><?= htmlspecialchars($claim['invoice_number']) ?></span></td>
    </tr>
</table>

<!-- Claimed Services Breakdown -->
<h6 class="fw-bold text-primary mb-2">بيان الخدمات الطبية والمبالغ المطالَب بها</h6>
<table class="w-100 table-custom mb-3">
    <thead class="bg-light">
        <tr>
            <th width="5%">#</th>
            <th width="55%">الخدمة أو الإجراء الطبي المقدم</th>
            <th width="10%" class="text-center">الكمية</th>
            <th width="15%" class="text-end">السعر (SDG)</th>
            <th width="15%" class="text-end">الإجمالي (SDG)</th>
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
            <th colspan="4" class="text-end bg-light">إجمالي قيمة الفاتورة:</th>
            <td class="text-end font-monospace fw-bold"><?= number_format($claim['total_gross'], 2) ?> SDG</td>
        </tr>
        <tr class="table-primary">
            <th colspan="4" class="text-end fs-6 fw-bold">المبلغ المطالَب به من شركة التأمين:</th>
            <td class="text-end font-monospace fs-6 fw-bold text-primary"><?= number_format($claim['claim_amount'], 2) ?> SDG</td>
        </tr>
        <?php if ($claim['approved_amount']): ?>
        <tr class="table-success">
            <th colspan="4" class="text-end fw-bold">المبلغ المعتمد بعد التدقيق:</th>
            <td class="text-end font-monospace fw-bold text-success"><?= number_format($claim['approved_amount'], 2) ?> SDG</td>
        </tr>
        <?php endif; ?>
    </tfoot>
</table>

<!-- Signatures -->
<div class="signature-box">
    <div class="sig-block">
        <div class="small fw-bold">مسؤول التأمين بالمستشفى</div>
        <div class="text-muted small"><?= htmlspecialchars($claim['creator_name'] ?? 'قسم التأمين') ?></div>
    </div>
    <div class="sig-block">
        <div class="small fw-bold">توقيع المريض المؤمَّن</div>
    </div>
    <div class="sig-block">
        <div class="small fw-bold">ختم واعتماد شركة التأمين</div>
    </div>
</div>

</body>
</html>
