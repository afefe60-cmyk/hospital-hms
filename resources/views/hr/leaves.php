<?php
$layout = 'layouts/main';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h4 mb-1 fw-bold"><i class="bi bi-calendar2-check text-primary me-2"></i>إدارة الإجازات والأذونات (Employee Leaves & Time-off)</h2>
        <p class="text-muted mb-0">متابعة طلبات الإجازات السنوية، المرضية، والطارئة واعتمادها إدارياً</p>
    </div>
    <div class="d-flex gap-2">
        <a href="/hr" class="btn btn-outline-secondary d-flex align-items-center gap-1">
            <i class="bi bi-arrow-right"></i> لوحة الموارد البشرية
        </a>
        <button type="button" class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#requestLeaveModal">
            <i class="bi bi-plus-circle"></i>
            <span>تقديم طلب إجازة جديد</span>
        </button>
    </div>
</div>

<!-- Filter Tabs -->
<div class="d-flex gap-2 mb-4">
    <a href="/hr/leaves" class="btn btn-sm <?= empty($selectedStatus) ? 'btn-primary' : 'btn-outline-secondary' ?>">
        كافة الطلبات
    </a>
    <a href="/hr/leaves?status=pending" class="btn btn-sm <?= ($selectedStatus === 'pending') ? 'btn-warning text-dark' : 'btn-outline-secondary' ?>">
        قيد الانتظار
    </a>
    <a href="/hr/leaves?status=approved" class="btn btn-sm <?= ($selectedStatus === 'approved') ? 'btn-success' : 'btn-outline-secondary' ?>">
        معتمدة
    </a>
    <a href="/hr/leaves?status=rejected" class="btn btn-sm <?= ($selectedStatus === 'rejected') ? 'btn-danger' : 'btn-outline-secondary' ?>">
        مرفوضة
    </a>
</div>

<!-- Leaves Table -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white py-3">
        <h5 class="card-title mb-0 fw-bold fs-6">
            <i class="bi bi-table text-primary me-2"></i>سجل طلبات الإجازات (<?= count($leaves) ?> طلب)
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>الموظف</th>
                        <th>القسم</th>
                        <th>نوع الإجازة</th>
                        <th>من تاريخ</th>
                        <th>إلى تاريخ</th>
                        <th>المدة</th>
                        <th>السبب والمبررات</th>
                        <th>الحالة والاعتماد</th>
                        <th class="text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($leaves)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-calendar-check fs-1 d-block mb-2 text-secondary"></i>
                                لا توجد طلبات إجازة مطابقة لهذا التصنيف.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($leaves as $l): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($l['employee_name']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($l['employee_code']) ?> &bull; <?= htmlspecialchars($l['job_title']) ?></small>
                                </td>
                                <td><?= htmlspecialchars($l['department_name_ar']) ?></td>
                                <td>
                                    <?php
                                    $typeAr = match($l['leave_type']) {
                                        'annual' => 'إجازة سنوية',
                                        'sick' => 'إجازة مرضية',
                                        'emergency' => 'إجازة طارئة',
                                        'maternity' => 'إجازة أمومة/وضع',
                                        'unpaid' => 'إجازة بدون راتب',
                                        default => $l['leave_type']
                                    };
                                    ?>
                                    <span class="badge bg-light text-dark border"><?= $typeAr ?></span>
                                </td>
                                <td><?= $l['start_date'] ?></td>
                                <td><?= $l['end_date'] ?></td>
                                <td>
                                    <span class="badge bg-info bg-opacity-10 text-info fw-bold fs-6 px-2 py-1">
                                        <?= $l['total_days'] ?> أيام
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted"><?= htmlspecialchars($l['reason']) ?></small>
                                </td>
                                <td>
                                    <?php if ($l['status'] === 'approved'): ?>
                                        <span class="badge bg-success mb-1 d-inline-block"><i class="bi bi-check2-circle me-1"></i>معتمدة</span>
                                        <?php if (!empty($l['approver_name'])): ?>
                                            <div class="small text-muted">بواسطة: <?= htmlspecialchars($l['approver_name']) ?></div>
                                        <?php endif; ?>
                                    <?php elseif ($l['status'] === 'pending'): ?>
                                        <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>قيد المراجعة</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>مرفوضة</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($l['status'] === 'pending'): ?>
                                        <div class="d-flex gap-1 justify-content-center">
                                            <form action="/hr/leaves/update-status" method="POST" class="d-inline">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="id" value="<?= $l['id'] ?>">
                                                <input type="hidden" name="status" value="approved">
                                                <button type="submit" class="btn btn-sm btn-success py-1 px-2" title="اعتماد الإجازة">
                                                    <i class="bi bi-check-lg"></i>
                                                </button>
                                            </form>
                                            <form action="/hr/leaves/update-status" method="POST" class="d-inline">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="id" value="<?= $l['id'] ?>">
                                                <input type="hidden" name="status" value="rejected">
                                                <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2" title="رفض الإجازة">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
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

<!-- Modal: Request Leave -->
<div class="modal fade" id="requestLeaveModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="/hr/leaves" method="POST" class="modal-content">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-calendar-plus text-primary me-2"></i>تقديم طلب إجازة لموظف</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">الموظف <span class="text-danger">*</span></label>
                    <select name="employee_id" class="form-select" required>
                        <option value="">-- اختر الموظف --</option>
                        <?php foreach ($employees as $emp): ?>
                            <option value="<?= $emp['id'] ?>">
                                <?= htmlspecialchars($emp['first_name'] . ' ' . $emp['second_name'] . ' ' . $emp['last_name']) ?> (<?= htmlspecialchars($emp['job_title']) ?> - <?= htmlspecialchars($emp['department_name_ar']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">نوع الإجازة <span class="text-danger">*</span></label>
                    <select name="leave_type" class="form-select" required>
                        <option value="annual">إجازة سنوية اعتيادية</option>
                        <option value="sick">إجازة مرضية (بتقرير طبي)</option>
                        <option value="emergency">إجازة طارئة / عارضة</option>
                        <option value="maternity">إجازة أمومة ووضع</option>
                        <option value="unpaid">إجازة بدون مرتب</option>
                    </select>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">تاريخ البداية <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">تاريخ النهاية <span class="text-danger">*</span></label>
                        <input type="date" name="end_date" class="form-control" value="<?= date('Y-m-d', strtotime('+3 days')) ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">سبب الإجازة ومبرراتها <span class="text-danger">*</span></label>
                    <textarea name="reason" class="form-control" rows="3" placeholder="تفاصيل سبب طلب الإجازة" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i>إرسال الطلب</button>
            </div>
        </form>
    </div>
</div>
