<div class="row g-4 justify-content-center">
    <div class="col-lg-9">
        <div class="card card-hms p-0 bg-white overflow-hidden shadow-sm">
            <div class="card-header bg-navy text-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 d-flex align-items-center gap-2">
                    <i data-lucide="bed-plus" style="width: 22px; height: 22px; color: #6EE7B7;"></i>
                    تسجيل إذن تنويم مريض جديد وتسكين السرير (IPD Admission)
                </h5>
                <a href="<?= url('/admissions') ?>" class="btn btn-sm btn-outline-light">رجوع للقائمة</a>
            </div>

            <form action="<?= url('/admissions') ?>" method="POST" class="p-4">
                <?= csrf_field() ?>

                <?php if (!empty($erCaseId)): ?>
                    <input type="hidden" name="emergency_case_id" value="<?= e($erCaseId) ?>">
                    <div class="alert alert-warning py-2 mb-3 d-flex align-items-center gap-2">
                        <i data-lucide="alert-circle"></i>
                        <span>هذا التنويم محول مباشرة من قسم الطوارئ (بلاغ رقم: #<?= e($erCaseId) ?>).</span>
                    </div>
                <?php endif; ?>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">المريض المراد تنويمه: *</label>
                        <select name="patient_id" class="form-select" required>
                            <option value="">-- اختر المريض من السجل الطبي --</option>
                            <?php foreach ($patients as $p): ?>
                                <option value="<?= $p['id'] ?>" <?= ((string)$p['id'] === (string)$preselectedPatientId) ? 'selected' : '' ?>>
                                    <?= e($p['first_name'] . ' ' . $p['second_name'] . ' ' . $p['last_name']) ?> (<?= e($p['mrn']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">القسم الداخلي المنوم به: *</label>
                        <select name="department_id" class="form-select" required>
                            <option value="">-- اختر القسم --</option>
                            <?php foreach ($departments as $d): ?>
                                <option value="<?= $d['id'] ?>"><?= e($d['name_ar']) ?> (<?= e($d['code']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">الطبيب المشرف والمعالج: *</label>
                        <select name="admitting_doctor_id" class="form-select" required>
                            <option value="">-- اختر الطبيب --</option>
                            <?php foreach ($doctors as $doc): ?>
                                <option value="<?= $doc['id'] ?>">د. <?= e($doc['employee_name'] ?? 'طبيب') ?> (<?= e($doc['specialty_name'] ?? '') ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-primary">السرير المتاح للتسكين: *</label>
                        <select name="bed_id" class="form-select border-primary" required>
                            <option value="">-- اختر سريراً متاحاً --</option>
                            <?php foreach ($availableBeds as $bed): ?>
                                <option value="<?= $bed['id'] ?>">
                                    سرير: <?= e($bed['bed_number']) ?> | غرفة: <?= e($bed['room_number']) ?> (<?= e($bed['ward_name']) ?>) - <?= number_format($bed['daily_rate'], 0) ?> SDG/يوم
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">سبب التنويم الطبي: *</label>
                        <textarea name="admission_reason" class="form-control" rows="2" placeholder="أسباب ومبررات التنويم السريري..." required></textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">التشخيص المبدئي للدخول (Provisional Diagnosis): *</label>
                        <textarea name="provisional_diagnosis" class="form-control" rows="2" placeholder="التشخيص المبدئي وخطة الدخول..." required></textarea>
                    </div>

                    <div class="col-12 mt-4 text-end">
                        <button type="submit" class="btn btn-primary px-5 py-2 fw-bold shadow-sm">
                            حفظ إذن التنويم وتسكين السرير
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
