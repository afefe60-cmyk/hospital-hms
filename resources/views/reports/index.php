<?php
$layout = 'layouts/main';
$clin = $metrics['clinical'];
$fin = $metrics['financial'];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h4 mb-1 fw-bold"><i class="bi bi-graph-up-arrow text-primary me-2"></i>التقارير الإحصائية والتحليلية الشاملة (Executive Analytics Hub)</h2>
        <p class="text-muted mb-0">لوحة المؤشرات السريرية، الطاقة الاستيعابية، الإيرادات والمصروفات بالجنيه السوداني (SDG)</p>
    </div>
    <div class="d-flex gap-2">
        <a href="/reports/financial" class="btn btn-outline-success d-flex align-items-center gap-1">
            <i class="bi bi-cash-coin"></i> التقرير المالي
        </a>
        <a href="/reports/clinical" class="btn btn-outline-primary d-flex align-items-center gap-1">
            <i class="bi bi-heart-pulse"></i> المؤشرات السريرية
        </a>
        <a href="/reports/inventory" class="btn btn-outline-warning d-flex align-items-center gap-1">
            <i class="bi bi-box-seam"></i> تقرير الصيدلية والمخزون
        </a>
        <a href="/reports/hr" class="btn btn-outline-info d-flex align-items-center gap-1">
            <i class="bi bi-people"></i> تقرير الكوادر
        </a>
        <a href="/reports/print?start_date=<?= $startDate ?>&end_date=<?= $endDate ?>" target="_blank" class="btn btn-primary d-flex align-items-center gap-2">
            <i class="bi bi-printer"></i>
            <span>طباعة التقرير الشامل (A4)</span>
        </a>
    </div>
</div>

<!-- Filter Bar -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body">
        <form action="/reports" method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small text-muted">من تاريخ</label>
                <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($startDate) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label small text-muted">إلى تاريخ</label>
                <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($endDate) ?>">
            </div>
            <div class="col-md-2 d-grid">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-filter me-1"></i>تحديث الإحصائيات
                </button>
            </div>
            <div class="col-md-2 d-grid">
                <a href="/reports/export?type=financial&start_date=<?= $startDate ?>&end_date=<?= $endDate ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-download me-1"></i>تصدير CSV
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Executive Clinical KPI Cards -->
<h5 class="fw-bold text-dark mb-3"><i class="bi bi-activity text-primary me-2"></i>المؤشرات الحيوية والتشغيلية (Clinical & Bed Capacity KPIs)</h5>
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3 text-primary me-3">
                    <i class="bi bi-people-fill fs-3"></i>
                </div>
                <div>
                    <span class="text-muted d-block small">إجمالي المرضى المسجلين</span>
                    <h4 class="fw-bold mb-0 text-primary"><?= number_format($clin['total_patients']) ?> <small class="fs-6 fw-normal text-muted">مريض</small></h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-success bg-opacity-10 p-3 text-success me-3">
                    <i class="bi bi-door-open fs-3"></i>
                </div>
                <div>
                    <span class="text-muted d-block small">معدل إشغال الأسرّة (Occupancy)</span>
                    <h4 class="fw-bold mb-0 text-success"><?= $clin['bed_occupancy_rate'] ?>% <small class="fs-6 fw-normal text-muted">(<?= $clin['occupied_beds'] ?>/<?= $clin['total_beds'] ?>)</small></h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-warning bg-opacity-10 p-3 text-warning me-3">
                    <i class="bi bi-clock-history fs-3"></i>
                </div>
                <div>
                    <span class="text-muted d-block small">متوسط مدة الإقامة (ALOS)</span>
                    <h4 class="fw-bold mb-0 text-warning"><?= $clin['alos'] ?> <small class="fs-6 fw-normal text-muted">يوم / حالة</small></h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-danger bg-opacity-10 p-3 text-danger me-3">
                    <i class="bi bi-scissors fs-3"></i>
                </div>
                <div>
                    <span class="text-muted d-block small">العمليات الجراحية المنفذة</span>
                    <h4 class="fw-bold mb-0 text-danger"><?= number_format($clin['period_surgeries']) ?> <small class="fs-6 fw-normal text-muted">عملية</small></h4>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Executive Financial KPI Cards -->
