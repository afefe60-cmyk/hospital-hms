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
    <!-- 1. Executive Hospital Header Banner -->
    <div class="col-12">
        <div class="card card-hms p-4 text-white" style="background: linear-gradient(135deg, #10293D 0%, #173B59 60%, #16856D 100%);">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-3 bg-white bg-opacity-10 rounded-3 border border-white border-opacity-25 d-none d-sm-block">
                        <i data-lucide="hospital" style="width: 42px; height: 42px; color: #6EE7B7;"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h2 class="fw-bold mb-0 text-white"><?= e($hospital['name_ar']) ?></h2>
                            <span class="badge bg-green px-2 py-1 fs-7">نظام تشغيلي متكامل</span>
                            <span class="badge bg-accent px-2 py-1 fs-7">الخرطوم، السودان</span>
                        </div>
                        <div class="text-light opacity-75 mt-1 small d-flex flex-wrap align-items-center gap-3">
                            <span><i data-lucide="calendar" style="width: 14px; height: 14px; display:inline-block; vertical-align:text-bottom;"></i> <?= date('Y-m-d') ?> | <?= date('l') ?></span>
                            <span><i data-lucide="phone-call" style="width: 14px; height: 14px; display:inline-block; vertical-align:text-bottom; color: #F87171;"></i> طوارئ: <strong><?= e($hospital['emergency']) ?></strong></span>
                            <span><i data-lucide="landmark" style="width: 14px; height: 14px; display:inline-block; vertical-align:text-bottom; color: #34D399;"></i> حساب بنكك المعتمد: <strong class="badge bg-white text-navy"><?= e($hospital['bankak_account']) ?></strong></span>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-sm btn-light d-flex align-items-center gap-2 shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#quickSearchModal">
                        <i data-lucide="search" style="width: 16px; height: 16px;"></i>
                        <span>بحث سريع عن مريض</span>
                        <kbd class="bg-secondary text-white px-1 rounded small">Ctrl+K</kbd>
                    </button>
                    <a href="<?= url('/reports') ?>" class="btn btn-sm btn-outline-light d-flex align-items-center gap-1">
                        <i data-lucide="bar-chart-2" style="width: 16px; height: 16px;"></i>
                        <span>التقارير الشاملة</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Quick Action Launchpad (أزرار الإجراءات السريعة) -->
    <div class="col-12">
        <div class="card card-hms p-3 bg-white">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="fw-bold text-navy small d-flex align-items-center gap-1">
                    <i data-lucide="zap" class="text-accent" style="width: 16px; height: 16px;"></i>
                    مسارات الوصول السريع والعمليات المباشرة (Quick Launchpad)
                </span>
                <span class="text-muted small">كل العمليات مرتبطة بلحظتها</span>
            </div>
            <div class="row g-2">
                <div class="col-6 col-md-3 col-xl">
                    <a href="<?= url('/patients/create') ?>" class="btn btn-outline-primary w-100 py-2 d-flex flex-column align-items-center justify-content-center gap-1 h-100">
                        <i data-lucide="user-plus" style="width: 20px; height: 20px;"></i>
                        <span class="small fw-bold">فتح ملف مريض</span>
                    </a>
                </div>
                <div class="col-6 col-md-3 col-xl">
                    <a href="<?= url('/emergency/create') ?>" class="btn btn-outline-danger w-100 py-2 d-flex flex-column align-items-center justify-content-center gap-1 h-100">
                        <i data-lucide="ambulance" style="width: 20px; height: 20px;"></i>
                        <span class="small fw-bold">استقبال طوارئ ER</span>
                    </a>
                </div>
                <div class="col-6 col-md-3 col-xl">
                    <a href="<?= url('/emr') ?>" class="btn btn-outline-success w-100 py-2 d-flex flex-column align-items-center justify-content-center gap-1 h-100">
                        <i data-lucide="stethoscope" style="width: 20px; height: 20px;"></i>
                        <span class="small fw-bold">عيادة الطبيب EMR</span>
                    </a>
                </div>
                <div class="col-6 col-md-3 col-xl">
                    <a href="<?= url('/admissions/create') ?>" class="btn btn-outline-info w-100 py-2 d-flex flex-column align-items-center justify-content-center gap-1 h-100">
                        <i data-lucide="bed" style="width: 20px; height: 20px;"></i>
                        <span class="small fw-bold">تنويم سريري IPD</span>
                    </a>
                </div>
                <div class="col-6 col-md-3 col-xl">
                    <a href="<?= url('/surgery/create') ?>" class="btn btn-outline-secondary w-100 py-2 d-flex flex-column align-items-center justify-content-center gap-1 h-100">
                        <i data-lucide="scissors" style="width: 20px; height: 20px;"></i>
                        <span class="small fw-bold">جدولة عملية OR</span>
                    </a>
                </div>
                <div class="col-6 col-md-3 col-xl">
                    <a href="<?= url('/lab/create') ?>" class="btn btn-outline-warning w-100 py-2 d-flex flex-column align-items-center justify-content-center gap-1 h-100">
                        <i data-lucide="test-tube" style="width: 20px; height: 20px;"></i>
                        <span class="small fw-bold">طلب فحص LIS</span>
                    </a>
                </div>
                <div class="col-6 col-md-3 col-xl">
                    <a href="<?= url('/pharmacy') ?>" class="btn btn-outline-success w-100 py-2 d-flex flex-column align-items-center justify-content-center gap-1 h-100">
                        <i data-lucide="pill" style="width: 20px; height: 20px;"></i>
                        <span class="small fw-bold">صرف صيدلية</span>
                    </a>
                </div>
                <div class="col-6 col-md-3 col-xl">
                    <a href="<?= url('/billing/invoices/create') ?>" class="btn btn-outline-dark w-100 py-2 d-flex flex-column align-items-center justify-content-center gap-1 h-100">
                        <i data-lucide="receipt" style="width: 20px; height: 20px;"></i>
                        <span class="small fw-bold">فاتورة / سند قبض</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Key Operational KPI Cards Strip -->
    <div class="col-sm-6 col-lg-4 col-xl-2">
        <div class="card card-hms p-3 bg-white h-100 border-start border-4 border-primary">
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
        <div class="card card-hms p-3 bg-white h-100 border-start border-4 border-info">
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
        <div class="card card-hms p-3 bg-white h-100 border-start border-4 <?= $metrics['bed_occupancy_rate'] > 85 ? 'border-danger' : 'border-success' ?>">
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
        <div class="card card-hms p-3 bg-white h-100 border-start border-4 border-danger">
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
        <div class="card card-hms p-3 bg-white h-100 border-start border-4 border-secondary">
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
        <div class="card card-hms p-3 bg-white h-100 border-start border-4 border-warning">
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
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>رقم البلاغ</th>
                                <th>اسم المريض</th>
                                <th>درجة الفرز</th>
                                <th>الشكوى الأساسية</th>
                                <th>الحالة</th>
                                <th>إجراء</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activeErCases as $er): 
                                $triageBadge = match($er['triage_level']) {
                                    'resuscitation', 'level_1_red', 'red' => 'bg-danger text-white',
                                    'emergency', 'level_2_orange', 'orange' => 'bg-warning text-dark',
                                    'urgent', 'level_3_yellow', 'yellow' => 'bg-warning-subtle text-dark border border-warning',
                                    default => 'bg-success text-white',
                                };
                            ?>
                                <tr>
                                    <td class="fw-bold text-navy"><?= e($er['case_number']) ?></td>
                                    <td><?= e($er['patient_name']) ?></td>
                                    <td><span class="badge <?= $triageBadge ?>"><?= e($er['triage_level']) ?></span></td>
                                    <td class="small text-truncate" style="max-width: 150px;"><?= e($er['chief_complaint'] ?? 'طوارئ') ?></td>
                                    <td><span class="badge bg-secondary"><?= e($er['status']) ?></span></td>
                                    <td>
                                        <a href="<?= url('/emergency/' . $er['id']) ?>" class="btn btn-sm btn-outline-primary py-0 px-2">معاينة</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
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
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>رقم العملية</th>
                                <th>المريض</th>
                                <th>الإجراء الجراحي</th>
                                <th>الجراح الرئيسي</th>
                                <th>الوقت المجدول</th>
                                <th>الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentSurgeries as $surg): ?>
                                <tr>
                                    <td class="fw-bold text-navy"><?= e($surg['surgery_number']) ?></td>
                                    <td>
                                        <span class="fw-semibold d-block"><?= e($surg['patient_name']) ?></span>
                                        <small class="text-muted"><?= e($surg['mrn']) ?></small>
                                    </td>
                                    <td>
                                        <span class="d-block text-truncate" style="max-width: 160px;"><?= e($surg['procedure_name']) ?></span>
                                        <small class="text-secondary"><?= e($surg['operating_room'] ?? 'غرفة 1') ?></small>
                                    </td>
                                    <td class="small"><?= e($surg['surgeon_name'] ?? 'جراح استشاري') ?></td>
                                    <td class="small text-nowrap"><?= date('H:i', strtotime($surg['scheduled_start'])) ?></td>
                                    <td>
                                        <?php if ($surg['status'] === 'completed'): ?>
                                            <span class="badge bg-success">اكتملت</span>
                                        <?php elseif ($surg['status'] === 'in_progress'): ?>
                                            <span class="badge bg-danger animate-pulse">جارية الآن</span>
                                        <?php else: ?>
                                            <span class="badge bg-primary">مجدولة</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
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

    <!-- 6. Bottom System Architecture & Sudan Operational Status Bar -->
    <div class="col-12">
        <div class="card card-hms p-3 bg-white">
            <div class="row align-items-center text-center text-md-start g-2">
                <div class="col-md-4">
                    <span class="text-muted small">المحرك المعماري:</span>
                    <strong class="text-navy ms-1">Native Pure PHP <?= PHP_VERSION ?> MVC</strong>
                    <span class="badge bg-success-subtle text-success ms-1">جاهزية 100%</span>
                </div>
                <div class="col-md-4 text-center">
                    <span class="text-muted small">العملة المعتمدة:</span>
                    <strong class="text-navy ms-1">الجنيه السوداني (SDG)</strong>
                    <span class="badge bg-info-subtle text-info ms-1">بنكك مفعّل</span>
                </div>
                <div class="col-md-4 text-md-end">
                    <span class="text-muted small">قاعدة البيانات:</span>
                    <strong class="text-navy ms-1">MySQL 8 InnoDB</strong>
                    <span class="badge bg-primary-subtle text-primary ms-1">Atomic Transactions</span>
                </div>
            </div>
        </div>
    </div>
</div>
