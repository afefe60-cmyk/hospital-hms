<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>التقرير الإحصائي والتنفيذي الشامل - مستشفى الأمل التخصصي</title>
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
            margin-bottom: 20px;
        }
        .table-custom th, .table-custom td {
            padding: 6px 10px;
            border: 1px solid #dee2e6;
        }
        .kpi-box {
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 10px;
            background-color: #f8f9fa;
            text-align: center;
        }
        .signature-box {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }
        .sig-block {
            text-align: center;
            width: 220px;
            border-top: 1px solid #444;
            padding-top: 6px;
        }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body class="p-4">

<div class="no-print mb-3 text-end">
    <button onclick="window.print()" class="btn btn-primary btn-sm px-4">طباعة التقرير التنفيذي (A4)</button>
    <button onclick="window.close()" class="btn btn-outline-secondary btn-sm px-3">إغلاق</button>
</div>

<!-- Header -->
<div class="header-box d-flex justify-content-between align-items-center">
    <div>
        <h4 class="fw-bold mb-1 text-primary"><?= htmlspecialchars($settings['hospital_name_ar'] ?? 'مستشفى الخرطوم التخصصي ') ?></h4>
        <div class="text-muted small"><?= htmlspecialchars($settings['hospital_name_en'] ?? 'Khartoum Specialized Teaching Hospital') ?></div>
        <div class="text-muted small"><?= htmlspecialchars($settings['hospital_address'] ?? 'الخرطوم، السودان') ?> &bull; هاتف: <?= htmlspecialchars($settings['hospital_phone'] ?? '+249183770000') ?></div>
    </div>
    <div class="text-start">
        <div class="fw-bold fs-5 text-dark">التقرير التنفيذي والإحصائي الشامل</div>
        <div class="small">عن الفترة من: <strong><?= $startDate ?></strong> إلى <strong><?= $endDate ?></strong></div>
        <div class="small text-muted">تاريخ الإصدار: <?= date('Y-m-d H:i') ?></div>
    </div>
</div>

<!-- Key Performance Indicators Matrix -->
<h6 class="fw-bold text-dark border-bottom pb-2 mb-3">أولاً: ملخص المؤشرات التشغيلية والسريرية</h6>
<div class="row g-2 mb-4">
    <div class="col-3">
        <div class="kpi-box">
            <span class="text-muted small d-block">إجمالي المرضى المسجلين</span>
            <h4 class="fw-bold text-primary mb-0"><?= number_format($metrics['clinical']['total_patients']) ?></h4>
        </div>
    </div>
    <div class="col-3">
        <div class="kpi-box">
            <span class="text-muted small d-block">معدل إشغال الأسرّة</span>
            <h4 class="fw-bold text-success mb-0"><?= $metrics['clinical']['bed_occupancy_rate'] ?>%</h4>
        </div>
    </div>
    <div class="col-3">
        <div class="kpi-box">
            <span class="text-muted small d-block">متوسط مدة الإقامة (ALOS)</span>
            <h4 class="fw-bold text-warning mb-0"><?= $metrics['clinical']['alos'] ?> يوم</h4>
        </div>
    </div>
    <div class="col-3">
        <div class="kpi-box">
            <span class="text-muted small d-block">العمليات الجراحية المنفذة</span>
            <h4 class="fw-bold text-danger mb-0"><?= number_format($metrics['clinical']['period_surgeries']) ?></h4>
        </div>
    </div>
</div>

