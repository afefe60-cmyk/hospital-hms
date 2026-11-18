<?php
$layout = 'layouts/main';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h4 mb-1 fw-bold"><i class="bi bi-heart-pulse text-danger me-2"></i>إدارة العمليات الجراحية وغرف العمليات (OR)</h2>
        <p class="text-muted mb-0">جدولة وتوثيق العمليات الجراحية، طواقم الجراحة والتخدير ومراقبة إشغال المسارح</p>
    </div>
    <div class="d-flex gap-2">
        <a href="/surgery/create" class="btn btn-primary d-flex align-items-center gap-2">
            <i class="bi bi-calendar-plus"></i>
            <span>حجز عملية جراحية جديدة</span>
        </a>
    </div>
</div>

<!-- Quick Stats Strip -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3 text-primary me-3">
                    <i class="bi bi-calendar-check fs-3"></i>
                </div>
                <div>
                    <span class="text-muted d-block small">عمليات اليوم المجدولة</span>
                    <h3 class="fw-bold mb-0"><?= number_format($stats['total_today']) ?></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-danger bg-opacity-10 p-3 text-danger me-3">
                    <i class="bi bi-activity fs-3"></i>
                </div>
                <div>
                    <span class="text-muted d-block small">عمليات جارية الآن</span>
                    <h3 class="fw-bold mb-0 text-danger"><?= number_format($stats['in_progress']) ?></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-success bg-opacity-10 p-3 text-success me-3">
                    <i class="bi bi-check-circle fs-3"></i>
                </div>
                <div>
                    <span class="text-muted d-block small">اكتملت اليوم</span>
                    <h3 class="fw-bold mb-0 text-success"><?= number_format($stats['completed_today']) ?></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-info bg-opacity-10 p-3 text-info me-3">
                    <i class="bi bi-clock-history fs-3"></i>
                </div>
                <div>
                    <span class="text-muted d-block small">مجدولة لاحقاً</span>
                    <h3 class="fw-bold mb-0"><?= number_format($stats['scheduled_future']) ?></h3>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Operating Rooms Status Cards -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-header bg-white py-3 border-light">
        <h5 class="card-title mb-0 fw-bold"><i class="bi bi-door-closed text-primary me-2"></i>حالة غرف العمليات والمسارح الجراحية</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <?php foreach ($operatingRooms as $or): ?>
                <?php
                    $statusClass = match($or['status']) {
                        'in_surgery' => 'border-danger bg-danger bg-opacity-10',
                        'cleaning' => 'border-warning bg-warning bg-opacity-10',
                        'maintenance' => 'border-secondary bg-secondary bg-opacity-10',
                        default => 'border-success bg-success bg-opacity-10'
                    };
                    $badgeClass = match($or['status']) {
                        'in_surgery' => 'bg-danger',
                        'cleaning' => 'bg-warning text-dark',
                        'maintenance' => 'bg-secondary',
                        default => 'bg-success'
                    };
                    $statusText = match($or['status']) {
                        'in_surgery' => 'عملية جارية',
                        'cleaning' => 'تعقيم وتجهيز',
                        'maintenance' => 'صيانة دورية',
                        default => 'شاغرة ومتاحة'
                    };
                ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border <?= $statusClass ?> rounded-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold mb-0"><?= htmlspecialchars($or['name_ar']) ?></h6>
                                <span class="badge <?= $badgeClass ?>"><?= $statusText ?></span>
                            </div>
                            <small class="text-muted d-block mb-2"><?= htmlspecialchars($or['name_en']) ?></small>
                            <?php if ($or['status'] === 'in_surgery'): ?>
                                <div class="alert alert-danger py-2 px-3 mb-0 small">
                                    <div class="fw-bold mb-1"><i class="bi bi-scissors me-1"></i><?= htmlspecialchars($or['current_procedure'] ?? 'عملية جراحية') ?></div>
                                    <div class="text-muted">الجراح: <?= htmlspecialchars($or['current_surgeon'] ?? 'طاقم الجراحة') ?></div>
                                </div>
                            <?php else: ?>
                                <p class="text-muted small mb-0"><i class="bi bi-info-circle me-1"></i>الغرفة معقمة وجاهزة للجدولة.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Surgeries Table with Filters -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white py-3 border-light">
        <form method="GET" action="/surgery" class="row g-2 align-items-center">
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- جميع الحالات --</option>
                    <option value="scheduled" <?= $selectedStatus === 'scheduled' ? 'selected' : '' ?>>مجدولة (Scheduled)</option>
                    <option value="in_progress" <?= $selectedStatus === 'in_progress' ? 'selected' : '' ?>>جارية الآن (In Progress)</option>
                    <option value="completed" <?= $selectedStatus === 'completed' ? 'selected' : '' ?>>مكتملة (Completed)</option>
                    <option value="postponed" <?= $selectedStatus === 'postponed' ? 'selected' : '' ?>>مؤجلة (Postponed)</option>
                    <option value="cancelled" <?= $selectedStatus === 'cancelled' ? 'selected' : '' ?>>ملغاة (Cancelled)</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="date" name="date" class="form-control form-control-sm" value="<?= htmlspecialchars($selectedDate ?? '') ?>" onchange="this.form.submit()">
            </div>
            <div class="col-md-4">
                <select name="or_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- جميع غرف العمليات --</option>
                    <?php foreach ($operatingRooms as $or): ?>
                        <option value="<?= $or['id'] ?>" <?= ((string)$selectedOr === (string)$or['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($or['name_ar']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 text-start">
                <a href="/surgery" class="btn btn-outline-secondary btn-sm w-100">إلغاء الفلترة</a>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>رقم الجراحة</th>
                        <th>المريض</th>
                        <th>غرفة العمليات</th>
                        <th>اسم الإجراء / العملية</th>
                        <th>النوع</th>
                        <th>الجراح الرئيسي</th>
                        <th>الموعد المجدول</th>
                        <th>الحالة</th>
                        <th class="text-center">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($surgeries)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                لا توجد عمليات جراحية مسجلة مطابقة للخيارات المحددة.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($surgeries as $s): ?>
                            <?php
                                $badgeClass = match($s['status']) {
                                    'scheduled' => 'bg-info',
                                    'in_progress' => 'bg-danger animate-pulse',
                                    'completed' => 'bg-success',
                                    'postponed' => 'bg-warning text-dark',
                                    'cancelled' => 'bg-secondary',
                                    default => 'bg-light text-dark'
                                };
                                $statusLabel = match($s['status']) {
                                    'scheduled' => 'مجدولة',
                                    'in_progress' => 'جارية الآن',
                                    'completed' => 'مكتملة',
                                    'postponed' => 'مؤجلة',
                                    'cancelled' => 'ملغاة',
                                    default => $s['status']
                                };
                            ?>
                            <tr>
                                <td class="fw-bold font-monospace text-primary"><?= htmlspecialchars($s['surgery_number']) ?></td>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($s['patient_name']) ?></div>
                                    <small class="text-muted font-monospace"><?= htmlspecialchars($s['mrn']) ?></small>
                                </td>
                                <td><?= htmlspecialchars($s['or_name_ar']) ?></td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($s['procedure_name']) ?></div>
                                </td>
                                <td>
                                    <span class="badge <?= $s['surgery_type'] === 'emergency' ? 'bg-danger' : 'bg-primary' ?>">
                                        <?= $s['surgery_type'] === 'emergency' ? 'طارئة' : 'اختيارية' ?>
                                    </span>
                                </td>
                                <td>
                                    <div><?= htmlspecialchars($s['lead_surgeon_name']) ?></div>
                                    <?php if (!empty($s['anesthesiologist_name'])): ?>
                                        <small class="text-muted d-block">تخدير: <?= htmlspecialchars($s['anesthesiologist_name']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="small fw-bold"><?= date('Y-m-d', strtotime($s['scheduled_start'])) ?></div>
                                    <div class="text-muted small"><?= date('h:i A', strtotime($s['scheduled_start'])) ?> - <?= date('h:i A', strtotime($s['scheduled_end'])) ?></div>
                                </td>
                                <td>
                                    <span class="badge <?= $badgeClass ?>"><?= $statusLabel ?></span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="/surgery/show?id=<?= $s['id'] ?>" class="btn btn-outline-primary" title="عرض وتوثيق الجراحة">
                                            <i class="bi bi-eye"></i> تفاصيل
                                        </a>
                                        <a href="/surgery/print?id=<?= $s['id'] ?>" target="_blank" class="btn btn-outline-secondary" title="طباعة تقرير العملية">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
