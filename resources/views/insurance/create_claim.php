<?php
$layout = 'layouts/main';
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="h4 mb-1 fw-bold"><i class="bi bi-file-earmark-plus text-primary me-2"></i>رفع مطالبة تأمينية جديدة</h3>
                <p class="text-muted mb-0">مطالبة شركة التأمين بتحمل الخدمات الطبية للفاتورة العلاجية</p>
            </div>
            <a href="/insurance" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-right me-1"></i>رجوع للمطالبات
            </a>
        </div>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                <form method="POST" action="/insurance/claims">
                    <?= csrf_field() ?>

                    <h5 class="fw-bold mb-3 text-primary border-bottom pb-2">1. الفاتورة والمريض</h5>
                    <div class="mb-4">
                        <label class="form-label fw-bold">اختر الفاتورة العلاجية <span class="text-danger">*</span></label>
                        <?php if ($invoice): ?>
                            <input type="hidden" name="invoice_id" value="<?= $invoice['id'] ?>">
                            <div class="form-control bg-light">
                                <strong>فاتورة: <?= htmlspecialchars($invoice['invoice_number']) ?></strong> -
                                المريض: <?= htmlspecialchars($invoice['patient_name']) ?> (MRN: <?= htmlspecialchars($invoice['mrn']) ?>) -
                                الإجمالي: <strong class="text-primary font-monospace"><?= number_format($invoice['total_gross'], 2) ?> SDG</strong>
                            </div>
                        <?php else: ?>
                            <select name="invoice_id" class="form-select select2" id="invoiceSelect" required>
                                <option value="">-- اختر الفاتورة المراد المطالبة بها --</option>
                                <?php foreach ($invoices as $inv): ?>
                                    <option value="<?= $inv['id'] ?>" data-gross="<?= $inv['total_gross'] ?>">
                                        <?= htmlspecialchars($inv['invoice_number']) ?> - المريض: <?= htmlspecialchars($inv['patient_name']) ?> (<?= number_format($inv['total_gross'], 2) ?> SDG)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                    </div>

                    <h5 class="fw-bold mb-3 text-primary border-bottom pb-2">2. بيانات شركة التأمين والوثيقة</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">شركة التأمين المطالَبة <span class="text-danger">*</span></label>
                            <select name="company_id" class="form-select" id="companySelect" required>
                                <option value="">-- اختر شركة التأمين --</option>
                                <?php foreach ($companies as $comp): ?>
                                    <option value="<?= $comp['id'] ?>" data-rate="<?= $comp['default_coverage_rate'] ?>" <?= ($policy && (int)$policy['company_id'] === (int)$comp['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($comp['name_ar']) ?> (تغطية: <?= $comp['default_coverage_rate'] ?>%)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">رقم وثيقة / بطاقة التأمين <span class="text-danger">*</span></label>
                            <input type="text" name="policy_number" class="form-control" value="<?= htmlspecialchars($policy['policy_number'] ?? '') ?>" placeholder="مثال: SHK-POL-2026-9901" required>
                        </div>
                    </div>

                    <h5 class="fw-bold mb-3 text-primary border-bottom pb-2">3. المبالغ والمطالبة</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">المبلغ المطلوب من شركة التأمين (SDG) <span class="text-danger">*</span></label>
                            <?php
                                $suggestedClaim = $invoice ? round(((float)$invoice['total_gross'] * (($policy['coverage_percentage'] ?? 80) / 100)), 2) : 0;
                            ?>
                            <input type="number" step="0.01" min="1" name="claim_amount" id="claimAmountInput" class="form-control form-control-lg font-monospace fw-bold text-primary" value="<?= $suggestedClaim ?: '' ?>" placeholder="0.00" required>
                            <small class="text-muted">المبلغ يمثل نسبة تغطية التأمين من إجمالي الفاتورة.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">ملاحظات المطالبة</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="أية ملاحظات أو استثناءات طبية مصاحبة للرفع..."></textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="/insurance" class="btn btn-outline-secondary px-4">إلغاء</a>
                        <button type="submit" class="btn btn-primary px-4 fw-bold">
                            <i class="bi bi-send me-1"></i>رفع وتأكيد المطالبة
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const invSelect = document.getElementById('invoiceSelect');
    const compSelect = document.getElementById('companySelect');
    const claimInput = document.getElementById('claimAmountInput');

    function updateSuggested() {
        let gross = 0;
        if (invSelect && invSelect.selectedIndex > 0) {
            gross = parseFloat(invSelect.options[invSelect.selectedIndex].getAttribute('data-gross')) || 0;
        }
        let rate = 80;
        if (compSelect && compSelect.selectedIndex > 0) {
            rate = parseFloat(compSelect.options[compSelect.selectedIndex].getAttribute('data-rate')) || 80;
        }

        if (gross > 0 && (!claimInput.value || claimInput.value == '0')) {
            claimInput.value = ((gross * rate) / 100).toFixed(2);
        }
    }

    if (invSelect) invSelect.addEventListener('change', updateSuggested);
    if (compSelect) compSelect.addEventListener('change', updateSuggested);
});
</script>
