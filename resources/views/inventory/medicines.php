<div class="row g-4">
    <!-- Header -->
    <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h3 class="fw-bold text-navy mb-1 d-flex align-items-center gap-2">
                <i data-lucide="pill" class="text-primary" style="width: 28px; height: 28px;"></i>
                دليل الأدوية والمستحضرات الطبية (Medicines Catalog)
            </h3>
            <p class="text-muted mb-0">قائمة الأدوية المعتمدة، أشكال الجرعات، والأرصدة الإجمالية في جميع المستودعات</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= url('/inventory/medicines/create') ?>" class="btn btn-primary d-inline-flex align-items-center gap-2 px-3 py-2 fw-bold shadow-sm">
                <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i>
                إضافة صنف دوائي جديد
            </a>
            <a href="<?= url('/inventory') ?>" class="btn btn-outline-secondary">رجوع للمخازن</a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="col-12">
        <div class="card card-hms p-3 bg-white shadow-sm">
            <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
                <div class="d-flex gap-2 align-items-center">
                    <span class="fw-bold text-muted small">التصنيف الدوائي:</span>
                    <a href="<?= url('/inventory/medicines') ?>" class="btn btn-sm <?= empty($selectedCategory) && empty($filter) ? 'btn-primary' : 'btn-outline-secondary' ?>">الكل</a>
                    <?php foreach ($categories as $cat): ?>
                        <a href="<?= url("/inventory/medicines?category_id={$cat['id']}") ?>" class="btn btn-sm <?= ($selectedCategory == $cat['id']) ? 'btn-primary' : 'btn-outline-secondary' ?>">
                            <?= e($cat['name_ar']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>

                <div>
                    <a href="<?= url('/inventory/medicines?filter=low_stock') ?>" class="btn btn-sm <?= ($filter === 'low_stock') ? 'btn-danger fw-bold' : 'btn-outline-danger' ?>">
                        ⚠️ عرض النواقص فقط (Under Minimum Stock)
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Medicines Table -->
    <div class="col-12">
        <div class="card card-hms p-0 bg-white overflow-hidden shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>الكود</th>
                            <th>الاسم التجاري</th>
                            <th>الاسم العلمي (Generic)</th>
                            <th>الشكل والتركيز</th>
                            <th>التصنيف</th>
                            <th>الوحدة</th>
                            <th>الرصيد الكلي</th>
                            <th>حد الخطر</th>
                            <th class="text-center">حالة المخزون</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($medicines)): ?>
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i data-lucide="box" style="width: 48px; height: 48px; margin: 0 auto 12px; color: #64748B;"></i>
                                    <h5 class="fw-bold text-navy">لا توجد أصناف دوائية مطابقة للفلتر</h5>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($medicines as $m): ?>
                                <tr>
                                    <td><code><?= e($m['code']) ?></code></td>
                                    <td class="fw-bold text-navy"><?= e($m['commercial_name']) ?></td>
                                    <td class="text-muted small"><?= e($m['scientific_name']) ?></td>
                                    <td><?= e($m['strength']) ?> (<?= e($m['dosage_form']) ?>)</td>
                                    <td><span class="badge bg-light text-dark border"><?= e($m['category_name']) ?></span></td>
                                    <td><?= e($m['unit']) ?></td>
                                    <td class="fw-bold fs-6 <?= ($m['total_stock'] <= $m['minimum_stock_alert']) ? 'text-danger' : 'text-primary' ?>">
                                        <?= number_format($m['total_stock']) ?>
                                    </td>
                                    <td class="text-muted small"><?= $m['minimum_stock_alert'] ?></td>
                                    <td class="text-center">
                                        <?php if ($m['total_stock'] <= 0): ?>
                                            <span class="badge bg-danger">منفد تماماً (Out of Stock)</span>
                                        <?php elseif ($m['total_stock'] <= $m['minimum_stock_alert']): ?>
                                            <span class="badge bg-warning text-dark">ناقص (Low Stock)</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">متوفر (In Stock)</span>
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
