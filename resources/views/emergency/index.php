<div class="row g-4">
    <!-- Header -->
    <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h3 class="fw-bold text-navy mb-1 d-flex align-items-center gap-2">
                <i data-lucide="ambulance" class="text-danger" style="width: 28px; height: 28px;"></i>
                قسم الحوادث والطوارئ والفرز السريري (ER & Triage)
            </h3>
            <p class="text-muted mb-0">لوحة المراقبة اللحظية لحالات الطوارئ وتصنيف درجات الخطورة السريرية</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= url('/emergency/create') ?>" class="btn btn-danger d-inline-flex align-items-center gap-2 px-3 py-2 fw-bold shadow-sm">
                <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i>
                تسجيل حالة طوارئ جديدة
            </a>
        </div>
    </div>

    <!-- Triage Color Summary Cards -->
    <div class="col-12">
        <div class="row g-3">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm p-3 bg-danger text-white rounded-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="fs-6 opacity-75">إنعاش فوري (Red)</span>
                            <h2 class="fw-bold mb-0 mt-1"><?= $triageCounts['red'] ?></h2>
                            <small class="opacity-75">خطر مهدد للحياة فوراً</small>
                        </div>
                        <i data-lucide="alert-triangle" style="width: 40px; height: 40px; opacity: 0.8;"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 shadow-sm p-3 bg-warning text-dark rounded-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="fs-6 opacity-75">حرج عاجل (Orange)</span>
                            <h2 class="fw-bold mb-0 mt-1"><?= $triageCounts['orange'] ?></h2>
                            <small class="opacity-75">معاينة خلال 10 دقائق</small>
                        </div>
                        <i data-lucide="alert-circle" style="width: 40px; height: 40px; opacity: 0.8;"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 shadow-sm p-3 bg-info text-dark rounded-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="fs-6 opacity-75">عاجل (Yellow)</span>
                            <h2 class="fw-bold mb-0 mt-1"><?= $triageCounts['yellow'] ?></h2>
                            <small class="opacity-75">معاينة خلال 30-60 دقيقة</small>
                        </div>
                        <i data-lucide="clock" style="width: 40px; height: 40px; opacity: 0.8;"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 shadow-sm p-3 bg-success text-white rounded-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="fs-6 opacity-75">أقل إلحاحاً (Green)</span>
                            <h2 class="fw-bold mb-0 mt-1"><?= $triageCounts['green'] ?></h2>
                            <small class="opacity-75">حالات مستقرة وغير حرجة</small>
                        </div>
                        <i data-lucide="check-circle" style="width: 40px; height: 40px; opacity: 0.8;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Emergency Cases Table -->
    <div class="col-12">
        <div class="card card-hms p-0 bg-white overflow-hidden">
            <div class="card-header bg-navy text-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 d-flex align-items-center gap-2">
                    <i data-lucide="activity" style="width: 20px; height: 20px; color: #EF4444;"></i>
                    طابور حالات الطوارئ الحالية
                </h5>
                <div class="btn-group btn-group-sm">
                    <a href="<?= url('/emergency?status=active') ?>" class="btn btn-sm <?= ($status === 'active') ? 'btn-light fw-bold' : 'btn-outline-light' ?>">الحالات النشطة</a>
                    <a href="<?= url('/emergency?status=all') ?>" class="btn btn-sm <?= ($status === 'all') ? 'btn-light fw-bold' : 'btn-outline-light' ?>">جميع الحالات</a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>درجة الفرز</th>
                            <th>رقم البلاغ</th>
                            <th>المريض</th>
                            <th>طريقة الوصول</th>
                            <th>الشكوى والعلامات</th>
                            <th>الطبيب المعالج</th>
                            <th>وقت الوصول</th>
                            <th>الحالة</th>
                            <th class="text-center">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($cases)): ?>
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i data-lucide="check-check" style="width: 48px; height: 48px; margin: 0 auto 12px; color: #16856D;"></i>
                                    <h5 class="fw-bold text-navy">لا توجد حالات طوارئ نشطة حالياً</h5>
                                    <p class="mb-0">قسم الطوارئ مستقر وجاهز لاستقبال أي بلاغات جديدة.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($cases as $c): ?>
                                <?php
                                    $triageBadge = match($c['triage_level']) {
                                        'red' => 'bg-danger text-white animate-pulse',
                                        'orange' => 'bg-warning text-dark',
                                        'yellow' => 'bg-info text-dark',
                                        'green' => 'bg-success text-white',
                                        default => 'bg-secondary text-white'
                                    };
                                    $arrivalBadge = match($c['arrival_mode']) {
                                        'ambulance' => '🚑 إسعاف',
                                        'police' => '🚓 شرطة',
                                        'transfer' => '🔄 تحويل',
                                        default => '🚶 مشياً (Walk-in)'
                                    };
                                ?>
                                <tr>
                                    <td>
                                        <span class="badge <?= $triageBadge ?> px-3 py-2 fs-6 text-uppercase fw-bold">
                                            <?= strtoupper($c['triage_level']) ?>
                                        </span>
                                    </td>
                                    <td class="fw-bold font-monospace text-navy">
                                        <a href="<?= url("/emergency/{$c['id']}") ?>" class="text-decoration-none">
                                            <?= e($c['case_number']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?php if ($c['registered_patient_name']): ?>
                                            <div class="fw-bold text-navy"><?= e($c['registered_patient_name']) ?></div>
                                            <small class="text-muted">MRN: <?= e($c['mrn']) ?> | <?= $c['patient_age'] ?> سنة</small>
                                        <?php else: ?>
                                            <div class="fw-bold text-danger"><?= e($c['temporary_patient_name'] ?? 'مجهول الهوية') ?></div>
                                            <small class="badge bg-secondary">حالة غير مسجلة</small>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge bg-light text-dark border"><?= $arrivalBadge ?></span></td>
                                    <td>
                                        <div class="text-truncate" style="max-width: 240px;" title="<?= e($c['chief_complaint']) ?>">
                                            <?= e($c['chief_complaint']) ?>
                                        </div>
                                        <?php if (!empty($c['systolic_bp'])): ?>
                                            <small class="text-muted">
                                                ضغط: <?= $c['systolic_bp'] ?>/<?= $c['diastolic_bp'] ?> | نبض: <?= $c['pulse_rate'] ?> | SpO2: <?= $c['oxygen_saturation'] ?>%
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= e($c['doctor_name'] ?? 'لم يحدد بعد') ?></td>
                                    <td class="small text-muted"><?= date('H:i d/m', strtotime($c['arrival_time'])) ?></td>
                                    <td>
                                        <?php
                                            $stClass = match($c['status']) {
                                                'triaged' => 'bg-secondary',
                                                'under_treatment' => 'bg-primary',
                                                'admitted' => 'bg-info text-dark',
                                                'discharged' => 'bg-success',
                                                'deceased' => 'bg-dark',
                                                default => 'bg-secondary'
                                            };
                                        ?>
                                        <span class="badge <?= $stClass ?>"><?= e($c['status']) ?></span>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= url("/emergency/{$c['id']}") ?>" class="btn btn-sm btn-outline-navy">
                                            معاينة الملف
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
