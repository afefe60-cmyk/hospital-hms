<?php
$layout = 'layouts/main';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <div class="d-flex align-items-center gap-3">
            <h3 class="h4 mb-0 fw-bold font-monospace text-primary"><?= htmlspecialchars($invoice['invoice_number']) ?></h3>
            <?php
                $badgeClass = match($invoice['status']) {
                    'paid' => 'bg-success',
                    'partially_paid' => 'bg-warning text-dark',
                    'issued' => 'bg-danger',
                    'cancelled' => 'bg-secondary',
                    default => 'bg-light text-dark'
                };
                $statusLabel = match($invoice['status']) {
                    'paid' => 'مسددة بالكامل',
                    'partially_paid' => 'سداد جزئي',
                    'issued' => 'بانتظار السداد',
                    'cancelled' => 'ملغاة',
                    default => $invoice['status']
                };
            ?>
            <span class="badge <?= $badgeClass ?> fs-6"><?= $statusLabel ?></span>
        </div>
        <p class="text-muted mb-0 mt-1">تاريخ الإصدار: <strong><?= date('Y-m-d h:i A', strtotime($invoice['created_at'])) ?></strong> | تم الإنشاء بواسطة: <?= htmlspecialchars($invoice['creator_name'] ?? 'الصراف') ?></p>
    </div>
    <div class="d-flex gap-2">
        <?php if ($remainingDue > 0): ?>
            <button type="button" class="btn btn-success d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#paymentModal">
                <i class="bi bi-cash-stack"></i>
                <span>تحصيل دفعة / سداد الفاتورة</span>
            </button>
        <?php endif; ?>
        <a href="/billing/<?= $invoice['id'] ?>/print" target="_blank" class="btn btn-outline-secondary">
            <i class="bi bi-printer me-1"></i>طباعة الفاتورة
        </a>
        <a href="/billing" class="btn btn-outline-primary">
            <i class="bi bi-arrow-right me-1"></i>رجوع للفوترة
        </a>
    </div>
</div>

