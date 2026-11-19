<?php
$layout = 'layouts/main';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h4 mb-1 fw-bold"><i class="bi bi-box-seam text-warning me-2"></i>تقرير الصيدلية والمخزون الطبي (Pharmacy & Stock Valuation)</h2>
        <p class="text-muted mb-0">تقييم المخزون الدوائي بالجنيه السوداني (SDG)، تتبع الصلاحيات بنظام FEFO، والتنبيهات المسبقة للرواكد</p>
    </div>
    <div class="d-flex gap-2">
        <a href="/reports" class="btn btn-outline-secondary d-flex align-items-center gap-1">
            <i class="bi bi-arrow-right"></i> لوحة الإحصائيات العامة
        </a>
        <a href="/pharmacy" class="btn btn-outline-primary d-flex align-items-center gap-1">
            <i class="bi bi-capsule"></i> صرف الصيدلية
        </a>
        <a href="/inventory" class="btn btn-outline-secondary d-flex align-items-center gap-1">
            <i class="bi bi-boxes"></i> مستودعات الأدوية
        </a>
    </div>
</div>

<!-- Stock KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body">
                <span class="text-muted small d-block">إجمالي الأصناف المسجلة</span>
                <h3 class="fw-bold mb-0 text-dark"><?= number_format($metrics['total_medicines']) ?> <small class="fs-6 fw-normal text-muted">صنف</small></h3>
                <small class="text-muted">أدوية ومحاليل ومستلزمات</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body">
                <span class="text-muted small d-block">القيمة الإجمالية للمخزون (SDG)</span>
                <h3 class="fw-bold mb-0 text-success"><?= number_format($metrics['total_stock_value'], 2) ?> <small class="fs-6 fw-normal text-muted">SDG</small></h3>
                <small class="text-muted">بسعر البيع المعتمد</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body">
                <span class="text-muted small d-block">إجمالي الوحدات المخزنة</span>
                <h3 class="fw-bold mb-0 text-primary"><?= number_format($metrics['total_units']) ?> <small class="fs-6 fw-normal text-muted">وحدة</small></h3>
                <small class="text-muted">عبر كافة المستودعات والصيدليات</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body">
                <span class="text-muted small d-block">أصناف وشيكة الانتهاء (FEFO &lt; 90 يوم)</span>
                <h3 class="fw-bold mb-0 text-danger"><?= number_format($metrics['near_expiry_count']) ?> <small class="fs-6 fw-normal text-muted">تشغيلة</small></h3>
                <small class="text-danger">تتطلب الصرف الفوري أو الإرجاع</small>
            </div>
        </div>
    </div>
</div>

<!-- Batches Table -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white py-3">
        <h5 class="card-title mb-0 fw-bold fs-6">
            <i class="bi bi-clock-history text-warning me-2"></i>جدول تشغيلات الأدوية وتواريخ الصلاحية مرتبة بنظام FEFO (الأقرب انتهاءً أولاً)
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>الدواء / الصنف</th>
                        <th>الاسم العلمي والعيار</th>
                        <th>المستودع</th>
                        <th>رقم التشغيلة (Batch #)</th>
                        <th>الرصيد المتبقي</th>
                        <th>تاريخ الصلاحية</th>
                        <th>الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($expiringBatches)): ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted">لا توجد تشغيلات مسجلة بالمخازن.</td></tr>
                    <?php else: ?>
                        <?php 
                        $today = date('Y-m-d');
                        $warnDate = date('Y-m-d', strtotime('+90 days'));
                        foreach ($expiringBatches as $b): 
                            $isExpired = ($b['expiry_date'] < $today);
                            $isNear = (!$isExpired && $b['expiry_date'] <= $warnDate);
                        ?>
                            <tr>
                                <td class="fw-bold"><?= htmlspecialchars($b['commercial_name']) ?></td>
                                <td>
                                    <div><?= htmlspecialchars($b['scientific_name']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($b['strength']) ?> &bull; <?= htmlspecialchars($b['dosage_form']) ?></small>
                                </td>
                                <td><?= htmlspecialchars($b['warehouse_name'] ?? 'الصيدلية الرئيسية') ?></td>
                                <td class="font-monospace fw-bold"><?= htmlspecialchars($b['batch_number']) ?></td>
                                <td class="font-monospace fs-6"><?= number_format($b['quantity_on_hand']) ?></td>
                                <td class="font-monospace <?= $isExpired ? 'text-danger fw-bold' : ($isNear ? 'text-warning fw-bold' : '') ?>">
                                    <?= $b['expiry_date'] ?>
                                </td>
                                <td>
                                    <?php if ($isExpired): ?>
                                        <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>منتهي الصلاحية</span>
                                    <?php elseif ($isNear): ?>
                                        <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle me-1"></i>وشيك الانتهاء (&lt;90 يوم)</span>
                                    <?php else: ?>
                                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>سليم وصالح</span>
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
