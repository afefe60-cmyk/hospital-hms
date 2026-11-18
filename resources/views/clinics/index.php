<div class="row g-4">
    <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h3 class="fw-bold text-navy mb-1 d-flex align-items-center gap-2">
                <i data-lucide="stethoscope" class="text-green" style="width: 28px; height: 28px;"></i>
                العيادات الخارجية (Outpatient Clinics)
            </h3>
            <p class="text-muted mb-0">تهيئة العيادات وغرف الكشف وتوزيعها على الأقسام الطبية</p>
        </div>

        <?php if (can('hospital.manage')): ?>
            <button type="button" class="btn btn-hms-primary d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#newClinicModal">
                <i data-lucide="plus" style="width: 18px; height: 18px;"></i>
                إضافة عيادة جديدة
            </button>
        <?php endif; ?>
    </div>

    <div class="col-12">
        <div class="card card-hms p-0 bg-white overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>القسم الطبي</th>
                            <th>رقم الغرفة</th>
                            <th>اسم العيادة (العربية)</th>
                            <th>اسم العيادة (English)</th>
                            <th>الحالة التشغيلية</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clinics as $c): ?>
                            <tr>
                                <td><?= $c['id'] ?></td>
                                <td><span class="badge bg-navy"><?= e($c['department_name_ar']) ?></span></td>
                                <td><span class="badge bg-light text-dark border">غرفة <?= e($c['room_number']) ?></span></td>
                                <td class="fw-bold text-navy"><?= e($c['name_ar']) ?></td>
                                <td class="text-muted"><?= e($c['name_en']) ?></td>
                                <td>
                                    <?php if ($c['is_active']): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">متاحة للكشف</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">مغلقة مؤقتاً</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Clinic Modal -->
<?php if (can('hospital.manage')): ?>
    <div class="modal fade" id="newClinicModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="<?= url('/clinics') ?>" method="POST">
                    <?= csrf_field() ?>
                    <div class="modal-header bg-navy text-white">
                        <h5 class="modal-title fw-bold">إضافة عيادة خارجية جديدة</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">القسم التابع له <span class="text-danger">*</span></label>
                            <select name="department_id" class="form-select" required>
                                <option value="">-- اختر القسم الطبي --</option>
                                <?php foreach ($departments as $dep): ?>
                                    <option value="<?= $dep['id'] ?>"><?= e($dep['name_ar']) ?> (<?= e($dep['code']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">رقم الغرفة / الموقع <span class="text-danger">*</span></label>
                            <input type="text" name="room_number" class="form-control" placeholder="مثال: 105" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">اسم العيادة بالعربية <span class="text-danger">*</span></label>
                            <input type="text" name="name_ar" class="form-control" placeholder="مثال: عيادة العظام 1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">اسم العيادة بالإنجليزية <span class="text-danger">*</span></label>
                            <input type="text" name="name_en" class="form-control" placeholder="مثال: Orthopedic Clinic 1" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-hms-primary">حفظ العيادة</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>
