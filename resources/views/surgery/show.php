<?php
$layout = 'layouts/main';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <div class="d-flex align-items-center gap-3">
            <h3 class="h4 mb-0 fw-bold font-monospace text-primary"><?= htmlspecialchars($surgery['surgery_number']) ?></h3>
            <?php
                $badgeClass = match($surgery['status']) {
                    'scheduled' => 'bg-info',
                    'in_progress' => 'bg-danger animate-pulse',
                    'completed' => 'bg-success',
                    'postponed' => 'bg-warning text-dark',
                    'cancelled' => 'bg-secondary',
                    default => 'bg-light text-dark'
                };
                $statusLabel = match($surgery['status']) {
                    'scheduled' => 'مجدولة',
                    'in_progress' => 'جارية الآن بالمسرح',
                    'completed' => 'مكتملة',
                    'postponed' => 'مؤجلة',
                    'cancelled' => 'ملغاة',
                    default => $surgery['status']
                };
            ?>
            <span class="badge <?= $badgeClass ?> fs-6"><?= $statusLabel ?></span>
            <span class="badge <?= $surgery['surgery_type'] === 'emergency' ? 'bg-danger' : 'bg-primary' ?>">
                <?= $surgery['surgery_type'] === 'emergency' ? 'جراحة طارئة' : 'جراحة اختيارية' ?>
            </span>
        </div>
        <p class="text-muted mb-0 mt-1"><?= htmlspecialchars($surgery['procedure_name']) ?></p>
    </div>
    <div class="d-flex gap-2">
        <a href="/surgery/print?id=<?= $surgery['id'] ?>" target="_blank" class="btn btn-outline-secondary">
            <i class="bi bi-printer me-1"></i>طباعة تقرير العملية
        </a>
        <a href="/surgery" class="btn btn-outline-primary">
            <i class="bi bi-arrow-right me-1"></i>رجوع للجدول
        </a>
    </div>
</div>

