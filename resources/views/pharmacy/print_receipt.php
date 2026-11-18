<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'فاتورة صيدلية') ?> - HOSPITAL HMS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Cairo', system-ui, -apple-system, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            padding: 20px;
        }
        .receipt-card {
            max-width: 650px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            padding: 25px;
        }
        .hospital-header {
            border-bottom: 2px dashed #0d9488;
            padding-bottom: 12px;
            margin-bottom: 15px;
            text-align: center;
        }
        .table th {
            background-color: #f1f5f9;
            font-size: 13px;
        }
        .table td {
            font-size: 13px;
        }
        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .receipt-card {
                border: none;
                box-shadow: none;
                max-width: 100%;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

<div class="container py-2">
    <div class="text-center mb-3 no-print">
        <button onclick="window.print()" class="btn btn-primary px-4 py-2 me-2">
            🖨️ طباعة الفاتورة (Print)
        </button>
        <button onclick="window.close(); if(!window.opener) history.back();" class="btn btn-secondary px-4 py-2">
            رجوع
        </button>
    </div>

    <div class="receipt-card">
        <!-- Header -->
        <div class="hospital-header">
            <h4 class="fw-bold text-success mb-1">مستشفى الأمل التخصصي</h4>
            <p class="text-muted small mb-0">صيدلية المستشفى | الخرطوم - السودان</p>
            <p class="text-muted small mb-0">هاتف: +249 183 000000</p>
            <div class="fw-bold mt-2">فاتورة صرف دواء نقدي</div>
        </div>

        <div class="d-flex justify-content-between small mb-2 border-bottom pb-2">
            <div>
                <div><strong>رقم الفاتورة:</strong> <?= htmlspecialchars($dispense['dispense_number']) ?></div>
                <div><strong>المريض:</strong> <?= htmlspecialchars($dispense['patient_name']) ?></div>
                <div><strong>MRN:</strong> <?= htmlspecialchars($dispense['mrn']) ?></div>
            </div>
            <div class="text-end">
                <div><strong>التاريخ:</strong> <?= date('d/m/Y H:i', strtotime($dispense['created_at'])) ?></div>
                <div><strong>الصيدلي:</strong> <?= htmlspecialchars($dispense['pharmacist_name']) ?></div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="table table-sm table-bordered align-middle mb-3">
            <thead>
                <tr>
                    <th>الصنف</th>
                    <th>التشغيلة</th>
                    <th class="text-center">الكمية</th>
                    <th class="text-end">السعر</th>
                    <th class="text-end">الإجمالي</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $it): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($it['commercial_name']) ?></strong>
                            <small class="d-block text-muted"><?= htmlspecialchars($it['strength']) ?></small>
                        </td>
                        <td class="font-monospace small"><?= htmlspecialchars($it['batch_number']) ?></td>
                        <td class="text-center"><?= $it['quantity'] ?></td>
                        <td class="text-end"><?= number_format($it['unit_price'], 2) ?></td>
                        <td class="text-end fw-bold"><?= number_format($it['total_price'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4" class="text-end fs-6">الإجمالي المدفوع:</th>
                    <th class="text-end fs-6 text-success font-monospace"><?= number_format($dispense['total_amount'], 2) ?> SDG</th>
                </tr>
            </tfoot>
        </table>

        <div class="text-center text-muted small mt-4 pt-2 border-top">
            * نتمنى لكم دوام الصحة والعافية *<br>
            * البضاعة المباعة لا ترد ولا تستبدل بعد خروجها من الصيدلية حفاظاً على شروط الحفظ والتبريد *
        </div>
    </div>
</div>

</body>
</html>
