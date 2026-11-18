<div class="row g-4">
    <!-- Header -->
    <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h3 class="fw-bold text-navy mb-1 d-flex align-items-center gap-2">
                <i data-lucide="scan" class="text-primary" style="width: 28px; height: 28px;"></i>
                نظام الأشعة والتصوير الطبي التشخيصي (Radiology Information System - RIS)
            </h3>
            <p class="text-muted mb-0">جدولة وتوثيق طلبات الأشعة (X-Ray, Ultrasound, CT Scan, Echo) واعتماد تقارير الاستشاري</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= url('/radiology/create') ?>" class="btn btn-primary d-inline-flex align-items-center gap-2 px-3 py-2 fw-bold shadow-sm">
                <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i>
                طلب أشعة جديد
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
                            <span class="text-muted small">طلبات جديدة مطلوبة</span>
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
                            <span class="text-muted small">مجدولة بقسم الأشعة</span>
                            <h2 class="fw-bold text-info mb-0 mt-1"><?= $counts['scheduled'] ?></h2>
                        </div>
                        <i data-lucide="calendar" style="width: 36px; height: 36px; color: #0284C7;"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-hms p-3 bg-white border-primary shadow-sm">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">تم التصوير (بانتظار التقرير)</span>
                            <h2 class="fw-bold text-primary mb-0 mt-1"><?= $counts['performed'] ?></h2>
                        </div>
                        <i data-lucide="camera" style="width: 36px; height: 36px; color: #173B59;"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-hms p-3 bg-white border-success shadow-sm">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">تقارير منجزة ومعتمدة</span>
                            <h2 class="fw-bold text-success mb-0 mt-1"><?= $counts['reported'] ?></h2>
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
                    <i data-lucide="list" style="width: 20px; height: 20px; color: #6EE7B7;"></i>
                    طلبات الأشعة والتصوير الطبي
                </h5>
                <div class="btn-group btn-group-sm">
                    <a href="<?= url('/radiology?status=all') ?>" class="btn btn-sm <?= ($status === 'all') ? 'btn-light fw-bold' : 'btn-outline-light' ?>">الكل (<?= $counts['total'] ?>)</a>
                    <a href="<?= url('/radiology?status=ordered') ?>" class="btn btn-sm <?= ($status === 'ordered') ? 'btn-light fw-bold' : 'btn-outline-light' ?>">جديدة</a>
                    <a href="<?= url('/radiology?status=performed') ?>" class="btn btn-sm <?= ($status === 'performed') ? 'btn-light fw-bold' : 'btn-outline-light' ?>">تم التصوير</a>
                    <a href="<?= url('/radiology?status=reported') ?>" class="btn btn-sm <?= ($status === 'reported') ? 'btn-light fw-bold' : 'btn-outline-light' ?>">صدر التقرير</a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>رقم الطلب</th>
                            <th>الأولوية</th>
                            <th>المريض</th>
                            <th>الفحص المطلوب</th>
                            <th>النوع (Modality)</th>
                            <th>الطبيب المعالج</th>
                            <th>تاريخ الطلب</th>
                            <th>الحالة</th>
                            <th>السعر (SDG)</th>
                            <th class="text-center">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted">
                                    <i data-lucide="film" style="width: 48px; height: 48px; margin: 0 auto 12px; color: #64748B;"></i>
                                    <h5 class="fw-bold text-navy">لا توجد طلبات أشعة مطابقة</h5>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($orders as $o): ?>
                                <tr>
                                    <td class="fw-bold font-monospace text-navy">
                                        <a href="<?= url("/radiology/{$o['id']}") ?>" class="text-decoration-none">
                                            <?= e($o['order_number']) ?>
                                        </a>
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
                                    <td class="fw-bold text-dark"><?= e($o['service_name']) ?></td>
                                    <td><span class="badge bg-navy text-uppercase"><?= e($o['modality']) ?></span></td>
                                    <td>د. <?= e($o['doctor_name']) ?></td>
                                    <td class="small text-muted"><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></td>
                                    <td>
                                        <?php
                                            $stBadge = match($o['status']) {
                                                'ordered' => 'bg-warning text-dark',
                                                'scheduled' => 'bg-info text-dark',
                                                'performed' => 'bg-primary text-white',
                                                'reported' => 'bg-success text-white',
                                                default => 'bg-secondary'
                                            };
                                        ?>
                                        <span class="badge <?= $stBadge ?>"><?= e($o['status']) ?></span>
                                    </td>
                                    <td class="fw-bold text-success"><?= number_format($o['price'], 2) ?></td>
                                    <td class="text-center">
                                        <a href="<?= url("/radiology/{$o['id']}") ?>" class="btn btn-sm btn-outline-navy">
                                            معاينة الملف
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
