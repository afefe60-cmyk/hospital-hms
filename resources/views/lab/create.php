<div class="row g-4 justify-content-center">
    <div class="col-lg-9">
        <div class="card card-hms p-0 bg-white overflow-hidden shadow-sm">
            <div class="card-header bg-navy text-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 d-flex align-items-center gap-2">
                    <i data-lucide="test-tube" style="width: 22px; height: 22px; color: #6EE7B7;"></i>
                    طلب تحاليل وفحوصات مخبرية جديدة (New Laboratory Order)
                </h5>
                <a href="<?= url('/lab') ?>" class="btn btn-sm btn-outline-light">رجوع للقائمة</a>
            </div>

            <form action="<?= url('/lab') ?>" method="POST" class="p-4">
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
                        <label class="form-label fw-semibold">الدواعي والملاحظات السريرية:</label>
                        <textarea name="clinical_indication" class="form-control" rows="2" placeholder="أعراض المريض، الشكوى، أو سبب الفحص..."></textarea>
                    </div>

                    <!-- Tests Selection Table -->
                    <div class="col-12 mt-3">
                        <label class="form-label fw-bold text-navy d-flex align-items-center gap-2 mb-2">
                            <i data-lucide="check-square" style="width: 18px; height: 18px;"></i>
                            اختر الفحوصات المعملية المطلوبة: *
                        </label>

                        <div class="table-responsive border rounded p-2 bg-light" style="max-height: 320px; overflow-y: auto;">
                            <table class="table table-hover align-middle mb-0 bg-white">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th style="width: 5%">تحديد</th>
                                        <th>كود الفحص</th>
                                        <th>اسم الفحص المعملي</th>
                                        <th>التصنيف</th>
                                        <th>نوع العينة</th>
                                        <th>السعر (SDG)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tests as $t): ?>
                                        <tr>
                                            <td class="text-center">
                                                <input class="form-check-input" type="checkbox" name="test_ids[]" value="<?= $t['id'] ?>" id="test_<?= $t['id'] ?>">
                                            </td>
                                            <td><code><?= e($t['code']) ?></code></td>
                                            <td>
                                                <label class="form-check-label fw-bold text-navy" for="test_<?= $t['id'] ?>">
                                                    <?= e($t['name_ar']) ?>
                                                    <small class="d-block text-muted"><?= e($t['name_en']) ?></small>
                                                </label>
                                            </td>
                                            <td><span class="badge bg-secondary"><?= e($t['category']) ?></span></td>
                                            <td><span class="badge bg-light text-dark border"><?= e($t['sample_type']) ?></span></td>
                                            <td class="fw-bold text-success"><?= number_format($t['price'], 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="col-12 mt-4 text-end">
                        <button type="submit" class="btn btn-primary px-5 py-2 fw-bold shadow-sm">
                            إصدار الطلب المعملي
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
