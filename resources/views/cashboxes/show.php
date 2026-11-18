<?php
$layout = 'layouts/main';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <div class="d-flex align-items-center gap-3">
            <h3 class="h4 mb-0 fw-bold font-monospace text-primary"><?= htmlspecialchars($session['session_number']) ?></h3>
            <span class="badge <?= $session['status'] === 'open' ? 'bg-success animate-pulse' : 'bg-secondary' ?> fs-6">
                <?= $session['status'] === 'open' ? 'وردية مفتوحة حالياً' : 'وردية مغلقة ومسلمة' ?>
            </span>
        </div>
        <p class="text-muted mb-0 mt-1">
            الصندوق: <strong><?= htmlspecialchars($session['cashbox_name']) ?></strong> |
            الصراف المسؤول: <strong><?= htmlspecialchars($session['cashier_name']) ?></strong> |
            وقت الفتح: <?= date('Y-m-d h:i A', strtotime($session['opened_at'])) ?>
        </p>
    </div>
    <div class="d-flex gap-2">
        <?php if ($session['status'] === 'open'): ?>
            <button type="button" class="btn btn-warning d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#closeSessionModal">
                <i class="bi bi-door-closed-fill"></i>
                <span>إغلاق وتسليم الوردية</span>
            </button>
        <?php endif; ?>
        <a href="/cashboxes/sessions/<?= $session['id'] ?>/print" target="_blank" class="btn btn-outline-secondary">
            <i class="bi bi-printer me-1"></i>طباعة تقرير الوردية
        </a>
        <a href="/cashboxes" class="btn btn-outline-primary">
            <i class="bi bi-arrow-right me-1"></i>لوحة الصناديق
        </a>
    </div>
</div>

<!-- Collections Breakdown Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
            <span class="text-muted small d-block">عهدة البداية (Float)</span>
            <h4 class="fw-bold mb-0 text-dark"><?= number_format($session['opening_balance'], 2) ?></h4>
            <small class="text-muted">SDG</small>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
            <span class="text-muted small d-block">المتحصل نقداً (Cash)</span>
            <h4 class="fw-bold mb-0 text-success"><?= number_format($breakdown['cash'], 2) ?></h4>
            <small class="text-muted">SDG</small>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
            <span class="text-muted small d-block">متحصلات بنكك (Bankak)</span>
            <h4 class="fw-bold mb-0 text-info"><?= number_format($breakdown['bankak'], 2) ?></h4>
            <small class="text-muted">SDG</small>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
            <span class="text-muted small d-block">إجمالي المتحصلات</span>
            <h4 class="fw-bold mb-0 text-primary"><?= number_format($breakdown['total'], 2) ?></h4>
            <small class="text-muted">SDG (<?= count($payments) ?> عمليات)</small>
        </div>
    </div>
</div>

