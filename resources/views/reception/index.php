<div class="row g-4">
    <!-- Header with Quick Action Buttons -->
    <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h3 class="fw-bold text-navy mb-1 d-flex align-items-center gap-2">
                <i data-lucide="bell" class="text-green" style="width: 28px; height: 28px;"></i>
                مكتب الاستقبال وإدارة المراجعين (Front Desk Hub)
            </h3>
            <p class="text-muted mb-0">تسجيل الوصول السريع، إدارة طوابير الانتظار، واستقبال حالات العيادات</p>
        </div>

        <div class="d-flex gap-2">
            <a href="<?= url('/patients/create') ?>" class="btn btn-hms-primary d-inline-flex align-items-center gap-2">
                <i data-lucide="user-plus" style="width: 18px; height: 18px;"></i>
                فتح ملف مريض جديد
            </a>
            <a href="<?= url('/appointments/create') ?>" class="btn btn-hms-secondary d-inline-flex align-items-center gap-2">
                <i data-lucide="calendar-plus" style="width: 18px; height: 18px;"></i>
                حجز موعد
            </a>
        </div>
    </div>

    <!-- Quick Patient Search Bar -->
    <div class="col-12">
        <div class="card card-hms p-4 bg-white" style="border-right: 5px solid var(--hms-green) !important;">
            <h5 class="fw-bold text-navy mb-2 d-flex align-items-center gap-2">
                <i data-lucide="search" class="text-green" style="width: 20px; height: 20px;"></i>
                البحث السريع عن مراجع بالاستقبال
            </h5>
            <form action="<?= url('/patients') ?>" method="GET" class="row g-3 align-items-center">
                <div class="col-md-10">
                    <input type="text"
                           name="q"
                           class="form-control form-control-lg"
                           placeholder="أدخل رقم الملف MRN، الرقم الوطني، رقم الهاتف، أو اسم المريض للبحث المباشر..."
                           autofocus>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-hms-primary btn-lg w-100">
                        بحث في السجلات
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Live Clinic Queues TV Links -->
    <div class="col-12">
        <h5 class="fw-bold text-navy mb-3 d-flex align-items-center gap-2">
            <i data-lucide="tv" class="text-accent" style="width: 22px; height: 22px;"></i>
            شاشات الانتظار اللحظية للعيادات (Live Waiting Screens)
        </h5>

        <div class="row g-3">
            <?php if (empty($queueSummaries)): ?>
                <div class="col-12">
                    <div class="card card-hms p-3 bg-white text-muted text-center">
                        لا توجد طوابير انتظار نشطة في العيادات حالياً.
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($queueSummaries as $qs): ?>
                    <div class="col-md-4 col-sm-6">
                        <div class="card card-hms p-3 bg-white h-100">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold text-navy mb-0"><?= e($qs['clinic']['name_ar']) ?></h6>
                                <span class="badge bg-light text-dark border">غرفة <?= e($qs['clinic']['room_number']) ?></span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center my-3 p-2 bg-light rounded">
                                <div>
                                    <small class="text-muted d-block">الرقم المستدعى الآن:</small>
                                    <span class="fw-bold fs-4 text-success">
                                        <?= $qs['current_call'] ? '#' . $qs['current_call']['queue_number'] : '--' ?>
                                    </span>
                                </div>
                                <div class="text-end">
                                    <small class="text-muted d-block">في الانتظار:</small>
                                    <span class="badge bg-warning text-dark fs-6"><?= $qs['waiting_count'] ?> مراجع</span>
                                </div>
                            </div>

                            <a href="<?= url("/queue/display/{$qs['clinic']['id']}") ?>" target="_blank" class="btn btn-sm btn-outline-primary w-100 d-inline-flex align-items-center justify-content-center gap-1">
                                <i data-lucide="monitor" style="width: 14px; height: 14px;"></i>
                                فتح شاشة العرض (TV Monitor)
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Today's Expected Arrivals and Check-In Queue -->
    <div class="col-12">
        <div class="card card-hms p-0 bg-white overflow-hidden">
            <div class="card-header bg-light py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold text-navy mb-0 d-flex align-items-center gap-2">
                    <i data-lucide="calendar-check" class="text-green" style="width: 20px; height: 20px;"></i>
                    مواعيد اليوم المتوقعة وتسجيل الوصول (Today: <?= date('Y-m-d') ?>)
                </h5>
                <span class="badge bg-navy fs-6"><?= count($todayAppointments) ?> موعد</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>رقم الدور</th>
                            <th>المريض</th>
                            <th>الرقم الطبي (MRN)</th>
                            <th>العيادة</th>
                            <th>الطبيب</th>
                            <th>الوقت</th>
                            <th>الحالة</th>
                            <th class="text-center">تسجيل الوصول</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($todayAppointments)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    لا توجد مواعيد مجدولة لليوم.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($todayAppointments as $app): ?>
                                <tr>
                                    <td><span class="badge bg-navy fs-6 px-3 py-2">#<?= $app['token_number'] ?></span></td>
                                    <td class="fw-bold text-navy">
                                        <a href="<?= url("/patients/{$app['patient_id']}") ?>" class="text-navy text-decoration-none">
                                            <?= e($app['patient_name']) ?>
                                        </a>
                                        <small class="d-block text-muted"><?= e($app['phone_primary']) ?></small>
                                    </td>
                                    <td><code><?= e($app['mrn']) ?></code></td>
                                    <td><?= e($app['clinic_name']) ?> (غرفة <?= e($app['room_number']) ?>)</td>
                                    <td>د. <?= e($app['doctor_name']) ?></td>
                                    <td><?= e($app['appointment_time']) ?></td>
                                    <td>
                                        <?php if ($app['status'] === 'checked_in'): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">تم الوصول (Checked In)</span>
                                        <?php elseif ($app['status'] === 'booked'): ?>
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">في انتظار الحضور</span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-dark border"><?= e($app['status']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($app['status'] === 'booked'): ?>
                                            <form action="<?= url("/appointments/{$app['id']}/check-in") ?>" method="POST" style="display:inline;">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-sm btn-success d-inline-flex align-items-center gap-1">
                                                    <i data-lucide="check-circle" style="width: 14px; height: 14px;"></i>
                                                    تسجيل وصول
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-muted small">مكتمل الوصول</span>
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
