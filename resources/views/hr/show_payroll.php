<?php
$layout = 'layouts/main';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h4 mb-1 fw-bold">
            <i class="bi bi-file-earmark-person text-primary me-2"></i>كشف مفردات راتب موظف (Payslip #<?= htmlspecialchars($payroll['payroll_number']) ?>)
        </h2>
        <p class="text-muted mb-0">عن شهر <?= $payroll['salary_month'] ?> / <?= $payroll['salary_year'] ?> &bull; مستشفى الخرطوم التخصصي</p>
    </div>
    <div class="d-flex gap-2">
        <a href="/hr/payroll?year=<?= $payroll['salary_year'] ?>&month=<?= $payroll['salary_month'] ?>" class="btn btn-outline-secondary d-flex align-items-center gap-1">
            <i class="bi bi-arrow-right"></i> العودة لمسير الرواتب
        </a>
        <a href="/hr/payroll/print/<?= $payroll['id'] ?>" target="_blank" class="btn btn-outline-primary d-flex align-items-center gap-1">
            <i class="bi bi-printer"></i> طباعة كشف المفردات (A4)
        </a>
        <?php if (!$payroll['is_paid']): ?>
            <button type="button" class="btn btn-success d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#paySalaryModal">
                <i class="bi bi-check2-circle"></i>
                <span>تسجيل صرف الراتب (بنكك/نقدي)</span>
            </button>
        <?php endif; ?>
    </div>
</div>

<div class="row g-4">
    <!-- Employee Info Card -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0 fw-bold fs-6">
                    <i class="bi bi-person-lines-fill text-primary me-2"></i>بيانات الموظف
                </h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center p-3 mb-2" style="width: 70px; height: 70px;">
                        <i class="bi bi-person-fill fs-1"></i>
                    </div>
                    <h5 class="fw-bold mb-1"><?= htmlspecialchars($payroll['employee_name']) ?></h5>
                    <div class="badge bg-secondary"><?= htmlspecialchars($payroll['job_title']) ?></div>
                </div>

                <ul class="list-group list-group-flush small">
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">الرقم الوظيفي:</span>
                        <strong class="font-monospace"><?= htmlspecialchars($payroll['employee_code']) ?></strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">القسم:</span>
                        <strong><?= htmlspecialchars($payroll['department_name_ar']) ?></strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">الرقم الوطني:</span>
                        <strong class="font-monospace"><?= htmlspecialchars($payroll['national_id']) ?></strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">رقم الهاتف:</span>
                        <strong dir="ltr"><?= htmlspecialchars($payroll['phone']) ?></strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">تاريخ التعيين:</span>
                        <span><?= $payroll['hire_date'] ?></span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Payment Status Card -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0 fw-bold fs-6">
                    <i class="bi bi-credit-card text-success me-2"></i>حالة الصرف والتحويل
                </h5>
            </div>
            <div class="card-body">
                <?php if ($payroll['is_paid']): ?>
                    <div class="alert alert-success d-flex align-items-center mb-3">
                        <i class="bi bi-check-circle-fill fs-3 me-3"></i>
                        <div>
                            <div class="fw-bold">تم صرف الراتب بنجاح</div>
                            <small class="d-block">تاريخ التحويل: <?= $payroll['payment_date'] ?></small>
                        </div>
                    </div>
                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">طريقة الصرف:</span>
                            <span class="badge bg-light text-dark border">
                                <?= $payroll['payment_method'] === 'bankak' ? 'تطبيق بنكك (بنك الخرطوم)' : ($payroll['payment_method'] === 'cash' ? 'نقدي من الخزينة' : 'تحويل بنكي') ?>
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">الرقم المرجعي (Reference):</span>
                            <strong class="font-monospace text-primary"><?= htmlspecialchars($payroll['transaction_reference'] ?? '-') ?></strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">ملاحظات التحويل:</span>
                            <span><?= htmlspecialchars($payroll['notes'] ?? '-') ?></span>
                        </li>
                    </ul>
                <?php else: ?>
                    <div class="alert alert-warning d-flex align-items-center mb-3">
                        <i class="bi bi-exclamation-triangle-fill fs-3 me-3"></i>
                        <div>
                            <div class="fw-bold">الراتب بانتظار التحويل</div>
                            <small class="d-block">لم يتم تسجيل صرف هذا الراتب بعد.</small>
                        </div>
                    </div>
                    <button type="button" class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#paySalaryModal">
                        <i class="bi bi-cash-coin me-1"></i>تسجيل الصرف الآن
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Salary Breakdown Card -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 fw-bold fs-6">
                    <i class="bi bi-calculator-fill text-primary me-2"></i>تفاصيل ومفردات الراتب (بالجنيه السوداني SDG)
                </h5>
                <span class="badge bg-light text-muted border">تم الاحتساب بواسطة: <?= htmlspecialchars($payroll['processor_name'] ?? 'النظام') ?></span>
            </div>
            <div class="card-body">
                <!-- Basic Salary Highlight -->
                <div class="p-3 bg-light rounded-3 mb-4 d-flex justify-content-between align-items-center border">
                    <div>
                        <span class="text-muted d-block small">الراتب الأساسي التعاقدي (Basic Salary)</span>
                        <h4 class="fw-bold mb-0 text-dark"><?= number_format((float)$payroll['basic_salary'], 2) ?> <small class="text-muted fs-6">SDG</small></h4>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-secondary">عقد دوام كامل</span>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- Allowances Table -->
                    <div class="col-md-6">
                        <div class="border rounded-3 p-3 h-100">
                            <h6 class="fw-bold text-success mb-3 border-bottom pb-2 d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-plus-circle me-1"></i>البدلات والحوافز الإضافية</span>
                                <span class="badge bg-success bg-opacity-10 text-success">+<?= number_format((float)$payroll['allowances'], 2) ?> SDG</span>
                            </h6>
                            <table class="table table-sm table-borderless mb-0">
                                <tbody>
                                    <?php 
                                    $hasAllowances = false;
                                    foreach ($payroll['items'] as $item): 
                                        if ($item['type'] === 'allowance'):
                                            $hasAllowances = true;
                                    ?>
                                        <tr class="border-bottom">
                                            <td class="text-muted py-2"><?= htmlspecialchars($item['title']) ?></td>
                                            <td class="text-end fw-bold text-success py-2">+<?= number_format((float)$item['amount'], 2) ?></td>
                                        </tr>
                                    <?php 
                                        endif;
                                    endforeach; 
                                    if (!$hasAllowances):
                                    ?>
                                        <tr><td colspan="2" class="text-muted text-center py-3">لا توجد بدلات مسجلة.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Deductions Table -->
                    <div class="col-md-6">
                        <div class="border rounded-3 p-3 h-100">
                            <h6 class="fw-bold text-danger mb-3 border-bottom pb-2 d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-dash-circle me-1"></i>الاستقطاعات والخصومات الرسمية</span>
                                <span class="badge bg-danger bg-opacity-10 text-danger">-<?= number_format((float)$payroll['deductions'], 2) ?> SDG</span>
                            </h6>
                            <table class="table table-sm table-borderless mb-0">
                                <tbody>
                                    <?php 
                                    $hasDeductions = false;
                                    foreach ($payroll['items'] as $item): 
                                        if ($item['type'] === 'deduction'):
                                            $hasDeductions = true;
                                    ?>
                                        <tr class="border-bottom">
                                            <td class="text-muted py-2"><?= htmlspecialchars($item['title']) ?></td>
                                            <td class="text-end fw-bold text-danger py-2">-<?= number_format((float)$item['amount'], 2) ?></td>
                                        </tr>
                                    <?php 
                                        endif;
                                    endforeach; 
                                    if (!$hasDeductions):
                                    ?>
                                        <tr><td colspan="2" class="text-muted text-center py-3">لا توجد استقطاعات مسجلة.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Net Pay Banner -->
                <div class="mt-4 p-4 rounded-3 text-white d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #198754 0%, #157347 100%);">
                    <div>
                        <span class="text-white-50 d-block small">صافي الراتب المستحق للصرف (Net Payable Salary)</span>
                        <h2 class="fw-bold mb-0 text-white"><?= number_format((float)$payroll['net_salary'], 2) ?> <small class="fs-5 text-white-50">SDG</small></h2>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-white text-success px-3 py-2 fs-6">
                            <?= $payroll['is_paid'] ? 'مسدد بالكامل' : 'مستحق الدفع' ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Pay Salary -->
<div class="modal fade" id="paySalaryModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="/hr/payroll/pay" method="POST" class="modal-content">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $payroll['id'] ?>">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-cash-coin text-success me-2"></i>تسجيل صرف راتب الموظف</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-light border mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">الموظف:</span>
                        <strong><?= htmlspecialchars($payroll['employee_name']) ?></strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">المبلغ الصافي المستحق:</span>
                        <strong class="text-success fs-5"><?= number_format((float)$payroll['net_salary'], 2) ?> SDG</strong>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">طريقة الصرف والتحويل <span class="text-danger">*</span></label>
                    <select name="payment_method" class="form-select" required>
                        <option value="bankak" selected>تطبيق بنكك - بنك الخرطوم (Bankak App)</option>
                        <option value="cash">نقداً من الخزينة الرئيسية (Cash)</option>
                        <option value="bank_transfer">تحويل مصرفي شيك / مقاصة (Bank Transfer)</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">الرقم المرجعي للإشعار / الإيصال</label>
                    <input type="text" name="transaction_reference" class="form-control" placeholder="مثال: BNK-202611-998822 أو رقم إشعار بنكك">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">ملاحظات الصرف</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="ملاحظات توثيقية إضافية"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="submit" class="btn btn-success"><i class="bi bi-check2-circle me-1"></i>تأكيد صرف الراتب</button>
            </div>
        </form>
    </div>
</div>