<?php if ($session['status'] === 'closed'): ?>
<!-- Reconciliation Result Alert -->
<div class="card border-0 shadow-sm rounded-3 mb-4 bg-light">
    <div class="card-body p-3">
        <div class="row g-3 align-items-center">
            <div class="col-md-3">
                <span class="text-muted small d-block">وقت الإغلاق</span>
                <strong><?= date('Y-m-d h:i A', strtotime($session['closed_at'])) ?></strong>
            </div>
            <div class="col-md-3">
                <span class="text-muted small d-block">المفترض نقداً بالدرج (عهدة + كاش)</span>
                <strong class="font-monospace fs-6"><?= number_format($session['expected_closing'], 2) ?> SDG</strong>
            </div>
            <div class="col-md-3">
                <span class="text-muted small d-block">الرصيد الفعلي بعد الجرد</span>
                <strong class="font-monospace fs-6 text-primary"><?= number_format($session['actual_closing'], 2) ?> SDG</strong>
            </div>
            <div class="col-md-3">
                <span class="text-muted small d-block">الفارق (عجز / زيادة)</span>
                <?php $diff = (float)$session['difference']; ?>
                <strong class="font-monospace fs-5 <?= $diff < 0 ? 'text-danger' : ($diff > 0 ? 'text-success' : 'text-muted') ?>">
                    <?= ($diff > 0 ? '+' : '') . number_format($diff, 2) ?> SDG
                </strong>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Payments List in Shift -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white py-3 border-light">
        <h5 class="card-title mb-0 fw-bold"><i class="bi bi-list-check text-primary me-2"></i>سجل العمليات والمقبوضات خلال الوردية</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>رقم السند</th>
                        <th>الفاتورة</th>
                        <th>المريض</th>
                        <th>التوقيت</th>
                        <th>طريقة الدفع</th>
                        <th>المرجع</th>
                        <th class="text-end">المبلغ (SDG)</th>
                        <th class="text-center">طباعة</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($payments)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">لم يتم تحصيل أي مبالغ في هذه الوردية بعد.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($payments as $p): ?>
                            <tr>
                                <td class="fw-bold font-monospace text-success"><?= htmlspecialchars($p['receipt_number']) ?></td>
                                <td class="font-monospace text-primary">
                                    <a href="/billing/<?= $p['invoice_id'] ?>"><?= htmlspecialchars($p['invoice_number']) ?></a>
                                </td>
                                <td>
                                    <div><?= htmlspecialchars($p['patient_name']) ?></div>
                                    <small class="text-muted font-monospace"><?= htmlspecialchars($p['mrn']) ?></small>
                                </td>
                                <td><?= date('h:i A', strtotime($p['created_at'])) ?></td>
                                <td>
                                    <span class="badge <?= $p['payment_method'] === 'bankak' ? 'bg-info text-white' : ($p['payment_method'] === 'card' ? 'bg-primary' : 'bg-success') ?>">
                                        <?= $p['payment_method'] === 'bankak' ? 'بنكك' : ($p['payment_method'] === 'card' ? 'بطاقة' : 'نقداً') ?>
                                    </span>
                                </td>
                                <td class="font-monospace small"><?= htmlspecialchars($p['transaction_reference'] ?? '-') ?></td>
                                <td class="text-end font-monospace fw-bold text-success"><?= number_format($p['amount'], 2) ?></td>
                                <td class="text-center">
                                    <a href="/billing/receipt/<?= $p['id'] ?>/print" target="_blank" class="btn btn-outline-secondary btn-sm"><i class="bi bi-printer"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Close Session -->
<div class="modal fade" id="closeSessionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/cashboxes/close">
                <?= csrf_field() ?>
                <input type="hidden" name="session_id" value="<?= $session['id'] ?>">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-warning"><i class="bi bi-door-closed-fill me-2"></i>إغلاق وتسليم وردية الصندوق</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-light border py-2 mb-3 small">
                        <div>عهدة البداية: <strong><?= number_format($session['opening_balance'], 2) ?> SDG</strong></div>
                        <div>المتحصل نقداً (كاش): <strong><?= number_format($breakdown['cash'], 2) ?> SDG</strong></div>
                        <hr class="my-1">
                        <div>المفترض نقداً بالدرج: <strong class="text-primary font-monospace"><?= number_format($session['opening_balance'] + $breakdown['cash'], 2) ?> SDG</strong></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">النقدية الفعلية بعد الجرد اليدوي (SDG) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" name="actual_closing" class="form-control form-control-lg font-monospace text-end fw-bold" placeholder="0.00" required>
                        <small class="text-muted">قم بعد النقدية الفعلية الموجودة في درج الصندوق وإدخالها هنا.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">ملاحظات الإغلاق والتسليم</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="ملاحظات تسليم الصندوق للمشرف أو الصراف التالي..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-warning fw-bold px-4">تأكيد الإغلاق والتسليم</button>
                </div>
            </form>
        </div>
    </div>
</div>
