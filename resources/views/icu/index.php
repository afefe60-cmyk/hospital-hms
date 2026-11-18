<?php
$layout = 'layouts/main';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h4 mb-1 fw-bold"><i class="bi bi-activity text-danger me-2"></i>وحدة العناية المركزة (ICU Dashboard)</h2>
        <p class="text-muted mb-0">مراقبة إشغال أسرة العناية المركزة، الحالات الحرجة، أجهزة التنفس الاصطناعي والمؤشرات الحيوية</p>
    </div>
    <div class="d-flex gap-2">
        <a href="/icu/create" class="btn btn-danger d-flex align-items-center gap-2">
            <i class="bi bi-person-plus-fill"></i>
            <span>إدخال مريض للعناية المركزة</span>
        </a>
    </div>
</div>

<!-- Metrics Strip -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-danger bg-opacity-10 p-3 text-danger me-3">
                    <i class="bi bi-hospital fs-3"></i>
                </div>
                <div>
                    <span class="text-muted d-block small">إجمالي أسرة العناية</span>
                    <h3 class="fw-bold mb-0"><?= number_format($stats['total_beds']) ?></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-warning bg-opacity-10 p-3 text-warning me-3">
                    <i class="bi bi-person-wheelchair fs-3"></i>
                </div>
                <div>
                    <span class="text-muted d-block small">الأسرة المشغولة</span>
                    <h3 class="fw-bold mb-0 text-warning"><?= number_format($stats['occupied_beds']) ?></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-success bg-opacity-10 p-3 text-success me-3">
                    <i class="bi bi-check2-circle fs-3"></i>
                </div>
                <div>
                    <span class="text-muted d-block small">الأسرة الشاغرة</span>
                    <h3 class="fw-bold mb-0 text-success"><?= number_format($stats['available_beds']) ?></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-info bg-opacity-10 p-3 text-info me-3">
                    <i class="bi bi-lungs fs-3"></i>
                </div>
                <div>
                    <span class="text-muted d-block small">مرضى على التنفس الاصطناعي</span>
                    <h3 class="fw-bold mb-0 text-info"><?= number_format($stats['ventilated_count']) ?></h3>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ICU Bed Matrix Grid -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-header bg-white py-3 border-light">
        <h5 class="card-title mb-0 fw-bold"><i class="bi bi-grid text-primary me-2"></i>خريطة توزيع ومصفوفة أسرة العناية المركزة</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <?php foreach ($icuBeds as $bed): ?>
                <?php
                    $isOccupied = !empty($bed['active_stay_id']) || $bed['status'] === 'occupied';
                    $cardClass = $isOccupied ? 'border-danger bg-danger bg-opacity-10' : 'border-success bg-success bg-opacity-10';
                    $badgeClass = $isOccupied ? 'bg-danger' : 'bg-success';
                ?>
                <div class="col-md-6 col-lg-4 col-xl-3">
                    <div class="card h-100 border <?= $cardClass ?> rounded-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold mb-0 font-monospace text-dark"><?= htmlspecialchars($bed['bed_number']) ?></h6>
                                <span class="badge <?= $badgeClass ?>"><?= $isOccupied ? 'مشغول' : 'شاغر' ?></span>
                            </div>
                            <small class="text-muted d-block mb-2">الغرفة: <?= htmlspecialchars($bed['room_number']) ?></small>
                            <?php if ($isOccupied && !empty($bed['active_stay_id'])): ?>
                                <div class="bg-white p-2 rounded border small">
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($bed['current_patient_name']) ?></div>
                                    <div class="text-muted font-monospace small">MRN: <?= htmlspecialchars($bed['current_patient_mrn']) ?></div>
                                    <div class="mt-1 d-flex justify-content-between align-items-center">
                                        <span class="badge <?= $bed['ventilator_status'] === 'invasive' ? 'bg-danger' : ($bed['ventilator_status'] === 'non_invasive' ? 'bg-warning text-dark' : 'bg-secondary') ?>">
                                            <?= $bed['ventilator_status'] === 'invasive' ? 'تنفس غازي (Invasive)' : ($bed['ventilator_status'] === 'non_invasive' ? 'تنفس غير غازي' : 'بدون جهاز تنفس') ?>
                                        </span>
                                        <a href="/icu/show?id=<?= $bed['active_stay_id'] ?>" class="btn btn-outline-danger btn-sm py-0 px-2">متابعة</a>
                                    </div>
                                </div>
                            <?php else: ?>
                                <p class="text-success small mb-2"><i class="bi bi-check-circle me-1"></i>السرير شاغر ومعقم ومجهز بأجهزة المراقبة.</p>
                                <a href="/icu/create" class="btn btn-sm btn-outline-success w-100">تسكين مريض</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Active ICU Stays Table -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white py-3 border-light d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0 fw-bold"><i class="bi bi-list-stars text-primary me-2"></i>قائمة مرضى العناية المركزة</h5>
        <div class="btn-group btn-group-sm">
            <a href="/icu?status=admitted" class="btn <?= $selectedStatus === 'admitted' ? 'btn-primary' : 'btn-outline-secondary' ?>">المنومون حالياً</a>
            <a href="/icu?status=discharged" class="btn <?= $selectedStatus === 'discharged' ? 'btn-primary' : 'btn-outline-secondary' ?>">المتعافون / مخرجون</a>
            <a href="/icu?status=all" class="btn <?= $selectedStatus === 'all' ? 'btn-primary' : 'btn-outline-secondary' ?>">الكل</a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>رقم الإقامة</th>
                        <th>المريض</th>
                        <th>السرير / الغرفة</th>
                        <th>توقيت الدخول</th>
                        <th>حالة التنفس الاصطناعي</th>
                        <th>مقياس APACHE II</th>
                        <th>الطبيب المعالج</th>
                        <th>آخر تدوين للمؤشرات</th>
                        <th>الحالة</th>
                        <th class="text-center">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($stays)): ?>
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                لا توجد إقامات مسجلة في وحدة العناية المركزة حالياً.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($stays as $s): ?>
                            <?php
                                $statusBadge = match($s['status']) {
                                    'admitted' => 'bg-danger animate-pulse',
                                    'transferred' => 'bg-warning text-dark',
                                    'discharged' => 'bg-success',
                                    'deceased' => 'bg-dark',
                                    default => 'bg-secondary'
                                };
                                $statusText = match($s['status']) {
                                    'admitted' => 'منوم بالعناية',
                                    'transferred' => 'تم نقله لعنبر',
                                    'discharged' => 'خرج بتحسن',
                                    'deceased' => 'وفاة',
                                    default => $s['status']
                                };
                            ?>
                            <tr>
                                <td class="fw-bold font-monospace text-danger"><?= htmlspecialchars($s['stay_number']) ?></td>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($s['patient_name']) ?></div>
                                    <small class="text-muted font-monospace"><?= htmlspecialchars($s['mrn']) ?> (<?= $s['patient_age'] ?> سنة)</small>
                                </td>
                                <td>
                                    <span class="badge bg-secondary font-monospace"><?= htmlspecialchars($s['bed_number']) ?></span>
                                    <small class="text-muted d-block"><?= htmlspecialchars($s['room_number']) ?></small>
                                </td>
                                <td>
                                    <div class="small fw-bold"><?= date('Y-m-d', strtotime($s['admitted_at'])) ?></div>
                                    <div class="text-muted small"><?= date('h:i A', strtotime($s['admitted_at'])) ?></div>
                                </td>
                                <td>
                                    <?php if ($s['ventilator_status'] === 'invasive'): ?>
                                        <span class="badge bg-danger"><i class="bi bi-lungs me-1"></i>تنفس غازي (Invasive)</span>
                                    <?php elseif ($s['ventilator_status'] === 'non_invasive'): ?>
                                        <span class="badge bg-warning text-dark"><i class="bi bi-lungs me-1"></i>غير غازي (NIV)</span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted">تنفس طبيعي</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong class="text-dark"><?= $s['apache_score'] !== null ? $s['apache_score'] : '-' ?></strong>
                                </td>
                                <td>د. <?= htmlspecialchars($s['doctor_name'] ?? 'طبيب العناية المناوب') ?></td>
                                <td>
                                    <?= !empty($s['last_observation_time']) ? date('h:i A', strtotime($s['last_observation_time'])) : '<span class="text-muted small">لم تدون بعد</span>' ?>
                                </td>
                                <td>
                                    <span class="badge <?= $statusBadge ?>"><?= $statusText ?></span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="/icu/show?id=<?= $s['id'] ?>" class="btn btn-outline-danger" title="عرض مخطط العناية والمؤشرات">
                                            <i class="bi bi-speedometer"></i> مخطط العناية
                                        </a>
                                        <a href="/icu/print?id=<?= $s['id'] ?>" target="_blank" class="btn btn-outline-secondary" title="طباعة تقرير الفلوشيت">
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
