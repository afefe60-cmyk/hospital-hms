<?php
$layout = 'layouts/main';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h4 mb-1 fw-bold"><i class="bi bi-calendar3 text-primary me-2"></i>جدول المناوبات والورديات (Staff Duty Rosters)</h2>
        <p class="text-muted mb-0">جدولة وتوزيع الأطباء، التمريض والكوادر الفنية على الورديات الصباحية والمسائية والليلية</p>
    </div>
    <div class="d-flex gap-2">
        <a href="/hr" class="btn btn-outline-secondary d-flex align-items-center gap-1">
            <i class="bi bi-arrow-right"></i> لوحة الموارد البشرية
        </a>
        <button type="button" class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#assignShiftModal">
            <i class="bi bi-calendar-plus"></i>
            <span>إضافة / جدولة مناوبة</span>
        </button>
    </div>
</div>

<!-- Filter Bar -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body">
        <form action="/hr/roster" method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small text-muted">من تاريخ</label>
                <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($startDate) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">إلى تاريخ</label>
                <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($endDate) ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label small text-muted">القسم / الإدارة</label>
                <select name="department_id" class="form-select">
                    <option value="">-- كافة الأقسام --</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?= $dept['id'] ?>" <?= ($departmentId == $dept['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($dept['name_ar']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-grid">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-filter me-1"></i>تصفية الجدول
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Roster Table -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0 fw-bold fs-6">
            <i class="bi bi-list-check text-primary me-2"></i>جدول مناوبات الفترة المحددة (<?= count($rosterList) ?> وردية مسجلة)
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>التاريخ</th>
                        <th>الموظف</th>
                        <th>القسم</th>
                        <th>الوردية والتوقيت</th>
                        <th>الدور والمهام</th>
                        <th>الحالة</th>
                        <th>ملاحظات</th>
                        <th class="text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rosterList)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-calendar-x fs-1 d-block mb-2 text-secondary"></i>
                                لا توجد مناوبات مجدولة في الفترة المحددة.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rosterList as $r): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold"><?= $r['duty_date'] ?></div>
                                    <small class="text-muted">
                                        <?php
                                        $timestamp = strtotime($r['duty_date']);
                                        $days = ['الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];
                                        echo $days[date('w', $timestamp)];
                                        ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($r['employee_name']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($r['employee_code']) ?> &bull; <?= htmlspecialchars($r['job_title']) ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border"><?= htmlspecialchars($r['department_name_ar']) ?></span>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($r['shift_name_ar']) ?></strong>
                                    <div class="small text-muted"><?= substr($r['start_time'], 0, 5) ?> - <?= substr($r['end_time'], 0, 5) ?></div>
                                </td>
                                <td>
                                    <span class="small"><?= htmlspecialchars($r['duty_role'] ?? 'تغطية روتينية') ?></span>
                                </td>
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
                                <td>
                                    <small class="text-muted"><?= htmlspecialchars($r['notes'] ?? '-') ?></small>
                                </td>
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            تحديث الحالة
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                            <li>
                                                <form action="/hr/roster/update-status" method="POST">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                                    <input type="hidden" name="status" value="attended">
                                                    <button type="submit" class="dropdown-item text-success"><i class="bi bi-check-circle me-2"></i>حاضر بالخدمة</button>
                                                </form>
                                            </li>
                                            <li>
                                                <form action="/hr/roster/update-status" method="POST">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                                    <input type="hidden" name="status" value="absent">
                                                    <button type="submit" class="dropdown-item text-danger"><i class="bi bi-x-circle me-2"></i>غياب</button>
                                                </form>
                                            </li>
                                            <li>
                                                <form action="/hr/roster/update-status" method="POST">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                                    <input type="hidden" name="status" value="on_leave">
                                                    <button type="submit" class="dropdown-item text-warning"><i class="bi bi-calendar-x me-2"></i>في إجازة</button>
                                                </form>
                                            </li>
                                            <li>
                                                <form action="/hr/roster/update-status" method="POST">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                                    <input type="hidden" name="status" value="swapped">
                                                    <button type="submit" class="dropdown-item text-secondary"><i class="bi bi-arrow-left-right me-2"></i>تم تبديل الوردية</button>
                                                </form>
                                            </li>
                                        </ul>
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

<!-- Modal: Assign Shift -->
<div class="modal fade" id="assignShiftModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="/hr/roster" method="POST" class="modal-content">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-calendar-plus text-primary me-2"></i>جدولة وردية لموظف</h5>
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

                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">القسم <span class="text-danger">*</span></label>
                        <select name="department_id" class="form-select" required>
                            <option value="">-- اختر القسم --</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['name_ar']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">الوردية <span class="text-danger">*</span></label>
                        <select name="shift_id" class="form-select" required>
                            <option value="">-- اختر الوردية --</option>
                            <?php foreach ($shifts as $s): ?>
                                <option value="<?= $s['id'] ?>">
                                    <?= htmlspecialchars($s['name_ar']) ?> (<?= substr($s['start_time'], 0, 5) ?> - <?= substr($s['end_time'], 0, 5) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">تاريخ المناوبة <span class="text-danger">*</span></label>
                    <input type="date" name="duty_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">الدور المكلف به والمهام</label>
                    <input type="text" name="duty_role" class="form-control" placeholder="مثال: استشاري مناوب، تغطية طوارئ، مشرفة عناية">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">ملاحظات إضافية</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="أي تعليمات أو ملاحظات خاصة بالوردية"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>حفظ الجدولة</button>
            </div>
        </form>
    </div>
</div>
