<?php
$layout = 'layouts/main';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h4 mb-1 fw-bold"><i class="bi bi-shield-check text-primary me-2"></i>إدارة التأمين الطبي والمطالبات (Health Insurance & Claims)</h2>
        <p class="text-muted mb-0">إدارة تعاقدات شركات التأمين، وثائق المرضى، نسب التغطية والتحمل، والمطالبات التأمينية</p>
    </div>
    <div class="d-flex gap-2">
        <a href="/insurance/companies" class="btn btn-outline-secondary d-flex align-items-center gap-1">
            <i class="bi bi-building"></i> شركات التأمين
        </a>
        <a href="/insurance/policies" class="btn btn-outline-primary d-flex align-items-center gap-1">
            <i class="bi bi-card-heading"></i> بطاقات وثائق المرضى
        </a>
        <a href="/insurance/claims/create" class="btn btn-primary d-flex align-items-center gap-2">
            <i class="bi bi-plus-circle"></i>
            <span>رفع مطالبة تأمينية جديدة</span>
        </a>
    </div>
</div>

<!-- Metrics Cards Strip -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3 text-primary me-3">
                    <i class="bi bi-file-earmark-medical fs-3"></i>
                </div>
                <div>
                    <span class="text-muted d-block small">إجمالي المطالبات المرفوعة</span>
                    <h4 class="fw-bold mb-0 text-primary"><?= number_format($stats['total_claimed'], 2) ?> <small class="fs-6 fw-normal text-muted">SDG</small></h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-success bg-opacity-10 p-3 text-success me-3">
                    <i class="bi bi-check-circle fs-3"></i>
                </div>
                <div>
                    <span class="text-muted d-block small">المبالغ المعتمدة من التأمين</span>
                    <h4 class="fw-bold mb-0 text-success"><?= number_format($stats['total_approved'], 2) ?> <small class="fs-6 fw-normal text-muted">SDG</small></h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-warning bg-opacity-10 p-3 text-warning me-3">
                    <i class="bi bi-hourglass-split fs-3"></i>
                </div>
                <div>
                    <span class="text-muted d-block small">مطالبات قيد المراجعة والتدقيق</span>
                    <h4 class="fw-bold mb-0 text-warning"><?= number_format($stats['pending_count']) ?> <small class="fs-6 fw-normal text-muted">مطالبة</small></h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-info bg-opacity-10 p-3 text-info me-3">
                    <i class="bi bi-building-check fs-3"></i>
                </div>
                <div>
                    <span class="text-muted d-block small">شركات التأمين المعتمدة</span>
                    <h4 class="fw-bold mb-0 text-info"><?= number_format($stats['companies_count']) ?> <small class="fs-6 fw-normal text-muted">شركة</small></h4>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Claims Table with Filters -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-header bg-white py-3 border-light d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="card-title mb-0 fw-bold"><i class="bi bi-list-check text-primary me-2"></i>جدول المطالبات التأمينية</h5>
        <div class="d-flex gap-2">
            <form method="GET" action="/insurance" class="d-flex gap-2 align-items-center">
                <select name="company_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- جميع الشركات --</option>
                    <?php foreach ($companies as $comp): ?>
                        <option value="<?= $comp['id'] ?>" <?= ((string)$selectedCompany === (string)$comp['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($comp['name_ar']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- جميع الحالات --</option>
                    <option value="submitted" <?= $selectedStatus === 'submitted' ? 'selected' : '' ?>>مرفوعة حديثاً (Submitted)</option>
                    <option value="under_review" <?= $selectedStatus === 'under_review' ? 'selected' : '' ?>>قيد التدقيق (Under Review)</option>
                    <option value="approved" <?= $selectedStatus === 'approved' ? 'selected' : '' ?>>معتمدة بالكامل (Approved)</option>
                    <option value="partially_approved" <?= $selectedStatus === 'partially_approved' ? 'selected' : '' ?>>معتمدة جزئياً</option>
                    <option value="rejected" <?= $selectedStatus === 'rejected' ? 'selected' : '' ?>>مرفوضة (Rejected)</option>
                    <option value="settled" <?= $selectedStatus === 'settled' ? 'selected' : '' ?>>تمت التسوية والتحصيل (Settled)</option>
                </select>
                <?php if ($selectedCompany || $selectedStatus): ?>
                    <a href="/insurance" class="btn btn-outline-secondary btn-sm text-nowrap">إلغاء الفلترة</a>
                <?php endif; ?>
            </form>
            <a href="/insurance/batch/print<?= $selectedCompany ? '?company_id='.$selectedCompany : '' ?>" target="_blank" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
                <i class="bi bi-printer"></i> كشف مجمع
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>رقم المطالبة</th>
                        <th>شركة التأمين</th>
                        <th>المريض</th>
                        <th>رقم الوثيقة</th>
                        <th>الفاتورة</th>
                        <th>تاريخ الرفع</th>
                        <th class="text-end">مبلغ المطالبة (SDG)</th>
                        <th class="text-end">المعتمد (SDG)</th>
                        <th>الحالة</th>
                        <th class="text-center">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($claims)): ?>
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                لا توجد مطالبات تأمينية مطابقة للاختيار المحدد.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($claims as $c): ?>
                            <?php
                                $statusBadge = match($c['status']) {
                                    'approved' => 'bg-success',
                                    'settled' => 'bg-primary',
                                    'partially_approved' => 'bg-info text-dark',
                                    'under_review' => 'bg-warning text-dark',
                                    'rejected' => 'bg-danger',
                                    default => 'bg-secondary'
                                };
                                $statusLabel = match($c['status']) {
                                    'approved' => 'معتمدة بالكامل',
                                    'settled' => 'تمت التسوية',
                                    'partially_approved' => 'اعتماد جزئي',
                                    'under_review' => 'قيد التدقيق',
                                    'rejected' => 'مرفوضة',
                                    default => 'مرفوعة حديثاً'
                                };
                            ?>
                            <tr>
                                <td class="fw-bold font-monospace text-primary">
                                    <a href="/insurance/claims/<?= $c['id'] ?>" class="text-decoration-none"><?= htmlspecialchars($c['claim_number']) ?></a>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($c['company_name']) ?></div>
                                    <small class="text-muted font-monospace"><?= htmlspecialchars($c['company_code']) ?></small>
                                </td>
                                <td>
                                    <div><?= htmlspecialchars($c['patient_name']) ?></div>
                                    <small class="text-muted font-monospace"><?= htmlspecialchars($c['mrn']) ?></small>
                                </td>
                                <td class="font-monospace small"><?= htmlspecialchars($c['policy_number']) ?></td>
                                <td>
                                    <a href="/billing/<?= $c['invoice_id'] ?>" class="font-monospace small text-muted"><?= htmlspecialchars($c['invoice_number']) ?></a>
                                </td>
                                <td><?= date('Y-m-d', strtotime($c['submission_date'])) ?></td>
                                <td class="text-end font-monospace fw-bold text-dark"><?= number_format($c['claim_amount'], 2) ?></td>
                                <td class="text-end font-monospace <?= $c['approved_amount'] ? 'text-success fw-bold' : 'text-muted' ?>">
                                    <?= $c['approved_amount'] ? number_format($c['approved_amount'], 2) : '-' ?>
                                </td>
                                <td>
                                    <span class="badge <?= $statusBadge ?>"><?= $statusLabel ?></span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="/insurance/claims/<?= $c['id'] ?>" class="btn btn-outline-primary" title="عرض وتدقيق المطالبة">
                                            <i class="bi bi-eye"></i> تفاصيل
                                        </a>
                                        <a href="/insurance/claims/<?= $c['id'] ?>/print" target="_blank" class="btn btn-outline-secondary" title="طباعة نموذج المطالبة">
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
