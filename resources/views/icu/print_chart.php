<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مخطط العناية المركزة (ICU Flowsheet) - <?= htmlspecialchars($stay['stay_number']) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #fff;
            color: #212529;
            font-size: 11px;
        }
        .header-box {
            border-bottom: 2px solid #dc3545;
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
    <button onclick="window.print()" class="btn btn-danger btn-sm px-4">
        <i class="bi bi-printer"></i> طباعة الفلوشيت
    </button>
    <button onclick="window.close()" class="btn btn-outline-secondary btn-sm px-3">إغلاق</button>
</div>

<!-- Hospital Header -->
<div class="header-box d-flex justify-content-between align-items-center">
    <div>
        <h4 class="fw-bold mb-1 text-danger">مستشفى الأمل التخصصي - الخرطوم</h4>
        <div class="text-muted small">قسم العناية المركزة والحالات الحرجة (Intensive Care Unit)</div>
    </div>
    <div class="text-start">
        <div class="fw-bold fs-5 text-dark">مخطط الملاحظات الحيوية (ICU Flowsheet)</div>
        <div class="font-monospace text-muted small">إقامة رقم: <?= htmlspecialchars($stay['stay_number']) ?></div>
        <div class="small">تاريخ الطباعة: <?= date('Y-m-d H:i') ?></div>
    </div>
</div>

<!-- Patient Details -->
<table class="w-100 table-custom mb-3">
    <tr>
        <th width="15%" class="bg-light">اسم المريض:</th>
        <td width="35%"><strong><?= htmlspecialchars($stay['patient_name']) ?></strong></td>
        <th width="15%" class="bg-light">الرقم الطبي (MRN):</th>
        <td width="35%"><span class="font-monospace"><?= htmlspecialchars($stay['mrn']) ?></span></td>
    </tr>
    <tr>
        <th class="bg-light">العمر والنوع:</th>
        <td><?= $stay['patient_age'] ?> سنة / <?= $stay['patient_gender'] === 'male' ? 'ذكر' : 'أنثى' ?></td>
        <th class="bg-light">سرير العناية:</th>
        <td><strong><?= htmlspecialchars($stay['bed_number']) ?></strong> (غرفة: <?= htmlspecialchars($stay['room_number']) ?>)</td>
    </tr>
    <tr>
        <th class="bg-light">تاريخ وساعة الدخول:</th>
        <td><?= date('Y-m-d h:i A', strtotime($stay['admitted_at'])) ?></td>
        <th class="bg-light">طبيب العناية المسؤول:</th>
        <td>د. <?= htmlspecialchars($stay['doctor_name'] ?? 'طبيب العناية المناوب') ?></td>
    </tr>
    <tr>
        <th class="bg-light">سبب التنويم:</th>
        <td colspan="3"><?= htmlspecialchars($stay['admission_reason']) ?></td>
    </tr>
</table>

<!-- Flowsheet Table -->
<h6 class="fw-bold text-danger mb-2">جدول المؤشرات الحيوية والملاحظات السريرية</h6>
<table class="w-100 table-custom text-center mb-4">
    <thead class="bg-light">
        <tr>
            <th>الوقت</th>
            <th>BP</th>
            <th>HR</th>
            <th>RR</th>
            <th>Temp</th>
            <th>SpO2</th>
            <th>GCS</th>
            <th>البؤبؤ</th>
            <th>وضع التنفس</th>
            <th>FiO2/PEEP</th>
            <th>سوائل IV</th>
            <th>بول</th>
            <th>ABG / ملاحظات سريرية</th>
            <th>المسجل</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($observations)): ?>
            <tr>
                <td colspan="14" class="py-3 text-muted">لا توجد ملاحظات مدونة في هذا المخطط.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($observations as $obs): ?>
                <tr>
                    <td class="font-monospace"><?= date('m-d H:i', strtotime($obs['recorded_at'])) ?></td>
                    <td><strong><?= $obs['systolic_bp'] ?>/<?= $obs['diastolic_bp'] ?></strong></td>
                    <td><?= $obs['pulse_rate'] ?></td>
                    <td><?= $obs['respiratory_rate'] ?></td>
                    <td><?= $obs['temperature'] ?>°</td>
                    <td><?= $obs['oxygen_saturation'] ?>%</td>
                    <td><?= $obs['gcs_score'] ?></td>
                    <td><?= htmlspecialchars($obs['pupil_reaction']) ?></td>
                    <td><?= htmlspecialchars($obs['ventilator_mode'] ?? '-') ?></td>
                    <td><?= $obs['fio2'] ? $obs['fio2'].'%' : '-' ?> / <?= $obs['peep'] ? $obs['peep'] : '-' ?></td>
                    <td><?= $obs['iv_fluids_ml'] ? '+' . $obs['iv_fluids_ml'] : '-' ?></td>
                    <td><?= $obs['urine_output_ml'] ? '-' . $obs['urine_output_ml'] : '-' ?></td>
                    <td class="text-start" style="font-size: 10px;">
                        <?= htmlspecialchars($obs['arterial_blood_gas'] ?? '') ?>
                        <?= !empty($obs['clinical_notes']) ? ' | ' . htmlspecialchars($obs['clinical_notes']) : '' ?>
                    </td>
                    <td><?= htmlspecialchars($obs['recorder_name'] ?? 'طاقم التمريض') ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<!-- Signatures -->
<div class="d-flex justify-content-between mt-5 pt-3">
    <div class="text-center" style="width: 200px; border-top: 1px solid #666;">
        <div class="small fw-bold">طبيب العناية المركزة</div>
        <div class="text-muted small">د. <?= htmlspecialchars($stay['doctor_name'] ?? 'طبيب العناية المناوب') ?></div>
    </div>
    <div class="text-center" style="width: 200px; border-top: 1px solid #666;">
        <div class="small fw-bold">مسؤول تمريض العناية</div>
    </div>
    <div class="text-center" style="width: 200px; border-top: 1px solid #666;">
        <div class="small fw-bold">ختم وحدة العناية المركزة</div>
    </div>
</div>

</body>
</html>
