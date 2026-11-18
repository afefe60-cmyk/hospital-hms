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
                                'sample_collected' => 'bg-info text-dark',
                                'in_progress' => 'bg-primary text-white',
                                'completed' => 'bg-success text-white',
                                default => 'bg-secondary'
                            };
                        ?>
                        <span class="badge <?= $stClass ?> fs-6"><?= e($order['status']) ?></span>
                    </div>
                    <div class="text-muted mt-2">
                        <span class="me-3">العمر: <?= $order['patient_age'] ?> سنة</span>
                        <span class="me-3">النوع: <?= $order['gender'] === 'male' ? 'ذكر' : 'أنثى' ?></span>
                        <span class="me-3">فصيلة الدم: <span class="badge bg-danger"><?= e($order['blood_group']) ?></span></span>
                        <span class="me-3">الأولوية: <span class="badge bg-light text-dark border"><?= strtoupper(e($order['priority'])) ?></span></span>
                        <span>الطبيب المعالج: د. <?= e($order['doctor_name']) ?> (<?= e($order['doctor_specialty']) ?>)</span>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <?php if (!$sample): ?>
                        <button type="button" class="btn btn-warning fw-bold d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#collectSampleModal">
                            <i data-lucide="barcode" style="width: 18px; height: 18px;"></i>
                            سحب العينة وإصدار الباركود
                        </button>
                    <?php endif; ?>

                    <?php if ($sample && ($order['status'] !== 'completed')): ?>
                        <button type="button" class="btn btn-primary fw-bold d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#enterResultsModal">
                            <i data-lucide="file-edit" style="width: 18px; height: 18px;"></i>
                            <?= $result ? 'تعديل النتائج المخبرية' : 'إدخال النتائج المخبرية' ?>
                        </button>
                    <?php endif; ?>

                    <?php if ($result && $result['status'] === 'draft'): ?>
                        <form action="<?= url("/lab/{$order['id']}/approve") ?>" method="POST" onsubmit="return confirm('هل أنت متأكد من اعتماد نتائج الفحص المخبري نهائياً؟');" class="d-inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-success fw-bold d-inline-flex align-items-center gap-2">
                                <i data-lucide="check-circle" style="width: 18px; height: 18px;"></i>
                                اعتماد التقرير رسمياً
                            </button>
                        </form>
                    <?php endif; ?>

                    <?php if ($result && $result['status'] === 'approved'): ?>
                        <a href="<?= url("/lab/{$order['id']}/print") ?>" target="_blank" class="btn btn-outline-primary fw-bold d-inline-flex align-items-center gap-2">
                            <i data-lucide="printer" style="width: 18px; height: 18px;"></i>
                            طباعة التقرير المخبري
                        </a>
                    <?php endif; ?>

                    <a href="<?= url('/lab') ?>" class="btn btn-outline-secondary">رجوع</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Sample & Clinical Indication Info -->
    <div class="col-lg-4">
        <!-- Sample Info Card -->
        <div class="card card-hms p-4 bg-white shadow-sm mb-4">
            <h5 class="fw-bold text-navy mb-3 d-flex align-items-center gap-2">
                <i data-lucide="barcode" class="text-primary" style="width: 20px; height: 20px;"></i>
                بيانات وتتبع العينة (Sample Tracking)
            </h5>

            <?php if ($sample): ?>
                <div class="p-3 bg-light rounded border text-center mb-3">
                    <div class="font-monospace fw-bold fs-5 text-primary mb-1">
                        🏷️ <?= e($sample['sample_barcode']) ?>
                    </div>
                    <span class="badge bg-success">تم الاستلام بالمختبر (Received)</span>
                </div>
                <div class="small">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">نوع العينة:</span>
                        <span class="fw-bold"><?= e($sample['sample_type']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">وقت السحب:</span>
                        <span><?= date('d/m/Y H:i', strtotime($sample['collected_at'])) ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">سحب بواسطة:</span>
                        <span><?= e($sample['collector_name']) ?></span>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-warning py-3 text-center mb-0">
                    <i data-lucide="clock" style="width: 30px; height: 30px; margin: 0 auto 8px;" class="d-block"></i>
                    لم يتم سحب العينة من المريض بعد.
                </div>
            <?php endif; ?>
        </div>

        <!-- Clinical Indication Card -->
        <div class="card card-hms p-4 bg-white shadow-sm">
            <h5 class="fw-bold text-navy mb-3 d-flex align-items-center gap-2">
                <i data-lucide="file-text" class="text-secondary" style="width: 20px; height: 20px;"></i>
                الدواعي السريرية للطلب
            </h5>
            <div class="p-3 bg-light rounded border small">
                <?= nl2br(e($order['clinical_indication'] ?? 'لا توجد ملاحظات سريرية مدونة')) ?>
            </div>

            <?php if ($result && !empty($result['general_comments'])): ?>
                <div class="mt-3">
                    <h6 class="fw-bold text-navy small mb-1">تعليقات وتوجيهات أخصائي المختبر:</h6>
                    <div class="p-2 bg-light rounded border small text-muted">
                        <?= nl2br(e($result['general_comments'])) ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Main Results & Tests Workspace -->
    <div class="col-lg-8">
        <!-- Tests Included in this Order -->
        <div class="card card-hms p-4 bg-white shadow-sm mb-4">
            <h5 class="fw-bold text-navy mb-3 d-flex align-items-center gap-2">
                <i data-lucide="list-checks" class="text-primary" style="width: 20px; height: 20px;"></i>
                الفحوصات المطلوبة في هذا الطلب
            </h5>

            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>الكود</th>
                            <th>اسم الفحص</th>
                            <th>التصنيف</th>
                            <th>نوع العينة</th>
                            <th>السعر (SDG)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><code><?= e($item['test_code']) ?></code></td>
                                <td class="fw-bold text-navy">
                                    <?= e($item['test_name_ar']) ?>
                                    <small class="text-muted d-block"><?= e($item['test_name_en']) ?></small>
                                </td>
                                <td><span class="badge bg-secondary"><?= e($item['category']) ?></span></td>
                                <td><span class="badge bg-light text-dark border"><?= e($item['sample_type']) ?></span></td>
                                <td class="fw-bold text-success"><?= number_format($item['price'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Laboratory Results Table -->
        <div class="card card-hms p-4 bg-white shadow-sm">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-navy mb-0 d-flex align-items-center gap-2">
                    <i data-lucide="microscope" class="text-success" style="width: 20px; height: 20px;"></i>
                    نتائج التحاليل المخبرية المعتمدة
                </h5>
                <?php if ($result): ?>
                    <span class="badge <?= ($result['status'] === 'approved') ? 'bg-success' : 'bg-warning text-dark' ?> fs-6">
                        <?= ($result['status'] === 'approved') ? '✓ معتمد رسمياً' : 'مسودة غير معتمدة' ?>
                    </span>
                <?php endif; ?>
            </div>

            <?php if (empty($resultItems)): ?>
                <div class="text-center py-5 text-muted">
                    <i data-lucide="clipboard-pen" style="width: 48px; height: 48px; margin: 0 auto 12px; color: #64748B;"></i>
                    <h5 class="fw-bold text-navy">النتائج لم تدخل بعد</h5>
                    <p class="mb-0">يرجى الضغط على زر "إدخال النتائج المخبرية" أعلاه لتدوين قراءات الفحص.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>المؤشر / الخاصية (Parameter)</th>
                                <th>النتيجة (Result)</th>
                                <th>الوحدة</th>
                                <th>المعدل الطبيعي (Reference Range)</th>
                                <th class="text-center">الدلالة السريرية</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($resultItems as $ri): ?>
                                <?php
                                    $flagClass = match($ri['flag']) {
                                        'critical' => 'bg-danger text-white fw-bold',
                                        'high' => 'bg-warning text-dark fw-bold',
                                        'low' => 'bg-info text-dark fw-bold',
                                        'positive' => 'bg-danger text-white fw-bold',
                                        'negative' => 'bg-success text-white',
                                        default => 'bg-light text-dark'
                                    };
                                ?>
                                <tr>
                                    <td class="fw-bold text-navy"><?= e($ri['parameter_name']) ?></td>
                                    <td class="fs-6 fw-bold <?= in_array($ri['flag'], ['high', 'critical', 'positive']) ? 'text-danger' : 'text-primary' ?>">
                                        <?= e($ri['result_value']) ?>
                                    </td>
                                    <td><small class="text-muted"><?= e($ri['unit']) ?></small></td>
                                    <td class="small font-monospace"><?= e($ri['reference_range']) ?></td>
                                    <td class="text-center">
                                        <span class="badge <?= $flagClass ?> px-3 py-1 text-uppercase">
                                            <?= e($ri['flag']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($result && $result['verified_by']): ?>
                    <div class="mt-3 text-end text-muted small">
                        تم التدقيق والاعتماد بواسطة: <strong><?= e($result['verifier_name']) ?></strong> في <?= date('d/m/Y H:i', strtotime($result['verified_at'])) ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal 1: Collect Sample -->
<div class="modal fade" id="collectSampleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold">سحب العينة وتوليد الباركود</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= url("/lab/{$order['id']}/collect-sample") ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">نوع العينة المسحوبة:</label>
                        <select name="sample_type" class="form-select">
                            <option value="whole_blood">دم كامل (Whole Blood - EDTA)</option>
                            <option value="serum">مصل دم (Serum - Plain)</option>
                            <option value="urine">عينة بول (Urine)</option>
                            <option value="stool">عينة براز (Stool)</option>
                            <option value="swab">مسحة (Swab)</option>
                            <option value="csf">سائل نخاعي (CSF)</option>
                        </select>
                    </div>
                    <p class="small text-muted mb-0">
                        * سيقوم النظام آلياً بتوليد رمز باركود فريد وتحديث حالة الطلب إلى (تم استلام العينة).
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-warning fw-bold">تأكيد سحب العينة</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 2: Enter Results -->
<div class="modal fade" id="enterResultsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">إدخال النتائج وقراءات الفحص المعملي</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= url("/lab/{$order['id']}/results") ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <p class="small text-muted mb-3">أدخل قراءات الفحوصات ووحدات القياس وحدد دلالة النتيجة السريرية (Normal, High, Low, Positive, Negative):</p>

                    <div id="resultsRowsContainer">
                        <?php if (!empty($resultItems)): ?>
                            <?php foreach ($resultItems as $idx => $ri): ?>
                                <div class="row g-2 align-items-center mb-2 result-row">
                                    <input type="hidden" name="lab_test_id[]" value="<?= $ri['lab_test_id'] ?>">
                                    <div class="col-md-3">
                                        <input type="text" name="parameter_name[]" class="form-control form-control-sm" value="<?= e($ri['parameter_name']) ?>" required placeholder="اسم المؤشر">
                                    </div>
                                    <div class="col-md-2">
                                        <input type="text" name="result_value[]" class="form-control form-control-sm fw-bold" value="<?= e($ri['result_value']) ?>" required placeholder="النتيجة">
                                    </div>
                                    <div class="col-md-2">
                                        <input type="text" name="unit[]" class="form-control form-control-sm" value="<?= e($ri['unit']) ?>" placeholder="الوحدة">
                                    </div>
                                    <div class="col-md-3">
                                        <input type="text" name="reference_range[]" class="form-control form-control-sm font-monospace" value="<?= e($ri['reference_range']) ?>" placeholder="المعدل الطبيعي">
                                    </div>
                                    <div class="col-md-2">
                                        <select name="flag[]" class="form-select form-select-sm">
                                            <option value="normal" <?= ($ri['flag'] === 'normal') ? 'selected' : '' ?>>طبيعي</option>
                                            <option value="high" <?= ($ri['flag'] === 'high') ? 'selected' : '' ?>>مرتفع High</option>
                                            <option value="low" <?= ($ri['flag'] === 'low') ? 'selected' : '' ?>>منخفض Low</option>
                                            <option value="positive" <?= ($ri['flag'] === 'positive') ? 'selected' : '' ?>>إيجابي Positive</option>
                                            <option value="negative" <?= ($ri['flag'] === 'negative') ? 'selected' : '' ?>>سلبي Negative</option>
                                            <option value="critical" <?= ($ri['flag'] === 'critical') ? 'selected' : '' ?>>حرج Critical</option>
                                        </select>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <?php foreach ($items as $item): ?>
                                <div class="row g-2 align-items-center mb-2 result-row">
                                    <input type="hidden" name="lab_test_id[]" value="<?= $item['lab_test_id'] ?>">
                                    <div class="col-md-3">
                                        <input type="text" name="parameter_name[]" class="form-control form-control-sm" value="<?= e($item['test_name_ar']) ?>" required placeholder="اسم المؤشر">
                                    </div>
                                    <div class="col-md-2">
                                        <input type="text" name="result_value[]" class="form-control form-control-sm fw-bold" required placeholder="النتيجة">
                                    </div>
                                    <div class="col-md-2">
                                        <input type="text" name="unit[]" class="form-control form-control-sm" value="<?= e($item['unit']) ?>" placeholder="الوحدة">
                                    </div>
                                    <div class="col-md-3">
                                        <input type="text" name="reference_range[]" class="form-control form-control-sm font-monospace" value="<?= e($item['normal_range']) ?>" placeholder="المعدل الطبيعي">
                                    </div>
                                    <div class="col-md-2">
                                        <select name="flag[]" class="form-select form-select-sm">
                                            <option value="normal" selected>طبيعي</option>
                                            <option value="high">مرتفع High</option>
                                            <option value="low">منخفض Low</option>
                                            <option value="positive">إيجابي Positive</option>
                                            <option value="negative">سلبي Negative</option>
                                            <option value="critical">حرج Critical</option>
                                        </select>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <div class="mt-3">
                        <label class="form-label fw-bold">ملاحظات وتعليقات عامة:</label>
                        <textarea name="general_comments" class="form-control" rows="2" placeholder="أي تعليق أو توضيح إضافي للطبيب المعالج..."><?= e($result['general_comments'] ?? '') ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary fw-bold">حفظ النتائج</button>
                </div>
            </form>
        </div>
    </div>
</div>
