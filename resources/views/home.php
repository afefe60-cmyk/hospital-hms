<?php
/** @var array $hospital */
/** @var array $metrics */
/** @var array $wardOccupancy */
/** @var array $recentSurgeries */
/** @var array $activeErCases */
/** @var array $nearExpiryBatches */
/** @var array $info */
?>

<div class="row g-4">
    <!-- 1. Operational Dashboard Title & Shift Strip (بدون تكرار اسم المستشفى المكرر في السايدبار) -->
    <div class="col-12">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 pt-1 pb-1">
            <div>
                <h4 class="fw-bold text-navy mb-0 d-flex align-items-center gap-2">
                    <i data-lucide="layout-dashboard" class="text-green" style="width: 24px; height: 24px;"></i>
                    لوحة القيادة والعمليات اليومية
                </h4>
                <div class="text-muted small mt-1 d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5 d-inline-flex align-items-center gap-1">
                        <span class="p-1 rounded-circle bg-success d-inline-block"></span>
                        الوردية الصباحية المعتمدة
                    </span>
                    <span>&bull;</span>
                    <span><i data-lucide="calendar" style="width: 13px; height: 13px; display:inline-block; vertical-align:text-bottom;"></i> <?= date('Y-m-d') ?> | <?= date('l') ?></span>
                </div>
            </div>
            <div>
                <a href="<?= url('/reports') ?>" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1.5 fw-bold shadow-sm px-3 py-1.5" style="border-radius: 8px;">
                    <i data-lucide="bar-chart-2" style="width: 16px; height: 16px; color: var(--hms-green);"></i>
                    <span>التقارير الشاملة &larr;</span>
                </a>
            </div>
        </div>
    </div>

    <!-- 2. Quick Action Launchpad (أزرار مسارات الوصول السريع المريحة) -->
    <div class="col-12">
        <div class="card card-hms p-3 bg-white shadow-sm" style="border-radius: 12px;">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="fw-bold text-navy small d-flex align-items-center gap-1.5">
                    <i data-lucide="zap" class="text-accent" style="width: 16px; height: 16px;"></i>
                    مسارات الوصول السريع والعمليات المباشرة (Quick Launchpad)
                </span>
                <span class="text-muted small">كل العمليات مرتبطة بلحظتها</span>
            </div>
            <div class="row g-2">
                <div class="col-6 col-md-3 col-xl">
                    <a href="<?= url('/patients/create') ?>" class="btn btn-outline-primary w-100 py-2.5 d-flex flex-column align-items-center justify-content-center gap-1.5 h-100 shadow-none">
                        <i data-lucide="user-plus" style="width: 22px; height: 22px;"></i>
                        <span class="small fw-bold">فتح ملف مريض</span>
                    </a>
                </div>
                <div class="col-6 col-md-3 col-xl">
                    <a href="<?= url('/emergency/create') ?>" class="btn btn-outline-danger w-100 py-2.5 d-flex flex-column align-items-center justify-content-center gap-1.5 h-100 shadow-none">
                        <i data-lucide="ambulance" style="width: 22px; height: 22px;"></i>
                        <span class="small fw-bold">استقبال طوارئ ER</span>
                    </a>
                </div>
                <div class="col-6 col-md-3 col-xl">
                    <a href="<?= url('/emr') ?>" class="btn btn-outline-success w-100 py-2.5 d-flex flex-column align-items-center justify-content-center gap-1.5 h-100 shadow-none">
                        <i data-lucide="stethoscope" style="width: 22px; height: 22px;"></i>
                        <span class="small fw-bold">عيادة الطبيب EMR</span>
                    </a>
                </div>
                <div class="col-6 col-md-3 col-xl">
                    <a href="<?= url('/admissions/create') ?>" class="btn btn-outline-info w-100 py-2.5 d-flex flex-column align-items-center justify-content-center gap-1.5 h-100 shadow-none">
                        <i data-lucide="bed" style="width: 22px; height: 22px;"></i>
                        <span class="small fw-bold">تنويم سريري IPD</span>
                    </a>
                </div>
                <div class="col-6 col-md-3 col-xl">
                    <a href="<?= url('/surgery/create') ?>" class="btn btn-outline-secondary w-100 py-2.5 d-flex flex-column align-items-center justify-content-center gap-1.5 h-100 shadow-none">
                        <i data-lucide="scissors" style="width: 22px; height: 22px;"></i>
                        <span class="small fw-bold">جدولة عملية OR</span>
                    </a>
                </div>
                <div class="col-6 col-md-3 col-xl">
                    <a href="<?= url('/lab/create') ?>" class="btn btn-outline-warning w-100 py-2.5 d-flex flex-column align-items-center justify-content-center gap-1.5 h-100 shadow-none">
                        <i data-lucide="test-tube" style="width: 22px; height: 22px;"></i>
                        <span class="small fw-bold">طلب فحص LIS</span>
                    </a>
                </div>
                <div class="col-6 col-md-3 col-xl">
                    <a href="<?= url('/pharmacy') ?>" class="btn btn-outline-success w-100 py-2.5 d-flex flex-column align-items-center justify-content-center gap-1.5 h-100 shadow-none">
                        <i data-lucide="pill" style="width: 22px; height: 22px;"></i>
                        <span class="small fw-bold">صرف صيدلية</span>
                    </a>
                </div>
                <div class="col-6 col-md-3 col-xl">
                    <a href="<?= url('/billing/invoices/create') ?>" class="btn btn-outline-dark w-100 py-2.5 d-flex flex-column align-items-center justify-content-center gap-1.5 h-100 shadow-none">
                        <i data-lucide="receipt" style="width: 22px; height: 22px;"></i>
                        <span class="small fw-bold">فاتورة / سند قبض</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Key Operational KPI Cards Strip (مؤشرات الأداء الحية المريحة) -->
    <div class="col-sm-6 col-lg-4 col-xl-2">
        <div class="card card-hms p-3 bg-white h-100 border-start border-4 border-primary shadow-sm" style="border-radius: 10px;">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <small class="text-muted d-block fw-semibold">مرضى اليوم</small>
                    <h3 class="fw-bold text-navy my-1"><?= number_format($metrics['today_visits']) ?></h3>
                    <small class="text-secondary">إجمالي الملفات: <?= number_format($metrics['total_patients']) ?></small>
                </div>
                <div class="p-2 rounded bg-primary-subtle text-primary">
                    <i data-lucide="users" style="width: 22px; height: 22px;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-4 col-xl-2">
        <div class="card card-hms p-3 bg-white h-100 border-start border-4 border-info shadow-sm" style="border-radius: 10px;">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <small class="text-muted d-block fw-semibold">المنومين حالياً</small>
                    <h3 class="fw-bold text-navy my-1"><?= number_format($metrics['active_inpatients']) ?></h3>
                    <small class="text-secondary">في كافة الأجنحة</small>
                </div>
                <div class="p-2 rounded bg-info-subtle text-info">
                    <i data-lucide="bed-double" style="width: 22px; height: 22px;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-4 col-xl-2">
        <div class="card card-hms p-3 bg-white h-100 border-start border-4 <?= $metrics['bed_occupancy_rate'] > 85 ? 'border-danger' : 'border-success' ?> shadow-sm" style="border-radius: 10px;">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <small class="text-muted d-block fw-semibold">إشغال الأسرة</small>
                    <h3 class="fw-bold <?= $metrics['bed_occupancy_rate'] > 85 ? 'text-danger' : 'text-green' ?> my-1">
                        <?= $metrics['bed_occupancy_rate'] ?>%
                    </h3>
                    <small class="text-secondary"><?= $metrics['available_beds'] ?> سرير شاغر</small>
                </div>
                <div class="p-2 rounded bg-success-subtle text-success">
                    <i data-lucide="activity" style="width: 22px; height: 22px;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-4 col-xl-2">
        <div class="card card-hms p-3 bg-white h-100 border-start border-4 border-danger shadow-sm" style="border-radius: 10px;">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <small class="text-muted d-block fw-semibold">حالات الطوارئ ER</small>
                    <h3 class="fw-bold text-danger my-1"><?= number_format($metrics['today_er_cases']) ?></h3>
                    <small class="text-secondary">حالات اليوم</small>
                </div>
                <div class="p-2 rounded bg-danger-subtle text-danger">
                    <i data-lucide="ambulance" style="width: 22px; height: 22px;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-4 col-xl-2">
        <div class="card card-hms p-3 bg-white h-100 border-start border-4 border-secondary shadow-sm" style="border-radius: 10px;">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <small class="text-muted d-block fw-semibold">عمليات جراحية</small>
                    <h3 class="fw-bold text-navy my-1"><?= number_format($metrics['today_surgeries']) ?></h3>
                    <small class="text-secondary">مجدولة اليوم</small>
                </div>
                <div class="p-2 rounded bg-secondary-subtle text-secondary">
                    <i data-lucide="scissors" style="width: 22px; height: 22px;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-4 col-xl-2">
        <div class="card card-hms p-3 bg-white h-100 border-start border-4 border-warning shadow-sm" style="border-radius: 10px;">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <small class="text-muted d-block fw-semibold">تحصيل اليوم (ج.س)</small>
                    <h4 class="fw-bold text-green my-1 fs-5"><?= number_format($metrics['today_collections_sdg']) ?></h4>
                    <small class="text-muted" title="بنكك">بنكك: <?= number_format($metrics['today_bankak_sdg']) ?></small>
                </div>
                <div class="p-2 rounded bg-warning-subtle text-warning">
                    <i data-lucide="wallet" style="width: 22px; height: 22px;"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Ward Occupancy Status & Active ER Cases -->
    <div class="col-lg-6">
        <div class="card card-hms p-4 bg-white h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-navy mb-0 d-flex align-items-center gap-2">
                    <i data-lucide="layout-grid" class="text-green" style="width: 20px; height: 20px;"></i>
                    حالة إشغال الأجنحة والأسرة (Ward Capacity)
                </h5>
                <a href="<?= url('/beds') ?>" class="btn btn-sm btn-outline-secondary">خريطة الأسرة &larr;</a>
            </div>

            <?php if (empty($wardOccupancy)): ?>
                <div class="alert alert-light text-center py-4 text-muted">لا توجد بيانات أجنحة مسجلة حالياً</div>
            <?php else: ?>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($wardOccupancy as $ward): 
                        $total = (int)$ward['total_beds'];
                        $occ = (int)$ward['occupied_beds'];
                        $avail = (int)$ward['available_beds'];
                        $pct = $total > 0 ? round(($occ / $total) * 100) : 0;
                        $barColor = $pct >= 85 ? 'bg-danger' : ($pct >= 60 ? 'bg-warning' : 'bg-success');
                    ?>
                        <div class="p-3 border rounded-3 bg-light">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div>
                                    <span class="fw-bold text-navy"><?= e($ward['name_ar']) ?></span>
                                    <span class="badge bg-secondary-subtle text-secondary ms-1 small">
                                        <?= $ward['gender_type'] === 'female' ? 'نساء' : ($ward['gender_type'] === 'male' ? 'رجال' : 'مشترك') ?>
                                    </span>
                                </div>
                                <span class="small fw-semibold <?= $pct >= 85 ? 'text-danger' : 'text-navy' ?>">
                                    <?= $occ ?> / <?= $total ?> سرير (<?= $pct ?>%)
                                </span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar <?= $barColor ?>" role="progressbar" style="width: <?= $pct ?>%"></div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <small class="text-muted"><?= $avail ?> سرير متاح حالياً</small>
                                <a href="<?= url('/admissions?ward_id=' . $ward['id']) ?>" class="small text-green text-decoration-none">عرض المرضى &rarr;</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Active ER Cases Widget -->
    <div class="col-lg-6">
        <div class="card card-hms p-4 bg-white h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-navy mb-0 d-flex align-items-center gap-2">
                    <i data-lucide="ambulance" class="text-danger" style="width: 20px; height: 20px;"></i>
                    الحالات النشطة بقسم الحوادث والطوارئ (Active ER Cases)
                </h5>
                <a href="<?= url('/emergency') ?>" class="btn btn-sm btn-outline-danger">شاشة الطوارئ &larr;</a>
            </div>

            <?php if (empty($activeErCases)): ?>
                <div class="alert alert-light text-center py-4 text-muted">
                    <i data-lucide="check-circle" class="text-success d-block mx-auto mb-2" style="width: 32px; height: 32px;"></i>
                    قسم الطوارئ مستقر حالياً، لا توجد حالات حرجة غير مفرزة.
                </div>
            <?php else: ?>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($activeErCases as $er): 
                        $triageBadge = match($er['triage_level']) {
                            'resuscitation', 'level_1_red', 'red' => 'bg-danger text-white',
                            'emergency', 'level_2_orange', 'orange' => 'bg-warning text-dark',
                            'urgent', 'level_3_yellow', 'yellow' => 'bg-warning-subtle text-dark border border-warning',
                            default => 'bg-success text-white',
                        };
                        $triageText = match($er['triage_level']) {
                            'resuscitation', 'level_1_red', 'red' => 'إنعاش فوري (أحمر)',
                            'emergency', 'level_2_orange', 'orange' => 'حالة حرجة (برتقالي)',
                            'urgent', 'level_3_yellow', 'yellow' => 'حالة طارئة (أصفر)',
                            'semi_urgent', 'level_4_green', 'green' => 'أقل إلحاحاً (أخضر)',
                            default => e($er['triage_level']),
                        };
                        $statusText = match($er['status']) {
                            'arrived' => 'وصل حديثاً',
                            'triaged' => 'تم الفرز',
                            'under_treatment' => 'تحت العلاج السريري',
                            'discharged' => 'خرج',
                            'admitted' => 'حُوّل للتنويم',
                            default => e($er['status']),
                        };
                        $isCritical = in_array($er['triage_level'], ['resuscitation', 'level_1_red', 'red']);
                    ?>
                        <div class="p-3 border rounded-3 <?= $isCritical ? 'bg-danger bg-opacity-10 border-danger border-opacity-25' : 'bg-light' ?>">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <strong class="text-navy fs-6"><?= e($er['patient_name']) ?></strong>
                                    <span class="badge <?= $triageBadge ?> px-2 py-1"><?= $triageText ?></span>
                                    <span class="badge bg-secondary-subtle text-secondary border px-2 py-1"><?= $statusText ?></span>
                                </div>
                                <a href="<?= url('/emergency/' . $er['id']) ?>" class="btn btn-sm btn-outline-danger px-3 py-1 fw-bold d-inline-flex align-items-center gap-1 shadow-sm">
                                    <span>معاينة الحالة</span>
                                    <i data-lucide="arrow-left" style="width: 14px; height: 14px;"></i>
                                </a>
                            </div>
                            <div class="d-flex flex-wrap justify-content-between align-items-center small text-muted pt-1 border-top border-light-subtle">
                                <div>
                                    <span class="font-monospace fw-bold text-primary"><?= e($er['case_number']) ?></span>
                                    <span class="mx-2">&bull;</span>
                                    <span>الشكوى: <strong class="text-dark"><?= e($er['chief_complaint'] ?? 'طوارئ') ?></strong></span>
                                </div>
                                <div>
                                    <span>وقت الوصول: <?= date('H:i', strtotime($er['arrival_time'] ?? 'now')) ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 5. Operating Theater Schedule & Critical FEFO Alerts -->
    <div class="col-lg-7">
        <div class="card card-hms p-4 bg-white h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-navy mb-0 d-flex align-items-center gap-2">
                    <i data-lucide="scissors" class="text-secondary" style="width: 20px; height: 20px;"></i>
                    جدول العمليات الجراحية (Surgical Theater)
                </h5>
                <a href="<?= url('/surgery') ?>" class="btn btn-sm btn-outline-secondary">جدول العمليات &larr;</a>
            </div>

            <?php if (empty($recentSurgeries)): ?>
                <div class="alert alert-light text-center py-4 text-muted">لا توجد عمليات جراحية مجدولة اليوم</div>
            <?php else: ?>
                <div class="d-flex flex-column gap-2.5">
                    <?php foreach ($recentSurgeries as $surg): ?>
                        <div class="p-3 border rounded-3 bg-light bg-opacity-50">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <div>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <strong class="text-navy fs-6"><?= e($surg['procedure_name']) ?></strong>
                                        <span class="badge bg-secondary-subtle text-secondary border">
                                            <i data-lucide="door-closed" style="width: 12px; height: 12px; display:inline-block; vertical-align:text-bottom;"></i>
                                            <?= e($surg['operating_room'] ?? 'غرفة العمليات') ?>
                                        </span>
                                    </div>
                                    <div class="text-muted small mt-1">
                                        المريض: <strong class="text-dark"><?= e($surg['patient_name']) ?></strong>
                                        <span class="mx-1.5 text-secondary">&bull;</span>
                                        <span class="font-monospace text-primary"><?= e($surg['mrn']) ?></span>
                                    </div>
                                </div>
                                <div class="text-end flex-shrink-0">
                                    <?php if ($surg['status'] === 'completed'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 fw-bold">اكتملت العملية</span>
                                    <?php elseif ($surg['status'] === 'in_progress'): ?>
                                        <span class="badge bg-danger text-white px-2.5 py-1 fw-bold animate-pulse">جارية الآن</span>
                                    <?php else: ?>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 fw-bold">مجدولة</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap justify-content-between align-items-center small text-muted pt-2 border-top border-light-subtle">
                                <div class="d-flex align-items-center gap-2">
                                    <span>الجراح: <strong class="text-dark"><?= e($surg['surgeon_name'] ?? 'جراح استشاري') ?></strong></span>
                                    <span class="text-secondary">&bull;</span>
                                    <span class="font-monospace text-muted"><?= e($surg['surgery_number']) ?></span>
                                </div>
                                <div class="d-flex align-items-center gap-1.5 text-navy fw-bold">
                                    <i data-lucide="clock" style="width: 14px; height: 14px; color: var(--hms-green);"></i>
                                    <span><?= date('H:i', strtotime($surg['scheduled_start'])) ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Critical FEFO Expiry Alerts -->
    <div class="col-lg-5">
        <div class="card card-hms p-4 bg-white h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-navy mb-0 d-flex align-items-center gap-2">
                    <i data-lucide="alert-triangle" class="text-warning" style="width: 20px; height: 20px;"></i>
                    تنبيهات FEFO: أدوية تقترب صلاحيتها (أقل من 90 يوم)
                </h5>
                <a href="<?= url('/inventory') ?>" class="btn btn-sm btn-outline-warning">المخزن &larr;</a>
            </div>

            <?php if (empty($nearExpiryBatches)): ?>
                <div class="alert alert-light text-center py-4 text-muted">
                    <i data-lucide="shield-check" class="text-success d-block mx-auto mb-2" style="width: 32px; height: 32px;"></i>
                    ممتاز! كافة الأدوية في المستودعات صالحة لأكثر من 90 يوماً.
                </div>
            <?php else: ?>
                <div class="d-flex flex-column gap-2">
                    <?php foreach ($nearExpiryBatches as $batch): ?>
                        <div class="p-2 border rounded-3 d-flex justify-content-between align-items-center bg-light">
                            <div>
                                <strong class="text-navy d-block"><?= e($batch['commercial_name'] ?? $batch['trade_name'] ?? 'دواء طبي') ?></strong>
                                <small class="text-muted">تشغيلة: <?= e($batch['batch_number']) ?> | متبقي: <?= (int)$batch['quantity_on_hand'] ?> وحدة</small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-warning text-dark d-block mb-1"><?= (int)$batch['days_left'] ?> يوم متبقي</span>
                                <small class="text-secondary"><?= e($batch['expiry_date']) ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
