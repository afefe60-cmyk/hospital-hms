<div class="row g-4">
    <!-- Header -->
    <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h3 class="fw-bold text-navy mb-1 d-flex align-items-center gap-2">
                <i data-lucide="test-tube-2" class="text-primary" style="width: 28px; height: 28px;"></i>
                نظام المختبرات الطبية والتحاليل (Laboratory Information System - LIS)
            </h3>
            <p class="text-muted mb-0">إدارة طلبات الفحوصات المعملية، تتبع العينات بالباركود، وتوثيق النتائج المخبرية</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= url('/lab/create') ?>" class="btn btn-primary d-inline-flex align-items-center gap-2 px-3 py-2 fw-bold shadow-sm">
                <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i>
                طلب فحص معملي جديد
            </a>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="col-12">
        <div class="row g-3">
            <div class="col-md-3">
                <div class="card card-hms p-3 bg-white border-warning shadow-sm">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">طلبات بانتظار العينة</span>
                            <h2 class="fw-bold text-warning mb-0 mt-1"><?= $counts['ordered'] ?></h2>
                        </div>
                        <i data-lucide="clock" style="width: 36px; height: 36px; color: #D97706;"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-hms p-3 bg-white border-info shadow-sm">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">عينات تم سحبها</span>
                            <h2 class="fw-bold text-info mb-0 mt-1"><?= $counts['sample_collected'] ?></h2>
                        </div>
                        <i data-lucide="barcode" style="width: 36px; height: 36px; color: #0284C7;"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-hms p-3 bg-white border-primary shadow-sm">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">قيد الفحص والتحليل</span>
                            <h2 class="fw-bold text-primary mb-0 mt-1"><?= $counts['in_progress'] ?></h2>
                        </div>
                        <i data-lucide="microscope" style="width: 36px; height: 36px; color: #173B59;"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-hms p-3 bg-white border-success shadow-sm">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">نتائج معتمدة مكتملة</span>
                            <h2 class="fw-bold text-success mb-0 mt-1"><?= $counts['completed'] ?></h2>
                        </div>
                        <i data-lucide="check-circle-2" style="width: 36px; height: 36px; color: #16856D;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="col-12">
        <div class="card card-hms p-0 bg-white overflow-hidden shadow-sm">
            <div class="card-header bg-navy text-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 d-flex align-items-center gap-2">
                    <i data-lucide="clipboard-list" style="width: 20px; height: 20px; color: #6EE7B7;"></i>
                    قائمة طلبات الفحوصات المعملية
                </h5>
                <div class="btn-group btn-group-sm">
                    <a href="<?= url('/lab?status=all') ?>" class="btn btn-sm <?= ($status === 'all') ? 'btn-light fw-bold' : 'btn-outline-light' ?>">الكل (<?= $counts['total'] ?>)</a>
                    <a href="<?= url('/lab?status=ordered') ?>" class="btn btn-sm <?= ($status === 'ordered') ? 'btn-light fw-bold' : 'btn-outline-light' ?>">بانتظار العينة</a>
                    <a href="<?= url('/lab?status=sample_collected') ?>" class="btn btn-sm <?= ($status === 'sample_collected') ? 'btn-light fw-bold' : 'btn-outline-light' ?>">عينة مسحوبة</a>
                    <a href="<?= url('/lab?status=in_progress') ?>" class="btn btn-sm <?= ($status === 'in_progress') ? 'btn-light fw-bold' : 'btn-outline-light' ?>">قيد التحليل</a>
                    <a href="<?= url('/lab?status=completed') ?>" class="btn btn-sm <?= ($status === 'completed') ? 'btn-light fw-bold' : 'btn-outline-light' ?>">مكتملة</a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>رقم الطلب</th>
                            <th>الأولوية</th>
                            <th>المريض</th>
                            <th>الطبيب الطالب</th>
                            <th>عدد الفحوصات</th>
                            <th>الباركود</th>
                            <th>الحالة</th>
                            <th>التكلفة (SDG)</th>
                            <th class="text-center">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i data-lucide="flask-conical" style="width: 48px; height: 48px; margin: 0 auto 12px; color: #64748B;"></i>
                                    <h5 class="fw-bold text-navy">لا توجد طلبات معملية مطابقة</h5>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($orders as $o): ?>
                                <tr>
                                    <td class="fw-bold font-monospace text-navy">
                                        <a href="<?= url("/lab/{$o['id']}") ?>" class="text-decoration-none">
                                            <?= e($o['order_number']) ?>
                                        </a>
                                        <small class="d-block text-muted"><?= date('d/m H:i', strtotime($o['created_at'])) ?></small>
                                    </td>
                                    <td>
                                        <?php
                                            $pBadge = match($o['priority']) {
                                                'stat' => 'bg-danger text-white animate-pulse',
                                                'urgent' => 'bg-warning text-dark',
                                                default => 'bg-secondary text-white'
                                            };
                                        ?>
                                        <span class="badge <?= $pBadge ?>"><?= strtoupper(e($o['priority'])) ?></span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-navy"><?= e($o['patient_name']) ?></div>
                                        <small class="text-muted">MRN: <?= e($o['mrn']) ?> | <?= $o['patient_age'] ?> سنة</small>
                                    </td>
                                    <td>د. <?= e($o['doctor_name']) ?></td>
                                    <td><span class="badge bg-light text-dark border"><?= $o['tests_count'] ?> فحص</span></td>
                                    <td>
                                        <?php if ($o['sample_barcode']): ?>
                                            <span class="font-monospace small badge bg-info-subtle text-info border">
                                                🏷️ <?= e($o['sample_barcode']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted small">لم تسحب</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                            $stBadge = match($o['status']) {
                                                'ordered' => 'bg-warning text-dark',
                                                'sample_collected' => 'bg-info text-dark',
                                                'in_progress' => 'bg-primary text-white',
                                                'completed' => 'bg-success text-white',
                                                default => 'bg-secondary'
                                            };
                                        ?>
                                        <span class="badge <?= $stBadge ?>"><?= e($o['status']) ?></span>
                                    </td>
                                    <td class="fw-bold text-success"><?= number_format($o['total_amount'], 2) ?></td>
                                    <td class="text-center">
                                        <a href="<?= url("/lab/{$o['id']}") ?>" class="btn btn-sm btn-outline-navy">
                                            فتح الملف
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
