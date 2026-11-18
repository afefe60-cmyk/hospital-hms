<?php
$layout = 'layouts/main';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h4 mb-1 fw-bold"><i class="bi bi-building text-primary me-2"></i>شركات التأمين الطبي المعتمدة (Insurance Companies)</h2>
        <p class="text-muted mb-0">بيانات الاتصال، نسب التغطية الافتراضية، وإجمالي المطالبات لكل شركة</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addCompanyModal">
            <i class="bi bi-plus-lg"></i>
            <span>إضافة شركة تأمين جديدة</span>
        </button>
        <a href="/insurance" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-right me-1"></i>لوحة التأمين
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>كود الشركة</th>
                        <th>اسم الشركة بالعربية</th>
                        <th>الاسم بالإنجليزية</th>
                        <th>هاتف الاتصال</th>
                        <th>البريد الإلكتروني</th>
                        <th class="text-center">نسبة التغطية الافتراضية</th>
                        <th class="text-center">الوثائق النشطة</th>
                        <th class="text-end">إجمالي المطالبات (SDG)</th>
                        <th class="text-center">الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($companies)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">لا توجد شركات تأمين مسجلة.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($companies as $comp): ?>
                            <tr>
                                <td class="font-monospace fw-bold text-primary"><?= htmlspecialchars($comp['code']) ?></td>
                                <td class="fw-bold text-dark"><?= htmlspecialchars($comp['name_ar']) ?></td>
                                <td class="text-muted"><?= htmlspecialchars($comp['name_en']) ?></td>
                                <td><?= htmlspecialchars($comp['phone']) ?></td>
                                <td><?= htmlspecialchars($comp['email'] ?? '-') ?></td>
                                <td class="text-center">
                                    <span class="badge bg-primary fs-6"><?= $comp['default_coverage_rate'] ?>%</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border"><?= $comp['active_policies_count'] ?? 0 ?> وثيقة</span>
                                </td>
                                <td class="text-end font-monospace fw-bold text-success">
                                    <?= number_format($comp['total_claims_amount'] ?? 0, 2) ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success">معتمدة ونشطة</span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Add Insurance Company -->
<div class="modal fade" id="addCompanyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/insurance/companies">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-primary"><i class="bi bi-building-add me-2"></i>إضافة شركة تأمين طبي جديدة</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">كود الشركة <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control" placeholder="مثال: INS-UNITED" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">اسم الشركة بالعربية <span class="text-danger">*</span></label>
                        <input type="text" name="name_ar" class="form-control" placeholder="مثال: شركة التأمين المتحدة" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">اسم الشركة بالإنجليزية</label>
                        <input type="text" name="name_en" class="form-control" placeholder="مثال: United Insurance Co.">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">هاتف الاتصال / إدارة المطالبات <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control" placeholder="+249..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">البريد الإلكتروني</label>
                        <input type="email" name="email" class="form-control" placeholder="claims@company.sd">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">العنوان</label>
                        <input type="text" name="address" class="form-control" placeholder="الخرطوم...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">نسبة التغطية الافتراضية (%)</label>
                        <input type="number" step="0.5" min="0" max="100" name="default_coverage_rate" class="form-control text-center" value="80.00">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary fw-bold">حفظ بيانات الشركة</button>
                </div>
            </form>
        </div>
    </div>
</div>
