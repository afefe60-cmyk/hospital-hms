<?php
$layout = 'layouts/main';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h4 mb-1 fw-bold"><i class="bi bi-tag text-primary me-2"></i>دليل أسعار الخدمات الطبية (Service Catalog)</h2>
        <p class="text-muted mb-0">قائمة أسعار الخدمات والكشوفات والعمليات والتحاليل بالجنيه السوداني (SDG)</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addServiceModal">
            <i class="bi bi-plus-lg"></i>
            <span>إضافة خدمة جديدة</span>
        </button>
        <a href="/billing" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-right me-1"></i>رجوع للفوترة
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>الكود</th>
                        <th>اسم الخدمة (عربي)</th>
                        <th>اسم الخدمة (إنجليزي)</th>
                        <th>القسم التابعة له</th>
                        <th class="text-end">السعر القياسي المعتمد (SDG)</th>
                        <th class="text-center">الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($services)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">لا توجد خدمات مسجلة في الدليل.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($services as $srv): ?>
                            <tr>
                                <td class="font-monospace fw-bold text-primary"><?= htmlspecialchars($srv['code']) ?></td>
                                <td class="fw-bold text-dark"><?= htmlspecialchars($srv['name_ar']) ?></td>
                                <td class="text-muted"><?= htmlspecialchars($srv['name_en']) ?></td>
                                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($srv['department_name']) ?></span></td>
                                <td class="text-end font-monospace fw-bold text-success fs-6"><?= number_format($srv['price_standard'], 2) ?> SDG</td>
                                <td class="text-center">
                                    <span class="badge bg-success">نشطة</span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Add Service -->
<div class="modal fade" id="addServiceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/billing/services">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-primary"><i class="bi bi-plus-circle me-2"></i>إضافة خدمة طبية جديدة للدليل</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">كود الخدمة الفريد <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control" placeholder="مثال: SRV-CONS-CARD" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">اسم الخدمة بالعربية <span class="text-danger">*</span></label>
                        <input type="text" name="name_ar" class="form-control" placeholder="مثال: رسم قلب كهربائي ECG" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">اسم الخدمة بالإنجليزية</label>
                        <input type="text" name="name_en" class="form-control" placeholder="مثال: Electrocardiogram (ECG)">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">القسم الطبي التابعة له <span class="text-danger">*</span></label>
                        <select name="department_id" class="form-select" required>
                            <option value="">-- اختر القسم --</option>
                            <?php foreach ($departments as $dep): ?>
                                <option value="<?= $dep['id'] ?>"><?= htmlspecialchars($dep['name_ar']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">السعر النقدي المعتمد (SDG) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" name="price_standard" class="form-control text-end font-monospace" placeholder="0.00" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary fw-bold">حفظ الخدمة</button>
                </div>
            </form>
        </div>
    </div>
</div>