<!-- Patient Info Strip -->
<div class="card border-0 shadow-sm rounded-3 mb-4 bg-light">
    <div class="card-body p-3">
        <div class="row g-3 align-items-center">
            <div class="col-md-3">
                <span class="text-muted d-block small">اسم المريض</span>
                <span class="fw-bold text-dark fs-6"><?= htmlspecialchars($invoice['patient_name']) ?></span>
            </div>
            <div class="col-md-2">
                <span class="text-muted d-block small">الرقم الطبي (MRN)</span>
                <span class="fw-bold font-monospace"><?= htmlspecialchars($invoice['mrn']) ?></span>
            </div>
            <div class="col-md-2">
                <span class="text-muted d-block small">العمر والنوع</span>
                <span><?= $invoice['patient_age'] ?> سنة (<?= $invoice['patient_gender'] === 'male' ? 'ذكر' : 'أنثى' ?>)</span>
            </div>
            <div class="col-md-2">
                <span class="text-muted d-block small">الهاتف</span>
                <span><?= htmlspecialchars($invoice['phone_primary']) ?></span>
            </div>
            <div class="col-md-3">
                <span class="text-muted d-block small">المتبقي المطلوب سداده</span>
                <span class="fw-bold fs-5 <?= $remainingDue > 0 ? 'text-danger' : 'text-success' ?>">
                    <?= number_format($remainingDue, 2) ?> SDG
                </span>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Left: Line Items -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3 border-light">
                <h5 class="card-title mb-0 fw-bold"><i class="bi bi-card-checklist text-primary me-2"></i>بنود الخدمات الطبية والعلاجية</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>الخدمة / البند</th>
                                <th class="text-center">الكمية</th>
                                <th class="text-end">سعر الوحدة</th>
                                <th class="text-end">الإجمالي</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; foreach ($items as $item): ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($item['description']) ?></div>
                                        <?php if (!empty($item['service_code'])): ?>
                                            <small class="text-muted font-monospace"><?= htmlspecialchars($item['service_code']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center"><?= (float)$item['quantity'] ?></td>
                                    <td class="text-end font-monospace"><?= number_format($item['unit_price'], 2) ?> SDG</td>
                                    <td class="text-end font-monospace fw-bold"><?= number_format($item['total_amount'], 2) ?> SDG</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Payments History -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white py-3 border-light d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 fw-bold"><i class="bi bi-cash-stack text-success me-2"></i>سجل التحصيل وسندات القبض</h5>
                <?php if ($remainingDue > 0): ?>
                    <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#paymentModal">
                        <i class="bi bi-plus-lg me-1"></i>تحصيل دفعة
                    </button>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>رقم السند</th>
                                <th>تاريخ القبض</th>
                                <th>طريقة الدفع</th>
                                <th>رقم المرجع / العملية</th>
                                <th class="text-end">المبلغ المقبوض</th>
                                <th>الصراف</th>
                                <th class="text-center">طباعة</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($payments)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        لم يتم تسجيل أي مدفوعات لهذه الفاتورة حتى الآن.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($payments as $p): ?>
                                    <?php
                                        $methodBadge = match($p['payment_method']) {
                                            'bankak' => 'bg-info text-white',
                                            'card' => 'bg-primary',
                                            'bank_transfer' => 'bg-dark',
                                            default => 'bg-success'
                                        };
                                        $methodLabel = match($p['payment_method']) {
                                            'bankak' => 'بنكك (Bankak)',
                                            'card' => 'بطاقة صراف',
                                            'bank_transfer' => 'تحويل بنكي',
                                            default => 'نقداً (كاش)'
                                        };
                                    ?>
                                    <tr>
                                        <td class="fw-bold font-monospace text-success"><?= htmlspecialchars($p['receipt_number']) ?></td>
                                        <td><?= date('Y-m-d h:i A', strtotime($p['created_at'])) ?></td>
                                        <td><span class="badge <?= $methodBadge ?>"><?= $methodLabel ?></span></td>
                                        <td class="font-monospace small"><?= htmlspecialchars($p['transaction_reference'] ?? '-') ?></td>
                                        <td class="text-end fw-bold font-monospace text-success"><?= number_format($p['amount'], 2) ?> SDG</td>
                                        <td><small class="text-muted"><?= htmlspecialchars($p['collector_name'] ?? 'الصراف') ?></small></td>
                                        <td class="text-center">
                                            <a href="/billing/receipt/<?= $p['id'] ?>/print" target="_blank" class="btn btn-outline-secondary btn-sm" title="طباعة سند القبض">
                                                <i class="bi bi-printer"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: Financial Summary -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3 border-light">
                <h5 class="card-title mb-0 fw-bold"><i class="bi bi-calculator text-primary me-2"></i>ملخص الحساب المالي</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush mb-3">
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span class="text-muted">إجمالي الخدمات (Gross):</span>
                        <strong class="font-monospace"><?= number_format($invoice['total_gross'], 2) ?> SDG</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span class="text-muted">الخصومات المعتمدة:</span>
                        <span class="font-monospace text-danger">- <?= number_format($invoice['total_discount'], 2) ?> SDG</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span class="text-muted">التغطية التأمينية:</span>
                        <span class="font-monospace text-muted">- <?= number_format($invoice['total_insurance'], 2) ?> SDG</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-light p-2 rounded">
                        <span class="fw-bold">الصافي المطلوب من المريض:</span>
                        <strong class="font-monospace fs-5 text-dark"><?= number_format($invoice['total_patient_due'], 2) ?> SDG</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span class="text-success fw-bold">المدفوع حتى الآن:</span>
                        <strong class="font-monospace text-success"><?= number_format($invoice['paid_amount'], 2) ?> SDG</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span class="text-danger fw-bold">المتبقي المطلوب سداده:</span>
                        <strong class="font-monospace text-danger fs-5"><?= number_format($remainingDue, 2) ?> SDG</strong>
                    </li>
                </ul>

                <?php if ($remainingDue > 0): ?>
                    <button type="button" class="btn btn-success w-100 py-2 fw-bold" data-bs-toggle="modal" data-bs-target="#paymentModal">
                        <i class="bi bi-cash-stack me-1"></i>تحصيل دفعة الآن
                    </button>
                <?php else: ?>
                    <div class="alert alert-success text-center mb-0 py-2 small fw-bold">
                        <i class="bi bi-check-circle-fill me-1"></i>الفاتورة مسددة بالكامل
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Collect Payment -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/billing/payment">
                <?= csrf_field() ?>
                <input type="hidden" name="invoice_id" value="<?= $invoice['id'] ?>">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-success"><i class="bi bi-cash-stack me-2"></i>تحصيل دفعة وإصدار سند قبض</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-light border py-2 mb-3 small">
                        <div>المريض: <strong><?= htmlspecialchars($invoice['patient_name']) ?></strong></div>
                        <div>الفاتورة: <strong class="font-monospace"><?= htmlspecialchars($invoice['invoice_number']) ?></strong></div>
                        <div>المتبقي: <strong class="text-danger font-monospace"><?= number_format($remainingDue, 2) ?> SDG</strong></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">المبلغ المحصل (SDG) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0.01" max="<?= $remainingDue ?>" name="amount" class="form-control form-control-lg text-end font-monospace fw-bold" value="<?= $remainingDue ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">طريقة الدفع <span class="text-danger">*</span></label>
                        <select name="payment_method" class="form-select" id="paymentMethodSelect" required>
                            <option value="cash">نقداً (كاش - Cash)</option>
                            <option value="bankak">تطبيق بنكك (Bankak - بنك الخرطوم)</option>
                            <option value="card">بطاقة صراف آلي (ATM / POS)</option>
                            <option value="bank_transfer">تحويل مصرفي (Bank Transfer)</option>
                        </select>
                    </div>

                    <div class="mb-3" id="refDiv">
                        <label class="form-label fw-bold">رقم إشعار التحويل / كود عملية بنكك</label>
                        <input type="text" name="transaction_reference" class="form-control" placeholder="مثال: TRX-BOC-1234567">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">ملاحظات التحصيل</label>
                        <input type="text" name="notes" class="form-control" placeholder="سداد رسوم كشف، أدوية، تنويم...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-success fw-bold px-4">تأكيد الاستلام وطباعة السند</button>
                </div>
            </form>
        </div>
    </div>
</div>
