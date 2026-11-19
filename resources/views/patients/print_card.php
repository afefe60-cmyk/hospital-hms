<?php
$hospitalName = \App\Models\SystemSetting::get('hospital_name_ar', 'مستشفى الأمل التخصصي');
$hospitalEn = \App\Models\SystemSetting::get('hospital_name_en', 'Al-Amal Specialized Hospital');
$hospitalPhone = \App\Models\SystemSetting::get('hospital_phone', '+249 183 770000');
$emergencyPhone = \App\Models\SystemSetting::get('emergency_hotline', '999 / +249 183 770111');
$bankakAccount = \App\Models\SystemSetting::get('bankak_account_number', '1820499');

$fullName = trim($patient['first_name'] . ' ' . ($patient['second_name'] ?? '') . ' ' . ($patient['third_name'] ?? '') . ' ' . $patient['last_name']);
$age = date_diff(date_create($patient['date_of_birth']), date_create('today'))->y;
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>بطاقة المريض الطبية - <?= e($patient['mrn']) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&family=Libre+Barcode+128&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
            padding: 30px;
        }

        .card-container {
            max-width: 480px;
            margin: 0 auto;
        }

        .medical-card {
            width: 100%;
            background: #ffffff;
            border-radius: 16px;
            border: 2px solid #173B59;
            box-shadow: 0 10px 25px rgba(23, 59, 89, 0.15);
            overflow: hidden;
            position: relative;
        }

        .card-header-bar {
            background: linear-gradient(135deg, #10293D 0%, #173B59 60%, #16856D 100%);
            color: #ffffff;
            padding: 14px 20px;
            border-bottom: 3px solid #16856D;
        }

        .barcode-text {
            font-family: 'Libre Barcode 128', cursive, monospace;
            font-size: 42px;
            line-height: 1;
            letter-spacing: 2px;
            color: #10293D;
        }

        .blood-badge {
            background-color: #DC2626;
            color: #ffffff;
            font-weight: 800;
            font-size: 1.15rem;
            padding: 4px 14px;
            border-radius: 8px;
            display: inline-block;
        }

        .allergy-alert-box {
            background-color: #FEF2F2;
            border: 1px solid #FCA5A5;
            color: #991B1B;
            border-radius: 8px;
            padding: 6px 12px;
            font-size: 0.8rem;
            font-weight: 700;
        }

        .info-label {
            font-size: 0.75rem;
            color: #64748B;
            display: block;
            margin-bottom: 2px;
        }

        .info-value {
            font-size: 0.92rem;
            font-weight: 700;
            color: #1E293B;
        }

        @media print {
            body {
                background-color: #ffffff !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .medical-card {
                box-shadow: none !important;
                border: 1.5px solid #000 !important;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

    <div class="card-container">
        <!-- Control buttons (Hidden in print) -->
        <div class="no-print d-flex justify-content-between align-items-center mb-4">
            <a href="<?= url('/patients/' . $patient['id']) ?>" class="btn btn-outline-secondary btn-sm">
                &larr; العودة لملف المريض
            </a>
            <button onclick="window.print()" class="btn btn-success btn-sm px-4 fw-bold">
                🖨️ طباعة البطاقة الطبية (Print ID)
            </button>
        </div>

        <!-- Medical Patient Card Face -->
        <div class="medical-card">
            <!-- Header -->
            <div class="card-header-bar d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-0 text-white"><?= e($hospitalName) ?></h5>
                    <small class="text-light opacity-75" style="font-size: 0.75rem;"><?= e($hospitalEn) ?> &bull; الخرطوم</small>
                </div>
                <div class="text-end">
                    <span class="badge bg-light text-dark fw-bold px-2 py-1" style="font-size: 0.7rem;">بطاقة مريض موحدة</span>
                </div>
            </div>

            <!-- Body -->
            <div class="p-4">
                <div class="row align-items-center mb-3">
                    <div class="col-8">
                        <span class="info-label">اسم المريض الرباعي:</span>
                        <div class="info-value fs-5 text-primary"><?= e($fullName) ?></div>
                    </div>
                    <div class="col-4 text-end">
                        <span class="info-label">فصيلة الدم:</span>
                        <span class="blood-badge"><?= e($patient['blood_group'] ?? 'O+') ?></span>
                    </div>
                </div>

                <div class="row g-2 mb-3 bg-light p-2 rounded-3 border">
                    <div class="col-6">
                        <span class="info-label">الرقم الطبي الموحد (MRN):</span>
                        <span class="info-value font-monospace text-navy fs-6"><?= e($patient['mrn']) ?></span>
                    </div>
                    <div class="col-6">
                        <span class="info-label">الرقم الوطني السوداني:</span>
                        <span class="info-value font-monospace"><?= e($patient['national_id'] ?? 'غير مسجل') ?></span>
                    </div>
                    <div class="col-4">
                        <span class="info-label">العمر / النوع:</span>
                        <span class="info-value"><?= $age ?> سنة / <?= $patient['gender'] === 'male' ? 'ذكر' : 'أنثى' ?></span>
                    </div>
                    <div class="col-4">
                        <span class="info-label">تاريخ الميلاد:</span>
                        <span class="info-value"><?= e($patient['date_of_birth']) ?></span>
                    </div>
                    <div class="col-4">
                        <span class="info-label">الهاتف الأساسي:</span>
                        <span class="info-value font-monospace" style="direction: ltr; display:inline-block;"><?= e($patient['phone_primary']) ?></span>
                    </div>
                </div>

                <!-- Critical Allergies Warning -->
                <?php if (!empty($allergies)): ?>
                    <div class="allergy-alert-box mb-3">
                        ⚠️ <strong>تحذير تحسس طبي حرج:</strong>
                        <span>
                            <?= implode('، ', array_map(fn($a) => e($a['allergen_name']) . " (" . e($a['severity']) . ")", $allergies)) ?>
                        </span>
                    </div>
                <?php endif; ?>

                <!-- Emergency Contact -->
                <div class="d-flex justify-content-between align-items-center mb-3 small text-muted border-bottom pb-2">
                    <div>
                        <span>جهة الطوارئ:</span>
                        <strong class="text-dark"><?= e($patient['emergency_contact_name'] ?? 'الأقارب') ?> (<?= e($patient['emergency_contact_phone'] ?? '-') ?>)</strong>
                    </div>
                    <div>
                        <span>طوارئ المستشفى:</span>
                        <strong class="text-danger"><?= e($emergencyPhone) ?></strong>
                    </div>
                </div>

                <!-- Barcode & Bankak Note -->
                <div class="text-center mt-2">
                    <div class="barcode-text">*<?= e($patient['mrn']) ?>*</div>
                    <div class="font-monospace small text-muted fw-bold"><?= e($patient['mrn']) ?></div>
                    <div class="mt-2 text-muted" style="font-size: 0.7rem;">
                        حساب بنكك المستشفى المعتمد لسداد الفواتير: <strong><?= e($bankakAccount) ?></strong> &bull; يرجى إبراز هذه البطاقة عند كل مراجعة
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
