<div class="row g-4">
    <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h3 class="fw-bold text-navy mb-1 d-flex align-items-center gap-2">
                <i data-lucide="badge-percent" class="text-green" style="width: 28px; height: 28px;"></i>
                سجل الموظفين والكوادر الصحية والإدارية
            </h3>
            <p class="text-muted mb-0">بيانات الكوادر، الأرقام الوطنية، الأقسام المعينة بها، والرواتب</p>
        </div>

        <?php if (can('hospital.manage')): ?>
            <button type="button" class="btn btn-hms-primary d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#newEmployeeModal">
                <i data-lucide="user-plus" style="width: 18px; height: 18px;"></i>
                تسجيل موظف جديد
            </button>
        <?php endif; ?>
    </div>

    <div class="col-12">
        <div class="card card-hms p-0 bg-white overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>الرقم الوظيفي</th>
                            <th>الاسم الكامل</th>
                            <th>المسمى الوظيفي</th>
                            <th>القسم</th>
                            <th>الرقم الوطني</th>
                            <th>الهاتف</th>
                            <th>تاريخ التعيين</th>
                            <th>الراتب الأساسي (SDG)</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($employees as $emp): ?>
                            <tr>
                                <td><?= $emp['id'] ?></td>
                                <td><code><?= e($emp['employee_code']) ?></code></td>
                                <td class="fw-bold text-navy"><?= e($emp['first_name'] . ' ' . $emp['second_name'] . ' ' . $emp['last_name']) ?></td>
                                <td><?= e($emp['job_title']) ?></td>
                                <td><span class="badge bg-navy"><?= e($emp['department_name_ar']) ?></span></td>
                                <td><small class="font-monospace text-secondary"><?= e($emp['national_id']) ?></small></td>
                                <td><small><?= e($emp['phone']) ?></small></td>
                                <td><small><?= e($emp['hire_date']) ?></small></td>
                                <td class="fw-bold"><?= format_currency($emp['basic_salary']) ?></td>
                                <td>
                                    <?php if ($emp['status'] === 'active'): ?>
                                        <span class="badge bg-success-subtle text-success">على رأس العمل</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary"><?= e($emp['status']) ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Employee Modal -->
<?php if (can('hospital.manage')): ?>
    <div class="modal fade" id="newEmployeeModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="<?= url('/employees') ?>" method="POST">
                    <?= csrf_field() ?>
                    <div class="modal-header bg-navy text-white">
                        <h5 class="modal-title fw-bold">تسجيل موظف جديد</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">الرقم الوظيفي <span class="text-danger">*</span></label>
                                <input type="text" name="employee_code" class="form-control" placeholder="مثال: EMP-2026-050" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">الرقم الوطني السوداني <span class="text-danger">*</span></label>
                                <input type="text" name="national_id" class="form-control" placeholder="1100..." required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">النوع <span class="text-danger">*</span></label>
                                <select name="gender" class="form-select" required>
                                    <option value="male">ذكر</option>
                                    <option value="female">أنثى</option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">الاسم الأول <span class="text-danger">*</span></label>
                                <input type="text" name="first_name" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">اسم الأب <span class="text-danger">*</span></label>
                                <input type="text" name="second_name" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">اسم العائلة / اللقب <span class="text-danger">*</span></label>
                                <input type="text" name="last_name" class="form-control" required>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">القسم <span class="text-danger">*</span></label>
                                <select name="department_id" class="form-select" required>
                                    <option value="">-- اختر القسم --</option>
                                    <?php foreach ($departments as $dep): ?>
                                        <option value="<?= $dep['id'] ?>"><?= e($dep['name_ar']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">المسمى الوظيفي <span class="text-danger">*</span></label>
                                <input type="text" name="job_title" class="form-control" placeholder="مثال: ممرض قانوني، فني مختبر..." required>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">رقم الهاتف <span class="text-danger">*</span></label>
                                <input type="text" name="phone" class="form-control" placeholder="+249..." required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">تاريخ التعيين <span class="text-danger">*</span></label>
                                <input type="date" name="hire_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">الراتب الأساسي (SDG)</label>
                                <input type="number" step="0.01" name="basic_salary" class="form-control" placeholder="0.00" value="0.00">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-hms-primary">تسجيل الموظف</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>
