<div class="row g-4">
    <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h3 class="fw-bold text-navy mb-1 d-flex align-items-center gap-2">
                <i data-lucide="bed" class="text-green" style="width: 28px; height: 28px;"></i>
                خريطة الأسِرّة والأجنحة التفاعلية (Bed Management Matrix)
            </h3>
            <p class="text-muted mb-0">المتابعة اللحظية لإشغال الأسرة، الغرف، وتوزيع المرضى بالأقسام الداخلية</p>
        </div>
    </div>

    <!-- Status Statistics KPI Cards -->
    <div class="col-md-2 col-sm-4 col-6">
        <div class="card card-hms p-3 bg-white text-center">
            <span class="text-muted small d-block">إجمالي الأسرة</span>
            <h3 class="fw-bold text-navy my-1"><?= $counts['total'] ?></h3>
            <small class="text-secondary">سعة المستشفى</small>
        </div>
    </div>

    <div class="col-md-2 col-sm-4 col-6">
        <div class="card card-hms p-3 bg-white text-center border-bottom border-success border-4">
            <span class="text-muted small d-block">أسِرّة شاغرة</span>
            <h3 class="fw-bold text-success my-1"><?= $counts['available'] ?></h3>
            <small class="text-success">جاهز للتسكين</small>
        </div>
    </div>

    <div class="col-md-2 col-sm-4 col-6">
        <div class="card card-hms p-3 bg-white text-center border-bottom border-danger border-4">
            <span class="text-muted small d-block">أسِرّة مشغولة</span>
            <h3 class="fw-bold text-danger my-1"><?= $counts['occupied'] ?></h3>
            <small class="text-danger">منوم حالياً</small>
        </div>
    </div>

    <div class="col-md-2 col-sm-4 col-6">
        <div class="card card-hms p-3 bg-white text-center border-bottom border-warning border-4">
            <span class="text-muted small d-block">قيد التعقيم</span>
            <h3 class="fw-bold text-warning my-1"><?= $counts['cleaning'] ?></h3>
            <small class="text-warning">تجهيز وتعقيم</small>
        </div>
    </div>

    <div class="col-md-2 col-sm-4 col-6">
        <div class="card card-hms p-3 bg-white text-center border-bottom border-secondary border-4">
            <span class="text-muted small d-block">تحت الصيانة</span>
            <h3 class="fw-bold text-secondary my-1"><?= $counts['maintenance'] ?></h3>
            <small class="text-muted">معطل للصيانة</small>
        </div>
    </div>

    <div class="col-md-2 col-sm-4 col-6">
        <div class="card card-hms p-3 bg-white text-center border-bottom border-primary border-4">
            <span class="text-muted small d-block">نسبة الإشغال</span>
            <?php
            $occRate = $counts['total'] > 0 ? round(($counts['occupied'] / $counts['total']) * 100, 1) : 0;
            ?>
            <h3 class="fw-bold text-primary my-1"><?= $occRate ?>%</h3>
            <small class="text-primary">معدل التشغيل</small>
        </div>
    </div>

    <!-- Bed Grid by Wards & Rooms -->
    <div class="col-12">
        <?php if (empty($matrix)): ?>
            <div class="card card-hms p-5 text-center bg-white">
                <i data-lucide="inbox" style="width: 48px; height: 48px; margin: 0 auto 12px; color: #94A3B8;"></i>
                <h5 class="fw-bold text-navy">لا توجد أسِرّة مسجلة بعد</h5>
                <p class="text-muted">يرجى تهيئة العنابر والغرف أولاً لإظهار خريطة الأسرة.</p>
            </div>
        <?php else: ?>
            <?php foreach ($matrix as $ward): ?>
                <div class="card card-hms p-4 bg-white mb-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center border-bottom pb-3 mb-4">
                        <div>
                            <h5 class="fw-bold text-navy mb-1 d-flex align-items-center gap-2">
                                <i data-lucide="hotel" class="text-green" style="width: 22px; height: 22px;"></i>
                                <?= e($ward['ward_name']) ?>
                            </h5>
                            <span class="badge bg-light text-navy border me-2"><?= e($ward['department']) ?></span>
                            <span class="badge bg-light text-muted border"><?= e($ward['floor']) ?></span>
                            <span class="badge bg-light text-muted border">تصنيف: <?= e($ward['gender']) ?></span>
                        </div>
                    </div>

                    <div class="row g-4">
                        <?php foreach ($ward['rooms'] as $room): ?>
                            <div class="col-lg-6 col-xl-4">
                                <div class="border rounded-3 p-3 bg-light-subtle h-100">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="fw-bold text-navy">غرفة <?= e($room['room_number']) ?></span>
                                        <div>
                                            <span class="badge bg-navy me-1"><?= e($room['room_type']) ?></span>
                                            <span class="text-muted small"><?= format_currency($room['daily_rate']) ?> / يوم</span>
                                        </div>
                                    </div>

                                    <div class="row g-2">
                                        <?php foreach ($room['beds'] as $bed): ?>
                                            <?php
                                            $statusColor = match($bed['status']) {
                                                'available' => 'bg-success text-white',
                                                'occupied' => 'bg-danger text-white',
                                                'cleaning' => 'bg-warning text-dark',
                                                'maintenance' => 'bg-secondary text-white',
                                                default => 'bg-info text-white',
                                            };
                                            $statusText = match($bed['status']) {
                                                'available' => 'شاغر',
                                                'occupied' => 'مشغول',
                                                'cleaning' => 'قيد التعقيم',
                                                'maintenance' => 'صيانة',
                                                default => $bed['status'],
                                            };
                                            ?>
                                            <div class="col-6">
                                                <div class="card p-2 text-center border <?= $statusColor ?> shadow-sm">
                                                    <i data-lucide="bed" style="width: 24px; height: 24px; margin: 0 auto 4px;"></i>
                                                    <div class="fw-bold small"><?= e($bed['bed_number']) ?></div>
                                                    <span class="badge bg-white text-dark mt-1" style="font-size: 0.7rem;"><?= $statusText ?></span>

                                                    <!-- Quick Action Dropdown -->
                                                    <div class="dropdown mt-2">
                                                        <button class="btn btn-sm btn-light py-0 px-2 dropdown-toggle" type="button" data-bs-toggle="dropdown" style="font-size: 0.75rem;">
                                                            تغيير الحالة
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end shadow">
                                                            <li><h6 class="dropdown-header">تحديث حالة السرير</h6></li>
                                                            <li>
                                                                <form action="<?= url("/beds/{$bed['bed_id']}/status") ?>" method="POST">
                                                                    <?= csrf_field() ?>
                                                                    <input type="hidden" name="status" value="available">
                                                                    <button type="submit" class="dropdown-item text-success">شاغر (Available)</button>
                                                                </form>
                                                            </li>
                                                            <li>
                                                                <form action="<?= url("/beds/{$bed['bed_id']}/status") ?>" method="POST">
                                                                    <?= csrf_field() ?>
                                                                    <input type="hidden" name="status" value="occupied">
                                                                    <button type="submit" class="dropdown-item text-danger">مشغول (Occupied)</button>
                                                                </form>
                                                            </li>
                                                            <li>
                                                                <form action="<?= url("/beds/{$bed['bed_id']}/status") ?>" method="POST">
                                                                    <?= csrf_field() ?>
                                                                    <input type="hidden" name="status" value="cleaning">
                                                                    <button type="submit" class="dropdown-item text-warning">قيد التعقيم (Cleaning)</button>
                                                                </form>
                                                            </li>
                                                            <li>
                                                                <form action="<?= url("/beds/{$bed['bed_id']}/status") ?>" method="POST">
                                                                    <?= csrf_field() ?>
                                                                    <input type="hidden" name="status" value="maintenance">
                                                                    <button type="submit" class="dropdown-item text-secondary">تحت الصيانة (Maintenance)</button>
                                                                </form>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
