<?php
$layout = 'layouts/main';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h4 mb-1 fw-bold"><i class="bi bi-receipt-cutoff text-success me-2"></i>الفوترة الطبية والتحصيل (Medical Billing & Cashier)</h2>
        <p class="text-muted mb-0">إصدار الفواتير العلاجية، تحصيل المدفوعات النقدية وعبر بنكك، وإدارة حسابات المرضى</p>
    </div>
    <div class="d-flex gap-2">
        <a href="/billing/services" class="btn btn-outline-secondary d-flex align-items-center gap-1">
            <i class="bi bi-tag"></i> دليل أسعار الخدمات
        </a>
        <a href="/cashboxes" class="btn btn-outline-primary d-flex align-items-center gap-1">
            <i class="bi bi-safe"></i> ورديات الصناديق
        </a>
        <a href="/billing/create" class="btn btn-success d-flex align-items-center gap-2">
            <i class="bi bi-plus-circle"></i>
            <span>إصدار فاتورة جديدة</span>
        </a>
    </div>
</div>

<!-- Metrics Cards Strip -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3 text-primary me-3">
                    <i class="bi bi-receipt fs-3"></i>
                </div>
                <div>
                    <span class="text-muted d-block small">فواتير اليوم الصادرة</span>
                    <h4 class="fw-bold mb-0"><?= number_format($stats['billed_today'], 2) ?> <small class="fs-6 fw-normal text-muted">SDG</small></h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-success bg-opacity-10 p-3 text-success me-3">
                    <i class="bi bi-cash-stack fs-3"></i>
                </div>
                <div>
                    <span class="text-muted d-block small">المتحصلات اليوم (كافة الطرق)</span>
                    <h4 class="fw-bold mb-0 text-success"><?= number_format($stats['collected_today'], 2) ?> <small class="fs-6 fw-normal text-muted">SDG</small></h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-info bg-opacity-10 p-3 text-info me-3">
                    <i class="bi bi-phone fs-3"></i>
                </div>
                <div>
                    <span class="text-muted d-block small">تحصيلات بنكك (Bankak)</span>
                    <h4 class="fw-bold mb-0 text-info"><?= number_format($stats['bankak_today'], 2) ?> <small class="fs-6 fw-normal text-muted">SDG</small></h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-danger bg-opacity-10 p-3 text-danger me-3">
                    <i class="bi bi-clock-history fs-3"></i>
                </div>
                <div>
                    <span class="text-muted d-block small">إجمالي المتبقي (الذمم المدينة)</span>
                    <h4 class="fw-bold mb-0 text-danger"><?= number_format($stats['total_receivable'], 2) ?> <small class="fs-6 fw-normal text-muted">SDG</small></h4>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Invoices Table with Filters -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white py-3 border-light">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="card-title mb-0 fw-bold"><i class="bi bi-list-check text-primary me-2"></i>سجل الفواتير الطبية</h5>
            <div class="btn-group btn-group-sm">
                <a href="/billing" class="btn <?= empty($selectedStatus) ? 'btn-primary' : 'btn-outline-secondary' ?>">الكل</a>
                <a href="/billing?status=issued" class="btn <?= $selectedStatus === 'issued' ? 'btn-primary' : 'btn-outline-secondary' ?>">غير مسددة (Issued)</a>
                <a href="/billing?status=partially_paid" class="btn <?= $selectedStatus === 'partially_paid' ? 'btn-primary' : 'btn-outline-secondary' ?>">مسددة جزئياً</a>
                <a href="/billing?status=paid" class="btn <?= $selectedStatus === 'paid' ? 'btn-primary' : 'btn-outline-secondary' ?>">مسددة بالكامل</a>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>رقم الفاتورة</th>
                        <th>المريض</th>
                        <th>تاريخ الإصدار</th>
                        <th>الإجمالي (SDG)</th>
                        <th>المدفوع (SDG)</th>
                        <th>المتبقي (SDG)</th>
                        <th>الحالة</th>
                        <th>بواسطة</th>
                        <th class="text-center">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($invoices)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-receipt fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                لا توجد فواتير مسجلة مطابقة للاختيار.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($invoices as $inv): ?>
                            <?php
                                $badgeClass = match($inv['status']) {
                                    'paid' => 'bg-success',
                                    'partially_paid' => 'bg-warning text-dark',
                                    'issued' => 'bg-danger',
                                    'cancelled' => 'bg-secondary',
                                    default => 'bg-light text-dark'
                                };
                                $statusLabel = match($inv['status']) {
                                    'paid' => 'مسددة بالكامل',
                                    'partially_paid' => 'سداد جزئي',
                                    'issued' => 'بانتظار السداد',
                                    'cancelled' => 'ملغاة',
                                    default => $inv['status']
                                };
                                $remaining = max(0.00, $inv['total_patient_due'] - $inv['paid_amount']);
                            ?>
                            <tr>
                                <td class="fw-bold font-monospace text-primary">
                                    <a href="/billing/<?= $inv['id'] ?>" class="text-decoration-none"><?= htmlspecialchars($inv['invoice_number']) ?></a>
                                </td>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($inv['patient_name']) ?></div>
                                    <small class="text-muted font-monospace"><?= htmlspecialchars($inv['mrn']) ?></small>
                                </td>
                                <td>
                                    <div class="small fw-bold"><?= date('Y-m-d', strtotime($inv['created_at'])) ?></div>
                                    <div class="text-muted small"><?= date('h:i A', strtotime($inv['created_at'])) ?></div>
                                </td>
                                <td class="fw-bold font-monospace"><?= number_format($inv['total_patient_due'], 2) ?></td>
                                <td class="text-success font-monospace"><?= number_format($inv['paid_amount'], 2) ?></td>
                                <td class="font-monospace <?= $remaining > 0 ? 'text-danger fw-bold' : 'text-muted' ?>">
                                    <?= number_format($remaining, 2) ?>
                                </td>
                                <td>
                                    <span class="badge <?= $badgeClass ?>"><?= $statusLabel ?></span>
                                </td>
                                <td><small class="text-muted"><?= htmlspecialchars($inv['creator_name'] ?? 'الصراف') ?></small></td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="/billing/<?= $inv['id'] ?>" class="btn btn-outline-primary" title="عرض وسداد">
                                            <i class="bi bi-eye"></i> تفاصيل
                                        </a>
                                        <a href="/billing/<?= $inv['id'] ?>/print" target="_blank" class="btn btn-outline-secondary" title="طباعة الفاتورة">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
