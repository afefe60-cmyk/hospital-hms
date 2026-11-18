<div class="row g-4">
    <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h3 class="fw-bold text-navy mb-1 d-flex align-items-center gap-2">
                <i data-lucide="building" class="text-green" style="width: 28px; height: 28px;"></i>
                الهيكل التنظيمي والأقسام الطبية والإدارية
            </h3>
            <p class="text-muted mb-0">تعريف أقسام المستشفى ومراكز التكلفة المرتبطة بها</p>
        </div>

        <?php if (can('hospital.manage')): ?>
            <button type="button" class="btn btn-hms-primary d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#newDepModal">
                <i data-lucide="plus" style="width: 18px; height: 18px;"></i>
                إضافة قسم جديد
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
                            <th>كود القسم</th>
                            <th>اسم القسم (العربية)</th>
                            <th>اسم القسم (English)</th>
                            <th>التصنيف الوظيفي</th>
                            <th>الحالة</th>
                            <?php if (can('hospital.manage')): ?>
                                <th class="text-center">الإجراءات</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($departments as $d): ?>
                            <tr>
                                <td><?= $d['id'] ?></td>
                                <td><span class="badge bg-light text-navy border font-monospace"><?= e($d['code']) ?></span></td>
                                <td class="fw-bold text-navy"><?= e($d['name_ar']) ?></td>
                                <td class="text-muted"><?= e($d['name_en']) ?></td>
                                <td>
                                    <?php
                                    $typeBadge = match($d['type']) {
                                        'medical' => 'bg-primary-subtle text-primary',
                                        'diagnostic' => 'bg-info-subtle text-info',
                                        'pharmaceutical' => 'bg-success-subtle text-success',
                                        'financial' => 'bg-warning-subtle text-warning',
                                        default => 'bg-secondary-subtle text-secondary',
                                    };
                                    ?>
                                    <span class="badge <?= $typeBadge ?>"><?= e($d['type']) ?></span>
                                </td>
                                <td>
                                    <?php if ($d['is_active']): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">نشط</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">معطل</span>
                                    <?php endif; ?>
                                </td>
                                <?php if (can('hospital.manage')): ?>
                                    <td class="text-center">
                                        <form action="<?= url("/departments/{$d['id']}/toggle-status") ?>" method="POST" style="display:inline;">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm <?= $d['is_active'] ? 'btn-outline-danger' : 'btn-outline-success' ?>" title="<?= $d['is_active'] ? 'تعطيل' : 'تفعيل' ?>">
                                                <i data-lucide="<?= $d['is_active'] ? 'power-off' : 'check' ?>" style="width: 14px; height: 14px;"></i>
                                            </button>
                                        </form>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Department Modal -->
<?php if (can('hospital.manage')): ?>
    <div class="modal fade" id="newDepModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="<?= url('/departments') ?>" method="POST">
                    <?= csrf_field() ?>
                    <div class="modal-header bg-navy text-white">
                        <h5 class="modal-title fw-bold">إضافة قسم جديد</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">كود القسم الفريد <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control" placeholder="مثال: DEP-ICU" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">اسم القسم بالعربية <span class="text-danger">*</span></label>
                            <input type="text" name="name_ar" class="form-control" placeholder="مثال: قسم العناية المركزة" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">اسم القسم بالإنجليزية <span class="text-danger">*</span></label>
                            <input type="text" name="name_en" class="form-control" placeholder="مثال: Intensive Care Unit" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">التصنيف الوظيفي <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" required>
                                <option value="medical">طبي وسريري (Medical)</option>
                                <option value="diagnostic">تشخيصي ومساند (Diagnostic)</option>
                                <option value="pharmaceutical">دوائي ومخزني (Pharmaceutical)</option>
                                <option value="financial">مالي ومحاسبي (Financial)</option>
                                <option value="administrative">إداري وعام (Administrative)</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-hms-primary">حفظ القسم</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>
