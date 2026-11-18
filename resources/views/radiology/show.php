<div class="row g-4">
    <!-- Header Banner -->
    <div class="col-12">
        <div class="card card-hms p-4 bg-white shadow-sm">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <h3 class="fw-bold text-navy mb-0"><?= e($order['patient_name']) ?></h3>
                        <span class="badge bg-navy font-monospace fs-6 px-3 py-1"><?= e($order['order_number']) ?></span>
                        <span class="badge bg-secondary font-monospace fs-6 px-3 py-1">MRN: <?= e($order['mrn']) ?></span>
                        <?php
                            $stClass = match($order['status']) {
                                'ordered' => 'bg-warning text-dark',
                                'scheduled' => 'bg-info text-dark',
                                'performed' => 'bg-primary text-white',
                                'reported' => 'bg-success text-white',
                                default => 'bg-secondary'
                            };
                        ?>
                        <span class="badge <?= $stClass ?> fs-6"><?= e($order['status']) ?></span>
                    </div>
                    <div class="text-muted mt-2">
                        <span class="me-3">العمر: <?= $order['patient_age'] ?> سنة</span>
                        <span class="me-3">النوع: <?= $order['gender'] === 'male' ? 'ذكر' : 'أنثى' ?></span>
                        <span class="me-3">الأولوية: <span class="badge bg-light text-dark border"><?= strtoupper(e($order['priority'])) ?></span></span>
                        <span>الطبيب الطالب: د. <?= e($order['doctor_name']) ?> (<?= e($order['doctor_specialty']) ?>)</span>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <?php if ($order['status'] === 'ordered'): ?>
                        <form action="<?= url("/radiology/{$order['id']}/status") ?>" method="POST" class="d-inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="status" value="performed">
                            <button type="submit" class="btn btn-primary fw-bold d-inline-flex align-items-center gap-2">
                                <i data-lucide="camera" style="width: 18px; height: 18px;"></i>
                                تأكيد إجراء التصوير الإشعاعي
                            </button>
                        </form>
                    <?php endif; ?>

                    <?php if (in_array($order['status'], ['performed', 'reported'])): ?>
                        <button type="button" class="btn btn-warning fw-bold d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#radiologyReportModal">
                            <i data-lucide="file-edit" style="width: 18px; height: 18px;"></i>
                            <?= $report ? 'تعديل تقرير الأشعة' : 'كتابة تقرير الأشعة التشخيصي' ?>
                        </button>
                    <?php endif; ?>

                    <?php if ($report): ?>
                        <a href="<?= url("/radiology/{$order['id']}/print") ?>" target="_blank" class="btn btn-outline-primary fw-bold d-inline-flex align-items-center gap-2">
                            <i data-lucide="printer" style="width: 18px; height: 18px;"></i>
                            طباعة التقرير الإشعاعي
                        </a>
                    <?php endif; ?>

                    <a href="<?= url('/radiology') ?>" class="btn btn-outline-secondary">رجوع</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Service & Indication Info -->
    <div class="col-lg-4">
        <!-- Service Details -->
        <div class="card card-hms p-4 bg-white shadow-sm mb-4">
            <h5 class="fw-bold text-navy mb-3 d-flex align-items-center gap-2">
                <i data-lucide="film" class="text-primary" style="width: 20px; height: 20px;"></i>
                تفاصيل الفحص الإشعاعي
            </h5>

            <div class="p-3 bg-light rounded border mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small">نوع التقنية (Modality)</span>
                    <span class="badge bg-navy fs-6 text-uppercase"><?= e($order['modality']) ?></span>
                </div>
                <div class="mb-2">
                    <span class="text-muted small d-block">اسم الفحص:</span>
                    <strong class="text-navy"><?= e($order['service_name_ar']) ?></strong>
                    <small class="text-muted d-block"><?= e($order['service_name_en']) ?></small>
                </div>
                <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                    <span class="text-muted small">السعر المعتمد</span>
                    <span class="fw-bold text-success fs-6"><?= number_format($order['price'], 2) ?> SDG</span>
                </div>
            </div>

            <?php if (!empty($order['preparation_instructions'])): ?>
                <div>
                    <strong class="text-navy small d-block mb-1">تعليمات التحضير للمريض:</strong>
                    <div class="alert alert-info py-2 px-3 small mb-0">
                        <?= nl2br(e($order['preparation_instructions'])) ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Clinical Indication -->
        <div class="card card-hms p-4 bg-white shadow-sm">
            <h5 class="fw-bold text-navy mb-3 d-flex align-items-center gap-2">
                <i data-lucide="file-text" class="text-secondary" style="width: 20px; height: 20px;"></i>
                الداعي السريري وملاحظات الطبيب
            </h5>
            <div class="p-3 bg-light rounded border small">
                <?= nl2br(e($order['clinical_indication'])) ?>
            </div>
        </div>
    </div>

    <!-- Diagnostic Radiology Report Workspace -->
    <div class="col-lg-8">
        <div class="card card-hms p-4 bg-white shadow-sm h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-navy mb-0 d-flex align-items-center gap-2">
                    <i data-lucide="clipboard" class="text-success" style="width: 20px; height: 20px;"></i>
                    تقرير استشاري الأشعة والتصوير الطبي (Radiology Report)
                </h5>
                <?php if ($report): ?>
                    <span class="badge bg-success fs-6">✓ تقرير نهائي معتمد (<?= e($report['status']) ?>)</span>
                <?php endif; ?>
            </div>

            <?php if (!$report): ?>
                <div class="text-center py-5 text-muted">
                    <i data-lucide="file-question" style="width: 48px; height: 48px; margin: 0 auto 12px; color: #64748B;"></i>
                    <h5 class="fw-bold text-navy">لم يتم تدوين تقرير الأشعة بعد</h5>
                    <p class="mb-0">يرجى الضغط على زر "كتابة تقرير الأشعة التشخيصي" بالأعلى لإضافة المشاهدات والاستنتاج الطبي.</p>
                </div>
            <?php else: ?>
                <div class="mb-4">
                    <h6 class="fw-bold text-navy border-bottom pb-2 mb-2">1. المشاهدات الإشعاعية والوصف (Findings):</h6>
                    <div class="p-3 bg-light rounded border fs-6 font-monospace" style="white-space: pre-line; line-height: 1.8;">
                        <?= e($report['findings']) ?>
                    </div>
                </div>

                <div class="mb-4">
                    <h6 class="fw-bold text-navy border-bottom pb-2 mb-2">2. الاستنتاج والتشخيص النهائي (Impression):</h6>
                    <div class="p-3 bg-light rounded border border-primary fs-6 fw-bold text-navy" style="white-space: pre-line; line-height: 1.8;">
                        <?= e($report['impression']) ?>
                    </div>
                </div>

                <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center text-muted small">
                    <div>
                        تاريخ كتابة التقرير: <?= date('d/m/Y H:i', strtotime($report['reported_at'])) ?>
                    </div>
                    <div>
                        استشاري الأشعة: <strong>د. <?= e($report['radiologist_name']) ?></strong> (<?= e($report['radiologist_specialty']) ?>)
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal: Radiology Report -->
<div class="modal fade" id="radiologyReportModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-navy text-white">
                <h5 class="modal-title fw-bold">تدوين تقرير الأشعة التشخيصي</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= url("/radiology/{$order['id']}/report") ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">استشاري / أخصائي الأشعة المعتمد: *</label>
                        <select name="radiologist_id" class="form-select" required>
                            <option value="">-- اختر طبيب الأشعة --</option>
                            <?php foreach ($doctors as $d): ?>
                                <option value="<?= $d['id'] ?>" <?= ($report && (string)$report['radiologist_id'] === (string)$d['id']) ? 'selected' : '' ?>>
                                    د. <?= e($d['employee_name'] ?? 'طبيب') ?> (<?= e($d['specialty_name'] ?? '') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">المشاهدات التفصيلية (Radiological Findings): *</label>
                        <textarea name="findings" class="form-control font-monospace" rows="5" required placeholder="وصف تفصيلي لما تم رصده في الصورة الإشعاعية..."><?= e($report['findings'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">الخلاصة والتشخيص الاستنتاجي (Impression / Conclusion): *</label>
                        <textarea name="impression" class="form-control fw-bold" rows="3" required placeholder="التشخيص النهائي المستنتج من الفحص..."><?= e($report['impression'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">نوع التقرير:</label>
                        <select name="report_status" class="form-select">
                            <option value="final" selected>تقرير نهائي معتمد (Final Report)</option>
                            <option value="preliminary">تقرير أولي مبدئي (Preliminary)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary fw-bold">اعتماد وحفظ التقرير</button>
                </div>
            </form>
        </div>
    </div>
</div>
