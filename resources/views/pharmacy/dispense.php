<div class="row g-4 justify-content-center">
    <div class="col-lg-10">
        <div class="card card-hms p-0 bg-white overflow-hidden shadow-sm">
            <div class="card-header bg-navy text-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 d-flex align-items-center gap-2">
                    <i data-lucide="shopping-cart" style="width: 22px; height: 22px; color: #6EE7B7;"></i>
                    شاشة صرف الأدوية والمستحضرات الطبية (FEFO Dispensing)
                </h5>
                <a href="<?= url('/pharmacy') ?>" class="btn btn-sm btn-outline-light">رجوع للقائمة</a>
            </div>

            <form action="<?= url('/pharmacy/dispense') ?>" method="POST" class="p-4" id="dispenseForm">
                <?= csrf_field() ?>

                <?php if ($rx): ?>
                    <input type="hidden" name="prescription_id" value="<?= $rx['id'] ?>">
                    <div class="alert alert-info py-3 mb-4 d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="fw-bold text-navy mb-1">
                                ℞ صرف مرتبط بروشتة إلكترونية معتمدة: <?= e($rx['prescription_code']) ?>
                            </h6>
                            <small class="text-muted">
                                الطبيب: د. <?= e($rx['doctor_name']) ?> | التاريخ: <?= date('d/m/Y H:i', strtotime($rx['created_at'])) ?>
                            </small>
                        </div>
                        <span class="badge bg-primary fs-6"><?= count($rxItems) ?> أصناف موصوفة</span>
                    </div>

                    <?php if (!empty($rx['notes'])): ?>
                        <div class="p-2 bg-light rounded border mb-3 small">
                            <strong>تعليمات الطبيب المعالج:</strong> <?= e($rx['notes']) ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">المريض المستلم: *</label>
                        <select name="patient_id" class="form-select" required <?= $rx ? 'readonly' : '' ?>>
                            <?php if ($patient): ?>
                                <option value="<?= $patient['id'] ?>" selected>
                                    <?= e($patient['first_name'] . ' ' . $patient['second_name'] . ' ' . $patient['last_name']) ?> (<?= e($patient['mrn']) ?>)
                                </option>
                            <?php else: ?>
                                <option value="">-- اختر المريض من السجل الطبي --</option>
                                <?php foreach ($patients as $p): ?>
                                    <option value="<?= $p['id'] ?>">
                                        <?= e($p['first_name'] . ' ' . $p['second_name'] . ' ' . $p['last_name']) ?> (<?= e($p['mrn']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">صيدلية / مخزن الصرف: *</label>
                        <select name="warehouse_id" class="form-select" required>
                            <?php foreach ($warehouses as $wh): ?>
                                <option value="<?= $wh['id'] ?>" <?= ($wh['type'] === 'pharmacy') ? 'selected' : '' ?>>
                                    <?= e($wh['name_ar']) ?> (<?= e($wh['code']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Items Dispensing Table with FEFO Batches -->
                    <div class="col-12 mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-bold text-navy mb-0">
                                💊 قائمة الأدوية والتشغيلات المحددة للصرف (الأقدم صلاحية أولاً - FEFO): *
                            </label>
                            <button type="button" class="btn btn-sm btn-outline-success fw-bold" onclick="addDispenseRow()">
                                + إضافة بند دوائي
                            </button>
                        </div>

                        <div class="table-responsive border rounded p-2 bg-light">
                            <table class="table table-bordered align-middle mb-0 bg-white" id="dispenseTable">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50%">الدواء والتشغيلة المتاحة (Batch - Expiry - Stock)</th>
                                        <th style="width: 15%">الكمية</th>
                                        <th style="width: 20%">سعر الوحدة (SDG)</th>
                                        <th style="width: 15%" class="text-center">حذف</th>
                                    </tr>
                                </thead>
                                <tbody id="dispenseRows">
                                    <?php if (!empty($batches)): ?>
                                        <tr class="dispense-item-row">
                                            <td>
                                                <select name="batch_id[]" class="form-select batch-select" required onchange="updateBatchPrice(this)">
                                                    <option value="">-- اختر الصنف والتشغيلة بنظام FEFO --</option>
                                                    <?php foreach ($batches as $b): ?>
                                                        <option value="<?= $b['id'] ?>" data-price="<?= $b['sale_price'] ?>" data-stock="<?= $b['quantity_on_hand'] ?>">
                                                            <?= e($b['commercial_name']) ?> | تشغيلة: <?= e($b['batch_number']) ?> (ينتهي: <?= date('d/m/Y', strtotime($b['expiry_date'])) ?>) [متوفر: <?= $b['quantity_on_hand'] ?>]
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" name="quantity[]" class="form-control qty-input" value="1" min="1" required onchange="calculateTotal()">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control price-input bg-light" readonly value="0.00">
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)">✕</button>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="col-12 mt-3">
                        <label class="form-label fw-semibold">ملاحظات الصرف:</label>
                        <input type="text" name="notes" class="form-control" placeholder="تعليمات إضافية للمريض أو الصيدلية...">
                    </div>

                    <!-- Total Amount Display -->
                    <div class="col-12 mt-4 p-3 bg-light rounded border d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold text-navy mb-0">المبلغ الإجمالي للفاتورة:</h5>
                        <h3 class="fw-bold text-success mb-0" id="grandTotalDisplay">0.00 SDG</h3>
                    </div>

                    <div class="col-12 mt-3 text-end">
                        <button type="submit" class="btn btn-success px-5 py-2 fw-bold shadow-sm">
                            تأكيد الصرف وخصم المخزون (Dispense & Deduct Stock)
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function updateBatchPrice(selectElem) {
    const row = selectElem.closest('.dispense-item-row');
    const selectedOpt = selectElem.options[selectElem.selectedIndex];
    const price = selectedOpt ? selectedOpt.getAttribute('data-price') || '0.00' : '0.00';
    const priceInput = row.querySelector('.price-input');
    if (priceInput) priceInput.value = parseFloat(price).toFixed(2);
    calculateTotal();
}

function calculateTotal() {
    let grandTotal = 0;
    const rows = document.querySelectorAll('.dispense-item-row');
    rows.forEach(row => {
        const qty = parseFloat(row.querySelector('.qty-input')?.value || 0);
        const price = parseFloat(row.querySelector('.price-input')?.value || 0);
        grandTotal += (qty * price);
    });
    document.getElementById('grandTotalDisplay').innerText = grandTotal.toLocaleString('en-US', {minimumFractionDigits: 2}) + ' SDG';
}

function removeRow(btn) {
    const rows = document.querySelectorAll('.dispense-item-row');
    if (rows.length > 1) {
        btn.closest('.dispense-item-row').remove();
        calculateTotal();
    }
}

function addDispenseRow() {
    const tbody = document.getElementById('dispenseRows');
    const firstRow = document.querySelector('.dispense-item-row');
    if (firstRow) {
        const clone = firstRow.cloneNode(true);
        clone.querySelector('.qty-input').value = '1';
        clone.querySelector('.price-input').value = '0.00';
        clone.querySelector('.batch-select').selectedIndex = 0;
        tbody.appendChild(clone);
    }
}
</script>
