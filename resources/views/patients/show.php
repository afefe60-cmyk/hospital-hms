<div class="row g-4">
    <!-- Patient Header Card -->
    <div class="col-12">
        <div class="card card-hms p-4 bg-white">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-3 rounded-circle <?= $patient['gender'] === 'male' ? 'bg-primary-subtle text-primary' : 'bg-danger-subtle text-danger' ?>">
                        <i data-lucide="user" style="width: 36px; height: 36px;"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h3 class="fw-bold text-navy mb-0">
                                <?= e($patient['first_name'] . ' ' . $patient['second_name'] . ' ' . $patient['third_name'] . ' ' . $patient['last_name']) ?>
                            </h3>
                            <span class="badge bg-navy font-monospace fs-6 px-3 py-1"><?= e($patient['mrn']) ?></span>
                        </div>
                        <div class="text-muted mt-1">
                            <span class="me-3"><i data-lucide="calendar" style="width: 14px; height: 14px; display:inline-block;"></i> تاريخ الميلاد: <?= e($patient['date_of_birth']) ?></span>
                            <span class="me-3"><i data-lucide="phone" style="width: 14px; height: 14px; display:inline-block;"></i> <?= e($patient['phone_primary']) ?></span>
                            <span><i data-lucide="map-pin" style="width: 14px; height: 14px; display:inline-block;"></i> <?= e($patient['address_state']) ?> - <?= e($patient['address_city']) ?></span>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <a href="<?= url("/appointments/create?patient_id={$patient['id']}") ?>" class="btn btn-hms-primary d-inline-flex align-items-center gap-2">
                        <i data-lucide="calendar-plus" style="width: 18px; height: 18px;"></i>
                        حجز موعد
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- CRITICAL CLINICAL SAFETY: Allergies Red Alert Banner -->
    <div class="col-12">
        <div class="card border-0 shadow-sm p-3 <?= !empty($allergies) ? 'bg-danger-subtle border-start border-danger border-5' : 'bg-success-subtle border-start border-success border-5' ?>">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="d-flex align-items-center gap-2">
                    <i data-lucide="<?= !empty($allergies) ? 'alert-triangle' : 'shield-check' ?>" style="width: 24px; height: 24px;" class="<?= !empty($allergies) ? 'text-danger' : 'text-success' ?>"></i>
                    <h5 class="fw-bold mb-0 <?= !empty($allergies) ? 'text-danger' : 'text-success' ?>">
                        <?= !empty($allergies) ? 'تحذير طبي حرج: توجد تحسسات مسجلة لهذا المريض' : 'لا يوجد تحسس طبي مسجل لهذا المريض' ?>
                    </h5>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#addAllergyModal">
                    <i data-lucide="plus" style="width: 14px; height: 14px; display:inline-block; vertical-align:text-bottom;"></i>
                    إضافة حساسية
                </button>
            </div>

            <?php if (!empty($allergies)): ?>
                <div class="d-flex flex-wrap gap-2 mt-3">
                    <?php foreach ($allergies as $alg): ?>
                        <?php
                        $badgeClass = match($alg['severity']) {
                            'life_threatening' => 'bg-danger text-white border-white',
                            'severe' => 'bg-danger-subtle text-danger border border-danger',
                            'moderate' => 'bg-warning-subtle text-warning-emphasis border border-warning',
                            default => 'bg-light text-dark border',
                        };
                        ?>
                        <span class="badge <?= $badgeClass ?> p-2 fs-6 d-inline-flex align-items-center gap-2">
                            <strong><?= e($alg['allergen_name']) ?></strong>
                            <small>(<?= e($alg['severity']) ?>)</small>
                            <?php if (!empty($alg['reaction_notes'])): ?>
                                <small class="opacity-75"> - <?= e($alg['reaction_notes']) ?></small>
                            <?php endif; ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Demographics & Chronic Conditions Columns -->
    <div class="col-lg-4">
        <!-- Demographics Details -->
        <div class="card card-hms p-4 bg-white mb-4">
            <h5 class="fw-bold text-navy border-bottom pb-2 mb-3 d-flex align-items-center gap-2">
                <i data-lucide="id-card" class="text-green" style="width: 20px; height: 20px;"></i>
                البيانات الثبوتية والديموغرافية
            </h5>
            <ul class="list-group list-group-flush small">
                <li class="list-group-item d-flex justify-content-between px-0">
                    <span class="text-muted">الرقم الوطني:</span>
                    <span class="font-monospace fw-bold"><?= e($patient['national_id'] ?? 'غير متوفر') ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between px-0">
                    <span class="text-muted">فصيلة الدم:</span>
                    <span class="badge bg-danger"><?= e($patient['blood_group']) ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between px-0">
                    <span class="text-muted">الحالة الاجتماعية:</span>
                    <span class="fw-semibold"><?= e($patient['marital_status'] ?? 'غير محدد') ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between px-0">
                    <span class="text-muted">هاتف إضافي:</span>
                    <span><?= e($patient['phone_secondary'] ?? 'غير متوفر') ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between px-0">
                    <span class="text-muted">جهة الطوارئ:</span>
                    <span class="fw-semibold"><?= e($patient['emergency_contact_name']) ?> (<?= e($patient['emergency_contact_relation']) ?>)</span>
                </li>
                <li class="list-group-item d-flex justify-content-between px-0">
                    <span class="text-muted">هاتف الطوارئ:</span>
                    <span class="fw-bold text-navy"><?= e($patient['emergency_contact_phone']) ?></span>
                </li>
            </ul>
        </div>

        <!-- Chronic Conditions -->
        <div class="card card-hms p-4 bg-white">
            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                <h5 class="fw-bold text-navy mb-0 d-flex align-items-center gap-2">
                    <i data-lucide="heart-pulse" class="text-accent" style="width: 20px; height: 20px;"></i>
                    الأمراض المزمنة
                </h5>
                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addConditionModal">
                    <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
                </button>
            </div>

            <?php if (empty($conditions)): ?>
                <p class="text-muted small text-center py-3 mb-0">لا توجد أمراض مزمنة موثقة في الملف.</p>
            <?php else: ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($conditions as $cond): ?>
                        <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-semibold d-block"><?= e($cond['condition_name']) ?></span>
                                <?php if (!empty($cond['icd10_code'])): ?>
                                    <span class="badge bg-light text-muted border font-monospace">ICD-10: <?= e($cond['icd10_code']) ?></span>
                                <?php endif; ?>
                            </div>
                            <span class="badge bg-warning-subtle text-warning border">مزمن</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    <!-- Clinical History: Appointments & Visits -->
    <div class="col-lg-8">
        <!-- Visits History -->
        <div class="card card-hms p-4 bg-white mb-4">
            <h5 class="fw-bold text-navy border-bottom pb-2 mb-3 d-flex align-items-center gap-2">
                <i data-lucide="activity" class="text-green" style="width: 20px; height: 20px;"></i>
                سجل الزيارات الطبية (Visits Timeline)
            </h5>

            <?php if (empty($visits)): ?>
                <p class="text-muted text-center py-4 mb-0">لا توجد زيارات طبية سابقة لهذا المريض.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>رقم الزيارة</th>
                                <th>المسار</th>
                                <th>القسم</th>
                                <th>الطبيب المعالج</th>
                                <th>وقت الوصول</th>
                                <th>الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($visits as $v): ?>
                                <tr>
                                    <td><code><?= e($v['visit_number']) ?></code></td>
                                    <td><span class="badge bg-navy text-uppercase"><?= e($v['visit_type']) ?></span></td>
                                    <td><?= e($v['department_name']) ?></td>
                                    <td><?= e($v['doctor_name'] ?? 'طوارئ / مناوب') ?></td>
                                    <td><small class="text-muted"><?= e($v['arrival_time']) ?></small></td>
                                    <td><span class="badge bg-success-subtle text-success"><?= e($v['status']) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Appointments History -->
        <div class="card card-hms p-4 bg-white">
            <h5 class="fw-bold text-navy border-bottom pb-2 mb-3 d-flex align-items-center gap-2">
                <i data-lucide="calendar" class="text-primary" style="width: 20px; height: 20px;"></i>
                مواعيد وحجوزات العيادات
            </h5>

            <?php if (empty($appointments)): ?>
                <p class="text-muted text-center py-4 mb-0">لا توجد مواعيد مسجلة.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>تاريخ الموعد</th>
                                <th>الوقت</th>
                                <th>العيادة / الغرفة</th>
                                <th>الطبيب</th>
                                <th>رقم الدور</th>
                                <th>الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($appointments as $app): ?>
                                <tr>
                                    <td class="fw-bold"><?= e($app['appointment_date']) ?></td>
                                    <td><?= e($app['appointment_time']) ?></td>
                                    <td><?= e($app['clinic_name']) ?> (غرفة <?= e($app['room_number']) ?>)</td>
                                    <td>د. <?= e($app['doctor_name']) ?></td>
                                    <td><span class="badge bg-primary">#<?= $app['token_number'] ?></span></td>
                                    <td><span class="badge bg-light text-dark border"><?= e($app['status']) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal: Add Allergy -->