<!-- Financial Summary Section -->
<h6 class="fw-bold text-dark border-bottom pb-2 mb-3">ثانياً: مؤشرات الأداء المالي والتحصيل بالجنيه السوداني (SDG)</h6>
<table class="table table-custom mb-4">
    <tbody>
        <tr>
            <th class="bg-light" style="width: 25%;">إجمالي الإيرادات المفوترة:</th>
            <td style="width: 25%;" class="fw-bold"><?= number_format($metrics['financial']['gross_billed'], 2) ?> SDG</td>
            <th class="bg-light" style="width: 25%;">إجمالي المتحصلات النقدية وعبر بنكك:</th>
            <td style="width: 25%;" class="fw-bold text-success"><?= number_format($metrics['financial']['total_collected'], 2) ?> SDG</td>
        </tr>
        <tr>
            <th class="bg-light">المتحصل عبر بنكك (Bankak):</th>
            <td class="text-primary font-monospace"><?= number_format($metrics['financial']['bankak_collected'], 2) ?> SDG</td>
            <th class="bg-light">المتحصل نقداً بالخزينة:</th>
            <td class="font-monospace"><?= number_format($metrics['financial']['cash_collected'], 2) ?> SDG</td>
        </tr>
        <tr>
            <th class="bg-light">المطالبات التأمينية المعتمدة:</th>
            <td class="text-primary"><?= number_format($metrics['financial']['insurance_approved'], 2) ?> SDG</td>
            <th class="bg-light">مصروفات رواتب الكادر (Payroll):</th>
            <td class="text-danger"><?= number_format($metrics['financial']['payroll_expenses'], 2) ?> SDG</td>
        </tr>
        <tr class="table-light">
            <th colspan="2" class="fs-6">صافي العائد التشغيلي للمستشفى:</th>
            <td colspan="2" class="fw-bold fs-6 text-success"><?= number_format($metrics['financial']['net_operating_income'], 2) ?> SDG</td>
        </tr>
    </tbody>
</table>

<!-- Revenue by Service Category -->
<h6 class="fw-bold text-dark border-bottom pb-2 mb-3">ثالثاً: تفصيل الإيرادات حسب نوع الخدمة الطبية</h6>
<table class="table table-custom mb-4">
    <thead class="table-light">
        <tr>
            <th>نوع الخدمة الطبية</th>
            <th class="text-center">عدد العمليات / الإجراءات</th>
            <th class="text-end">الإيراد الإجمالي (SDG)</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($serviceBreakdown as $sb): ?>
            <tr>
                <td class="fw-bold"><?= htmlspecialchars($sb['item_type']) ?></td>
                <td class="text-center"><?= number_format($sb['transaction_count']) ?></td>
                <td class="text-end fw-bold text-success"><?= number_format((float)$sb['total_revenue'], 2) ?> SDG</td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Pharmacy & Supply -->
<h6 class="fw-bold text-dark border-bottom pb-2 mb-3">رابعاً: موقف المخزون الدوائي والصيدلية</h6>
<table class="table table-custom mb-4">
    <tbody>
        <tr>
            <th class="bg-light" style="width: 25%;">القيمة الإجمالية للمخزون:</th>
            <td style="width: 25%;" class="fw-bold text-success"><?= number_format($pharmacyMetrics['total_stock_value'], 2) ?> SDG</td>
            <th class="bg-light" style="width: 25%;">إجمالي الوحدات المخزنة:</th>
            <td style="width: 25%;"><?= number_format($pharmacyMetrics['total_units']) ?> وحدة</td>
        </tr>
        <tr>
            <th class="bg-light">أصناف وشيكة الانتهاء (&lt;90 يوم):</th>
            <td class="text-danger fw-bold"><?= number_format($pharmacyMetrics['near_expiry_count']) ?> صنف</td>
            <th class="bg-light">أصناف منتهية الصلاحية:</th>
            <td class="text-danger fw-bold"><?= number_format($pharmacyMetrics['expired_count']) ?> صنف</td>
        </tr>
    </tbody>
</table>

<!-- Signatures -->
<div class="signature-box">
    <div class="sig-block">
        <div>إعداد قسم الإحصاء الطبي والمعلومات</div>
        <small class="text-muted d-block mt-4">التوقيع / التاريخ</small>
    </div>
    <div class="sig-block">
        <div>المدير المالي والإداري</div>
        <small class="text-muted d-block mt-4">التوقيع / التاريخ</small>
    </div>
    <div class="sig-block">
        <div>اعتماد المدير العام للمستشفى</div>
        <small class="text-muted d-block mt-4">التوقيع / الختم الرسمي</small>
    </div>
</div>

</body>
</html>
