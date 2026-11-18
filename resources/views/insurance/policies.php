<?php
$layout = 'layouts/main';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h4 mb-1 fw-bold"><i class="bi bi-card-heading text-primary me-2"></i>وثائق وبطاقات التأمين للمرضى (Patient Policies)</h2>
        <p class="text-muted mb-0">ربط المرضى بشركات التأمين المعتمدة وتحديد أرقام العضوية ونسب التغطية وسريان البطاقة</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addPolicyModal">
            <i class="bi bi-plus-lg"></i>
            <span>ربط وثيقة تأمين لمريض</span>
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
                        <th>رقم الوثيقة</th>
                        <th>رقم العضوية</th>
                        <th>المريض</th>
                        <th>شركة التأمين</th>
                        <th class="text-center">نسبة التغطية</th>
                        <th class="text-end">حد التحمل (Copay SDG)</th>
                        <th>تاريخ الانتهاء</th>
                        <th class="text-center">الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($policies)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">لا توجد وثائق تأمين مسجلة للمرضى.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($policies as $pol): ?>
                            <?php
                                $isExpired = strtotime($pol['expiry_date']) < time();
                            ?>
                            <tr>
                                <td class="font-monospace fw-bold text-primary"><?= htmlspecialchars($pol['policy_number']) ?></td>
                                <td class="font-monospace"><?= htmlspecialchars($pol['member_number'] ?? '-') ?></td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($pol['patient_name']) ?></div>
                                    <small class="text-muted font-monospace"><?= htmlspecialchars($pol['mrn']) ?></small>
                                </td>
                                <td><?= htmlspecialchars($pol['company_name']) ?></td>
                                <td class="text-center">
                                    <span class="badge bg-primary fs-6"><?= $pol['coverage_percentage'] ?>%</span>
                                </td>
                                <td class="text-end font-monospace">
                                    <?= $pol['copay_limit'] ? number_format($pol['copay_limit'], 2) : 'غير محدد' ?>
                                </td>
                                <td>
                                    <span class="<?= $isExpired ? 'text-danger fw-bold' : '' ?>"><?= $pol['expiry_date'] ?></span>
                                </td>
                                <td class="text-center">
                                    <?php if ($isExpired): ?>
                                        <span class="badge bg-danger">منتهية الصلاحية</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">سارية ومفعلة</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Add Policy to Patient -->
<div class="modal fade" id="addPolicyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/insurance/policies">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-primary"><i class="bi bi-card-plus me-2"></i>ربط وثيقة تأمين بملف المريض</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">اختر المريض <span class="text-danger">*</span></label>
                        <select name="patient_id" class="form-select select2" required>
                            <option value="">-- ابحث أو اختر المريض --</option>
                            <?php foreach ($patients as $p): ?>
                                <option value="<?= $p['id'] ?>">
                                    <?= htmlspecialchars($p['name']) ?> (MRN: <?= htmlspecialchars($p['mrn']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">شركة التأمين المتعاقدة <span class="text-danger">*</span></label>
                        <select name="company_id" class="form-select" required>
                            <option value="">-- اختر شركة التأمين --</option>
                            <?php foreach ($companies as $comp): ?>
                                <option value="<?= $comp['id'] ?>"><?= htmlspecialchars($comp['name_ar']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">رقم الوثيقة (Policy Number) <span class="text-danger">*</span></label>
                        <input type="text" name="policy_number" class="form-control" placeholder="مثال: SHK-POL-2026-1234" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">رقم العضوية بالبطاقة (Member / Card ID)</label>
                        <input type="text" name="member_number" class="form-control" placeholder="مثال: MEM-9944">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold">نسبة التغطية (%) <span class="text-danger">*</span></label>
                            <input type="number" step="0.5" min="1" max="100" name="coverage_percentage" class="form-control text-center" value="80.00" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold">حد التحمل (Copay SDG)</label>
                            <input type="number" step="100" min="0" name="copay_limit" class="form-control font-monospace" placeholder="اختياري">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">تاريخ انتهاء صلاحية البطاقة <span class="text-danger">*</span></label>
                        <input type="date" name="expiry_date" class="form-control" value="<?= date('Y-12-31', strtotime('+1 year')) ?>" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary fw-bold">حفظ وربط الوثيقة</button>
                </div>
            </form>
        </div>
    </div>
</div>