<h5 class="fw-bold text-dark mb-3"><i class="bi bi-wallet2 text-success me-2"></i>الأداء المالي والتحصيل بالجنيه السوداني (Financial & Revenue KPIs - SDG)</h5>
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body">
                <span class="text-muted d-block small mb-1">إجمالي الإيرادات المفوترة</span>
                <h4 class="fw-bold mb-0 text-dark"><?= number_format($fin['gross_billed'], 2) ?> <small class="fs-6 fw-normal text-muted">SDG</small></h4>
                <div class="small text-muted mt-2">عن كافة الخدمات والعيادات</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body">
                <span class="text-muted d-block small mb-1">المتحصلات النقدية وعبر بنكك</span>
                <h4 class="fw-bold mb-0 text-success"><?= number_format($fin['total_collected'], 2) ?> <small class="fs-6 fw-normal text-muted">SDG</small></h4>
                <div class="small text-success mt-2">
                    <i class="bi bi-phone me-1"></i>بنكك: <?= number_format($fin['bankak_collected'], 2) ?> | نقدي: <?= number_format($fin['cash_collected'], 2) ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body">
                <span class="text-muted d-block small mb-1">المطالبات التأمينية المعتمدة</span>
                <h4 class="fw-bold mb-0 text-primary"><?= number_format($fin['insurance_approved'], 2) ?> <small class="fs-6 fw-normal text-muted">SDG</small></h4>
                <div class="small text-muted mt-2">من إجمالي مطالب: <?= number_format($fin['insurance_claimed'], 2) ?> SDG</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body">
                <span class="text-muted d-block small mb-1">صافي العائد التشغيلي للمستشفى</span>
                <?php $isPositive = $fin['net_operating_income'] >= 0; ?>
                <h4 class="fw-bold mb-0 <?= $isPositive ? 'text-success' : 'text-danger' ?>">
                    <?= number_format($fin['net_operating_income'], 2) ?> <small class="fs-6 fw-normal text-muted">SDG</small>
                </h4>
                <div class="small text-muted mt-2">بعد خصم مصروفات الرواتب (<?= number_format($fin['payroll_expenses'], 2) ?> SDG)</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Departmental Visits Distribution -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-header bg-white py-3">
                <h6 class="card-title mb-0 fw-bold">
                    <i class="bi bi-bar-chart-steps text-primary me-2"></i>توزيع الزيارات والمراجعين حسب الأقسام
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>القسم التخصصي</th>
                                <th class="text-center">عدد الزيارات</th>
                                <th>نسبة التردد</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $totVisits = max(1, $clin['period_visits']);
                            foreach ($deptBreakdown as $d): 
                                $pct = round(($d['visit_count'] / $totVisits) * 100, 1);
                            ?>
                                <tr>
                                    <td class="fw-bold"><?= htmlspecialchars($d['department_name']) ?></td>
                                    <td class="text-center font-monospace fs-6"><?= number_format($d['visit_count']) ?></td>
                                    <td style="width: 35%;">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height: 8px;">
                                                <div class="progress-bar bg-primary" style="width: <?= $pct ?>%"></div>
                                            </div>
                                            <small class="text-muted"><?= $pct ?>%</small>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue by Service Category -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-header bg-white py-3">
                <h6 class="card-title mb-0 fw-bold">
                    <i class="bi bi-pie-chart text-success me-2"></i>توزيع الإيرادات المحققة حسب نوع الخدمة (SDG)
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>نوع الخدمة الطبية</th>
                                <th class="text-center">عدد العمليات</th>
                                <th class="text-end">الإيراد الإجمالي (SDG)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($serviceBreakdown)): ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">لا توجد حركات فوترة في هذه الفترة.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($serviceBreakdown as $sb): ?>
                                    <tr>
                                        <td>
                                            <?php
                                            $icon = match($sb['item_type']) {
                                                'consultation' => 'bi-stethoscope text-primary',
                                                'laboratory' => 'bi-test-tube text-info',
                                                'radiology' => 'bi-scan text-secondary',
                                                'pharmacy' => 'bi-capsule text-warning',
                                                'surgery' => 'bi-scissors text-danger',
                                                'bed_stay' => 'bi-bed text-success',
                                                default => 'bi-check-circle text-dark'
                                            };
                                            $titleAr = match($sb['item_type']) {
                                                'consultation' => 'كشوفات واستشارات العيادات',
                                                'laboratory' => 'فحوصات وتحاليل المختبر LIS',
                                                'radiology' => 'أشعة وتصوير طبي RIS',
                                                'pharmacy' => 'أدوية ومستلزمات الصيدلية',
                                                'surgery' => 'عمليات جراحية وتخدير OR',
                                                'bed_stay' => 'إقامة وتنويم داخلي IPD',
                                                default => $sb['item_type']
                                            };
                                            ?>
                                            <i class="bi <?= $icon ?> me-2"></i>
                                            <strong><?= $titleAr ?></strong>
                                        </td>
                                        <td class="text-center font-monospace"><?= number_format($sb['transaction_count']) ?></td>
                                        <td class="text-end fw-bold text-success font-monospace"><?= number_format((float)$sb['total_revenue'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
