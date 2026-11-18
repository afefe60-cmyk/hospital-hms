<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'التقرير المخبري') ?> - HOSPITAL HMS</title>
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
        .report-card {
            max-width: 850px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            padding: 35px;
        }
        .hospital-header {
            border-bottom: 2px solid #0d9488;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .patient-info-box {
            background-color: #f0fdfa;
            border: 1px solid #ccfbf1;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 25px;
        }
        .table th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: 700;
        }
        .doctor-signature {
            margin-top: 50px;
            border-top: 1px dashed #cbd5e1;
            padding-top: 20px;
        }
        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .report-card {
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

<div class="container py-3">
    <div class="text-center mb-3 no-print">
        <button onclick="window.print()" class="btn btn-primary px-4 py-2 me-2">
            🖨️ طباعة التقرير (Print Report)
        </button>
        <button onclick="window.close(); if(!window.opener) history.back();" class="btn btn-secondary px-4 py-2">
            رجوع
        </button>
    </div>

    <div class="report-card">
        <!-- Header -->
        <div class="hospital-header d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold text-success mb-1">مستشفى الأمل التخصصي</h3>
                <p class="text-muted small mb-0">قسم المختبرات الطبية وبنك الدم (Diagnostic Clinical Laboratories)</p>
                <p class="text-muted small mb-0">الخرطوم - جمهورية السودان | هاتف: +249 183 000000</p>
            </div>
            <div class="text-end">
                <div class="fw-bold fs-5 text-dark">تقرير تحاليل مخبرية</div>
                <div class="fw-bold text-muted small">رقم الفحص: <?= htmlspecialchars($order['order_number']) ?></div>
                <?php if ($sample): ?>
                    <div class="font-monospace small text-primary">باركود: <?= htmlspecialchars($sample['sample_barcode']) ?></div>
                <?php endif; ?>
                <div class="text-muted small">التاريخ: <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></div>
            </div>
        </div>

        <!-- Patient Demographics -->
        <div class="patient-info-box">
            <div class="row g-2">
                <div class="col-md-5">
                    <strong>اسم المريض:</strong> <?= htmlspecialchars($order['patient_name']) ?>
                </div>
                <div class="col-md-3">
                    <strong>السجل الطبي (MRN):</strong> <span class="badge bg-secondary"><?= htmlspecialchars($order['mrn']) ?></span>
                </div>
                <div class="col-md-4">
                    <strong>النوع / العمر:</strong> <?= ($order['gender'] === 'male') ? 'ذكر' : 'أنثى' ?> / <?= $order['patient_age'] ?> سنة
                </div>
                <div class="col-md-6 mt-2">
                    <strong>الطبيب المعالج:</strong> د. <?= htmlspecialchars($order['doctor_name']) ?> (<?= htmlspecialchars($order['doctor_specialty']) ?>)
                </div>
                <div class="col-md-6 mt-2">
                    <strong>تاريخ سحب العينة:</strong> <?= $sample ? date('d/m/Y H:i', strtotime($sample['collected_at'])) : 'غير محدد' ?>
                </div>
            </div>
        </div>

        <!-- Results Table -->
        <div class="mb-4">
            <h5 class="fw-bold mb-3 text-dark">📋 نتائج الفحوصات المعملية (Laboratory Test Results)</h5>
            <table class="table table-bordered table-striped align-middle">
                <thead>
                    <tr>
                        <th style="width: 35%">اسم المؤشر (Parameter)</th>
                        <th style="width: 20%">النتيجة (Result)</th>
                        <th style="width: 15%">الوحدة</th>
                        <th style="width: 20%">المعدل الطبيعي</th>
                        <th style="width: 10%" class="text-center">الدلالة</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($resultItems)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-3">لا توجد نتائج مسجلة</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($resultItems as $ri): ?>
                            <tr>
                                <td class="fw-bold text-dark"><?= htmlspecialchars($ri['parameter_name']) ?></td>
                                <td class="fw-bold <?= in_array($ri['flag'], ['high', 'critical', 'positive']) ? 'text-danger' : 'text-primary' ?>">
                                    <?= htmlspecialchars($ri['result_value']) ?>
                                </td>
                                <td><small class="text-muted"><?= htmlspecialchars($ri['unit']) ?></small></td>
                                <td class="small font-monospace"><?= htmlspecialchars($ri['reference_range']) ?></td>
                                <td class="text-center">
                                    <span class="badge <?= in_array($ri['flag'], ['high', 'critical', 'positive']) ? 'bg-danger text-white' : 'bg-success text-white' ?>">
                                        <?= strtoupper(htmlspecialchars($ri['flag'])) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($result && !empty($result['general_comments'])): ?>
            <div class="p-3 bg-light rounded border mb-4">
                <strong>ملاحظات وتوجيهات أخصائي المختبر:</strong>
                <p class="mb-0 text-muted small mt-1"><?= nl2br(htmlspecialchars($result['general_comments'])) ?></p>
            </div>
        <?php endif; ?>

        <!-- Footer / Signatures -->
        <div class="doctor-signature d-flex justify-content-between align-items-end">
            <div class="text-muted small">
                * تم إجراء هذا التحليل باستخدام أجهزة مخبرية أوتوماتيكية معيرة طبياً.<br>
                * النتائج معتمدة إلكترونياً من المختبر الطبي بمستشفى الأمل التخصصي.
            </div>
            <div class="text-center" style="min-width: 220px;">
                <p class="fw-bold mb-4">اعتماد أخصائي التحاليل الطبية</p>
                <div style="border-bottom: 2px solid #000; width: 170px; margin: 0 auto 5px;"></div>
                <small class="text-muted">د. <?= htmlspecialchars($result['verifier_name'] ?? 'أخصائي المختبر') ?></small>
            </div>
        </div>
    </div>
</div>

</body>
</html>
