<div class="row g-4">
    <!-- Header -->
    <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h3 class="fw-bold text-navy mb-1 d-flex align-items-center gap-2">
                <i data-lucide="layers" class="text-primary" style="width: 28px; height: 28px;"></i>
                تتبع التشغيلات وتواريخ الصلاحية (FEFO Batches Tracker)
            </h3>
            <p class="text-muted mb-0">مراقبة تواريخ انتهاء الصلاحية لكل دفعة دوائية وفق معيار الأقدم صلاحية أولاً (First Expired, First Out)</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= url('/inventory/batches/create') ?>" class="btn btn-success d-inline-flex align-items-center gap-2 px-3 py-2 fw-bold shadow-sm">
                <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i>
                إدخال تشغيلة دوائية جديدة
            </a>
            <a href="<?= url('/inventory') ?>" class="btn btn-outline-secondary">لوحة المخازن</a>
        </div>
    </div>

    <!-- Filter by Warehouse -->
    <div class="col-12">
        <div class="card card-hms p-3 bg-white shadow-sm">
            <div class="d-flex gap-2 align-items-center">
                <span class="fw-bold text-muted small">تصفية بحسب المستودع:</span>
                <a href="<?= url('/inventory/batches') ?>" class="btn btn-sm <?= empty($selectedWarehouse) ? 'btn-primary' : 'btn-outline-secondary' ?>">جميع المستودعات</a>
                <?php foreach ($warehouses as $wh): ?>
                    <a href="<?= url("/inventory/batches?warehouse_id={$wh['id']}") ?>" class="btn btn-sm <?= ($selectedWarehouse == $wh['id']) ? 'btn-primary' : 'btn-outline-secondary' ?>">
                        <?= e($wh['name_ar']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Batches Table -->
    <div class="col-12">
        <div class="card card-hms p-0 bg-white overflow-hidden shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>رقم التشغيلة (Batch)</th>
                            <th>الصنف الدوائي</th>
                            <th>المستودع</th>
                            <th>تاريخ الانتهاء</th>
                            <th>المتبقي للصلاحية</th>
                            <th>الرصيد المتاح</th>
                            <th>سعر التكلفة</th>
                            <th>سعر البيع (SDG)</th>
                            <th class="text-center">أولوية الصرف FEFO</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($batches)): ?>
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">لا توجد تشغيلات مسجلة</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($batches as $b): ?>
                                <?php
                                    $days = (int)$b['days_to_expiry'];
                                    if ($days <= 0) {
                                        $expBadge = 'bg-danger text-white';
                                        $expText = 'منتهية الصلاحية (Expired)';
                                    } elseif ($days <= 90) {
                                        $expBadge = 'bg-warning text-dark';
                                        $expText = "قريبة الانتهاء ({$days} يوم)";
                                    } else {
                                        $expBadge = 'bg-success text-white';
                                        $expText = "سارية ({$days} يوم)";
                                    }
                                ?>
                                <tr>
                                    <td class="fw-bold font-monospace text-navy">
                                        🏷️ <?= e($b['batch_number']) ?>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-navy"><?= e($b['commercial_name']) ?></div>
                                        <small class="text-muted"><?= e($b['scientific_name']) ?> - <?= e($b['strength']) ?></small>
                                    </td>
                                    <td><span class="badge bg-light text-dark border"><?= e($b['warehouse_name']) ?></span></td>
                                    <td class="font-monospace fw-bold"><?= date('d/m/Y', strtotime($b['expiry_date'])) ?></td>
                                    <td><span class="badge <?= $expBadge ?>"><?= $expText ?></span></td>
                                    <td class="fw-bold fs-6 <?= ($b['quantity_on_hand'] <= 0) ? 'text-muted' : 'text-primary' ?>">
                                        <?= number_format($b['quantity_on_hand']) ?>
                                    </td>
                                    <td><?= number_format($b['cost_price'], 2) ?></td>
                                    <td class="fw-bold text-success"><?= number_format($b['sale_price'], 2) ?></td>
                                    <td class="text-center">
                                        <?php if ($b['quantity_on_hand'] > 0 && $days > 0): ?>
                                            <span class="badge bg-primary-subtle text-primary border fw-bold">✓ أولوية صرف (FEFO)</span>
                                        <?php elseif ($days <= 0): ?>
                                            <span class="badge bg-danger">محظور الصرف</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">نافد</span>
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
</div>
