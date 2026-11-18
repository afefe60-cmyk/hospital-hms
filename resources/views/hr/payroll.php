<?php
$layout = 'layouts/main';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h4 mb-1 fw-bold"><i class="bi bi-cash-stack text-primary me-2"></i>مسيرات الرواتب الشهرية والبدلات (Staff Monthly Payroll)</h2>
        <p class="text-muted mb-0">إصدار مسيرات المرتبات الشهرية، احتساب البدلات والاستقطاعات، وصرف المرتبات عبر بنكك / نقدي بالجنيه السوداني (SDG)</p>
    </div>
    <div class="d-flex gap-2">
        <a href="/hr" class="btn btn-outline-secondary d-flex align-items-center gap-1">
            <i class="bi bi-arrow-right"></i> لوحة الموارد البشرية
        </a>
        <a href="/hr/payroll/print-sheet?year=<?= $selectedYear ?>&month=<?= $selectedMonth ?><?= $selectedDept ? '&department_id='.$selectedDept : '' ?>" target="_blank" class="btn btn-outline-primary d-flex align-items-center gap-1">
            <i class="bi bi-printer"></i> طباعة المسير المجمع
        </a>
        <button type="button" class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#generatePayrollModal">
            <i class="bi bi-calculator"></i>
            <span>توليد مسير رواتب الشهر</span>
        </button>
    </div>
</div>

