<?php
$layout = 'layouts/main';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h4 mb-1 fw-bold"><i class="bi bi-people text-info me-2"></i>تقرير الكوادر وتغطية الورديات (Workforce & Shift Rosters Report)</h2>
        <p class="text-muted mb-0">تحليل القوى العاملة، التوزيع المهني، نسب تغطية الورديات، ومصروفات الرواتب بالجنيه السوداني (SDG)</p>
    </div>
    <div class="d-flex gap-2">
        <a href="/reports" class="btn btn-outline-secondary d-flex align-items-center gap-1">
            <i class="bi bi-arrow-right"></i> لوحة الإحصائيات العامة
        </a>
        <a href="/hr/roster" class="btn btn-outline-primary d-flex align-items-center gap-1">
            <i class="bi bi-calendar3"></i> جدول المناوبات
        </a>
        <a href="/hr/payroll" class="btn btn-outline-success d-flex align-items-center gap-1">
            <i class="bi bi-cash-stack"></i> مسير الرواتب
        </a>
    </div>
</div>

<!-- HR Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body">
                <span class="text-muted small d-block">إجمالي الكادر النشط</span>
                <h3 class="fw-bold mb-0 text-primary"><?= count($activeStaff) ?> <small class="fs-6 fw-normal text-muted">موظف</small></h3>
                <small class="text-muted">أطباء وتمريض وفنيين وإداريين</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body">
                <span class="text-muted small d-block">إجمالي رواتب الشهر (SDG)</span>
                <h3 class="fw-bold mb-0 text-success"><?= number_format((float)($payrollSummary['sum_net'] ?? 0), 2) ?> <small class="fs-6 fw-normal text-muted">SDG</small></h3>
                <small class="text-muted">صافي الاستحقاقات الشهرية</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body">
                <span class="text-muted small d-block">الرواتب المصروفة بنجاح</span>
                <h3 class="fw-bold mb-0 text-dark"><?= number_format((float)($payrollSummary['sum_paid'] ?? 0), 2) ?> <small class="fs-6 fw-normal text-muted">SDG</small></h3>
                <small class="text-success"><?= (int)($payrollSummary['count_paid'] ?? 0) ?> كشف مسدد عبر بنكك/نقدي</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body">
                <span class="text-muted small d-block">الرواتب بانتظار التحويل</span>
                <h3 class="fw-bold mb-0 text-warning"><?= number_format((float)($payrollSummary['sum_unpaid'] ?? 0), 2) ?> <small class="fs-6 fw-normal text-muted">SDG</small></h3>
                <small class="text-muted"><?= (int)($payrollSummary['count_unpaid'] ?? 0) ?> كشف قيد الانتظار</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Shift Coverage Analysis -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-header bg-white py-3">
                <h6 class="card-title mb-0 fw-bold">
                    <i class="bi bi-clock-history text-primary me-2"></i>تحليل تغطية الورديات (الشهر الحالي)
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>الوردية</th>
                                <th class="text-center">المجدول</th>
                                <th class="text-center">حضور</th>
                                <th class="text-center">غياب</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($shiftCoverage as $sc): ?>
                                <tr>
                                    <td class="fw-bold"><?= htmlspecialchars($sc['shift_name']) ?></td>
                                    <td class="text-center font-monospace"><?= number_format($sc['scheduled_count']) ?></td>
                                    <td class="text-center font-monospace text-success"><?= number_format($sc['attended_count']) ?></td>
                                    <td class="text-center font-monospace text-danger"><?= number_format($sc['absent_count']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Employees Directory Table -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-header bg-white py-3">
                <h6 class="card-title mb-0 fw-bold">
                    <i class="bi bi-person-badge text-info me-2"></i>قائمة الكوادر النشطة والرواتب الأساسية
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>الموظف</th>
                                <th>القسم</th>
                                <th>الوظيفة</th>
                                <th class="text-end">الأساسي (SDG)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activeStaff as $emp): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($emp['first_name'] . ' ' . $emp['second_name'] . ' ' . $emp['last_name']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($emp['employee_code']) ?></small>
                                    </td>
                                    <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($emp['department_name_ar']) ?></span></td>
                                    <td><small><?= htmlspecialchars($emp['job_title']) ?></small></td>
                                    <td class="text-end font-monospace fw-bold"><?= number_format((float)$emp['basic_salary'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
