<div class="row g-4">
    <!-- Header -->
    <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h3 class="fw-bold text-navy mb-1 d-flex align-items-center gap-2">
                <i data-lucide="clipboard-check" class="text-green" style="width: 28px; height: 28px;"></i>
                محطة التمريض السريرية وجدول إعطاء الأدوية (Nursing Station & MAR)
            </h3>
            <p class="text-muted mb-0">مراقبة المرضى المنومين بالأجنحة، إعطاء الجرعات الدوائية في مواعيدها وتسليم الورديات</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= url('/admissions') ?>" class="btn btn-outline-navy d-inline-flex align-items-center gap-2 px-3 py-2 fw-bold">
                <i data-lucide="bed-double" style="width: 18px; height: 18px;"></i>
                إدارة التنويم IPD
            </a>
            <a href="<?= url('/beds') ?>" class="btn btn-primary d-inline-flex align-items-center gap-2 px-3 py-2 fw-bold">
                <i data-lucide="layout-grid" style="width: 18px; height: 18px;"></i>
                خريطة الأسرة الحية
            </a>
        </div>
    </div>

    <!-- Central MAR Schedule (Today's Pending Doses) -->
    <div class="col-12">
        <div class="card card-hms p-0 bg-white overflow-hidden shadow-sm">
            <div class="card-header bg-danger text-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 d-flex align-items-center gap-2">
                    <i data-lucide="pill" style="width: 20px; height: 20px;"></i>
                    جدول الجرعات الدوائية المستحقة للوردية الحالية (Active MAR Schedule)
                </h5>
                <span class="badge bg-white text-danger fs-6 fw-bold"><?= count($pendingMar) ?> جرعة مستحقة</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>الوقت المحدد</th>
                            <th>المريض</th>
                            <th>السرير والجناح</th>
                            <th>الدواء الموصوف والجرعة</th>
                            <th>طريقة الإعطاء</th>
                            <th>ملاحظات</th>
                            <th class="text-center">إجراء الإعطاء (Nurse Action)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pendingMar)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i data-lucide="check-circle" style="width: 48px; height: 48px; margin: 0 auto 12px; color: #16856D;"></i>
                                    <h5 class="fw-bold text-navy">لا توجد جرعات دوائية معلقة حالياً</h5>
                                    <p class="mb-0">تم إعطاء جميع الأدوية المجدولة للمرضى المنومين بنجاح.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($pendingMar as $mar): ?>
                                <tr>
                                    <td class="fw-bold font-monospace text-danger">
                                        ⏰ <?= date('H:i', strtotime($mar['scheduled_time'])) ?>
                                        <small class="d-block text-muted"><?= date('d/m/Y', strtotime($mar['scheduled_time'])) ?></small>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-navy"><?= e($mar['patient_name']) ?></div>
                                        <small class="text-muted">MRN: <?= e($mar['mrn']) ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary fs-6">سرير: <?= e($mar['bed_number'] ?? '-') ?></span>
                                        <small class="d-block text-muted">غرفة: <?= e($mar['room_number'] ?? '-') ?> (<?= e($mar['ward_name'] ?? '') ?>)</small>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark fs-6"><?= e($mar['medicine_name']) ?></div>
                                        <span class="text-muted small">الجرعة: <?= e($mar['dosage']) ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border"><?= strtoupper(e($mar['route'])) ?></span>
                                    </td>
                                    <td class="small text-muted"><?= e($mar['notes'] ?? '-') ?></td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <form action="<?= url("/nursing/mar/{$mar['id']}") ?>" method="POST" class="d-inline">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="status" value="given">
                                                <button type="submit" class="btn btn-sm btn-success fw-bold px-3 d-inline-flex align-items-center gap-1">
                                                    <i data-lucide="check" style="width: 16px; height: 16px;"></i>
                                                    تم الإعطاء
                                                </button>
                                            </form>
                                            <form action="<?= url("/nursing/mar/{$mar['id']}") ?>" method="POST" class="d-inline">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="status" value="held">
                                                <button type="submit" class="btn btn-sm btn-outline-secondary" title="تأجيل أو تعليق الجرعة">
                                                    تعليق
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Currently Admitted Patients Overview -->
    <div class="col-12">
        <div class="card card-hms p-0 bg-white overflow-hidden shadow-sm">
            <div class="card-header bg-navy text-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 d-flex align-items-center gap-2">
                    <i data-lucide="users" style="width: 20px; height: 20px; color: #6EE7B7;"></i>
                    المرضى المنومين في الأجنحة حالياً
                </h5>
                <span class="badge bg-green fs-6"><?= count($inpatients) ?> مريض منوم</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>رقم السرير</th>
                            <th>المريض</th>
                            <th>الرقم الطبي</th>
                            <th>الجناح والغرفة</th>
                            <th>القسم</th>
                            <th>تاريخ الدخول</th>
                            <th class="text-center">الملف التمريضي</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($inpatients)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">لا يوجد مرضى منومين حالياً</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($inpatients as $inp): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-navy fs-6 px-3 py-1">سرير <?= e($inp['bed_number']) ?></span>
                                    </td>
                                    <td class="fw-bold text-navy">
                                        <a href="<?= url("/admissions/{$inp['id']}") ?>" class="text-decoration-none">
                                            <?= e($inp['patient_name']) ?>
                                        </a>
                                        <small class="d-block text-muted"><?= $inp['patient_age'] ?> سنة | <?= $inp['gender'] === 'male' ? 'ذكر' : 'أنثى' ?></small>
                                    </td>
                                    <td><code><?= e($inp['mrn']) ?></code></td>
                                    <td>غرفة <?= e($inp['room_number']) ?> (<?= e($inp['ward_name']) ?>)</td>
                                    <td><span class="badge bg-light text-dark border"><?= e($inp['department_name']) ?></span></td>
                                    <td class="small text-muted"><?= date('d/m/Y H:i', strtotime($inp['admission_date'])) ?></td>
                                    <td class="text-center">
                                        <a href="<?= url("/admissions/{$inp['id']}") ?>" class="btn btn-sm btn-outline-primary fw-bold">
                                            فتح ملف التنويم و MAR
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
