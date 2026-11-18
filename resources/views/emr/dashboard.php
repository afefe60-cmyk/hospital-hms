<div class="row g-4">
    <!-- Header -->
    <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h3 class="fw-bold text-navy mb-1 d-flex align-items-center gap-2">
                <i data-lucide="stethoscope" class="text-green" style="width: 28px; height: 28px;"></i>
                محطة الطبيب السريرية والسجل الطبي (Doctor EMR Station)
            </h3>
            <p class="text-muted mb-0">معاينة المرضى في طابور الانتظار، فحص العلامات الحيوية، والتشخيص وكتابة الوصفات</p>
        </div>
    </div>

    <!-- Waiting Patients Queue Table -->
    <div class="col-12">
        <div class="card card-hms p-0 bg-white overflow-hidden">
            <div class="card-header bg-navy text-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 d-flex align-items-center gap-2">
                    <i data-lucide="user-check" style="width: 20px; height: 20px; color: #6EE7B7;"></i>
                    طابور المرضى المسجلين للكشف اليوم (<?= date('Y-m-d') ?>)
                </h5>
                <span class="badge bg-green fs-6"><?= count($waitingPatients) ?> مريض مسجل</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>رقم الدور</th>
                            <th>المريض</th>
                            <th>الرقم الطبي (MRN)</th>
                            <th>العمر / النوع</th>
                            <th>فصيلة الدم</th>
                            <th>القسم الطبي</th>
                            <th>حالة الانتظار</th>
                            <th class="text-center">إجراء المعاينة</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($waitingPatients)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i data-lucide="check-circle" style="width: 48px; height: 48px; margin: 0 auto 12px; color: #16856D;"></i>
                                    <h5 class="fw-bold text-navy">لا يوجد مرضى في قائمة الانتظار حالياً</h5>
                                    <p class="mb-0">سيتم ظهور المرضى هنا فور تسجيل وصولهم (Check-In) من مكتب الاستقبال.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($waitingPatients as $p): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-navy fs-5 px-3 py-2">
                                            #<?= $p['queue_number'] ?? '-' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-navy fs-6"><?= e($p['patient_name']) ?></div>
                                        <small class="text-muted">رقم الزيارة: <code><?= e($p['visit_number']) ?></code></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-navy font-monospace border"><?= e($p['mrn']) ?></span>
                                    </td>
                                    <td>
                                        <?= $p['patient_age'] ?> سنة
                                        <span class="badge <?= $p['gender'] === 'male' ? 'bg-primary-subtle text-primary' : 'bg-danger-subtle text-danger' ?> ms-1">
                                            <?= $p['gender'] === 'male' ? 'ذكر' : 'أنثى' ?>
                                        </span>
                                    </td>
                                    <td><span class="badge bg-danger"><?= e($p['blood_group']) ?></span></td>
                                    <td><?= e($p['department_name']) ?></td>
                                    <td>
                                        <?php if (!empty($p['active_encounter_id'])): ?>
                                            <span class="badge bg-warning text-dark border p-2">قيد المعاينة الآن</span>
                                        <?php else: ?>
                                            <span class="badge bg-info-subtle text-info border p-2">في الانتظار</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if (!empty($p['active_encounter_id'])): ?>
                                            <a href="<?= url("/emr/encounter/{$p['active_encounter_id']}") ?>" class="btn btn-warning btn-sm fw-bold d-inline-flex align-items-center gap-1">
                                                <i data-lucide="play" style="width: 14px; height: 14px;"></i>
                                                متابعة الكشف
                                            </a>
                                        <?php else: ?>
                                            <form action="<?= url("/emr/visit/{$p['id']}/start") ?>" method="POST" style="display:inline;">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-hms-primary btn-sm d-inline-flex align-items-center gap-1">
                                                    <i data-lucide="stethoscope" style="width: 14px; height: 14px;"></i>
                                                    بدء الكشف السريري
                                                </button>
                                            </form>
                                        <?php endif; ?>
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
