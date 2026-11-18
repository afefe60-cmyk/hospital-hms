<div class="row g-4">
    <!-- Header Banner -->
    <div class="col-12">
        <div class="card card-hms p-4 bg-white shadow-sm">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <?php
                            $triageBadge = match($case['triage_level']) {
                                'red' => 'bg-danger text-white',
                                'orange' => 'bg-warning text-dark',
                                'yellow' => 'bg-info text-dark',
                                'green' => 'bg-success text-white',
                                default => 'bg-secondary text-white'
                            };
                        ?>
                        <span class="badge <?= $triageBadge ?> px-3 py-2 fs-6 text-uppercase fw-bold">
                            <?= strtoupper($case['triage_level']) ?> TRIAGE
                        </span>
                        <h3 class="fw-bold text-navy mb-0">
                            <?= e($case['registered_patient_name'] ?? $case['temporary_patient_name']) ?>
                        </h3>
                        <span class="badge bg-navy font-monospace fs-6 px-3 py-1"><?= e($case['case_number']) ?></span>
                    </div>
                    <div class="text-muted mt-2">
                        <?php if ($case['patient_id']): ?>
                            <span class="me-3">MRN: <?= e($case['mrn']) ?></span>
                            <span class="me-3">العمر: <?= $case['patient_age'] ?> سنة</span>
                            <span class="me-3">النوع: <?= $case['gender'] === 'male' ? 'ذكر' : 'أنثى' ?></span>
                        <?php else: ?>
                            <span class="badge bg-secondary me-3">حالة غير مسجلة مسبقاً</span>
                        <?php endif; ?>
                        <span class="me-3">وقت الوصول: <?= date('H:i - Y/m/d', strtotime($case['arrival_time'])) ?></span>
                        <span>الطبيب المسؤول: د. <?= e($case['doctor_name'] ?? 'لم يحدد') ?></span>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <?php if ($case['status'] !== 'admitted' && $case['patient_id']): ?>
                        <a href="<?= url("/admissions/create?patient_id={$case['patient_id']}&er_case_id={$case['id']}") ?>" class="btn btn-primary d-inline-flex align-items-center gap-2 fw-bold">
                            <i data-lucide="bed" style="width: 18px; height: 18px;"></i>
                            قرار تنويم المريض (Admit to IPD)
                        </a>
                    <?php endif; ?>

                    <button type="button" class="btn btn-outline-navy fw-bold" data-bs-toggle="modal" data-bs-target="#updateStatusModal">
                        تحديث حالة البلاغ
                    </button>
                    <a href="<?= url('/emergency') ?>" class="btn btn-outline-secondary">رجوع</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Triage Assessment Details -->
    <div class="col-lg-6">
        <div class="card card-hms p-4 bg-white shadow-sm h-100">
            <h5 class="fw-bold text-navy mb-3 d-flex align-items-center gap-2">
                <i data-lucide="activity" class="text-danger" style="width: 20px; height: 20px;"></i>
                التقييم السريري الأولي (ABC & Vital Signs)
            </h5>

            <?php if ($triage): ?>
                <div class="row g-3">
                    <div class="col-6">
                        <div class="p-3 bg-light rounded border">
                            <small class="text-muted d-block">مجرى الهواء (Airway)</small>
                            <span class="fw-bold fs-6"><?= e($triage['airway_status']) ?></span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 bg-light rounded border">
                            <small class="text-muted d-block">التنفس (Breathing)</small>
                            <span class="fw-bold fs-6"><?= e($triage['breathing_status']) ?></span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 bg-light rounded border">
                            <small class="text-muted d-block">الدورة الدموية (Circulation)</small>
                            <span class="fw-bold fs-6"><?= e($triage['circulation_status']) ?></span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 bg-light rounded border">
                            <small class="text-muted d-block">مقياس غلاسكو للوعي (GCS)</small>
                            <span class="fw-bold fs-6 text-primary"><?= $triage['gcs_score'] ?? 'غير محدد' ?> / 15</span>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 bg-light rounded border text-center">
                            <small class="text-muted d-block">ضغط الدم</small>
                            <span class="fw-bold"><?= $triage['systolic_bp'] ?? '-' ?>/<?= $triage['diastolic_bp'] ?? '-' ?></span>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 bg-light rounded border text-center">
                            <small class="text-muted d-block">النبض</small>
                            <span class="fw-bold"><?= $triage['pulse_rate'] ?? '-' ?> bpm</span>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 bg-light rounded border text-center">
                            <small class="text-muted d-block">تشبع الأكسجين</small>
                            <span class="fw-bold"><?= $triage['oxygen_saturation'] ?? '-' ?>%</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 bg-light rounded border text-center">
                            <small class="text-muted d-block">الحرارة</small>
                            <span class="fw-bold"><?= $triage['temperature'] ?? '-' ?> °C</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 bg-light rounded border text-center">
                            <small class="text-muted d-block">مقياس الألم</small>
                            <span class="fw-bold text-danger"><?= $triage['pain_score'] ?? '-' ?> / 10</span>
                        </div>
                    </div>
                    <div class="col-12 mt-3">
                        <div class="p-3 bg-light rounded border">
                            <small class="text-muted d-block mb-1">ملاحظات التمريض:</small>
                            <p class="mb-0 text-dark"><?= e($triage['notes'] ?? 'لا توجد ملاحظات إضافية') ?></p>
                            <small class="text-muted mt-2 d-block">الممرض المسؤول: <?= e($triage['nurse_name']) ?> (<?= date('H:i d/m', strtotime($triage['assessed_at'])) ?>)</small>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="text-center py-4 text-muted">
                    <p>لم يتم تسجيل تقييم فرز سريري مفصل لهذه الحالة بعد.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Case Details & Chief Complaint -->
    <div class="col-lg-6">
        <div class="card card-hms p-4 bg-white shadow-sm h-100">
            <h5 class="fw-bold text-navy mb-3 d-flex align-items-center gap-2">
                <i data-lucide="file-text" class="text-primary" style="width: 20px; height: 20px;"></i>
                الشكوى الطبية ونتائج الطوارئ
            </h5>

            <div class="mb-3">
                <label class="fw-bold text-muted small">الشكوى الرئيسية وسبب الحضور:</label>
                <div class="p-3 bg-light rounded border mt-1 fs-6">
                    <?= nl2br(e($case['chief_complaint'])) ?>
                </div>
            </div>

            <div class="mb-3">
                <label class="fw-bold text-muted small">الحالة الحالية:</label>
                <div>
                    <span class="badge bg-navy fs-6 px-3 py-2"><?= e($case['status']) ?></span>
                </div>
            </div>

            <?php if (!empty($case['outcome_notes'])): ?>
                <div class="mb-3">
                    <label class="fw-bold text-muted small">التقرير الختامي ومخرجات الطوارئ (Outcome Notes):</label>
                    <div class="p-3 bg-light rounded border mt-1">
                        <?= nl2br(e($case['outcome_notes'])) ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Update Status -->
