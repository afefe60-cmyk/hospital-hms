<div class="row g-4">
    <!-- Header -->
    <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h3 class="fw-bold text-navy mb-1 d-flex align-items-center gap-2">
                <i data-lucide="shopping-bag" class="text-primary" style="width: 28px; height: 28px;"></i>
                أوامر الشراء والتوريد (Purchase Orders & Procurement)
            </h3>
            <p class="text-muted mb-0">إصدار طلبات الشراء للموردين، متابعة التوريد وتغذية المستودعات بالأدوية</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= url('/inventory') ?>" class="btn btn-outline-secondary">لوحة المخازن</a>
        </div>
    </div>

    <!-- PO List Table -->
    <div class="col-12">
        <div class="card card-hms p-0 bg-white overflow-hidden shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>رقم أمر الشراء (PO)</th>
                            <th>المورد</th>
                            <th>المستودع المستلم</th>
                            <th>تاريخ الطلب</th>
                            <th>عدد البنود</th>
                            <th>القيمة الإجمالية (SDG)</th>
                            <th>الحالة</th>
                            <th>حرر بواسطة</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">لا توجد أوامر شراء مسجلة</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($orders as $po): ?>
                                <tr>
                                    <td class="fw-bold font-monospace text-navy">
                                        <?= e($po['po_number']) ?>
                                    </td>
                                    <td class="fw-bold"><?= e($po['supplier_name']) ?></td>
                                    <td><span class="badge bg-light text-dark border"><?= e($po['warehouse_name']) ?></span></td>
                                    <td><?= date('d/m/Y', strtotime($po['order_date'])) ?></td>
                                    <td><span class="badge bg-secondary"><?= $po['items_count'] ?> أصناف</span></td>
                                    <td class="fw-bold text-success font-monospace"><?= number_format($po['total_amount'], 2) ?></td>
                                    <td>
                                        <?php
                                            $poBadge = match($po['status']) {
                                                'received' => 'bg-success',
                                                'approved' => 'bg-primary',
                                                'cancelled' => 'bg-danger',
                                                default => 'bg-warning text-dark'
                                            };
                                        ?>
                                        <span class="badge <?= $poBadge ?>"><?= e($po['status']) ?></span>
                                    </td>
                                    <td class="small text-muted"><?= e($po['creator_name']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
