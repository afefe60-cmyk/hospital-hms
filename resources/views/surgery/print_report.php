<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير العملية الجراحية والتخدير - <?= htmlspecialchars($surgery['surgery_number']) ?></title>
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
        .section-title {
            background-color: #f1f4f9;
            padding: 6px 12px;
            font-weight: bold;
            font-size: 13px;
            border-right: 4px solid #0d6efd;
            margin-top: 15px;
            margin-bottom: 10px;
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
        <i class="bi bi-printer"></i> طباعة التقرير
    </button>
    <button onclick="window.close()" class="btn btn-outline-secondary btn-sm px-3">إغلاق</button>
</div>

<!-- Header -->
<div class="header-box d-flex justify-content-between align-items-center">
    <div>
        <h4 class="fw-bold mb-1 text-primary">مستشفى الأمل التخصصي - الخرطوم</h4>
        <div class="text-muted small">قسم العمليات الجراحية والتخدير (Surgical Services & OR)</div>
    </div>
    <div class="text-start">
        <div class="fw-bold fs-5 text-dark">سجل العملية والتخدير</div>
        <div class="font-monospace text-muted small">رقم: <?= htmlspecialchars($surgery['surgery_number']) ?></div>
        <div class="small">التاريخ: <?= date('Y-m-d', strtotime($surgery['scheduled_start'])) ?></div>
    </div>
</div>

<!-- Patient & Admission Info -->
<div class="section-title">بيانات المريض والتنويم (Patient & Admission Details)</div>
<table class="w-100 table-custom mb-3">
    <tr>
        <th width="15%" class="bg-light">اسم المريض:</th>
        <td width="35%"><strong><?= htmlspecialchars($surgery['patient_name']) ?></strong></td>
        <th width="15%" class="bg-light">الرقم الطبي (MRN):</th>
        <td width="35%"><span class="font-monospace"><?= htmlspecialchars($surgery['mrn']) ?></span></td>
    </tr>
    <tr>
        <th class="bg-light">العمر والنوع:</th>
        <td><?= $surgery['patient_age'] ?> سنة / <?= $surgery['patient_gender'] === 'male' ? 'ذكر' : 'أنثى' ?></td>
        <th class="bg-light">فصيلة الدم:</th>
        <td><strong class="text-danger"><?= htmlspecialchars($surgery['blood_group'] ?? 'غير مسجل') ?></strong></td>
    </tr>
    <tr>
        <th class="bg-light">غرفة العمليات:</th>
        <td><?= htmlspecialchars($surgery['or_name_ar']) ?> (<?= htmlspecialchars($surgery['or_name_en']) ?>)</td>
        <th class="bg-light">نوع الجراحة:</th>
        <td><?= $surgery['surgery_type'] === 'emergency' ? 'طارئة (Emergency)' : 'اختيارية (Elective)' ?></td>
    </tr>
</table>

<!-- Surgical Team -->
<div class="section-title">الطاقم الجراحي والتخديري (Surgical & Anesthesia Team)</div>
<table class="w-100 table-custom mb-3">
    <tr>
        <th width="20%" class="bg-light">الجراح الرئيسي:</th>
        <td width="30%">د. <?= htmlspecialchars($surgery['lead_surgeon_name']) ?></td>
        <th width="20%" class="bg-light">الجراح المساعد:</th>
        <td width="30%"><?= !empty($surgery['assistant_surgeon_name']) ? 'د. ' . htmlspecialchars($surgery['assistant_surgeon_name']) : 'لا يوجد' ?></td>
    </tr>
    <tr>
        <th class="bg-light">طبيب التخدير:</th>
        <td><?= !empty($surgery['anesthesiologist_name']) ? 'د. ' . htmlspecialchars($surgery['anesthesiologist_name']) : 'لم يحدد' ?></td>
        <th class="bg-light">تمريض التعقيم:</th>
        <td><?= !empty($surgery['scrub_nurse_name']) ? htmlspecialchars($surgery['scrub_nurse_name']) : 'تمريض العمليات' ?></td>
    </tr>
</table>

<!-- Timings & Procedure -->
<div class="section-title">تفاصيل الإجراء الجراحي (Procedure Details)</div>
<div class="p-2 border rounded mb-2">
    <strong>اسم العملية / الإجراء:</strong> <?= htmlspecialchars($surgery['procedure_name']) ?>
</div>
<table class="w-100 table-custom mb-3">
    <tr>
        <th width="25%" class="bg-light">البداية الفعلية:</th>
        <td width="25%"><?= $surgery['actual_start'] ? date('Y-m-d h:i A', strtotime($surgery['actual_start'])) : '-' ?></td>
        <th width="25%" class="bg-light">النهاية الفعلية:</th>
        <td width="25%"><?= $surgery['actual_end'] ? date('Y-m-d h:i A', strtotime($surgery['actual_end'])) : '-' ?></td>
    </tr>
</table>

<!-- Diagnoses & Operation Notes -->
<div class="section-title">التقرير الجراحي وملاحظات العملية (Operative Findings & Notes)</div>
<div class="mb-2">
    <strong>التشخيص قبل العملية:</strong>
    <div class="p-2 bg-light border rounded small mt-1"><?= nl2br(htmlspecialchars($surgery['pre_op_diagnosis'] ?? 'لا يوجد')) ?></div>
</div>
<div class="mb-2">
    <strong>التشخيص النهائي بعد العملية:</strong>
    <div class="p-2 bg-light border rounded small mt-1"><?= nl2br(htmlspecialchars($surgery['post_op_diagnosis'] ?? 'قيد التوثيق')) ?></div>
</div>
<div class="mb-3">
    <strong>خطوات وسير العملية الجراحية (Operative Summary):</strong>
    <div class="p-3 bg-light border rounded small mt-1" style="min-height: 80px;"><?= nl2br(htmlspecialchars($surgery['surgery_notes'] ?? 'لا توجد ملاحظات مدونة.')) ?></div>
</div>

<!-- Anesthesia Section -->
<?php if ($anesthesia): ?>
    <div class="section-title">سجل التخدير والإفاقة (Anesthesia & Recovery Record)</div>
    <table class="w-100 table-custom mb-2">
        <tr>
            <th width="20%" class="bg-light">نوع التخدير:</th>
            <td width="30%"><?= htmlspecialchars($anesthesia['anesthesia_type']) ?></td>
            <th width="20%" class="bg-light">تصنيف ASA:</th>
            <td width="30%">ASA <?= htmlspecialchars($anesthesia['asa_physical_status']) ?></td>
        </tr>
        <tr>
            <th class="bg-light">تقييم مجرى الهواء:</th>
            <td><?= htmlspecialchars($anesthesia['airway_assessment'] ?? '-') ?></td>
            <th class="bg-light">مقياس الإفاقة (Aldrete):</th>
            <td><?= htmlspecialchars($anesthesia['recovery_score'] ?? '-') ?></td>
        </tr>
        <tr>
            <th class="bg-light">الأدوية المستخدمة:</th>
            <td colspan="3"><?= htmlspecialchars($anesthesia['agents_used'] ?? 'لا يوجد') ?></td>
        </tr>
        <tr>
            <th class="bg-light">المضاعفات والملاحظات:</th>
            <td colspan="3"><?= htmlspecialchars($anesthesia['complications'] ?? 'لا توجد مضاعفات') ?> - <?= htmlspecialchars($anesthesia['anesthesia_notes'] ?? '') ?></td>
        </tr>
    </table>
<?php endif; ?>

<!-- Signatures -->
<div class="signature-box">
    <div class="sig-block">
        <div class="small fw-bold">توقيع الجراح الرئيسي</div>
        <div class="text-muted small">د. <?= htmlspecialchars($surgery['lead_surgeon_name']) ?></div>
    </div>
    <div class="sig-block">
        <div class="small fw-bold">توقيع طبيب التخدير</div>
        <div class="text-muted small"><?= !empty($surgery['anesthesiologist_name']) ? 'د. ' . htmlspecialchars($surgery['anesthesiologist_name']) : 'طبيب التخدير' ?></div>
    </div>
    <div class="sig-block">
        <div class="small fw-bold">ختم العمليات / إدارة المستشفى</div>
    </div>
</div>

</body>
</html>
