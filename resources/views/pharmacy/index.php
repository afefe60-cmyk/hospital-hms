<div class="row g-4">
    <!-- Header -->
    <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h3 class="fw-bold text-navy mb-1 d-flex align-items-center gap-2">
                <i data-lucide="pill" class="text-success" style="width: 28px; height: 28px;"></i>
                إدارة الصيدلية وصرف الروشتات (Pharmacy Dispensing - FEFO)
            </h3>
            <p class="text-muted mb-0">صرف الوصفات الطبية الإلكترونية، التحقق من التفاعلات، وتطبيق مبدأ الأقدم صلاحية أولاً</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= url('/pharmacy/dispense') ?>" class="btn btn-success d-inline-flex align-items-center gap-2 px-3 py-2 fw-bold shadow-sm">
                <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i>
                صرف مباشر / روشتة جديدة
            </a>
            <a href="<?= url('/inventory') ?>" class="btn btn-outline-navy d-inline-flex align-items-center gap-2 px-3 py-2 fw-bold">
                <i data-lucide="boxes" style="width: 18px; height: 18px;"></i>
                إدارة المخزون والمستودعات
            </a>
        </div>
    </div>

    <!-- Metrics Cards -->
    <div class="col-12">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="card card-hms p-3 bg-white border-warning shadow-sm">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">روشتات بانتظار الصرف</span>
                            <h2 class="fw-bold text-warning mb-0 mt-1"><?= count($pendingRx) ?></h2>
                        </div>
                        <i data-lucide="file-clock" style="width: 36px; height: 36px; color: #D97706;"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card card-hms p-3 bg-white border-success shadow-sm">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">فواتير الصرف اليوم</span>
                            <h2 class="fw-bold text-success mb-0 mt-1"><?= $dispenseCount ?></h2>
                        </div>
                        <i data-lucide="receipt" style="width: 36px; height: 36px; color: #16856D;"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card card-hms p-3 bg-white border-primary shadow-sm">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">مبيعات الصيدلية اليوم</span>
                            <h2 class="fw-bold text-primary mb-0 mt-1"><?= number_format($todaySales, 2) ?> <small class="fs-6">SDG</small></h2>
                        </div>
                        <i data-lucide="coins" style="width: 36px; height: 36px; color: #173B59;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Prescriptions Table -->
    <div class="col-12">
        <div class="card card-hms p-0 bg-white overflow-hidden shadow-sm mb-4">
            <div class="card-header bg-navy text-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 d-flex align-items-center gap-2">
                    <i data-lucide="scroll" style="width: 20px; height: 20px; color: #F59E0B;"></i>
                    الروشتات الطبية الإلكترونية المعلقة للصرف
                </h5>
                <span class="badge bg-warning text-dark fs-6"><?= count($pendingRx) ?> روشتة</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>رقم الروشتة</th>
                            <th>المريض</th>
                            <th>الطبيب المعالج</th>
                            <th>عدد الأصناف</th>
                            <th>تاريخ التحويل</th>
                            <th>الحالة</th>
                            <th class="text-center">إجراء الصرف</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pendingRx)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i data-lucide="check-check" style="width: 40px; height: 40px; margin: 0 auto 8px; color: #16856D;"></i>
                                    <p class="mb-0">لا توجد روشتات معلقة حالياً، تم صرف جميع طلبات المرضى.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($pendingRx as $rx): ?>
                                <tr>
                                    <td class="fw-bold font-monospace text-navy"><?= e($rx['prescription_code']) ?></td>
                                    <td>
                                        <div class="fw-bold text-navy"><?= e($rx['patient_name']) ?></div>
                                        <small class="text-muted">MRN: <?= e($rx['mrn']) ?> | <?= $rx['patient_age'] ?> سنة</small>
                                    </td>
                                    <td>د. <?= e($rx['doctor_name']) ?></td>
                                    <td><span class="badge bg-light text-dark border"><?= $rx['items_count'] ?> أصناف</span></td>
                                    <td class="small text-muted"><?= date('d/m/Y H:i', strtotime($rx['created_at'])) ?></td>
                                    <td><span class="badge bg-warning text-dark">بانتظار الصرف</span></td>
                                    <td class="text-center">
                                        <a href="<?= url("/pharmacy/dispense/{$rx['id']}") ?>" class="btn btn-sm btn-success fw-bold px-3 d-inline-flex align-items-center gap-1">
                                            <i data-lucide="shopping-cart" style="width: 16px; height: 16px;"></i>
                                            صرف الروشتة
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Dispenses Table -->
    <div class="col-12">
        <div class="card card-hms p-0 bg-white overflow-hidden shadow-sm">
            <div class="card-header bg-navy text-white py-3">
                <h5 class="fw-bold mb-0 d-flex align-items-center gap-2">
                    <i data-lucide="history" style="width: 20px; height: 20px; color: #6EE7B7;"></i>
                    سجل فواتير الأدوية المنصرفة مؤخراً
                </h5>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>رقم الفاتورة</th>
                            <th>المريض</th>
                            <th>الصيدلية / المخزن</th>
                            <th>الصيدلي المسؤول</th>
                            <th>عدد الأصناف</th>
                            <th>الإجمالي (SDG)</th>
                            <th>وقت الصرف</th>
                            <th class="text-center">معاينة الإيصال</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($dispenses)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">لا توجد عمليات صرف مسجلة مؤخراً</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($dispenses as $d): ?>
                                <tr>
                                    <td class="fw-bold font-monospace text-navy">
                                        <a href="<?= url("/pharmacy/receipt/{$d['id']}") ?>" class="text-decoration-none">
                                            <?= e($d['dispense_number']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-navy"><?= e($d['patient_name']) ?></div>
                                        <small class="text-muted">MRN: <?= e($d['mrn']) ?></small>
                                    </td>
                                    <td><span class="badge bg-light text-dark border"><?= e($d['warehouse_name']) ?></span></td>
                                    <td><?= e($d['pharmacist_name']) ?></td>
                                    <td><?= $d['items_count'] ?></td>
                                    <td class="fw-bold text-success"><?= number_format($d['total_amount'], 2) ?></td>
                                    <td class="small text-muted"><?= date('d/m/Y H:i', strtotime($d['created_at'])) ?></td>
                                    <td class="text-center">
                                        <a href="<?= url("/pharmacy/receipt/{$d['id']}") ?>" class="btn btn-sm btn-outline-navy">
                                            عرض الفاتورة
                                        </a>
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
