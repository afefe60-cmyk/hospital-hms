<?php
$layout = 'layouts/main';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <div class="d-flex align-items-center gap-3">
            <h3 class="h4 mb-0 fw-bold font-monospace text-primary"><?= htmlspecialchars($claim['claim_number']) ?></h3>
            <?php
                $statusBadge = match($claim['status']) {
                    'approved' => 'bg-success',
                    'settled' => 'bg-primary',
                    'partially_approved' => 'bg-info text-dark',
                    'under_review' => 'bg-warning text-dark',
                    'rejected' => 'bg-danger',
                    default => 'bg-secondary'
                };
                $statusLabel = match($claim['status']) {
                    'approved' => 'معتمدة بالكامل',
                    'settled' => 'تمت التسوية المالية',
                    'partially_approved' => 'اعتماد جزئي',
                    'under_review' => 'قيد التدقيق من شركة التأمين',
                    'rejected' => 'مرفوضة من التأمين',
                    default => 'مرفوعة حديثاً'
                };
            ?>
            <span class="badge <?= $statusBadge ?> fs-6"><?= $statusLabel ?></span>
        </div>
        <p class="text-muted mb-0 mt-1">تاريخ الرفع: <strong><?= date('Y-m-d', strtotime($claim['submission_date'])) ?></strong> | شركة التأمين: <strong><?= htmlspecialchars($claim['company_name_ar']) ?></strong></p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-warning d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#reviewModal">
            <i class="bi bi-pencil-square"></i> تحديث حالة التدقيق والاعتماد
        </button>
        <a href="/insurance/claims/<?= $claim['id'] ?>/print" target="_blank" class="btn btn-outline-secondary">
            <i class="bi bi-printer me-1"></i>طباعة المطالبة
        </a>
        <a href="/insurance" class="btn btn-outline-primary">
            <i class="bi bi-arrow-right me-1"></i>لوحة التأمين
        </a>
    </div>
</div>

