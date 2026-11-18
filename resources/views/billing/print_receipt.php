<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سند قبض - <?= htmlspecialchars($payment['receipt_number']) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #fff;
            color: #212529;
            font-size: 13px;
        }
        .receipt-card {
            max-width: 650px;
            margin: auto;
            border: 2px dashed #198754;
            padding: 20px;
            border-radius: 8px;
        }
        .header-box {
            border-bottom: 2px solid #198754;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .amount-box {
            background-color: #e8f5e9;
            border: 1px solid #c8e6c9;
            padding: 10px;
            border-radius: 6px;
            font-size: 18px;
            font-weight: bold;
            color: #2e7d32;
            text-align: center;
            margin: 15px 0;
        }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body class="p-4">

<div class="no-print mb-3 text-center">
    <button onclick="window.print()" class="btn btn-success btn-sm px-4">
        <i class="bi bi-printer"></i> طباعة إيصال القبض
    </button>
    <button onclick="window.close()" class="btn btn-outline-secondary btn-sm px-3">إغلاق</button>
</div>

<div class="receipt-card">
    <div class="header-box d-flex justify-content-between align-items-center">
        <div>
            <h5 class="fw-bold mb-1 text-success">مستشفى الأمل التخصصي - الخرطوم</h5>
            <small class="text-muted">الخزينة والتحصيل المالي (Cashier)</small>
        </div>
        <div class="text-start">
            <h5 class="fw-bold mb-0 text-dark">سند قبض مالي (Receipt)</h5>
            <div class="font-monospace text-success fw-bold"><?= htmlspecialchars($payment['receipt_number']) ?></div>
            <small class="text-muted"><?= date('Y-m-d H:i', strtotime($payment['created_at'])) ?></small>
        </div>
    </div>

    <div class="amount-box">
        المبلغ المقبوض: <?= number_format($payment['amount'], 2) ?> جنيه سوداني (SDG)
    </div>

    <table class="table table-sm table-borderless mb-3">
        <tr>
            <th width="25%">استلمنا من السيد/ة:</th>
            <td width="75%"><strong><?= htmlspecialchars($payment['patient_name']) ?></strong> (MRN: <?= htmlspecialchars($payment['mrn']) ?>)</td>
        </tr>
        <tr>
            <th>وذلك سداداً لـ:</th>
            <td>فاتورة علاجية رقم <strong class="font-monospace text-primary"><?= htmlspecialchars($payment['invoice_number']) ?></strong></td>
        </tr>
        <tr>
            <th>طريقة الدفع:</th>
            <td>
                <?php
                    $methodLabel = match($payment['payment_method']) {
                        'bankak' => 'تطبيق بنكك (Bankak - بنك الخرطوم)',
                        'card' => 'بطاقة صراف آلي (POS)',
                        'bank_transfer' => 'تحويل مصرفي',
                        default => 'نقداً (كاش)'
                    };
                ?>
                <strong><?= $methodLabel ?></strong>
                <?php if (!empty($payment['transaction_reference'])): ?>
                    - رقم العملية: <span class="font-monospace fw-bold text-dark"><?= htmlspecialchars($payment['transaction_reference']) ?></span>
                <?php endif; ?>
            </td>
        </tr>
        <?php if (!empty($payment['notes'])): ?>
        <tr>
            <th>البيان / ملاحظات:</th>
            <td><?= htmlspecialchars($payment['notes']) ?></td>
        </tr>
        <?php endif; ?>
    </table>

    <div class="d-flex justify-content-between mt-4 pt-3 border-top">
        <div class="text-center" style="width: 150px;">
            <small class="text-muted d-block">المستلم / الصراف</small>
            <strong><?= htmlspecialchars($payment['cashier_name'] ?? 'أمين الصندوق') ?></strong>
        </div>
        <div class="text-center" style="width: 150px;">
            <small class="text-muted d-block">ختم الصندوق المالي</small>
        </div>
    </div>
</div>

</body>
</html>