<!-- Patient Summary Banner -->
<div class="card border-0 shadow-sm rounded-3 mb-4 bg-light">
    <div class="card-body p-3">
        <div class="row g-3 align-items-center">
            <div class="col-md-3">
                <span class="text-muted d-block small">اسم المريض</span>
                <span class="fw-bold text-dark fs-6"><?= htmlspecialchars($surgery['patient_name']) ?></span>
            </div>
            <div class="col-md-2">
                <span class="text-muted d-block small">الرقم الطبي (MRN)</span>
                <span class="fw-bold font-monospace"><?= htmlspecialchars($surgery['mrn']) ?></span>
            </div>
            <div class="col-md-2">
                <span class="text-muted d-block small">العمر / النوع</span>
                <span class="fw-bold"><?= $surgery['patient_age'] ?> سنة (<?= $surgery['patient_gender'] === 'male' ? 'ذكر' : 'أنثى' ?>)</span>
            </div>
            <div class="col-md-2">
                <span class="text-muted d-block small">فصيلة الدم</span>
                <span class="badge bg-danger fs-6"><?= htmlspecialchars($surgery['blood_group'] ?? 'غير محدد') ?></span>
            </div>
            <div class="col-md-3">
                <span class="text-muted d-block small">غرفة العمليات</span>
                <span class="fw-bold text-primary"><i class="bi bi-door-open me-1"></i><?= htmlspecialchars($surgery['or_name_ar']) ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Status Transition Actions Strip -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-speedometer2 text-muted"></i>
            <span class="fw-bold">التحكم في مسار العملية:</span>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <?php if ($surgery['status'] === 'scheduled'): ?>
                <form method="POST" action="/surgery/update-status" class="d-inline" onsubmit="return confirm('هل تريد بدء العملية الجراحية الآن وتغيير حالة غرفة العمليات إلى مشغولة؟')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= $surgery['id'] ?>">
                    <input type="hidden" name="status" value="in_progress">
                    <button type="submit" class="btn btn-success btn-sm px-3">
                        <i class="bi bi-play-circle me-1"></i>بدء العملية (Start Surgery)
                    </button>
                </form>
                <form method="POST" action="/surgery/update-status" class="d-inline" onsubmit="return confirm('تأكيد تأجيل العملية؟')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= $surgery['id'] ?>">
                    <input type="hidden" name="status" value="postponed">
                    <button type="submit" class="btn btn-warning btn-sm px-3">
                        <i class="bi bi-pause-circle me-1"></i>تأجيل
                    </button>
                </form>
                <form method="POST" action="/surgery/update-status" class="d-inline" onsubmit="return confirm('تأكيد إلغاء العملية؟')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= $surgery['id'] ?>">
                    <input type="hidden" name="status" value="cancelled">
                    <button type="submit" class="btn btn-outline-danger btn-sm px-3">
                        <i class="bi bi-x-circle me-1"></i>إلغاء العملية
                    </button>
                </form>
            <?php elseif ($surgery['status'] === 'in_progress'): ?>
                <button type="button" class="btn btn-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#completeSurgeryModal">
                    <i class="bi bi-check2-circle me-1"></i>إنهاء العملية وتوثيق التقرير
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Surgery Info & Surgical Team -->
    <div class="col-lg-5">
        <!-- Team Card -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3 border-light">
                <h5 class="card-title mb-0 fw-bold"><i class="bi bi-people text-primary me-2"></i>الطاقم الجراحي والتخديري</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span class="text-muted"><i class="bi bi-person-badge me-2"></i>الجراح الرئيسي:</span>
                        <strong class="text-dark">د. <?= htmlspecialchars($surgery['lead_surgeon_name']) ?></strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span class="text-muted"><i class="bi bi-person me-2"></i>الجراح المساعد:</span>
                        <span><?= !empty($surgery['assistant_surgeon_name']) ? 'د. ' . htmlspecialchars($surgery['assistant_surgeon_name']) : '<em class="text-muted">لا يوجد</em>' ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span class="text-muted"><i class="bi bi-activity me-2"></i>طبيب التخدير:</span>
                        <span><?= !empty($surgery['anesthesiologist_name']) ? 'د. ' . htmlspecialchars($surgery['anesthesiologist_name']) : '<span class="text-danger">لم يحدد بعد</span>' ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span class="text-muted"><i class="bi bi-heart me-2"></i>تمريض العمليات:</span>
                        <span><?= !empty($surgery['scrub_nurse_name']) ? htmlspecialchars($surgery['scrub_nurse_name']) : '<em class="text-muted">غير محدد</em>' ?></span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Schedule & Timing -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3 border-light">
                <h5 class="card-title mb-0 fw-bold"><i class="bi bi-clock-history text-primary me-2"></i>التوقيتات والجدول الزمني</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6">
                        <small class="text-muted d-block">البداية المجدولة</small>
                        <strong><?= date('Y-m-d h:i A', strtotime($surgery['scheduled_start'])) ?></strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">النهاية المجدولة</small>
                        <strong><?= date('Y-m-d h:i A', strtotime($surgery['scheduled_end'])) ?></strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">البداية الفعلية</small>
                        <span class="text-success fw-bold"><?= $surgery['actual_start'] ? date('h:i A', strtotime($surgery['actual_start'])) : 'لم تبدأ بعد' ?></span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">النهاية الفعلية</small>
                        <span class="text-primary fw-bold"><?= $surgery['actual_end'] ? date('h:i A', strtotime($surgery['actual_end'])) : '-' ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Notes & Anesthesia Record -->
    <div class="col-lg-7">
        <!-- Diagnosis and Notes Card -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white py-3 border-light">
                <h5 class="card-title mb-0 fw-bold"><i class="bi bi-file-medical text-primary me-2"></i>التشخيص وتقرير الجراحة الإكلينيكي</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <span class="text-muted d-block small fw-bold mb-1">التشخيص قبل العملية (Pre-operative Diagnosis):</span>
                    <div class="p-3 bg-light rounded border text-dark"><?= nl2br(htmlspecialchars($surgery['pre_op_diagnosis'] ?? 'لا يوجد')) ?></div>
                </div>
                <div class="mb-3">
                    <span class="text-muted d-block small fw-bold mb-1">التشخيص بعد العملية (Post-operative Diagnosis):</span>
                    <div class="p-3 bg-light rounded border text-dark"><?= nl2br(htmlspecialchars($surgery['post_op_diagnosis'] ?? 'قيد التوثيق بعد انتهاء العملية')) ?></div>
                </div>
                <div>
                    <span class="text-muted d-block small fw-bold mb-1">تقرير العملية وملاحظات الجراح (Operation Notes):</span>
                    <div class="p-3 bg-light rounded border text-dark" style="min-height: 80px;"><?= nl2br(htmlspecialchars($surgery['surgery_notes'] ?? 'لم يتم تدوين ملاحظات الجراحة بعد.')) ?></div>
                </div>
            </div>
        </div>

        <!-- Anesthesia Record Card -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white py-3 border-light d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 fw-bold"><i class="bi bi-capsule text-danger me-2"></i>سجل التخدير الطبي (Anesthesia Record)</h5>
                <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#anesthesiaModal">
                    <i class="bi bi-pencil-square me-1"></i><?= $anesthesia ? 'تعديل سجل التخدير' : 'توثيق التخدير' ?>
                </button>
            </div>
            <div class="card-body">
                <?php if ($anesthesia): ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <span class="text-muted small d-block">طبيب التخدير:</span>
                            <strong>د. <?= htmlspecialchars($anesthesia['anesthesiologist_name']) ?></strong>
                        </div>
                        <div class="col-md-3">
                            <span class="text-muted small d-block">نوع التخدير:</span>
                            <span class="badge bg-secondary"><?= htmlspecialchars($anesthesia['anesthesia_type']) ?></span>
                        </div>
                        <div class="col-md-3">
                            <span class="text-muted small d-block">تصنيف ASA:</span>
                            <span class="badge bg-danger">ASA <?= htmlspecialchars($anesthesia['asa_physical_status']) ?></span>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted small d-block">تقييم مجرى الهواء (Airway):</span>
                            <span><?= htmlspecialchars($anesthesia['airway_assessment'] ?? '-') ?></span>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted small d-block">مقياس الإفاقة (Recovery Score):</span>
                            <strong class="text-success"><?= htmlspecialchars($anesthesia['recovery_score'] ?? '-') ?></strong>
                        </div>
                        <div class="col-12">
                            <span class="text-muted small d-block">الأدوية والغازات المخدرة المستخدمة:</span>
                            <div class="p-2 bg-light rounded border small"><?= htmlspecialchars($anesthesia['agents_used'] ?? 'لا يوجد') ?></div>
                        </div>
                        <div class="col-12">
                            <span class="text-muted small d-block">ملاحظات التخدير والمضاعفات:</span>
                            <div class="p-2 bg-light rounded border small">
                                <strong>المضاعفات:</strong> <?= htmlspecialchars($anesthesia['complications'] ?? 'لا توجد مضاعفات') ?><br>
                                <strong>الملاحظات:</strong> <?= nl2br(htmlspecialchars($anesthesia['anesthesia_notes'] ?? 'لا يوجد')) ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-file-earmark-medical fs-2 d-block mb-2 text-secondary opacity-50"></i>
                        لم يتم توثيق سجل التخدير لهذه العملية حتى الآن.
                        <div class="mt-2">
                            <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#anesthesiaModal">
                                <i class="bi bi-plus-circle me-1"></i>إضافة سجل التخدير الآن
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Complete Surgery -->
<div class="modal fade" id="completeSurgeryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="/surgery/update-status">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= $surgery['id'] ?>">
                <input type="hidden" name="status" value="completed">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-check2-circle text-success me-2"></i>إنهاء العملية وتوثيق تقرير الجراحة</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">التشخيص النهائي بعد العملية (Post-operative Diagnosis) <span class="text-danger">*</span></label>
                        <textarea name="post_op_diagnosis" class="form-control" rows="2" required placeholder="التشخيص الجراحي المؤكد..."><?= htmlspecialchars($surgery['post_op_diagnosis'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">تقرير تفاصيل العملية الجراحية (Operation Notes) <span class="text-danger">*</span></label>
                        <textarea name="surgery_notes" class="form-control" rows="6" required placeholder="سجل الخطوات الجراحية، الإجراءات المتبعة، فحص العينات، النزيف، الغرز الجراحية..."><?= htmlspecialchars($surgery['surgery_notes'] ?? '') ?></textarea>
                    </div>
                    <div class="alert alert-info py-2 small mb-0">
                        <i class="bi bi-info-circle me-1"></i>عند إنهاء العملية، سيتم تسجيل التوقيت الفعلي وتغيير حالة غرفة العمليات إلى "تعقيم وتجهيز".
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-success fw-bold">تأكيد إنهاء العملية</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Anesthesia Record -->
<div class="modal fade" id="anesthesiaModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="/surgery/save-anesthesia">
                <?= csrf_field() ?>
                <input type="hidden" name="surgery_id" value="<?= $surgery['id'] ?>">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-capsule text-danger me-2"></i>توثيق سجل التخدير الطبي</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">طبيب التخدير <span class="text-danger">*</span></label>
                            <select name="anesthesiologist_id" class="form-select" required>
                                <?php foreach ($anesthesiologists as $doc): ?>
                                    <option value="<?= $doc['id'] ?>" <?= ($anesthesia && (int)$anesthesia['anesthesiologist_id'] === (int)$doc['id']) ? 'selected' : '' ?>>
                                        د. <?= htmlspecialchars($doc['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">نوع التخدير</label>
                            <select name="anesthesia_type" class="form-select">
                                <?php foreach (['general' => 'عام (General)', 'spinal' => 'نصفي شوكي (Spinal)', 'epidural' => 'فوق الجافية (Epidural)', 'local' => 'موضعي (Local)', 'sedation' => 'تهدئة وريدية (Sedation)'] as $k => $v): ?>
                                    <option value="<?= $k ?>" <?= ($anesthesia && $anesthesia['anesthesia_type'] === $k) ? 'selected' : '' ?>><?= $v ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">تصنيف ASA</label>
                            <select name="asa_physical_status" class="form-select">
                                <?php foreach (['I', 'II', 'III', 'IV', 'V', 'VI', 'E'] as $asa): ?>
                                    <option value="<?= $asa ?>" <?= ($anesthesia && $anesthesia['asa_physical_status'] === $asa) ? 'selected' : '' ?>>ASA <?= $asa ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">تقييم مجرى الهواء (Airway Assessment)</label>
                            <input type="text" name="airway_assessment" class="form-control" value="<?= htmlspecialchars($anesthesia['airway_assessment'] ?? 'Mallampati Class I') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">مقياس الإفاقة (Aldrete Score)</label>
                            <input type="text" name="recovery_score" class="form-control" value="<?= htmlspecialchars($anesthesia['recovery_score'] ?? 'Aldrete Score 10/10') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">الأدوية والجرعات المستخدمة</label>
                            <input type="text" name="agents_used" class="form-control" placeholder="مثال: Propofol 150mg, Fentanyl 100mcg, Isoflurane..." value="<?= htmlspecialchars($anesthesia['agents_used'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">المضاعفات إن وجدت (Complications)</label>
                            <input type="text" name="complications" class="form-control" placeholder="لا توجد مضاعفات..." value="<?= htmlspecialchars($anesthesia['complications'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">ملاحظات التخدير (Anesthesia Notes)</label>
                            <textarea name="anesthesia_notes" class="form-control" rows="3"><?= htmlspecialchars($anesthesia['anesthesia_notes'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-danger fw-bold">حفظ سجل التخدير</button>
                </div>
            </form>
        </div>
    </div>
</div>