<!-- Patient & Policy Strip -->
<div class="card border-0 shadow-sm rounded-3 mb-4 bg-light">
    <div class="card-body p-3">
        <div class="row g-3 align-items-center">
            <div class="col-md-3">
                <span class="text-muted d-block small">اسم المريض</span>
                <span class="fw-bold text-dark fs-6"><?= htmlspecialchars($claim['patient_name']) ?></span>
            </div>
            <div class="col-md-2">
                <span class="text-muted d-block small">الرقم الطبي (MRN)</span>
                <span class="fw-bold font-monospace"><?= htmlspecialchars($claim['mrn']) ?></span>
            </div>
            <div class="col-md-2">
                <span class="text-muted d-block small">رقم وثيقة التأمين</span>
                <span class="fw-bold font-monospace text-primary"><?= htmlspecialchars($claim['policy_number']) ?></span>
            </div>
            <div class="col-md-2">
                <span class="text-muted d-block small">الفاتورة المرتبطة</span>
                <a href="/billing/<?= $claim['invoice_id'] ?>" class="fw-bold font-monospace text-decoration-none"><?= htmlspecialchars($claim['invoice_number']) ?></a>
            </div>
            <div class="col-md-3">
                <span class="text-muted d-block small">مبلغ المطالبة المرفوع</span>
                <strong class="font-monospace fs-5 text-primary"><?= number_format($claim['claim_amount'], 2) ?> SDG</strong>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Left: Invoice Line Items -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white py-3 border-light">
                <h5 class="card-title mb-0 fw-bold"><i class="bi bi-card-checklist text-primary me-2"></i>تفاصيل الخدمات الطبية المشمولة بالمطالبة</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>الخدمة / الإجراء</th>
                                <th class="text-center">الكمية</th>
                                <th class="text-end">سعر الوحدة</th>
                                <th class="text-end">الإجمالي</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; foreach ($items as $it): ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($it['description']) ?></div>
                                        <?php if (!empty($it['service_code'])): ?>
                                            <small class="text-muted font-monospace"><?= htmlspecialchars($it['service_code']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center"><?= (float)$it['quantity'] ?></td>
                                    <td class="text-end font-monospace"><?= number_format($it['unit_price'], 2) ?></td>
                                    <td class="text-end font-monospace fw-bold"><?= number_format($it['total_amount'], 2) ?> SDG</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="4" class="text-end">إجمالي الفاتورة الأصلي:</th>
                                <td class="text-end font-monospace fw-bold"><?= number_format($claim['total_gross'], 2) ?> SDG</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: Audit & Decision -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3 border-light">
                <h5 class="card-title mb-0 fw-bold"><i class="bi bi-shield-check text-success me-2"></i>قرار شركة التأمين والتدقيق</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush mb-3">
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span class="text-muted">المبلغ المطالب به:</span>
                        <strong class="font-monospace text-primary"><?= number_format($claim['claim_amount'], 2) ?> SDG</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span class="text-success fw-bold">المعتمد للصرف:</span>
                        <strong class="font-monospace text-success fs-5"><?= $claim['approved_amount'] ? number_format($claim['approved_amount'], 2) . ' SDG' : '<span class="text-muted fs-6">قيد التدقيق</span>' ?></strong>
                    </li>
                    <?php if ($claim['rejected_amount'] > 0): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span class="text-danger fw-bold">المرفوض:</span>
                        <strong class="font-monospace text-danger">- <?= number_format($claim['rejected_amount'], 2) ?> SDG</strong>
                    </li>
                    <?php endif; ?>
                    <?php if ($claim['settlement_date']): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span class="text-muted">تاريخ التسوية والتحصيل:</span>
                        <span class="fw-bold"><?= $claim['settlement_date'] ?></span>
                    </li>
                    <?php endif; ?>
                </ul>

                <?php if (!empty($claim['rejection_reason'])): ?>
                    <div class="alert alert-danger py-2 small mb-3">
                        <strong>أسباب الرفض / الاستقطاع:</strong><br>
                        <?= nl2br(htmlspecialchars($claim['rejection_reason'])) ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($claim['notes'])): ?>
                    <div class="text-muted small border-top pt-2">
                        <strong>ملاحظات:</strong> <?= htmlspecialchars($claim['notes']) ?>
                    </div>
                <?php endif; ?>

                <button type="button" class="btn btn-outline-warning w-100 mt-3" data-bs-toggle="modal" data-bs-target="#reviewModal">
                    <i class="bi bi-pencil-square me-1"></i>تعديل حالة التدقيق
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Review Claim -->
<div class="modal fade" id="reviewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/insurance/claims/status">
                <?= csrf_field() ?>
                <input type="hidden" name="claim_id" value="<?= $claim['id'] ?>">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-warning"><i class="bi bi-shield-check me-2"></i>تحديث حالة وموافقة شركة التأمين</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">حالة المطالبة بعد التدقيق <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            <option value="under_review" <?= $claim['status'] === 'under_review' ? 'selected' : '' ?>>قيد التدقيق (Under Review)</option>
                            <option value="approved" <?= $claim['status'] === 'approved' ? 'selected' : '' ?>>معتمدة بالكامل (Approved)</option>
                            <option value="partially_approved" <?= $claim['status'] === 'partially_approved' ? 'selected' : '' ?>>معتمدة جزئياً (Partially Approved)</option>
                            <option value="rejected" <?= $claim['status'] === 'rejected' ? 'selected' : '' ?>>مرفوضة من التأمين (Rejected)</option>
                            <option value="settled" <?= $claim['status'] === 'settled' ? 'selected' : '' ?>>تم استلام وتسوية المبلغ (Settled)</option>
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold">المبلغ المعتمد (SDG)</label>
                            <input type="number" step="0.01" min="0" name="approved_amount" class="form-control font-monospace" value="<?= $claim['approved_amount'] ?? $claim['claim_amount'] ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold">المبلغ المرفوض (SDG)</label>
                            <input type="number" step="0.01" min="0" name="rejected_amount" class="form-control font-monospace" value="<?= $claim['rejected_amount'] ?? '0.00' ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">أسباب الرفض أو الاستقطاع إن وجدت</label>
                        <textarea name="rejection_reason" class="form-control" rows="3" placeholder="خدمة غير مشمولة بالوثيقة، تجاوز حد التحمل..."><?= htmlspecialchars($claim['rejection_reason'] ?? '') ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-warning fw-bold px-4">حفظ القرار والاعتماد</button>
                </div>
            </form>
        </div>
    </div>
</div>
