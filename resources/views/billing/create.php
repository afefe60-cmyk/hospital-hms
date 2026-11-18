<?php
$layout = 'layouts/main';
?>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="h4 mb-1 fw-bold"><i class="bi bi-file-earmark-plus text-success me-2"></i>إصدار فاتورة علاجية جديدة</h3>
                <p class="text-muted mb-0">تحديد المريض وإضافة بنود الخدمات الطبية والتحاليل والإجراءات</p>
            </div>
            <a href="/billing" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-right me-1"></i>رجوع للفوترة
            </a>
        </div>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                <form method="POST" action="/billing/store" id="invoiceForm">
                    <?= csrf_field() ?>

                    <h5 class="fw-bold mb-3 text-success border-bottom pb-2">1. بيانات المريض</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-8">
                            <label class="form-label fw-bold">اختر المريض <span class="text-danger">*</span></label>
                            <?php if ($patient): ?>
                                <input type="hidden" name="patient_id" value="<?= $patient['id'] ?>">
                                <div class="form-control bg-light">
                                    <strong><?= htmlspecialchars($patient['first_name'] . ' ' . $patient['second_name'] . ' ' . $patient['last_name']) ?></strong>
                                    (MRN: <?= htmlspecialchars($patient['mrn']) ?>) - هاتف: <?= htmlspecialchars($patient['phone_primary']) ?>
                                </div>
                            <?php else: ?>
                                <select name="patient_id" class="form-select select2" required>
                                    <option value="">-- ابحث أو اختر المريض --</option>
                                    <?php foreach ($patients as $p): ?>
                                        <option value="<?= $p['id'] ?>">
                                            <?= htmlspecialchars($p['name']) ?> (MRN: <?= htmlspecialchars($p['mrn']) ?>) - <?= htmlspecialchars($p['phone_primary']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">ملاحظات الفاتورة</label>
                            <input type="text" name="notes" class="form-control" placeholder="فاتورة عيادة خارجية، تنويم، تحاليل...">
                        </div>
                    </div>

                    <h5 class="fw-bold mb-3 text-success border-bottom pb-2 d-flex justify-content-between align-items-center">
                        <span>2. بنود الخدمات الطبية</span>
                        <button type="button" class="btn btn-sm btn-outline-success" id="addRowBtn">
                            <i class="bi bi-plus-lg me-1"></i>إضافة بند آخر
                        </button>
                    </h5>

                    <div class="table-responsive mb-4">
                        <table class="table table-bordered align-middle" id="itemsTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="35%">الخدمة الطبية (من الدليل)</th>
                                    <th width="30%">الوصف الإكلينيكي / البند</th>
                                    <th width="12%">الكمية</th>
                                    <th width="15%">سعر الوحدة (SDG)</th>
                                    <th width="15%">المجموع (SDG)</th>
                                    <th width="5%" class="text-center">حذف</th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                <tr>
                                    <td>
                                        <select name="service_id[]" class="form-select service-select">
                                            <option value="">-- اختر خدمة أو بند مخصص --</option>
                                            <?php foreach ($services as $srv): ?>
                                                <option value="<?= $srv['id'] ?>" data-price="<?= $srv['price_standard'] ?>" data-name="<?= htmlspecialchars($srv['name_ar']) ?>">
                                                    <?= htmlspecialchars($srv['name_ar']) ?> (<?= number_format($srv['price_standard'], 0) ?> SDG)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="description[]" class="form-control desc-input" placeholder="وصف الخدمة أو الفحص" required>
                                    </td>
                                    <td>
                                        <input type="number" step="1" min="1" name="quantity[]" class="form-control text-center qty-input" value="1" required>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" name="unit_price[]" class="form-control text-end price-input" value="0.00" required>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control text-end row-total bg-light font-monospace fw-bold" value="0.00" readonly>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-outline-danger btn-sm remove-row-btn" disabled><i class="bi bi-trash"></i></button>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" class="text-end">الإجمالي الكلي للخدمات (Gross Total):</th>
                                    <td class="text-end fw-bold font-monospace fs-6" id="grossTotalDisplay">0.00 SDG</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <th colspan="4" class="text-end">الخصم المعتمد (Discount):</th>
                                    <td>
                                        <input type="number" step="0.01" min="0" name="total_discount" id="discountInput" class="form-control text-end" value="0.00">
                                    </td>
                                    <td></td>
                                </tr>
                                <tr class="table-success">
                                    <th colspan="4" class="text-end fs-5 fw-bold">الصافي المستحق على المريض (Net Due):</th>
                                    <td class="text-end fs-5 fw-bold text-success font-monospace" id="netDueDisplay">0.00 SDG</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="/billing" class="btn btn-outline-secondary px-4">إلغاء</a>
                        <button type="submit" class="btn btn-success px-4 fw-bold">
                            <i class="bi bi-check-lg me-1"></i>تأكيد وإصدار الفاتورة
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tableBody = document.getElementById('itemsBody');
    const addBtn = document.getElementById('addRowBtn');
    const discountInput = document.getElementById('discountInput');
    const grossDisplay = document.getElementById('grossTotalDisplay');
    const netDisplay = document.getElementById('netDueDisplay');

    function calculateTotals() {
        let gross = 0;
        tableBody.querySelectorAll('tr').forEach(row => {
            const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
            const price = parseFloat(row.querySelector('.price-input').value) || 0;
            const total = qty * price;
            row.querySelector('.row-total').value = total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            gross += total;
        });

        const discount = parseFloat(discountInput.value) || 0;
        const net = Math.max(0, gross - discount);

        grossDisplay.textContent = gross.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' SDG';
        netDisplay.textContent = net.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' SDG';
    }

    function attachRowEvents(row) {
        const srvSelect = row.querySelector('.service-select');
        const descInput = row.querySelector('.desc-input');
        const priceInput = row.querySelector('.price-input');
        const qtyInput = row.querySelector('.qty-input');
        const removeBtn = row.querySelector('.remove-row-btn');

        srvSelect.addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            if (opt.value) {
                descInput.value = opt.getAttribute('data-name');
                priceInput.value = opt.getAttribute('data-price');
            }
            calculateTotals();
        });

        qtyInput.addEventListener('input', calculateTotals);
        priceInput.addEventListener('input', calculateTotals);

        if (removeBtn) {
            removeBtn.addEventListener('click', function() {
                if (tableBody.querySelectorAll('tr').length > 1) {
                    row.remove();
                    calculateTotals();
                    updateRemoveButtons();
                }
            });
        }
    }

    function updateRemoveButtons() {
        const rows = tableBody.querySelectorAll('tr');
        rows.forEach(r => {
            const btn = r.querySelector('.remove-row-btn');
            if (btn) btn.disabled = (rows.length === 1);
        });
    }

    // Attach to initial row
    attachRowEvents(tableBody.querySelector('tr'));

    addBtn.addEventListener('click', function() {
        const firstRow = tableBody.querySelector('tr');
        const clone = firstRow.cloneNode(true);
        clone.querySelector('.service-select').selectedIndex = 0;
        clone.querySelector('.desc-input').value = '';
        clone.querySelector('.qty-input').value = '1';
        clone.querySelector('.price-input').value = '0.00';
        clone.querySelector('.row-total').value = '0.00';
        tableBody.appendChild(clone);
        attachRowEvents(clone);
        updateRemoveButtons();
    });

    discountInput.addEventListener('input', calculateTotals);
});
</script>