<!-- Financial KPI Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3 text-primary me-3">
                    <i class="bi bi-cash fs-3"></i>
                </div>
                <div>
                    <span class="text-muted d-block small">إجمالي الرواتب الأساسية</span>
                    <h4 class="fw-bold mb-0 text-primary"><?= number_format((float)($summary['sum_basic'] ?? 0), 2) ?> <small class="fs-6 fw-normal text-muted">SDG</small></h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-info bg-opacity-10 p-3 text-info me-3">
                    <i class="bi bi-plus-circle fs-3"></i>
                </div>
                <div>
                    <span class="text-muted d-block small">إجمالي البدلات والحوافز</span>
                    <h4 class="fw-bold mb-0 text-info"><?= number_format((float)($summary['sum_allowances'] ?? 0), 2) ?> <small class="fs-6 fw-normal text-muted">SDG</small></h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-danger bg-opacity-10 p-3 text-danger me-3">
                    <i class="bi bi-dash-circle fs-3"></i>
                </div>
                <div>
                    <span class="text-muted d-block small">إجمالي الاستقطاعات والضرائب</span>
                    <h4 class="fw-bold mb-0 text-danger"><?= number_format((float)($summary['sum_deductions'] ?? 0), 2) ?> <small class="fs-6 fw-normal text-muted">SDG</small></h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-success bg-opacity-10 p-3 text-success me-3">
                    <i class="bi bi-wallet-fill fs-3"></i>
                </div>
                <div>
                    <span class="text-muted d-block small">صافي الرواتب المستحقة</span>
                    <h4 class="fw-bold mb-0 text-success"><?= number_format((float)($summary['sum_net'] ?? 0), 2) ?> <small class="fs-6 fw-normal text-muted">SDG</small></h4>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters Bar -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body">
        <form action="/hr/payroll" method="GET" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label small text-muted">السنة</label>
                <select name="year" class="form-select">
                    <?php for ($y = 2025; $y <= 2028; $y++): ?>
                        <option value="<?= $y ?>" <?= ($selectedYear == $y) ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">الشهر</label>
                <select name="month" class="form-select">
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?= $m ?>" <?= ($selectedMonth == $m) ? 'selected' : '' ?>>
                            شهر <?= $m ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small text-muted">القسم</label>
                <select name="department_id" class="form-select">
                    <option value="">-- كافة الأقسام --</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?= $dept['id'] ?>" <?= ($selectedDept == $dept['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($dept['name_ar']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">حالة الصرف</label>
                <select name="is_paid" class="form-select">
                    <option value="">-- الكل --</option>
                    <option value="1" <?= ($selectedPaid === '1') ? 'selected' : '' ?>>تم الصرف</option>
                    <option value="0" <?= ($selectedPaid === '0') ? 'selected' : '' ?>>بانتظار الصرف</option>
                </select>
            </div>
            <div class="col-md-2 d-grid">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-filter me-1"></i>تصفية
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Payroll Table -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0 fw-bold fs-6">
            <i class="bi bi-list-stars text-primary me-2"></i>كشوفات المرتبات لشهر <?= $selectedMonth ?>/<?= $selectedYear ?> (<?= count($payrollList) ?> كشف مسجل)
        </h5>
        <div class="small text-muted">
            المصروف: <strong class="text-success"><?= number_format((float)($summary['sum_paid'] ?? 0), 2) ?> SDG</strong> | 
            المتبقي: <strong class="text-warning"><?= number_format((float)($summary['sum_unpaid'] ?? 0), 2) ?> SDG</strong>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>رقم الكشف</th>
                        <th>الموظف والوظيفة</th>
                        <th>القسم</th>
                        <th>الراتب الأساسي</th>
                        <th>إجمالي البدلات</th>
                        <th>الاستقطاعات</th>
                        <th>صافي الراتب (SDG)</th>
                        <th>حالة الصرف</th>
                        <th class="text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($payrollList)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-file-earmark-x fs-1 d-block mb-2 text-secondary"></i>
                                لم يتم العثور على كشوفات رواتب للشهر والقسم المحددين.<br>
                                <button type="button" class="btn btn-sm btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#generatePayrollModal">
                                    <i class="bi bi-calculator me-1"></i>توليد كشوفات الرواتب الآن
                                </button>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($payrollList as $p): ?>
                            <tr>
                                <td>
                                    <a href="/hr/payroll/<?= $p['id'] ?>" class="fw-bold text-decoration-none">
                                        <?= htmlspecialchars($p['payroll_number']) ?>
                                    </a>
                                </td>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($p['employee_name']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($p['employee_code']) ?> &bull; <?= htmlspecialchars($p['job_title']) ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border"><?= htmlspecialchars($p['department_name_ar']) ?></span>
                                </td>
                                <td><?= number_format((float)$p['basic_salary'], 2) ?></td>
                                <td class="text-info">+<?= number_format((float)$p['allowances'], 2) ?></td>
                                <td class="text-danger">-<?= number_format((float)$p['deductions'], 2) ?></td>
                                <td>
                                    <span class="fw-bold text-success fs-6"><?= number_format((float)$p['net_salary'], 2) ?></span>
                                </td>
                                <td>
                                    <?php if ($p['is_paid']): ?>
                                        <span class="badge bg-success"><i class="bi bi-check2-circle me-1"></i>تم الصرف</span>
                                        <div class="small text-muted"><?= $p['payment_method'] === 'bankak' ? 'بنكك' : 'نقدي' ?> (<?= $p['payment_date'] ?>)</div>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>بانتظار الصرف</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="/hr/payroll/<?= $p['id'] ?>" class="btn btn-outline-primary" title="عرض المفردات والصرف">
                                            <i class="bi bi-eye"></i> التفاصيل
                                        </a>
                                        <a href="/hr/payroll/print/<?= $p['id'] ?>" target="_blank" class="btn btn-outline-secondary" title="طباعة كشف مفردات مرتب">
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

<!-- Modal: Generate Monthly Payroll -->
<div class="modal fade" id="generatePayrollModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="/hr/payroll/generate" method="POST" class="modal-content">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-calculator text-primary me-2"></i>توليد مسير الرواتب الشهري آلياً</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info small mb-3">
                    <i class="bi bi-info-circle me-1"></i>
                    يقوم النظام باحتساب مسير الرواتب لكافة الموظفين النشطين بناءً على الراتب الأساسي، مع إضافة البدلات التلقائية (بدل السكن والترحيل 15%، حافز المناوبات 10%)، وخصم الاستقطاعات الرسمية (التأمين الاجتماعي 8%، ضريبة كسب العمل 5%).
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">السنة <span class="text-danger">*</span></label>
                        <select name="year" class="form-select" required>
                            <?php for ($y = 2025; $y <= 2028; $y++): ?>
                                <option value="<?= $y ?>" <?= ($selectedYear == $y) ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">الشهر <span class="text-danger">*</span></label>
                        <select name="month" class="form-select" required>
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <option value="<?= $m ?>" <?= ($selectedMonth == $m) ? 'selected' : '' ?>>شهر <?= $m ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">القسم المستهدف</label>
                    <select name="department_id" class="form-select">
                        <option value="">-- كافة الأقسام (المستشفى بالكامل) --</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['name_ar']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text small">اترك فارغاً لتوليد المسير لكافة موظفي المستشفى.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-play-circle me-1"></i>بدء الاحتساب والتوليد</button>
            </div>
        </form>
    </div>
</div>
