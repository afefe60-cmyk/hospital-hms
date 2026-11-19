<?php
$layout = 'layouts/main';
$clin = $metrics['clinical'];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h4 mb-1 fw-bold"><i class="bi bi-heart-pulse text-primary me-2"></i>تقرير المؤشرات السريرية والجودة الطبية (Clinical Quality & Inpatient KPIs)</h2>
        <p class="text-muted mb-0">معدلات إشغال الأسرّة، متوسط مدة الإقامة (ALOS)، العمليات الجراحية، التنويم والطوارئ</p>
    </div>
    <div class="d-flex gap-2">
        <a href="/reports" class="btn btn-outline-secondary d-flex align-items-center gap-1">
            <i class="bi bi-arrow-right"></i> لوحة الإحصائيات العامة
        </a>
        <a href="/reports/export?type=clinical&start_date=<?= $startDate ?>&end_date=<?= $endDate ?>" class="btn btn-outline-primary d-flex align-items-center gap-1">
            <i class="bi bi-download"></i> تصدير البيانات (CSV)
        </a>
        <a href="/reports/print?start_date=<?= $startDate ?>&end_date=<?= $endDate ?>" target="_blank" class="btn btn-primary d-flex align-items-center gap-2">
            <i class="bi bi-printer"></i> طباعة التقرير
        </a>
    </div>
</div>

<!-- Filter Bar -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body">
        <form action="/reports/clinical" method="GET" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label small text-muted">من تاريخ</label>
                <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($startDate) ?>">
            </div>
            <div class="col-md-5">
                <label class="form-label small text-muted">إلى تاريخ</label>
                <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($endDate) ?>">
            </div>
            <div class="col-md-2 d-grid">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-funnel me-1"></i>تطبيق الفلترة
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Metrics Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body">
                <span class="text-muted small d-block">معدل إشغال الأسرّة (Occupancy)</span>
                <h3 class="fw-bold mb-0 text-success"><?= $clin['bed_occupancy_rate'] ?>%</h3>
                <small class="text-muted"><?= $clin['occupied_beds'] ?> أسرّة مشغولة من <?= $clin['total_beds'] ?> سريراً</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body">
                <span class="text-muted small d-block">متوسط مدة الإقامة (ALOS)</span>
                <h3 class="fw-bold mb-0 text-warning"><?= $clin['alos'] ?> <small class="fs-6 fw-normal text-muted">يوم</small></h3>
                <small class="text-muted">للحالات المنومة خلال الفترة</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body">
                <span class="text-muted small d-block">حالات التنويم الداخلي (IPD)</span>
                <h3 class="fw-bold mb-0 text-primary"><?= number_format($clin['period_admissions']) ?></h3>
                <small class="text-muted">دخول للعنابر التخصصية</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body">
                <span class="text-muted small d-block">العمليات الجراحية المنفذة</span>
                <h3 class="fw-bold mb-0 text-danger"><?= number_format($clin['period_surgeries']) ?></h3>
                <small class="text-muted">جراحة كبرى وصغرى وطوارئ</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Inpatient Admissions Table -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-header bg-white py-3">
                <h6 class="card-title mb-0 fw-bold">
                    <i class="bi bi-hospital text-primary me-2"></i>سجل حالات التنويم الداخلي (<?= count($admissions) ?> حالة)
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>رقم الدخول</th>
                                <th>المريض</th>
                                <th>العنبر / السرير</th>
                                <th>تاريخ الدخول</th>
                                <th>الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($admissions)): ?>
                                <tr><td colspan="5" class="text-center py-4 text-muted">لا توجد حالات تنويم مسجلة في هذه الفترة.</td></tr>
                            <?php else: ?>
                                <?php foreach ($admissions as $adm): ?>
                                    <tr>
                                        <td class="font-monospace fw-bold"><?= htmlspecialchars($adm['admission_number']) ?></td>
                                        <td>
                                            <div class="fw-bold"><?= htmlspecialchars($adm['patient_name']) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($adm['mrn']) ?></small>
                                        </td>
                                        <td>
                                            <div><?= htmlspecialchars($adm['ward_name']) ?></div>
                                            <small class="text-muted">سرير: <?= htmlspecialchars($adm['bed_number']) ?></small>
                                        </td>
                                        <td><?= $adm['admission_date'] ?></td>
                                        <td>
                                            <span class="badge <?= ($adm['status'] === 'admitted') ? 'bg-primary' : 'bg-success' ?>">
                                                <?= ($adm['status'] === 'admitted') ? 'منوم حالياً' : 'تم الخروج' ?>
                                            </span>
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

    <!-- Surgeries Table -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-header bg-white py-3">
                <h6 class="card-title mb-0 fw-bold">
                    <i class="bi bi-scissors text-danger me-2"></i>سجل العمليات الجراحية (<?= count($surgeries) ?> عملية)
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>رقم العملية</th>
                                <th>المريض</th>
                                <th>الجراح الرئيسي</th>
                                <th>التاريخ والتوقيت</th>
                                <th>الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($surgeries)): ?>
                                <tr><td colspan="5" class="text-center py-4 text-muted">لا توجد عمليات مسجلة في هذه الفترة.</td></tr>
                            <?php else: ?>
                                <?php foreach ($surgeries as $surg): ?>
                                    <tr>
                                        <td class="font-monospace fw-bold"><?= htmlspecialchars($surg['surgery_number']) ?></td>
                                        <td>
                                            <div class="fw-bold"><?= htmlspecialchars($surg['patient_name']) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($surg['procedure_name'] ?? 'عملية جراحية') ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($surg['doctor_name']) ?></td>
                                        <td>
                                            <div><?= $surg['surgery_date'] ?></div>
                                            <small class="text-muted"><?= substr($surg['start_time'] ?? '', 0, 5) ?></small>
                                        </td>
                                        <td>
                                            <span class="badge <?= ($surg['status'] === 'completed') ? 'bg-success' : 'bg-primary' ?>">
                                                <?= ($surg['status'] === 'completed') ? 'مكتملة' : 'مجدولة' ?>
                                            </span>
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
</div>
