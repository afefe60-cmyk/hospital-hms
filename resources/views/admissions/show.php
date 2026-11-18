<div class="row g-4">
    <!-- Header Patient Banner -->
    <div class="col-12">
        <div class="card card-hms p-4 bg-white shadow-sm">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <h3 class="fw-bold text-navy mb-0"><?= e($admission['patient_name']) ?></h3>
                        <span class="badge bg-navy font-monospace fs-6 px-3 py-1"><?= e($admission['admission_number']) ?></span>
                        <span class="badge bg-secondary font-monospace fs-6 px-3 py-1">MRN: <?= e($admission['mrn']) ?></span>
                        <?php
                            $stClass = match($admission['status']) {
                                'admitted' => 'bg-success',
                                'discharged' => 'bg-secondary',
                                default => 'bg-info'
                            };
                        ?>
                        <span class="badge <?= $stClass ?> fs-6"><?= e($admission['status']) ?></span>
                    </div>
                    <div class="text-muted mt-2">
                        <span class="me-3">العمر: <?= $admission['patient_age'] ?> سنة</span>
                        <span class="me-3">النوع: <?= $admission['gender'] === 'male' ? 'ذكر' : 'أنثى' ?></span>
                        <span class="me-3">فصيلة الدم: <span class="badge bg-danger"><?= e($admission['blood_group']) ?></span></span>
                        <span class="me-3">القسم: <strong><?= e($admission['department_name']) ?></strong></span>
                        <span>الطبيب المشرف: د. <?= e($admission['doctor_name']) ?> (<?= e($admission['doctor_specialty']) ?>)</span>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <?php if ($admission['status'] === 'admitted'): ?>
                        <button type="button" class="btn btn-outline-primary fw-bold d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#transferBedModal">
                            <i data-lucide="arrow-left-right" style="width: 18px; height: 18px;"></i>
                            تحويل السرير
                        </button>

                        <button type="button" class="btn btn-success fw-bold d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#dischargeModal">
                            <i data-lucide="log-out" style="width: 18px; height: 18px;"></i>
                            إجراء خروج المريض (Discharge)
                        </button>
                    <?php endif; ?>

                    <a href="<?= url('/admissions') ?>" class="btn btn-outline-secondary">رجوع</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Bed & Admission Info -->
    <div class="col-lg-4">
        <div class="card card-hms p-4 bg-white shadow-sm mb-4">
            <h5 class="fw-bold text-navy mb-3 d-flex align-items-center gap-2">
                <i data-lucide="bed" class="text-primary" style="width: 20px; height: 20px;"></i>
                بيانات إشغال السرير الحالي
            </h5>

            <?php if ($activeBed): ?>
                <div class="p-3 bg-light rounded border mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small">رقم السرير</span>
                        <span class="badge bg-primary fs-6 px-3 py-1"><?= e($activeBed['bed_number']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small">الغرفة والجناح</span>
                        <span class="fw-bold text-navy">غرفة <?= e($activeBed['room_number']) ?> (<?= e($activeBed['ward_name']) ?>)</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small">نوع الغرفة</span>
                        <span class="badge bg-secondary"><?= e($activeBed['room_type']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">تكلفة السرير اليومية</span>
                        <span class="fw-bold text-success"><?= number_format($activeBed['daily_rate'], 2) ?> SDG</span>
                    </div>
                </div>
                <small class="text-muted d-block">
                    تاريخ التسكين: <?= date('d/m/Y H:i', strtotime($activeBed['assigned_from'])) ?>
                </small>
            <?php else: ?>
                <div class="alert alert-secondary py-3 text-center mb-0">
                    تم إخلاء السرير أو لم يتم التسكين بعد.
                </div>
            <?php endif; ?>
        </div>

        <div class="card card-hms p-4 bg-white shadow-sm">
            <h5 class="fw-bold text-navy mb-3 d-flex align-items-center gap-2">
                <i data-lucide="file-text" class="text-secondary" style="width: 20px; height: 20px;"></i>
                تفاصيل التنويم والتشخيص
            </h5>
            <div class="mb-3">
                <small class="text-muted d-block fw-bold">سبب التنويم:</small>
                <div class="p-2 bg-light rounded border mt-1 small">
                    <?= nl2br(e($admission['admission_reason'])) ?>
                </div>
            </div>
            <div>
                <small class="text-muted d-block fw-bold">التشخيص المبدئي:</small>
                <div class="p-2 bg-light rounded border mt-1 small">
                    <?= nl2br(e($admission['provisional_diagnosis'])) ?>
                </div>
            </div>

            <?php if ($dischargeRecord): ?>
                <div class="mt-4 pt-3 border-top">
                    <h6 class="fw-bold text-success d-flex align-items-center gap-1">
                        <i data-lucide="check-circle" style="width: 18px; height: 18px;"></i>
                        بيانات الخروج المعتمد
                    </h6>
                    <small class="text-muted d-block">تاريخ الخروج: <?= date('d/m/Y H:i', strtotime($dischargeRecord['discharged_at'])) ?></small>
                    <small class="text-muted d-block">الحالة عند الخروج: <span class="badge bg-success"><?= e($dischargeRecord['discharge_condition']) ?></span></small>
                    <small class="text-muted d-block mt-2 fw-bold">ملخص الخروج:</small>
                    <p class="small bg-light p-2 rounded border mt-1 mb-0"><?= nl2br(e($dischargeRecord['discharge_summary'])) ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Main Clinical Inpatient Workspace (Care Plan, MAR, Nursing Notes) -->
    <div class="col-lg-8">
        <!-- MAR (Medication Administration Record) Section -->
        <div class="card card-hms p-4 bg-white shadow-sm mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-navy mb-0 d-flex align-items-center gap-2">
                    <i data-lucide="pill" class="text-danger" style="width: 20px; height: 20px;"></i>
                    سجل إعطاء الأدوية (MAR - Medication Administration Record)
                </h5>
                <?php if ($admission['status'] === 'admitted'): ?>
                    <button type="button" class="btn btn-sm btn-outline-danger fw-bold" data-bs-toggle="modal" data-bs-target="#addMarDoseModal">
                        + جدولة جرعة دواء جديدة
                    </button>
                <?php endif; ?>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>الدواء والجرعة</th>
                            <th>طريقة الإعطاء</th>
                            <th>الموعد المحدد</th>
                            <th>الحالة</th>
                            <th>المنفذ والوقت</th>
                            <?php if ($admission['status'] === 'admitted'): ?>
                                <th class="text-center">إجراء تمريضي</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($marList)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    لا توجد جرعات دوائية مجدولة حالياً لهذا المريض.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($marList as $mar): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-navy"><?= e($mar['medicine_name']) ?></div>
                                        <small class="text-muted">الجرعة: <?= e($mar['dosage']) ?></small>
                                    </td>
                                    <td><span class="badge bg-light text-dark border"><?= strtoupper(e($mar['route'])) ?></span></td>
                                    <td class="small"><?= date('H:i d/m', strtotime($mar['scheduled_time'])) ?></td>
                                    <td>
                                        <?php
                                            $marClass = match($mar['status']) {
                                                'given' => 'bg-success',
                                                'pending' => 'bg-warning text-dark',
                                                'missed' => 'bg-danger',
                                                'held' => 'bg-secondary',
                                                default => 'bg-secondary'
                                            };
                                        ?>
                                        <span class="badge <?= $marClass ?>"><?= e($mar['status']) ?></span>
                                    </td>
                                    <td class="small">
                                        <?php if ($mar['administered_by']): ?>
                                            <div><?= e($mar['nurse_name']) ?></div>
                                            <span class="text-muted"><?= date('H:i d/m', strtotime($mar['administered_time'])) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">قيد الانتظار</span>
                                        <?php endif; ?>
                                    </td>
                                    <?php if ($admission['status'] === 'admitted'): ?>
                                        <td class="text-center">
                                            <?php if ($mar['status'] === 'pending'): ?>
                                                <form action="<?= url("/nursing/mar/{$mar['id']}") ?>" method="POST" class="d-inline">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="status" value="given">
                                                    <button type="submit" class="btn btn-sm btn-success fw-bold px-3">
                                                        ✓ إعطاء
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="text-success small fw-bold">مكتمل</span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Nursing Care Plans Section -->
        <div class="card card-hms p-4 bg-white shadow-sm mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-navy mb-0 d-flex align-items-center gap-2">
                    <i data-lucide="clipboard-list" class="text-primary" style="width: 20px; height: 20px;"></i>
                    خطط الرعاية التمريضية (Nursing Care Plans)
                </h5>
                <?php if ($admission['status'] === 'admitted'): ?>
                    <button type="button" class="btn btn-sm btn-outline-primary fw-bold" data-bs-toggle="modal" data-bs-target="#addCarePlanModal">
                        + إضافة خطة رعاية
                    </button>
                <?php endif; ?>
            </div>

            <?php if (empty($carePlans)): ?>
                <div class="text-muted text-center py-3">لم يتم إنشاء خطط رعاية تمريضية بعد.</div>
            <?php else: ?>
                <?php foreach ($carePlans as $cp): ?>
                    <div class="p-3 bg-light rounded border mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold text-navy mb-0">تشخيص التمريض: <?= e($cp['nursing_diagnosis']) ?></h6>
                            <span class="badge bg-success"><?= e($cp['status']) ?></span>
                        </div>
                        <div class="small mb-1"><strong>الأهداف المرجوة:</strong> <?= e($cp['goals']) ?></div>
                        <div class="small mb-1"><strong>التدخلات التمريضية:</strong> <?= e($cp['interventions']) ?></div>
                        <?php if ($cp['evaluation']): ?>
                            <div class="small text-muted"><strong>التقييم:</strong> <?= e($cp['evaluation']) ?></div>
                        <?php endif; ?>
                        <div class="text-end text-muted" style="font-size: 11px;">
                            المسؤول: <?= e($cp['creator_name']) ?> | <?= date('d/m/Y H:i', strtotime($cp['created_at'])) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Nursing Notes Section -->
        <div class="card card-hms p-4 bg-white shadow-sm">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-navy mb-0 d-flex align-items-center gap-2">
                    <i data-lucide="message-square" class="text-success" style="width: 20px; height: 20px;"></i>
                    الملاحظات السريرية وتسليم الورديات (Nursing Notes)
                </h5>
                <?php if ($admission['status'] === 'admitted'): ?>
                    <button type="button" class="btn btn-sm btn-outline-success fw-bold" data-bs-toggle="modal" data-bs-target="#addNursingNoteModal">
                        + تدوين ملاحظة تمريضية
                    </button>
                <?php endif; ?>
            </div>

            <?php if (empty($notes)): ?>
                <div class="text-muted text-center py-3">لا توجد ملاحظات تمريضية مسجلة بعد.</div>
            <?php else: ?>
                <?php foreach ($notes as $n): ?>
                    <div class="p-3 bg-light rounded border mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="badge bg-secondary"><?= e($n['note_type']) ?></span>
                            <small class="text-muted"><?= date('H:i d/m/Y', strtotime($n['recorded_at'])) ?> - تمريض: <?= e($n['nurse_name']) ?></small>
                        </div>
                        <p class="mb-0 text-dark small"><?= nl2br(e($n['note_content'])) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal 1: Transfer Bed -->
<div class="modal fade" id="transferBedModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">تحويل المريض إلى سرير آخر</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= url("/admissions/{$admission['id']}/transfer-bed") ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">السرير الجديد الشاغر:</label>
                        <select name="new_bed_id" class="form-select" required>
                            <option value="">-- اختر سريراً متاحاً --</option>
                            <?php foreach ($availableBeds as $ab): ?>
                                <option value="<?= $ab['id'] ?>">
                                    سرير: <?= e($ab['bed_number']) ?> | غرفة: <?= e($ab['room_number']) ?> (<?= e($ab['ward_name']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">سبب التحويل:</label>
                        <input type="text" name="transfer_reason" class="form-control" placeholder="مثال: تحويل إلى العناية أو غرفة مفردة بطلب الطبيب">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary fw-bold">تأكيد تحويل السرير</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 2: Discharge Patient -->
<div class="modal fade" id="dischargeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold">إجراء خروج المريض وتفريغ السرير</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= url("/admissions/{$admission['id']}/discharge") ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">الطبيب المعتمد لقرار الخروج: *</label>
                        <select name="discharge_doctor_id" class="form-select" required>
                            <option value="">-- اختر الطبيب --</option>
                            <?php foreach ($doctors as $d): ?>
                                <option value="<?= $d['id'] ?>">د. <?= e($d['employee_name'] ?? 'طبيب') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">نوع الخروج:</label>
                        <select name="discharge_type" class="form-select">
                            <option value="normal">خروج طبيعي مكتمل العلاج (Normal)</option>
                            <option value="referral">تحويل لمستشفى آخر (Referral)</option>
                            <option value="against_medical_advice">خروج على مسؤولية المريض (DAMA)</option>
                            <option value="deceased">وفاة (Deceased)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">الحالة الصحية عند الخروج:</label>
                        <select name="discharge_condition" class="form-select">
                            <option value="recovered">شفاء تام (Recovered)</option>
                            <option value="improved" selected>تحسن ملحوظ (Improved)</option>
                            <option value="unchanged">مستقرة دون تغيير (Unchanged)</option>
                            <option value="worse">تدهور (Worse)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">ملخص تقرير الخروج والتوصيات العلاجية: *</label>
                        <textarea name="discharge_summary" class="form-control" rows="3" placeholder="ملخص الحالة، مدة التنويم، والتوصيات الدوائية للمنزل..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-success fw-bold">اعتماد الخروج وتفريغ السرير</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 3: Add MAR Dose -->
<div class="modal fade" id="addMarDoseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold">جدولة جرعة دوائية جديدة (MAR)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= url("/nursing/{$admission['id']}/mar-dose") ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">اسم الصنف الدوائي: *</label>
                        <input type="text" name="medicine_name" class="form-control" placeholder="مثال: Ceftriaxone 1g IV" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">الجرعة المقررة: *</label>
                        <input type="text" name="dosage" class="form-control" placeholder="مثال: 1 جم وريدياً" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold">طريقة الإعطاء:</label>
                            <select name="route" class="form-select">
                                <option value="iv">وريدي (IV)</option>
                                <option value="im">عضلي (IM)</option>
                                <option value="oral">عن طريق الفم (Oral)</option>
                                <option value="sc">تحت الجلد (SC)</option>
                                <option value="inhalation">استنشاق (Inhalation)</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold">الوقت المحدد للجرعة:</label>
                            <input type="datetime-local" name="scheduled_time" class="form-control" value="<?= date('Y-m-d\TH:i') ?>">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-danger fw-bold">إضافة لجدول الأدوية</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 4: Add Care Plan -->
<div class="modal fade" id="addCarePlanModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">إضافة خطة رعاية تمريضية</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= url("/nursing/{$admission['id']}/care-plan") ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">تشخيص التمريض (Nursing Diagnosis): *</label>
                        <input type="text" name="nursing_diagnosis" class="form-control" required placeholder="مثال: ألم حاد مرتبط بالعملية الجراحية">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">الأهداف المرجوة (Goals): *</label>
                        <textarea name="goals" class="form-control" rows="2" required placeholder="مثال: تسكين الألم والوصول لمستوى أقل من 3/10"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">التدخلات التمريضية (Interventions): *</label>
                        <textarea name="interventions" class="form-control" rows="2" required placeholder="مثال: إعطاء المسكنات في مواعيدها ومراقبة العلامات الحيوية"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary fw-bold">حفظ الخطة</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 5: Add Nursing Note -->
<div class="modal fade" id="addNursingNoteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold">تدوين ملاحظة تمريضية</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= url("/nursing/{$admission['id']}/note") ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">نوع الملاحظة:</label>
                        <select name="note_type" class="form-select">
                            <option value="routine">ملاحظة روتينية (Routine)</option>
                            <option value="shift_handover">تسليم وردية تمريض (Shift Handover)</option>
                            <option value="incident">طارئ أو حادثة سريرية (Incident)</option>
                            <option value="doctor_order_execution">تنفيذ أمر طبي (Doctor Order)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">نص الملاحظة السريرية: *</label>
                        <textarea name="note_content" class="form-control" rows="3" required placeholder="اكتب الملاحظة التمريضية بدقة..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-success fw-bold">حفظ الملاحظة</button>
                </div>
            </form>
        </div>
    </div>
</div>
