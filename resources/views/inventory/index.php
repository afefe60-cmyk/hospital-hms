<div class="row g-4">
    <!-- Header -->
    <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h3 class="fw-bold text-navy mb-1 d-flex align-items-center gap-2">
                <i data-lucide="boxes" class="text-primary" style="width: 28px; height: 28px;"></i>
                المخازن والمستودعات الطبية وحركة المخزون (Inventory & Warehouses)
            </h3>
            <p class="text-muted mb-0">مراقبة الأرصدة، مستويات إعادة الطلب، تتبع التشغيلات وصلاحيات الأدوية (FEFO)</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?= url('/inventory/medicines') ?>" class="btn btn-outline-navy fw-bold d-inline-flex align-items-center gap-1">
                <i data-lucide="pill" style="width: 16px; height: 16px;"></i>
                دليل الأدوية
            </a>
            <a href="<?= url('/inventory/batches') ?>" class="btn btn-outline-primary fw-bold d-inline-flex align-items-center gap-1">
                <i data-lucide="layers" style="width: 16px; height: 16px;"></i>
                تتبع التشغيلات (FEFO)
            </a>
            <a href="<?= url('/inventory/batches/create') ?>" class="btn btn-success fw-bold d-inline-flex align-items-center gap-1">
                <i data-lucide="plus" style="width: 16px; height: 16px;"></i>
                إدخال تشغيلة مخزون
            </a>
            <a href="<?= url('/inventory/suppliers') ?>" class="btn btn-outline-secondary fw-bold">
                الموردين
            </a>
            <a href="<?= url('/inventory/purchase-orders') ?>" class="btn btn-primary fw-bold">
                أوامر الشراء
            </a>
        </div>
    </div>

    <!-- Inventory Valuation & Alert Metrics -->
    <div class="col-12">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="card card-hms p-3 bg-white border-primary shadow-sm">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">إجمالي القيمة التقديرية للمخزون</span>
                            <h2 class="fw-bold text-primary mb-0 mt-1"><?= number_format($totalValuation, 2) ?> <small class="fs-6">SDG</small></h2>
                        </div>
                        <i data-lucide="vault" style="width: 38px; height: 38px; color: #173B59;"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card card-hms p-3 bg-white border-danger shadow-sm">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">أصناف قاربت على النفاد (حد الخطر)</span>
                            <h2 class="fw-bold text-danger mb-0 mt-1"><?= $lowStockCount ?></h2>
                            <a href="<?= url('/inventory/medicines?filter=low_stock') ?>" class="small text-danger text-decoration-none fw-bold">عرض النواقص</a>
                        </div>
                        <i data-lucide="alert-triangle" style="width: 38px; height: 38px; color: #EF4444;"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card card-hms p-3 bg-white border-warning shadow-sm">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">تشغيلات قريبة الانتهاء (&le; 90 يوم)</span>
                            <h2 class="fw-bold text-warning mb-0 mt-1"><?= $expiringCount ?></h2>
                            <a href="<?= url('/inventory/batches') ?>" class="small text-warning text-decoration-none fw-bold">جدول الصلاحيات FEFO</a>
                        </div>
                        <i data-lucide="hourglass" style="width: 38px; height: 38px; color: #F59E0B;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Warehouses Breakdown -->
    <div class="col-12">
        <div class="card card-hms p-0 bg-white overflow-hidden shadow-sm mb-4">
            <div class="card-header bg-navy text-white py-3">
                <h5 class="fw-bold mb-0 d-flex align-items-center gap-2">
                    <i data-lucide="warehouse" style="width: 20px; height: 20px; color: #6EE7B7;"></i>
                    حالة المستودعات والمخازن التابعة للمستشفى
                </h5>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>كود المخزن</th>
                            <th>اسم المستودع</th>
                            <th>النوع</th>
                            <th>الموقع</th>
                            <th>عدد الأصناف الفريدة</th>
                            <th>إجمالي الوحدات</th>
                            <th class="text-end">قيمة المخزون (SDG)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($warehouseStats as $ws): ?>
                            <tr>
                                <td class="fw-bold font-monospace text-navy"><?= e($ws['code']) ?></td>
                                <td class="fw-bold"><?= e($ws['name_ar']) ?></td>
                                <td><span class="badge bg-secondary text-uppercase"><?= e($ws['type']) ?></span></td>
                                <td class="text-muted small"><?= e($ws['location'] ?? '-') ?></td>
                                <td><span class="badge bg-light text-dark border"><?= $ws['distinct_medicines'] ?> صنف</span></td>
                                <td class="fw-bold"><?= number_format($ws['total_units']) ?></td>
                                <td class="text-end fw-bold text-success font-monospace"><?= number_format($ws['warehouse_value'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Stock Movements Log -->
    <div class="col-12">
        <div class="card card-hms p-0 bg-white overflow-hidden shadow-sm">
            <div class="card-header bg-navy text-white py-3">
                <h5 class="fw-bold mb-0 d-flex align-items-center gap-2">
                    <i data-lucide="activity" style="width: 20px; height: 20px; color: #6EE7B7;"></i>
                    سجل حركات المخزون والتدقيق (Stock Audit Trail)
                </h5>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>رقم الحركة</th>
                            <th>نوع الحركة</th>
                            <th>الصنف الدوائي</th>
                            <th>التشغيلة</th>
                            <th>المستودع</th>
                            <th>الكمية</th>
                            <th>المسؤول</th>
                            <th>التاريخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($movements)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">لا توجد حركات مخزون مسجلة بعد</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($movements as $m): ?>
                                <tr>
                                    <td class="font-monospace small text-navy fw-bold"><?= e($m['movement_number']) ?></td>
                                    <td>
                                        <?php
                                            $mvBadge = match($m['movement_type']) {
                                                'purchase_receipt' => 'bg-success text-white',
                                                'dispense' => 'bg-info text-dark',
                                                'expired_disposal' => 'bg-danger text-white',
                                                default => 'bg-secondary text-white'
                                            };
                                        ?>
                                        <span class="badge <?= $mvBadge ?>"><?= e($m['movement_type']) ?></span>
                                    </td>
                                    <td class="fw-bold text-navy"><?= e($m['commercial_name']) ?></td>
                                    <td><code><?= e($m['batch_number']) ?></code></td>
                                    <td><?= e($m['warehouse_name']) ?></td>
                                    <td class="fw-bold <?= ($m['quantity'] > 0) ? 'text-success' : 'text-danger' ?>">
                                        <?= ($m['quantity'] > 0) ? '+' . $m['quantity'] : $m['quantity'] ?>
                                    </td>
                                    <td class="small"><?= e($m['user_name']) ?></td>
                                    <td class="small text-muted"><?= date('d/m/Y H:i', strtotime($m['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
