<div class="row g-4">
    <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h3 class="fw-bold text-navy mb-1 d-flex align-items-center gap-2">
                <i data-lucide="calendar" class="text-green" style="width: 28px; height: 28px;"></i>
                جدول المواعيد وحجوزات العيادات الخارجية
            </h3>
            <p class="text-muted mb-0">متابعة الحجوزات اليومية، تسجيل وصول المرضى، وإصدار تذاكر الطوابير</p>
        </div>

        <a href="<?= url('/appointments/create') ?>" class="btn btn-hms-primary d-inline-flex align-items-center gap-2">
            <i data-lucide="calendar-plus" style="width: 18px; height: 18px;"></i>
            حجز موعد جديد
        </a>
    </div>

    <!-- Filter by Date & Status -->
    <div class="col-12">
        <div class="card card-hms p-3 bg-white">
            <form action="<?= url('/appointments') ?>" method="GET" class="row g-3 align-items-center">
                <div class="col-md-4">
                    <label class="form-label small text-muted">تاريخ الموعد</label>
                    <input type="date" name="date" class="form-control" value="<?= e($selectedDate) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted">حالة الحجز</label>
                    <select name="status" class="form-select">
                        <option value="">جميع الحالات</option>
                        <option value="booked" <?= $selectedStatus === 'booked' ? 'selected' : '' ?>>محجوز (Booked)</option>
                        <option value="checked_in" <?= $selectedStatus === 'checked_in' ? 'selected' : '' ?>>تم الوصول (Checked In)</option>
                        <option value="in_consultation" <?= $selectedStatus === 'in_consultation' ? 'selected' : '' ?>>قيد الكشف (In Consultation)</option>
                        <option value="completed" <?= $selectedStatus === 'completed' ? 'selected' : '' ?>>مكتمل (Completed)</option>
                        <option value="cancelled" <?= $selectedStatus === 'cancelled' ? 'selected' : '' ?>>ملغي (Cancelled)</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-hms-secondary flex-grow-1">عرض المواعيد</button>
                    <a href="<?= url('/appointments?date=' . date('Y-m-d')) ?>" class="btn btn-outline-primary">اليوم</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Appointments Table -->
    <div class="col-12">
        <div class="card card-hms p-0 bg-white overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>رقم الدور</th>
                            <th>المريض</th>
                            <th>الرقم الطبي (MRN)</th>
                            <th>العيادة / الغرفة</th>
                            <th>الطبيب المعالج</th>
                            <th>الوقت المحدد</th>
                            <th>الحالة</th>
                            <th class="text-center">إجراء الاستقبال</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($appointments)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i data-lucide="calendar-x" style="width: 40px; height: 40px; margin: 0 auto 8px; color: #94A3B8;"></i>
                                    لا توجد مواعيد مسجلة في هذا التاريخ.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($appointments as $app): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-navy fs-6 px-3 py-2">#<?= $app['token_number'] ?></span>
                                    </td>
                                    <td>
                                        <a href="<?= url("/patients/{$app['patient_id']}") ?>" class="fw-bold text-navy text-decoration-none">
                                            <?= e($app['patient_name']) ?>
                                        </a>
                                        <small class="d-block text-muted"><?= e($app['phone_primary']) ?></small>
                                    </td>
                                    <td><code><?= e($app['mrn']) ?></code></td>
                                    <td>
                                        <span class="fw-semibold text-dark"><?= e($app['clinic_name']) ?></span>
                                        <small class="d-block text-muted">غرفة <?= e($app['room_number']) ?></small>
                                    </td>
                                    <td>
                                        <span>د. <?= e($app['doctor_name']) ?></span>
                                        <small class="d-block text-secondary"><?= e($app['specialty_name']) ?></small>
                                    </td>
                                    <td class="fw-bold text-navy"><?= e($app['appointment_time']) ?></td>
                                    <td>
                                        <?php
                                        $statusClass = match($app['status']) {
                                            'checked_in' => 'bg-info text-white',
                                            'in_consultation' => 'bg-warning text-dark',
                                            'completed' => 'bg-success text-white',
                                            'cancelled' => 'bg-danger text-white',
                                            default => 'bg-secondary text-white',
                                        };
                                        $statusText = match($app['status']) {
                                            'booked' => 'محجوز',
                                            'checked_in' => 'تم الوصول',
                                            'in_consultation' => 'بالداخل',
                                            'completed' => 'مكتمل',
                                            'cancelled' => 'ملغي',
                                            default => $app['status'],
                                        };
                                        ?>
                                        <span class="badge <?= $statusClass ?>"><?= $statusText ?></span>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($app['status'] === 'booked'): ?>
                                            <form action="<?= url("/appointments/{$app['id']}/check-in") ?>" method="POST" style="display:inline;">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-sm btn-success d-inline-flex align-items-center gap-1">
                                                    <i data-lucide="check-circle" style="width: 14px; height: 14px;"></i>
                                                    تسجيل الوصول (Check In)
                                                </button>
                                            </form>
                                        <?php elseif ($app['status'] === 'checked_in'): ?>
                                            <span class="badge bg-success-subtle text-success p-2">في طابور الانتظار</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="p-3 border-top d-flex justify-content-between align-items-center">
                    <small class="text-muted">إجمالي المواعيد: <?= $totalRecords ?></small>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                                <li class="page-item <?= $p === $currentPage ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= url("/appointments?page={$p}&date=" . urlencode($selectedDate) . '&status=' . urlencode($selectedStatus)) ?>"><?= $p ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
