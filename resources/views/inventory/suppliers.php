<div class="row g-4">
    <!-- Header -->
    <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h3 class="fw-bold text-navy mb-1 d-flex align-items-center gap-2">
                <i data-lucide="truck" class="text-primary" style="width: 28px; height: 28px;"></i>
                دليل موردي الأدوية والمستلزمات الطبية (Suppliers Directory)
            </h3>
            <p class="text-muted mb-0">بيانات الشركات والمصانع الدوائية المعتمدة والموردين</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-2 px-3 py-2 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#newSupplierModal">
                <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i>
                إضافة مورد جديد
            </button>
            <a href="<?= url('/inventory') ?>" class="btn btn-outline-secondary">لوحة المخازن</a>
        </div>
    </div>

    <!-- Suppliers Table -->
    <div class="col-12">
        <div class="card card-hms p-0 bg-white overflow-hidden shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>الكود</th>
                            <th>اسم الشركة / المورد</th>
                            <th>الشخص المسؤول</th>
                            <th>رقم الهاتف</th>
                            <th>البريد الإلكتروني</th>
                            <th>العنوان</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($suppliers)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">لا يوجد موردين مسجلين بعد</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($suppliers as $s): ?>
                                <tr>
                                    <td><code><?= e($s['code']) ?></code></td>
                                    <td class="fw-bold text-navy"><?= e($s['name']) ?></td>
                                    <td><?= e($s['contact_person'] ?? '-') ?></td>
                                    <td class="font-monospace"><?= e($s['phone']) ?></td>
                                    <td class="text-muted small"><?= e($s['email'] ?? '-') ?></td>
                                    <td class="text-muted small"><?= e($s['address'] ?? '-') ?></td>
                                    <td><span class="badge bg-success"><?= e($s['status']) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: New Supplier -->
<div class="modal fade" id="newSupplierModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-navy text-white">
                <h5 class="modal-title fw-bold">إضافة مورد أدوية ومستلزمات جديد</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= url('/inventory/suppliers') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">كود المورد: *</label>
                        <input type="text" name="code" class="form-control font-monospace" placeholder="مثال: SUP-003" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">اسم الشركة أو المورد: *</label>
                        <input type="text" name="name" class="form-control" required placeholder="مثال: شركة النيل للأدوية">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">الشخص المسؤول (Contact Person):</label>
                        <input type="text" name="contact_person" class="form-control" placeholder="اسم مدير المبيعات أو المندوب">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">رقم الهاتف: *</label>
                        <input type="text" name="phone" class="form-control" required placeholder="+249 ...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">العنوان:</label>
                        <input type="text" name="address" class="form-control" placeholder="المدينة، الحي أو الشارع">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary fw-bold">حفظ المورد</button>
                </div>
            </form>
        </div>
    </div>
</div>
