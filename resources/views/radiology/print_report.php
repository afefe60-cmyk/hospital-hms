<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'تقرير الأشعة') ?> - HOSPITAL HMS</title>
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
            border-bottom: 2px solid #0284c7;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .patient-info-box {
            background-color: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 25px;
        }
        .findings-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 18px;
            line-height: 1.8;
            font-size: 15px;
            white-space: pre-line;
        }
        .impression-box {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            padding: 18px;
            line-height: 1.8;
            font-size: 16px;
            font-weight: 700;
            color: #166534;
            white-space: pre-line;
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
                <h3 class="fw-bold text-primary mb-1">مستشفى الأمل التخصصي</h3>
                <p class="text-muted small mb-0">قسم الأشعة والتصوير الطبي التشخيصي (Diagnostic Radiology & Medical Imaging)</p>
                <p class="text-muted small mb-0">الخرطوم - جمهورية السودان | هاتف: +249 183 000000</p>
            </div>
            <div class="text-end">
                <div class="fw-bold fs-5 text-dark">تقرير أشعة تشخيصي</div>
                <div class="fw-bold text-muted small">رقم الفحص: <?= htmlspecialchars($order['order_number']) ?></div>
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
                    <strong>نوع الفحص:</strong> <?= htmlspecialchars($order['service_name_ar']) ?> (<?= htmlspecialchars($order['service_name_en']) ?>)
                </div>
                <div class="col-md-6 mt-2">
                    <strong>الطبيب المعالج:</strong> د. <?= htmlspecialchars($order['doctor_name']) ?> (<?= htmlspecialchars($order['doctor_specialty']) ?>)
                </div>
            </div>
        </div>

        <!-- Findings -->
        <div class="mb-4">
            <h5 class="fw-bold text-dark mb-2">1. المشاهدات والتقرير التفصيلي (Findings):</h5>
            <div class="findings-box">
                <?= htmlspecialchars($report['findings'] ?? 'لم يتم تدوين مشاهدات بعد.') ?>
            </div>
        </div>

        <!-- Impression -->
        <div class="mb-4">
            <h5 class="fw-bold text-dark mb-2">2. الاستنتاج والتشخيص النهائي (Impression / Conclusion):</h5>
            <div class="impression-box">
                <?= htmlspecialchars($report['impression'] ?? 'لم يتم تدوين استنتاج بعد.') ?>
            </div>
        </div>

        <!-- Footer / Signatures -->
        <div class="doctor-signature d-flex justify-content-between align-items-end">
            <div class="text-muted small">
                * التقرير صادر ومعتمد من قسم الأشعة والتصوير الطبي بمستشفى الأمل التخصصي.<br>
                * يرجى عرض هذا التقرير على الطبيب المعالج لاستكمال الخطة العلاجية.
            </div>
            <div class="text-center" style="min-width: 220px;">
                <p class="fw-bold mb-4">استشاري الأشعة والتصوير الطبي</p>
                <div style="border-bottom: 2px solid #000; width: 170px; margin: 0 auto 5px;"></div>
                <small class="text-muted">د. <?= htmlspecialchars($report['radiologist_name'] ?? 'استشاري الأشعة') ?></small>
            </div>
        </div>
    </div>
</div>

</body>
</html>
