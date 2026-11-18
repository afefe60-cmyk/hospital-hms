<div class="row g-4">
    <!-- Header -->
    <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h3 class="fw-bold text-navy mb-1 d-flex align-items-center gap-2">
                <i data-lucide="bed-double" class="text-primary" style="width: 28px; height: 28px;"></i>
                إدارة التنويم والأقسام الداخلية (Inpatient Department - IPD)
            </h3>
            <p class="text-muted mb-0">متابعة المرضى المنومين، تخصيص الأسرة، ومراقبة مسارات العلاج والتمريض</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= url('/beds') ?>" class="btn btn-outline-navy d-inline-flex align-items-center gap-2 px-3 py-2 fw-bold">
                <i data-lucide="layout-grid" style="width: 18px; height: 18px;"></i>
                خريطة الأسرة الحية
            </a>
            <a href="<?= url('/admissions/create') ?>" class="btn btn-primary d-inline-flex align-items-center gap-2 px-3 py-2 fw-bold shadow-sm">
                <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i>
                تسجيل إذن تنويم جديد
            </a>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="col-12">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="card card-hms p-3 bg-white border-primary shadow-sm">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">المرضى المنومين حالياً</span>
                            <h2 class="fw-bold text-primary mb-0 mt-1"><?= $counts['admitted'] ?></h2>
                        </div>
                        <i data-lucide="bed" style="width: 38px; height: 38px; color: #173B59;"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card card-hms p-3 bg-white border-success shadow-sm">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">حالات الخروج المكتملة</span>
                            <h2 class="fw-bold text-success mb-0 mt-1"><?= $counts['discharged'] ?></h2>
                        </div>
                        <i data-lucide="check-circle-2" style="width: 38px; height: 38px; color: #16856D;"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card card-hms p-3 bg-white shadow-sm">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">إجمالي سجلات التنويم</span>
                            <h2 class="fw-bold text-navy mb-0 mt-1"><?= $counts['total'] ?></h2>
                        </div>
                        <i data-lucide="folder-clock" style="width: 38px; height: 38px; color: #64748B;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Admissions Table -->
    <div class="col-12">
        <div class="card card-hms p-0 bg-white overflow-hidden shadow-sm">
            <div class="card-header bg-navy text-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 d-flex align-items-center gap-2">
                    <i data-lucide="list" style="width: 20px; height: 20px; color: #6EE7B7;"></i>
                    سجلات المرضى المنومين
                </h5>
                <div class="btn-group btn-group-sm">
                    <a href="<?= url('/admissions?status=admitted') ?>" class="btn btn-sm <?= ($status === 'admitted') ? 'btn-light fw-bold' : 'btn-outline-light' ?>">المنومين حالياً</a>
                    <a href="<?= url('/admissions?status=discharged') ?>" class="btn btn-sm <?= ($status === 'discharged') ? 'btn-light fw-bold' : 'btn-outline-light' ?>">تم خروجهم</a>
                    <a href="<?= url('/admissions?status=all') ?>" class="btn btn-sm <?= ($status === 'all') ? 'btn-light fw-bold' : 'btn-outline-light' ?>">الكل</a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>رقم التنويم</th>
                            <th>المريض</th>
                            <th>القسم الطبي</th>
                            <th>مكان السرير</th>
                            <th>الطبيب المعالج</th>
                            <th>تاريخ الدخول</th>
                            <th>الحالة</th>
                            <th class="text-center">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($admissions)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i data-lucide="bed-single" style="width: 48px; height: 48px; margin: 0 auto 12px; color: #64748B;"></i>
                                    <h5 class="fw-bold text-navy">لا توجد سجلات تنويم مطابقة للفلاتر المختارة</h5>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($admissions as $adm): ?>
                                <tr>
                                    <td class="fw-bold font-monospace text-navy">
                                        <a href="<?= url("/admissions/{$adm['id']}") ?>" class="text-decoration-none">
                                            <?= e($adm['admission_number']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-navy"><?= e($adm['patient_name']) ?></div>
                                        <small class="text-muted">MRN: <?= e($adm['mrn']) ?> | <?= $adm['patient_age'] ?> سنة</small>
                                    </td>
                                    <td><span class="badge bg-light text-dark border"><?= e($adm['department_name']) ?></span></td>
                                    <td>
                                        <?php if ($adm['bed_number']): ?>
                                            <div class="fw-bold text-primary">سرير: <?= e($adm['bed_number']) ?></div>
                                            <small class="text-muted">غرفة: <?= e($adm['room_number']) ?> (<?= e($adm['ward_name']) ?>)</small>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">غير مسكن بسرير</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>د. <?= e($adm['doctor_name']) ?></td>
                                    <td class="small text-muted"><?= date('d/m/Y H:i', strtotime($adm['admission_date'])) ?></td>
                                    <td>
                                        <?php
                                            $stBadge = match($adm['status']) {
                                                'admitted' => 'bg-success',
                                                'discharged' => 'bg-secondary',
                                                'deceased' => 'bg-dark',
                                                default => 'bg-info'
                                            };
                                        ?>
                                        <span class="badge <?= $stBadge ?>"><?= e($adm['status']) ?></span>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= url("/admissions/{$adm['id']}") ?>" class="btn btn-sm btn-outline-navy">
                                            الملف السريري
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
