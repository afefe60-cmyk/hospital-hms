<div class="row g-4 justify-content-center">
    <div class="col-lg-8">
        <div class="card card-hms p-0 bg-white overflow-hidden shadow-sm">
            <div class="card-header bg-navy text-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 d-flex align-items-center gap-2">
                    <i data-lucide="layers" style="width: 22px; height: 22px; color: #6EE7B7;"></i>
                    تسجيل تشغيلة دوائية وتوريد مخزون (Batch Inflow)
                </h5>
                <a href="<?= url('/inventory/batches') ?>" class="btn btn-sm btn-outline-light">رجوع للقائمة</a>
            </div>

            <form action="<?= url('/inventory/batches') ?>" method="POST" class="p-4">
                <?= csrf_field() ?>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">الصنف الدوائي: *</label>
                        <select name="medicine_id" class="form-select" required>
                            <option value="">-- اختر الصنف الدوائي --</option>
                            <?php foreach ($medicines as $m): ?>
                                <option value="<?= $m['id'] ?>">
                                    <?= e($m['commercial_name']) ?> (<?= e($m['scientific_name']) ?>) - <?= e($m['strength']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">المستودع المستقبل: *</label>
                        <select name="warehouse_id" class="form-select" required>
                            <option value="">-- اختر المستودع --</option>
                            <?php foreach ($warehouses as $wh): ?>
                                <option value="<?= $wh['id'] ?>"><?= e($wh['name_ar']) ?> (<?= e($wh['code']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">رقم التشغيلة (Batch Number): *</label>
                        <input type="text" name="batch_number" class="form-control font-monospace" placeholder="مثال: BATCH-2026-X1" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-danger">تاريخ انتهاء الصلاحية (Expiry Date): *</label>
                        <input type="date" name="expiry_date" class="form-control font-monospace border-danger" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">الكمية الموردة: *</label>
                        <input type="number" name="quantity_on_hand" class="form-control fw-bold" min="1" placeholder="100" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">سعر التكلفة للوحدة (SDG):</label>
                        <input type="number" step="0.01" name="cost_price" class="form-control" placeholder="0.00">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-success">سعر البيع للجمهور (SDG): *</label>
                        <input type="number" step="0.01" name="sale_price" class="form-control border-success fw-bold" placeholder="0.00" required>
                    </div>

                    <div class="col-12 mt-4 text-end">
                        <button type="submit" class="btn btn-success px-5 py-2 fw-bold shadow-sm">
                            إضافة التشغيلة وتحديث المخزون
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
