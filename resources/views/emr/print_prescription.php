<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'الروشتة الطبية') ?> - HOSPITAL HMS</title>
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
        .prescription-card {
            max-width: 800px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        .hospital-header {
            border-bottom: 2px solid #0d9488;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .rx-symbol {
            font-size: 38px;
            font-weight: 800;
            color: #0d9488;
            font-family: serif;
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
            .prescription-card {
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
            🖨️ طباعة الروشتة (Print)
        </button>
        <button onclick="window.close(); if(!window.opener) history.back();" class="btn btn-secondary px-4 py-2">
            رجوع
        </button>
    </div>

    <div class="prescription-card">
        <!-- Header -->
        <div class="hospital-header d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold text-success mb-1">مستشفى الأمل التخصصي</h3>
                <p class="text-muted small mb-0">جمهورية السودان - الخرطوم | هاتف: +249 183 000000</p>
                <p class="text-muted small mb-0">قسم العيادات الخارجية والطب السريري (OPD/EMR)</p>
            </div>
            <div class="text-end">
                <span class="rx-symbol">℞</span>
                <div class="fw-bold text-muted small">رقم الروشتة: <?= htmlspecialchars($prescription['prescription_code']) ?></div>
                <div class="text-muted small">التاريخ: <?= date('d/m/Y H:i', strtotime($prescription['created_at'])) ?></div>
            </div>
        </div>

        <!-- Patient Info -->
        <div class="patient-info-box">
            <div class="row g-2">
                <div class="col-md-4">
                    <strong>اسم المريض:</strong> <?= htmlspecialchars($prescription['patient_name'] ?? 'غير محدد') ?>
                </div>
                <div class="col-md-4">
                    <strong>رقم السجل الطبي (MRN):</strong> <span class="badge bg-secondary"><?= htmlspecialchars($prescription['mrn'] ?? '') ?></span>
                </div>
                <div class="col-md-4">
                    <strong>النوع / العمر:</strong> <?= ($prescription['gender'] ?? '') === 'male' ? 'ذكر' : 'أنثى' ?> / <?= htmlspecialchars($prescription['patient_age'] ?? '-') ?> سنة
                </div>
                <div class="col-md-6 mt-2">
                    <strong>الطبيب المعالج:</strong> د. <?= htmlspecialchars($prescription['doctor_name'] ?? 'الطبيب العام') ?> (<?= htmlspecialchars($prescription['specialty_name'] ?? 'طب عام') ?>)
                </div>
                <div class="col-md-6 mt-2">
                    <strong>رقم الجلسة السريرية:</strong> #<?= htmlspecialchars($prescription['encounter_id']) ?>
                </div>
            </div>
        </div>

        <!-- Medicines Table -->
        <div class="mb-4">
            <h5 class="fw-bold mb-3 text-dark d-flex align-items-center">
                <span class="me-2">📋</span> الأدوية الموصوفة والتعليمات
            </h5>
            <table class="table table-bordered table-striped align-middle">
                <thead>
                    <tr>
                        <th style="width: 5%">#</th>
                        <th style="width: 35%">اسم الدواء (Medicine)</th>
                        <th style="width: 15%">الجرعة (Dose)</th>
                        <th style="width: 15%">التكرار (Frequency)</th>
                        <th style="width: 10%">المدة</th>
                        <th style="width: 20%">التعليمات الخاصة</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($items)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">لا توجد أصناف دوائية مدرجة</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($items as $idx => $item): ?>
                            <tr>
                                <td class="text-center"><?= $idx + 1 ?></td>
                                <td class="fw-bold text-primary"><?= htmlspecialchars($item['medicine_name']) ?></td>
                                <td><?= htmlspecialchars($item['dosage']) ?></td>
                                <td><?= htmlspecialchars($item['frequency']) ?></td>
                                <td><?= htmlspecialchars($item['duration_days']) ?> يوم</td>
                                <td class="small"><?= htmlspecialchars($item['instructions'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (!empty($prescription['notes'])): ?>
            <div class="p-3 bg-light rounded border mb-4">
                <strong>ملاحظات الطبيب وتوجيهات الصيدلية:</strong>
                <p class="mb-0 text-muted small mt-1"><?= nl2br(htmlspecialchars($prescription['notes'])) ?></p>
            </div>
        <?php endif; ?>

        <!-- Footer / Signature -->
        <div class="doctor-signature d-flex justify-content-between align-items-end">
            <div class="text-muted small">
                * هذه الوصفة الطبية صادرة إلكترونياً ومعتمدة وفق بروتوكول نظام HOSPITAL HMS.<br>
                * يرجى إبراز هذه الروشتة في صيدلية المستشفى لصرف العلاج.
            </div>
            <div class="text-center" style="min-width: 200px;">
                <p class="fw-bold mb-4">توقيع وختم الطبيب المعالج</p>
                <div style="border-bottom: 2px solid #000; width: 160px; margin: 0 auto 5px;"></div>
                <small class="text-muted">د. <?= htmlspecialchars($prescription['doctor_name'] ?? '') ?></small>
            </div>
        </div>
    </div>
</div>

</body>
</html>