<div class="modal fade" id="updateStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-navy text-white">
                <h5 class="modal-title fw-bold">تحديث حالة بلاغ الطوارئ</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= url("/emergency/{$case['id']}/status") ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">الحالة الجديدة:</label>
                        <select name="status" class="form-select" required>
                            <option value="triaged" <?= ($case['status'] === 'triaged') ? 'selected' : '' ?>>تم الفرز (Triaged)</option>
                            <option value="under_treatment" <?= ($case['status'] === 'under_treatment') ? 'selected' : '' ?>>تحت العلاج والمراقبة (Under Treatment)</option>
                            <option value="admitted" <?= ($case['status'] === 'admitted') ? 'selected' : '' ?>>تم التنويم بالأقسام (Admitted)</option>
                            <option value="discharged" <?= ($case['status'] === 'discharged') ? 'selected' : '' ?>>خروج وتحسن (Discharged)</option>
                            <option value="transferred" <?= ($case['status'] === 'transferred') ? 'selected' : '' ?>>تحويل لمستشفى آخر (Transferred)</option>
                            <option value="deceased" <?= ($case['status'] === 'deceased') ? 'selected' : '' ?>>وفاة (Deceased)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">الملاحظات الطبية ونتائج المتابعة:</label>
                        <textarea name="outcome_notes" class="form-control" rows="3"><?= e($case['outcome_notes'] ?? '') ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary fw-bold">حفظ التحديث</button>
                </div>
            </form>
        </div>
    </div>
</div>
