<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-navy mb-1 d-flex align-items-center gap-2">
                    <i data-lucide="calendar-plus" class="text-green" style="width: 28px; height: 28px;"></i>
                    حجز موعد عيادة خارجية (Book Appointment)
                </h3>
                <p class="text-muted mb-0">تحديد العيادة والطبيب وموعد الكشف وتوليد رقم التذكرة</p>
            </div>
            <a href="<?= url('/appointments') ?>" class="btn btn-outline-secondary btn-sm">
                <i data-lucide="arrow-right" style="width: 16px; height: 16px; display:inline-block; vertical-align:text-bottom;"></i>
                العودة للمواعيد
            </a>
        </div>

        <div class="card card-hms p-4 bg-white">
            <form action="<?= url('/appointments') ?>" method="POST">
                <?= csrf_field() ?>

                <!-- Patient Selection -->
                <div class="mb-4">
                    <label class="form-label fw-semibold">المريض <span class="text-danger">*</span></label>
                    <?php if ($selectedPatient): ?>
                        <div class="p-3 border rounded bg-light d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="fw-bold text-navy mb-0"><?= e($selectedPatient['first_name'] . ' ' . $selectedPatient['last_name']) ?></h5>
                                <span class="badge bg-navy font-monospace">MRN: <?= e($selectedPatient['mrn']) ?></span>
                                <span class="text-muted small ms-2"><?= e($selectedPatient['phone_primary']) ?></span>
                            </div>
                            <input type="hidden" name="patient_id" value="<?= $selectedPatient['id'] ?>">
                            <a href="<?= url('/appointments/create') ?>" class="btn btn-sm btn-outline-secondary">تغيير المريض</a>
                        </div>
                    <?php else: ?>
                        <div class="input-group">
                            <input type="number" name="patient_id" class="form-control" placeholder="أدخل معرف المريض (Patient ID) أو ابحث في دليل المرضى" required>
                            <a href="<?= url('/patients') ?>" class="btn btn-outline-primary" target="_blank">
                                <i data-lucide="search" style="width: 16px; height: 16px; display:inline-block; vertical-align:text-bottom;"></i>
                                دليل المرضى
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Clinic & Doctor Selection -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">العيادة الخارجية <span class="text-danger">*</span></label>
                        <select name="clinic_id" class="form-select" required>
                            <option value="">-- اختر العيادة --</option>
                            <?php foreach ($clinics as $c): ?>
                                <option value="<?= $c['id'] ?>">
                                    <?= e($c['name_ar']) ?> (غرفة <?= e($c['room_number']) ?>) - <?= e($c['department_name_ar']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">الطبيب المعالج <span class="text-danger">*</span></label>
                        <select name="doctor_id" class="form-select" required>
                            <option value="">-- اختر الطبيب الاستشاري --</option>
                            <?php foreach ($doctors as $doc): ?>
                                <option value="<?= $doc['id'] ?>">
                                    د. <?= e($doc['full_name']) ?> (<?= e($doc['specialty_name_ar']) ?>) - كشف: <?= format_currency($doc['consultation_fee']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Appointment Date & Time -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">تاريخ الموعد <span class="text-danger">*</span></label>
                        <input type="date" name="appointment_date" class="form-control" value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">الوقت المقترح <span class="text-danger">*</span></label>
                        <input type="time" name="appointment_time" class="form-control" value="09:00" required>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="<?= url('/appointments') ?>" class="btn btn-outline-secondary">إلغاء</a>
                    <button type="submit" class="btn btn-hms-primary px-4">
                        <i data-lucide="check" style="width: 18px; height: 18px; display:inline-block; vertical-align:text-bottom;"></i>
                        تأكيد وحجز الموعد
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
