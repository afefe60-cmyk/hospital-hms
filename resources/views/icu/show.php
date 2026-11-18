<?php
$layout = 'layouts/main';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <div class="d-flex align-items-center gap-3">
            <h3 class="h4 mb-0 fw-bold font-monospace text-danger"><?= htmlspecialchars($stay['stay_number']) ?></h3>
            <?php
                $statusBadge = match($stay['status']) {
                    'admitted' => 'bg-danger animate-pulse',
                    'transferred' => 'bg-warning text-dark',
                    'discharged' => 'bg-success',
                    'deceased' => 'bg-dark',
                    default => 'bg-secondary'
                };
                $statusText = match($stay['status']) {
                    'admitted' => 'منوم بالعناية المركزة',
                    'transferred' => 'تم النقل لقسم آخر',
                    'discharged' => 'خرج بتحسن',
                    'deceased' => 'وفاة',
                    default => $stay['status']
                };
            ?>
            <span class="badge <?= $statusBadge ?> fs-6"><?= $statusText ?></span>
            <span class="badge <?= $stay['ventilator_status'] === 'invasive' ? 'bg-danger' : ($stay['ventilator_status'] === 'non_invasive' ? 'bg-warning text-dark' : 'bg-secondary') ?> fs-6">
                <?= $stay['ventilator_status'] === 'invasive' ? 'جهاز تنفس اصطناعي (Invasive)' : ($stay['ventilator_status'] === 'non_invasive' ? 'تنفس غير غازي (NIV)' : 'بدون جهاز تنفس') ?>
            </span>
        </div>
        <p class="text-muted mb-0 mt-1">سرير العناية: <strong><?= htmlspecialchars($stay['bed_number']) ?></strong> | الغرفة: <strong><?= htmlspecialchars($stay['room_number']) ?></strong> (<?= htmlspecialchars($stay['ward_name_ar']) ?>)</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#recordObsModal" <?= $stay['status'] !== 'admitted' ? 'disabled' : '' ?>>
            <i class="bi bi-plus-circle me-1"></i>تدوين مؤشرات حيوية وملاحظة
        </button>
        <?php if ($stay['status'] === 'admitted'): ?>
            <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#dischargeModal">
                <i class="bi bi-box-arrow-left me-1"></i>تخريج / نقل المريض
            </button>
        <?php endif; ?>
        <a href="/icu/print?id=<?= $stay['id'] ?>" target="_blank" class="btn btn-outline-secondary">
            <i class="bi bi-printer me-1"></i>طباعة الفلوشيت
        </a>
        <a href="/icu" class="btn btn-outline-primary">
            <i class="bi bi-arrow-right me-1"></i>لوحة العناية
        </a>
    </div>
</div>

<!-- Patient Demographics Banner -->
<div class="card border-0 shadow-sm rounded-3 mb-4 bg-light">
    <div class="card-body p-3">
        <div class="row g-3 align-items-center">
            <div class="col-md-3">
                <span class="text-muted d-block small">اسم المريض</span>
                <span class="fw-bold text-dark fs-6"><?= htmlspecialchars($stay['patient_name']) ?></span>
            </div>
            <div class="col-md-2">
                <span class="text-muted d-block small">الرقم الطبي (MRN)</span>
                <span class="fw-bold font-monospace"><?= htmlspecialchars($stay['mrn']) ?></span>
            </div>
            <div class="col-md-2">
                <span class="text-muted d-block small">العمر / النوع</span>
                <span class="fw-bold"><?= $stay['patient_age'] ?> سنة (<?= $stay['patient_gender'] === 'male' ? 'ذكر' : 'أنثى' ?>)</span>
            </div>
            <div class="col-md-2">
                <span class="text-muted d-block small">فصيلة الدم</span>
                <span class="badge bg-danger fs-6"><?= htmlspecialchars($stay['blood_group'] ?? 'غير محدد') ?></span>
            </div>
            <div class="col-md-3">
                <span class="text-muted d-block small">طبيب العناية المسؤول</span>
                <span class="fw-bold text-primary">د. <?= htmlspecialchars($stay['doctor_name'] ?? 'طبيب العناية المناوب') ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Live ICU Monitor Strip (Latest Parameters) -->
