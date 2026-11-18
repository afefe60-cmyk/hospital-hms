<?php
$layout = 'layouts/main';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h4 mb-1 fw-bold"><i class="bi bi-people text-primary me-2"></i>الموارد البشرية وجداول المناوبات والرواتب (HR & Payroll)</h2>
        <p class="text-muted mb-0">إدارة الكوادر الطبية والتمريضية، جداول الورديات والمناوبات، الإجازات، ومسيرات الرواتب بالجنيه السوداني (SDG)</p>
    </div>
    <div class="d-flex gap-2">
        <a href="/hr/roster" class="btn btn-outline-primary d-flex align-items-center gap-1">
            <i class="bi bi-calendar3"></i> جدول الورديات والمناوبات
        </a>
        <a href="/hr/leaves" class="btn btn-outline-secondary d-flex align-items-center gap-1">
            <i class="bi bi-calendar2-check"></i> إدارة الإجازات
        </a>
        <a href="/hr/payroll" class="btn btn-primary d-flex align-items-center gap-2">
            <i class="bi bi-cash-stack"></i>
            <span>مسيرات الرواتب الشهرية</span>
        </a>
    </div>
</div>

<!-- Metrics Cards Strip -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3 text-primary me-3">
                    <i class="bi bi-person-badge fs-3"></i>
                </div>
                <div>
                    <span class="text-muted d-block small">إجمالي الكادر النشط</span>
                    <h4 class="fw-bold mb-0 text-primary"><?= number_format($totalEmployees) ?> <small class="fs-6 fw-normal text-muted">موظف</small></h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-success bg-opacity-10 p-3 text-success me-3">
                    <i class="bi bi-clock-history fs-3"></i>
                </div>
                <div>
                    <span class="text-muted d-block small">على رأس العمل اليوم</span>
                    <h4 class="fw-bold mb-0 text-success"><?= number_format($onDutyToday) ?> <small class="fs-6 fw-normal text-muted">مناوب</small></h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-warning bg-opacity-10 p-3 text-warning me-3">
                    <i class="bi bi-calendar-x fs-3"></i>
                </div>
                <div>
                    <span class="text-muted d-block small">في إجازة رسمية اليوم</span>
                    <h4 class="fw-bold mb-0 text-warning"><?= number_format($onLeaveToday) ?> <small class="fs-6 fw-normal text-muted">موظف</small></h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-info bg-opacity-10 p-3 text-info me-3">
                    <i class="bi bi-wallet2 fs-3"></i>
                </div>
                <div>
                    <span class="text-muted d-block small">إجمالي رواتب شهر <?= $currentMonth ?>/<?= $currentYear ?></span>
                    <h4 class="fw-bold mb-0 text-info"><?= number_format((float)($payrollStats['sum_net'] ?? 0), 2) ?> <small class="fs-6 fw-normal text-muted">SDG</small></h4>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Today's Duty Roster -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 fw-bold fs-6">
                    <i class="bi bi-calendar-event text-primary me-2"></i>جدول مناوبات اليوم (<?= date('Y-m-d') ?>)
                </h5>
                <a href="/hr/roster" class="btn btn-sm btn-link text-decoration-none">عرض الجدول الأسبوعي الكامل &larr;</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>الموظف</th>
                                <th>القسم</th>
                                <th>الوردية</th>
                                <th>الدور والمهام</th>
                                <th>الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($todayRoster)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">لا توجد مناوبات مجدولة لليوم حتى الآن.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($todayRoster as $r): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold"><?= htmlspecialchars($r['employee_name']) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($r['employee_code']) ?> | <?= htmlspecialchars($r['job_title']) ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($r['department_name_ar']) ?></td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                <i class="bi bi-clock me-1"></i><?= htmlspecialchars($r['shift_name_ar']) ?>
                                            </span>
                                            <div class="small text-muted"><?= substr($r['start_time'], 0, 5) ?> - <?= substr($r['end_time'], 0, 5) ?></div>
                                        </td>
                                        <td><small><?= htmlspecialchars($r['duty_role'] ?? 'تغطية روتينية') ?></small></td>
                                        <td>
                                            <?php
                                            $stClass = match($r['status']) {
                                                'attended' => 'bg-success',
                                                'scheduled' => 'bg-primary',
                                                'absent' => 'bg-danger',
                                                'on_leave' => 'bg-warning text-dark',
                                                'swapped' => 'bg-secondary',
                                                default => 'bg-light text-dark'
                                            };
                                            $stAr = match($r['status']) {
                                                'attended' => 'حاضر بالخدمة',
                                                'scheduled' => 'مجدول',
                                                'absent' => 'غياب',
                                                'on_leave' => 'في إجازة',
                                                'swapped' => 'تبديل وردية',
                                                default => $r['status']
                                            };
                                            ?>
                                            <span class="badge <?= $stClass ?>"><?= $stAr ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Monthly Payroll List -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 fw-bold fs-6">
                    <i class="bi bi-file-earmark-spreadsheet text-success me-2"></i>كشوفات المرتبات لشهر <?= $currentMonth ?>/<?= $currentYear ?>
                </h5>
                <a href="/hr/payroll?year=<?= $currentYear ?>&month=<?= $currentMonth ?>" class="btn btn-sm btn-link text-decoration-none">عرض المسير الشامل &larr;</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>رقم الكشف</th>
                                <th>الموظف</th>
                                <th>الأساسي</th>
                                <th>صافي الراتب (SDG)</th>
                                <th>الحالة</th>
                                <th>إجراء</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentPayroll)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">لم يتم إصدار مسير الرواتب لهذا الشهر بعد.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentPayroll as $p): ?>
                                    <tr>
                                        <td>
                                            <a href="/hr/payroll/<?= $p['id'] ?>" class="fw-bold text-decoration-none">
                                                <?= htmlspecialchars($p['payroll_number']) ?>
                                            </a>
                                        </td>
                                        <td>
                                            <div class="fw-bold"><?= htmlspecialchars($p['employee_name']) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($p['job_title']) ?></small>
                                        </td>
                                        <td><?= number_format((float)$p['basic_salary'], 2) ?></td>
                                        <td class="fw-bold text-success"><?= number_format((float)$p['net_salary'], 2) ?></td>
                                        <td>
                                            <?php if ($p['is_paid']): ?>
                                                <span class="badge bg-success"><i class="bi bi-check2-circle me-1"></i>تم الصرف</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>قيد الانتظار</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="/hr/payroll/<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary py-0 px-2">التفاصيل</a>
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

    <!-- Right Sidebar: Quick Actions & Pending Leaves -->
    <div class="col-lg-5">
        <!-- Pending Leave Requests -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 fw-bold fs-6">
                    <i class="bi bi-calendar2-range text-warning me-2"></i>طلبات إجازات بانتظار الاعتماد
                </h5>
                <a href="/hr/leaves?status=pending" class="btn btn-sm btn-link text-decoration-none">عرض الكل &larr;</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($pendingLeaves)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-check-circle-fill text-success fs-2 mb-2 d-block"></i>
                        لا توجد طلبات إجازة معلقة حالياً.
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($pendingLeaves as $pl): ?>
                            <div class="list-group-item p-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-0 fw-bold"><?= htmlspecialchars($pl['employee_name']) ?></h6>
                                        <small class="text-muted"><?= htmlspecialchars($pl['department_name_ar']) ?> | <?= htmlspecialchars($pl['job_title']) ?></small>
                                    </div>
                                    <span class="badge bg-warning text-dark">
                                        <?= $pl['total_days'] ?> أيام
                                    </span>
                                </div>
                                <div class="small mb-2">
                                    <span class="text-muted">الفترة:</span> 
                                    <strong><?= $pl['start_date'] ?></strong> إلى <strong><?= $pl['end_date'] ?></strong>
                                    <div class="text-muted mt-1">السبب: <?= htmlspecialchars($pl['reason']) ?></div>
                                </div>
                                <div class="d-flex gap-2">
                                    <form action="/hr/leaves/update-status" method="POST" class="d-inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= $pl['id'] ?>">
                                        <input type="hidden" name="status" value="approved">
                                        <button type="submit" class="btn btn-sm btn-success py-1 px-3">
                                            <i class="bi bi-check-lg me-1"></i>اعتماد
                                        </button>
                                    </form>
                                    <form action="/hr/leaves/update-status" method="POST" class="d-inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= $pl['id'] ?>">
                                        <input type="hidden" name="status" value="rejected">
                                        <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-3">
                                            <i class="bi bi-x-lg me-1"></i>رفض
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Payroll Month Summary Card -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0 fw-bold fs-6">
                    <i class="bi bi-pie-chart text-primary me-2"></i>ملخص صرف مرتبات الشهر (SDG)
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between small mb-1">
                        <span>الرواتب المصروفة (<?= (int)($payrollStats['count_paid'] ?? 0) ?> موظف)</span>
                        <strong class="text-success"><?= number_format((float)($payrollStats['sum_paid'] ?? 0), 2) ?> SDG</strong>
                    </div>
                    <?php 
                    $netTot = (float)($payrollStats['sum_net'] ?? 0);
                    $paidTot = (float)($payrollStats['sum_paid'] ?? 0);
                    $pct = ($netTot > 0) ? round(($paidTot / $netTot) * 100, 1) : 0;
                    ?>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-success" style="width: <?= $pct ?>%"></div>
                    </div>
                </div>

                <div class="d-flex justify-content-between small text-muted mb-2 border-bottom pb-2">
                    <span>الرواتب بانتظار التحويل (بنكك/نقدي)</span>
                    <strong class="text-warning"><?= number_format((float)($payrollStats['sum_unpaid'] ?? 0), 2) ?> SDG</strong>
                </div>

                <div class="d-flex justify-content-between small text-muted mb-2 border-bottom pb-2">
                    <span>إجمالي البدلات والحوافز</span>
                    <strong class="text-dark"><?= number_format((float)($payrollStats['sum_allowances'] ?? 0), 2) ?> SDG</strong>
                </div>

                <div class="d-flex justify-content-between small text-muted mb-3 border-bottom pb-2">
                    <span>إجمالي الاستقطاعات (معاشات وضرائب)</span>
                    <strong class="text-danger"><?= number_format((float)($payrollStats['sum_deductions'] ?? 0), 2) ?> SDG</strong>
                </div>

                <div class="d-grid gap-2">
                    <a href="/hr/payroll/print-sheet?year=<?= $currentYear ?>&month=<?= $currentMonth ?>" target="_blank" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-printer me-1"></i>طباعة كشف مسير الرواتب المجمع (A4)
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
