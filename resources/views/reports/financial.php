<?php
$layout = 'layouts/main';
$fin = $metrics['financial'];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h4 mb-1 fw-bold"><i class="bi bi-cash-stack text-success me-2"></i>التقرير المالي والإيرادات التفصيلية (Financial & Revenue Report)</h2>
        <p class="text-muted mb-0">كشف الإيرادات، المتحصلات النقدية والبنكية (بنكك)، الفواتير والمطالبات بالجنيه السوداني (SDG)</p>
    </div>
    <div class="d-flex gap-2">
        <a href="/reports" class="btn btn-outline-secondary d-flex align-items-center gap-1">
            <i class="bi bi-arrow-right"></i> لوحة الإحصائيات العامة
        </a>
        <a href="/reports/export?type=financial&start_date=<?= $startDate ?>&end_date=<?= $endDate ?>" class="btn btn-outline-primary d-flex align-items-center gap-1">
            <i class="bi bi-download"></i> تصدير البيانات (CSV)
        </a>
        <a href="/reports/print?start_date=<?= $startDate ?>&end_date=<?= $endDate ?>" target="_blank" class="btn btn-primary d-flex align-items-center gap-2">
            <i class="bi bi-printer"></i> طباعة التقرير
        </a>
    </div>
</div>

<!-- Filter Bar -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body">
        <form action="/reports/financial" method="GET" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label small text-muted">من تاريخ</label>
                <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($startDate) ?>">
            </div>
            <div class="col-md-5">
                <label class="form-label small text-muted">إلى تاريخ</label>
                <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($endDate) ?>">
            </div>
            <div class="col-md-2 d-grid">
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-funnel me-1"></i>تطبيق الفلترة
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Financial Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body">
                <span class="text-muted small d-block">إجمالي الفواتير الصادرة</span>
                <h4 class="fw-bold mb-0 text-dark"><?= number_format($fin['gross_billed'], 2) ?> <small class="fs-6 fw-normal text-muted">SDG</small></h4>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body">
                <span class="text-muted small d-block">المتحصلات النقدية وعبر بنكك</span>
                <h4 class="fw-bold mb-0 text-success"><?= number_format($fin['total_collected'], 2) ?> <small class="fs-6 fw-normal text-muted">SDG</small></h4>
                <div class="small text-muted mt-1">نقدي: <?= number_format($fin['cash_collected'], 2) ?> | بنكك: <?= number_format($fin['bankak_collected'], 2) ?></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body">
                <span class="text-muted small d-block">مستحقات التأمين المعتمدة</span>
                <h4 class="fw-bold mb-0 text-primary"><?= number_format($fin['insurance_approved'], 2) ?> <small class="fs-6 fw-normal text-muted">SDG</small></h4>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body">
                <span class="text-muted small d-block">المتبقي الآجل على المرضى</span>
                <h4 class="fw-bold mb-0 text-danger"><?= number_format($fin['total_unpaid'], 2) ?> <small class="fs-6 fw-normal text-muted">SDG</small></h4>
            </div>
        </div>
    </div>
</div>

<!-- Invoices Table -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0 fw-bold fs-6">
            <i class="bi bi-receipt text-primary me-2"></i>فواتير الفترة المحددة (<?= count($invoices) ?> فاتورة)
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>رقم الفاتورة</th>
                        <th>المريض</th>
                        <th>التاريخ</th>
                        <th>الإجمالي (SDG)</th>
                        <th>تغطية التأمين</th>
                        <th>المدفوع نقداً/بنكك</th>
                        <th>المتبقي</th>
                        <th>الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($invoices)): ?>
                        <tr><td colspan="8" class="text-center py-4 text-muted">لا توجد فواتير مسجلة في هذه الفترة.</td></tr>
                    <?php else: ?>
                        <?php foreach ($invoices as $inv): ?>
                            <tr>
                                <td class="font-monospace fw-bold">
                                    <a href="/billing/<?= $inv['id'] ?>" class="text-decoration-none"><?= htmlspecialchars($inv['invoice_number']) ?></a>
                                </td>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($inv['patient_name']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($inv['mrn']) ?></small>
                                </td>
                                <td><?= $inv['invoice_date'] ?></td>
                                <td class="fw-bold"><?= number_format((float)$inv['total_amount'], 2) ?></td>
                                <td class="text-primary"><?= number_format((float)$inv['insurance_amount'], 2) ?></td>
                                <td class="text-success"><?= number_format((float)$inv['paid_amount'], 2) ?></td>
                                <td class="<?= ((float)$inv['remaining_amount'] > 0) ? 'text-danger fw-bold' : 'text-muted' ?>">
                                    <?= number_format((float)$inv['remaining_amount'], 2) ?>
                                </td>
                                <td>
                                    <?php
                                    $stBadge = match($inv['status']) {
                                        'paid' => 'bg-success',
                                        'partially_paid' => 'bg-warning text-dark',
                                        'unpaid' => 'bg-danger',
                                        default => 'bg-secondary'
                                    };
                                    $stName = match($inv['status']) {
                                        'paid' => 'مسددة بالكامل',
                                        'partially_paid' => 'مسددة جزئياً',
                                        'unpaid' => 'غير مسددة',
                                        default => $inv['status']
                                    };
                                    ?>
                                    <span class="badge <?= $stBadge ?>"><?= $stName ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Collections & Payments Table -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0 fw-bold fs-6">
            <i class="bi bi-cash text-success me-2"></i>سندات التحصيل والمقبوضات بالفترة (<?= count($payments) ?> سند)
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>رقم السند</th>
                        <th>الفاتورة والمريض</th>
                        <th>تاريخ السداد</th>
                        <th>طريقة الدفع</th>
                        <th>الرقم المرجعي (بنكك/إيصال)</th>
                        <th class="text-end">المبلغ المحصل (SDG)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($payments)): ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">لا توجد مقبوضات مسجلة في هذه الفترة.</td></tr>
                    <?php else: ?>
                        <?php foreach ($payments as $pm): ?>
                            <tr>
                                <td class="font-monospace fw-bold"><?= htmlspecialchars($pm['receipt_number']) ?></td>
                                <td>
                                    <div><?= htmlspecialchars($pm['invoice_number']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($pm['patient_name']) ?></small>
                                </td>
                                <td><?= $pm['payment_date'] ?></td>
                                <td>
                                    <?php if ($pm['payment_method'] === 'bankak'): ?>
                                        <span class="badge bg-primary"><i class="bi bi-phone me-1"></i>بنكك (Bankak)</span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-dark border"><i class="bi bi-cash me-1"></i>نقداً (Cash)</span>
                                    <?php endif; ?>
                                </td>
                                <td class="font-monospace"><?= htmlspecialchars($pm['transaction_reference'] ?? '-') ?></td>
                                <td class="text-end fw-bold text-success fs-6"><?= number_format((float)$pm['amount'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