<?php if ($latest): ?>
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-3 bg-white text-center py-2">
            <small class="text-muted d-block">ضغط الدم (BP)</small>
            <h4 class="fw-bold mb-0 text-danger"><?= $latest['systolic_bp'] ?>/<?= $latest['diastolic_bp'] ?></h4>
            <span class="text-muted small">mmHg</span>
        </div>
    </div>
    <div class="col-sm-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-3 bg-white text-center py-2">
            <small class="text-muted d-block">النبض (Pulse)</small>
            <h4 class="fw-bold mb-0 text-primary"><?= $latest['pulse_rate'] ?></h4>
            <span class="text-muted small">bpm</span>
        </div>
    </div>
    <div class="col-sm-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-3 bg-white text-center py-2">
            <small class="text-muted d-block">تشبع الأكسجين (SpO2)</small>
            <h4 class="fw-bold mb-0 <?= $latest['oxygen_saturation'] < 92 ? 'text-danger' : 'text-success' ?>"><?= $latest['oxygen_saturation'] ?>%</h4>
            <span class="text-muted small">O2 Sat</span>
        </div>
    </div>
    <div class="col-sm-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-3 bg-white text-center py-2">
            <small class="text-muted d-block">مقياس كوما (GCS)</small>
            <h4 class="fw-bold mb-0 <?= $latest['gcs_score'] < 9 ? 'text-danger' : 'text-dark' ?>"><?= $latest['gcs_score'] ?>/15</h4>
            <span class="text-muted small">Glasgow Coma</span>
        </div>
    </div>
    <div class="col-sm-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-3 bg-white text-center py-2">
            <small class="text-muted d-block">جهاز التنفس (Vent)</small>
            <h5 class="fw-bold mb-0 text-info mt-1"><?= htmlspecialchars($latest['ventilator_mode'] ?? 'Off') ?></h5>
            <span class="text-muted small">FiO2: <?= $latest['fio2'] ? $latest['fio2'].'%' : '-' ?></span>
        </div>
    </div>
    <div class="col-sm-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-3 bg-white text-center py-2">
            <small class="text-muted d-block">ميزان السوائل (Net)</small>
            <h5 class="fw-bold mb-0 <?= $fluidBalance['net'] >= 0 ? 'text-primary' : 'text-warning' ?> mt-1">
                <?= ($fluidBalance['net'] > 0 ? '+' : '') . number_format($fluidBalance['net']) ?>
            </h5>
            <span class="text-muted small">ml (صافي)</span>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Admission Summary Card -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-header bg-white py-3 border-light">
        <h5 class="card-title mb-0 fw-bold"><i class="bi bi-file-earmark-medical text-danger me-2"></i>سبب الدخول والحالة الإكلينيكية</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-9">
                <span class="text-muted small d-block mb-1">سبب التنويم بالعناية المركزة (Admission Reason):</span>
                <div class="p-3 bg-light rounded border text-dark"><?= nl2br(htmlspecialchars($stay['admission_reason'])) ?></div>
            </div>
            <div class="col-md-3">
                <div class="p-3 bg-light rounded border h-100">
                    <span class="text-muted small d-block">تاريخ الدخول:</span>
                    <strong><?= date('Y-m-d h:i A', strtotime($stay['admitted_at'])) ?></strong>
                    <hr class="my-2">
                    <span class="text-muted small d-block">مقياس الخطورة (APACHE II):</span>
                    <strong class="text-danger fs-5"><?= $stay['apache_score'] !== null ? $stay['apache_score'] : '-' ?></strong>
                    <?php if (!empty($stay['discharge_summary'])): ?>
                        <hr class="my-2">
                        <span class="text-muted small d-block">ملخص الخروج:</span>
                        <small class="text-dark"><?= htmlspecialchars($stay['discharge_summary']) ?></small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Observations Flowsheet Table -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-header bg-white py-3 border-light d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0 fw-bold"><i class="bi bi-clock-history text-primary me-2"></i>مخطط الملاحظات والمؤشرات الحيوية الدورية (Flowsheet Chart)</h5>
        <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#recordObsModal" <?= $stay['status'] !== 'admitted' ? 'disabled' : '' ?>>
            <i class="bi bi-plus-lg me-1"></i>تسجيل ملاحظة جديدة
        </button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-center small">
                <thead class="table-light">
                    <tr>
                        <th class="text-start">التوقيت</th>
                        <th>BP (mmHg)</th>
                        <th>HR (bpm)</th>
                        <th>RR</th>
                        <th>Temp (°C)</th>
                        <th>SpO2</th>
                        <th>GCS</th>
                        <th>بؤبؤ العين</th>
                        <th>وضع التنفس</th>
                        <th>FiO2 / PEEP</th>
                        <th>سوائل IV (ml)</th>
                        <th>بول (ml)</th>
                        <th>غازات الدم (ABG) وملاحظات</th>
                        <th>المسجل</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($observations)): ?>
                        <tr>
                            <td colspan="14" class="text-center py-4 text-muted">
                                لم يتم تسجيل أية ملاحظات دورية لهذا المريض حتى الآن.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($observations as $obs): ?>
                            <tr>
                                <td class="text-start fw-bold font-monospace">
                                    <?= date('m-d h:i A', strtotime($obs['recorded_at'])) ?>
                                </td>
                                <td><strong class="text-danger"><?= $obs['systolic_bp'] ?>/<?= $obs['diastolic_bp'] ?></strong></td>
                                <td><?= $obs['pulse_rate'] ?></td>
                                <td><?= $obs['respiratory_rate'] ?></td>
                                <td><?= $obs['temperature'] ?>°</td>
                                <td><span class="badge <?= $obs['oxygen_saturation'] < 92 ? 'bg-danger' : 'bg-success' ?>"><?= $obs['oxygen_saturation'] ?>%</span></td>
                                <td><strong><?= $obs['gcs_score'] ?></strong></td>
                                <td><?= htmlspecialchars($obs['pupil_reaction']) ?></td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($obs['ventilator_mode'] ?? '-') ?></span></td>
                                <td>
                                    <?php if ($obs['fio2'] || $obs['peep']): ?>
                                        <?= $obs['fio2'] ? $obs['fio2'].'%' : '-' ?> / <?= $obs['peep'] ? $obs['peep'] : '-' ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td class="text-primary font-monospace"><?= $obs['iv_fluids_ml'] ? '+' . $obs['iv_fluids_ml'] : '-' ?></td>
                                <td class="text-warning font-monospace"><?= $obs['urine_output_ml'] ? '-' . $obs['urine_output_ml'] : '-' ?></td>
                                <td class="text-start" style="max-width: 250px;">
                                    <?php if (!empty($obs['arterial_blood_gas'])): ?>
                                        <div class="fw-bold text-primary small mb-1"><?= htmlspecialchars($obs['arterial_blood_gas']) ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($obs['clinical_notes'])): ?>
                                        <div class="text-muted small"><?= htmlspecialchars($obs['clinical_notes']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><small class="text-muted"><?= htmlspecialchars($obs['recorder_name'] ?? 'طاقم التمريض') ?></small></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Record Observation -->
<div class="modal fade" id="recordObsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="/icu/record-observation">
                <?= csrf_field() ?>
                <input type="hidden" name="icu_stay_id" value="<?= $stay['id'] ?>">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-danger"><i class="bi bi-clipboard2-pulse me-2"></i>تدوين مؤشرات حيوية وملاحظة دورية بالعناية</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h6 class="fw-bold text-primary border-bottom pb-1 mb-3">1. العلامات الحيوية (Vital Signs)</h6>
                    <div class="row g-2 mb-3">
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">الضغط الانقباضي</label>
                            <input type="number" name="systolic_bp" class="form-control form-control-sm" placeholder="120">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">الضغط الانبساطي</label>
                            <input type="number" name="diastolic_bp" class="form-control form-control-sm" placeholder="80">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">النبض (Pulse)</label>
                            <input type="number" name="pulse_rate" class="form-control form-control-sm" placeholder="80">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">معدل التنفس</label>
                            <input type="number" name="respiratory_rate" class="form-control form-control-sm" placeholder="18">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">الحرارة (°C)</label>
                            <input type="number" step="0.1" name="temperature" class="form-control form-control-sm" placeholder="37.0">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">الأكسجين (SpO2 %)</label>
                            <input type="number" step="0.1" name="oxygen_saturation" class="form-control form-control-sm" placeholder="98">
                        </div>
                    </div>

                    <h6 class="fw-bold text-primary border-bottom pb-1 mb-3">2. التقييم العصبي والتنفس الصناعي (Neuro & Ventilator)</h6>
                    <div class="row g-2 mb-3">
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">مقياس كوما (GCS 3-15)</label>
                            <input type="number" name="gcs_score" class="form-control form-control-sm" min="3" max="15" placeholder="15">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">تفاعل البؤبؤ (Pupils)</label>
                            <select name="pupil_reaction" class="form-select form-select-sm">
                                <option value="brisk">سريع التفاعل (Brisk)</option>
                                <option value="sluggish">بطيء التفاعل (Sluggish)</option>
                                <option value="fixed">ثابت متوسع (Fixed)</option>
                                <option value="unequal">غير متناظر (Unequal)</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">نمط التنفس الصناعي</label>
                            <input type="text" name="ventilator_mode" class="form-control form-control-sm" placeholder="SIMV/CPAP/Off">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">FiO2 %</label>
                            <input type="number" name="fio2" class="form-control form-control-sm" placeholder="40" min="21" max="100">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold">PEEP (cmH2O)</label>
                            <input type="number" name="peep" class="form-control form-control-sm" placeholder="5">
                        </div>
                    </div>

                    <h6 class="fw-bold text-primary border-bottom pb-1 mb-3">3. ميزان السوائل والتحاليل (Fluid Balance & Notes)</h6>
                    <div class="row g-2 mb-3">
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">سوائل IV مدخلة (ml)</label>
                            <input type="number" name="iv_fluids_ml" class="form-control form-control-sm" placeholder="250">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">إدرار البول (ml)</label>
                            <input type="number" name="urine_output_ml" class="form-control form-control-sm" placeholder="60">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">غازات الدم الشرياني (ABG)</label>
                            <input type="text" name="arterial_blood_gas" class="form-control form-control-sm" placeholder="pH: 7.38, PaCO2: 40, PaO2: 95, HCO3: 24">
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-bold">ملاحظات التمريض والحالة السريرية</label>
                        <textarea name="clinical_notes" class="form-control form-control-sm" rows="2" placeholder="أية ملاحظات سريرية، أدوية رافعة للضغط، استجابة المريض..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-danger fw-bold">حفظ الملاحظة الآن</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Discharge / Transfer Patient -->
<div class="modal fade" id="dischargeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="/icu/update-status">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= $stay['id'] ?>">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-warning"><i class="bi bi-box-arrow-left me-2"></i>إنهاء الإقامة بالعناية المركزة</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">حالة الخروج / الوجهة <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            <option value="transferred">تحويل لعنبر التنويم الداخلي (Transferred to Inpatient Ward)</option>
                            <option value="discharged">خروج مباشر من المستشفى بتحسن (Discharged Home)</option>
                            <option value="deceased">حالة وفاة (Deceased)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">ملخص تقرير الخروج من العناية</label>
                        <textarea name="discharge_summary" class="form-control" rows="4" placeholder="ملخص استقرار الحالة والمؤشرات والتوصيات الدوائية..."></textarea>
                    </div>
                    <div class="alert alert-warning py-2 small mb-0">
                        <i class="bi bi-exclamation-triangle me-1"></i>سيتم تحويل حالة سرير العناية تلقائياً إلى "تعقيم وتجهيز" لاستقبال حالة جديدة.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-warning fw-bold">تأكيد إنهاء الإقامة</button>
                </div>
            </form>
        </div>
    </div>
</div>
