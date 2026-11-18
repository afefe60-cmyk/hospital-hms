<div class="row g-4 justify-content-center">
    <div class="col-lg-8">
        <div class="card card-hms p-0 bg-white overflow-hidden shadow-sm">
            <div class="card-header bg-navy text-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 d-flex align-items-center gap-2">
                    <i data-lucide="scan" style="width: 22px; height: 22px; color: #6EE7B7;"></i>
                    طلب فحص وتصوير إشعاعي جديد (New Radiology Order)
                </h5>
                <a href="<?= url('/radiology') ?>" class="btn btn-sm btn-outline-light">رجوع للقائمة</a>
            </div>

            <form action="<?= url('/radiology') ?>" method="POST" class="p-4">
                <?= csrf_field() ?>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">المريض: *</label>
                        <select name="patient_id" class="form-select" required>
                            <option value="">-- اختر المريض من السجل الطبي --</option>
                            <?php foreach ($patients as $p): ?>
                                <option value="<?= $p['id'] ?>" <?= ((string)$p['id'] === (string)$preselectedPatientId) ? 'selected' : '' ?>>
                                    <?= e($p['first_name'] . ' ' . $p['second_name'] . ' ' . $p['last_name']) ?> (<?= e($p['mrn']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">الطبيب الطالب: *</label>
                        <select name="doctor_id" class="form-select" required>
                            <option value="">-- اختر الطبيب --</option>
                            <?php foreach ($doctors as $d): ?>
                                <option value="<?= $d['id'] ?>">د. <?= e($d['employee_name'] ?? 'طبيب') ?> (<?= e($d['specialty_name'] ?? '') ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-semibold">الأولوية: *</label>
                        <select name="priority" class="form-select">
                            <option value="routine" selected>عادي (Routine)</option>
                            <option value="urgent">عاجل (Urgent)</option>
                            <option value="stat">طارئ جداً (STAT)</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold text-primary">الفحص الإشعاعي المطلوب: *</label>
                        <select name="service_id" class="form-select border-primary" required>
                            <option value="">-- اختر الفحص الإشعاعي من القائمة --</option>
                            <?php foreach ($services as $s): ?>
                                <option value="<?= $s['id'] ?>">
                                    <?= e($s['name_ar']) ?> (<?= e($s['name_en']) ?>) - [<?= strtoupper(e($s['modality'])) ?>] - <?= number_format($s['price'], 2) ?> SDG
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">الدواعي والملاحظات السريرية: *</label>
                        <textarea name="clinical_indication" class="form-control" rows="3" placeholder="الشكوى الطبية، الأعراض، العضو المراد فحصه، أو سبب طلب الأشعة..." required></textarea>
                    </div>

                    <div class="col-12 mt-4 text-end">
                        <button type="submit" class="btn btn-primary px-5 py-2 fw-bold shadow-sm">
                            إصدار وتأكيد طلب الأشعة
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