<div class="modal fade" id="addAllergyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= url("/patients/{$patient['id']}/allergies") ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold">إضافة تحسس طبي حرج (Allergy)</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">اسم الدواء أو المسبب <span class="text-danger">*</span></label>
                        <input type="text" name="allergen_name" class="form-control" placeholder="مثال: Penicillin, Aspirin, Sulfa, بيض..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">نوع الحساسية</label>
                        <select name="allergen_type" class="form-select">
                            <option value="drug">دوائية (Drug Allergy)</option>
                            <option value="food">غذائية (Food Allergy)</option>
                            <option value="environmental">بيئية (Environmental)</option>
                            <option value="other">أخرى (Other)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">درجة الخطورة <span class="text-danger">*</span></label>
                        <select name="severity" class="form-select" required>
                            <option value="life_threatening" class="text-danger fw-bold">مهددة للحياة (Life-Threatening)</option>
                            <option value="severe" selected>شديدة (Severe)</option>
                            <option value="moderate">متوسطة (Moderate)</option>
                            <option value="mild">خفيفة (Mild)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">الأعراض السريرية السابقة</label>
                        <textarea name="reaction_notes" class="form-control" rows="2" placeholder="مثال: صعوبة تنفس، تورم بالوجه، طفح جلدي حاد..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-danger">توثيق التحسس</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Add Condition -->
<div class="modal fade" id="addConditionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= url("/patients/{$patient['id']}/conditions") ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header bg-navy text-white">
                    <h5 class="modal-title fw-bold">توثيق مرض مزمن</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">اسم المرض المزمن <span class="text-danger">*</span></label>
                        <input type="text" name="condition_name" class="form-control" placeholder="مثال: داء السكري، ارتفاع ضغط الدم، أزمة ربوية..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">رمز ICD-10 المعتمد (اختياري)</label>
                        <input type="text" name="icd10_code" class="form-control" placeholder="مثال: E11, I10, J45">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">تاريخ التشخيص</label>
                        <input type="date" name="diagnosed_date" class="form-control" value="<?= date('Y-m-d') ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-hms-primary">حفظ الحالة المزمنة</button>
                </div>
            </form>
        </div>
    </div>
</div>
