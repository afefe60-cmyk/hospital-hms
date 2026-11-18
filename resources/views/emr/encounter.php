<div class="row g-4">
    <!-- Header Patient Banner -->
    <div class="col-12">
        <div class="card card-hms p-4 bg-white">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <h3 class="fw-bold text-navy mb-0"><?= e($encounter['patient_name']) ?></h3>
                        <span class="badge bg-navy font-monospace fs-6 px-3 py-1"><?= e($encounter['mrn']) ?></span>
                        <span class="badge bg-light text-dark border">جلسة كشف #<?= $encounter['id'] ?></span>
                    </div>
                    <div class="text-muted mt-2">
                        <span class="me-3">العمر: <?= $encounter['patient_age'] ?> سنة</span>
                        <span class="me-3">النوع: <?= $encounter['gender'] === 'male' ? 'ذكر' : 'أنثى' ?></span>
                        <span class="me-3">فصيلة الدم: <span class="badge bg-danger"><?= e($encounter['blood_group']) ?></span></span>
                        <span class="me-3">رقم الزيارة: <code><?= e($encounter['visit_number']) ?></code></span>
                        <span>الطبيب الفاحص: د. <?= e($encounter['doctor_name']) ?> (<?= e($encounter['specialty_name']) ?>)</span>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <form action="<?= url("/emr/encounter/{$encounter['id']}/complete") ?>" method="POST" onsubmit="return confirm('هل أنت متأكد من إنهاء جلسة الكشف الطبي وإغلاق الزيارة؟');">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-success d-inline-flex align-items-center gap-2 px-4 py-2 fw-bold">
                            <i data-lucide="check-circle-2" style="width: 20px; height: 20px;"></i>
                            إنهاء الكشف وإغلاق الزيارة
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- CRITICAL CLINICAL SAFETY: Allergies Red Alert Banner -->
    <div class="col-12">
        <div class="card border-0 shadow-sm p-3 <?= !empty($allergies) ? 'bg-danger text-white' : 'bg-success-subtle text-success' ?>">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i data-lucide="alert-octagon" style="width: 26px; height: 26px;"></i>
                    <h5 class="fw-bold mb-0">
                        <?= !empty($allergies) ? 'تحذير طبي حرج: المريض يعاني من الحساسيات التالية (يرجى مراجعتها قبل كتابة أي دواء)' : 'لا توجد حساسيات دوائية مسجلة في ملف المريض' ?>
                    </h5>
                </div>
            </div>

            <?php if (!empty($allergies)): ?>
                <div class="d-flex flex-wrap gap-2 mt-2">
                    <?php foreach ($allergies as $alg): ?>
                        <span class="badge bg-white text-danger fs-6 p-2 shadow-sm">
                            ⚠️ <strong><?= e($alg['allergen_name']) ?></strong> (<?= e($alg['severity']) ?>) - <?= e($alg['reaction_notes']) ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 1. Vital Signs & Clinical Examination Section -->
    <div class="col-lg-6">
        <!-- Vital Signs Card -->
        <div class="card card-hms p-4 bg-white mb-4">
            <h5 class="fw-bold text-navy border-bottom pb-2 mb-3 d-flex align-items-center justify-content-between">
                <span class="d-flex align-items-center gap-2">
                    <i data-lucide="activity" class="text-green" style="width: 20px; height: 20px;"></i>
                    العلامات الحيوية (Vital Signs)
                </span>
                <?php if ($vitals && $vitals['bmi']): ?>
                    <span class="badge bg-info text-dark fs-6">BMI: <?= $vitals['bmi'] ?> kg/m²</span>
                <?php endif; ?>
            </h5>

            <form action="<?= url("/emr/encounter/{$encounter['id']}/vitals") ?>" method="POST">
                <?= csrf_field() ?>

                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <label class="form-label small text-muted">ضغط الدم الانقباضي (BP Sys)</label>
                        <input type="number" name="systolic_bp" class="form-control" placeholder="120 mmHg" value="<?= e($vitals['systolic_bp'] ?? '') ?>">
                    </div>
                    <div class="col-6">
                        <label class="form-label small text-muted">ضغط الدم الانبساطي (BP Dia)</label>
                        <input type="number" name="diastolic_bp" class="form-control" placeholder="80 mmHg" value="<?= e($vitals['diastolic_bp'] ?? '') ?>">
                    </div>
                </div>

                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <label class="form-label small text-muted">النبض (Pulse Rate)</label>
                        <input type="number" name="pulse_rate" class="form-control" placeholder="75 bpm" value="<?= e($vitals['pulse_rate'] ?? '') ?>">
                    </div>
                    <div class="col-6">
                        <label class="form-label small text-muted">درجة الحرارة (Temp °C)</label>
                        <input type="number" step="0.1" name="temperature" class="form-control" placeholder="37.0 °C" value="<?= e($vitals['temperature'] ?? '') ?>">
                    </div>
                </div>

                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <label class="form-label small text-muted">معدل التنفس (Resp)</label>
                        <input type="number" name="respiratory_rate" class="form-control" placeholder="18 /min" value="<?= e($vitals['respiratory_rate'] ?? '') ?>">
                    </div>
                    <div class="col-6">
                        <label class="form-label small text-muted">تشبع الأكسجين (SpO2 %)</label>
                        <input type="number" name="oxygen_saturation" class="form-control" placeholder="98 %" value="<?= e($vitals['oxygen_saturation'] ?? '') ?>">
                    </div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-4">
                        <label class="form-label small text-muted">الوزن (Weight kg)</label>
                        <input type="number" step="0.1" name="weight_kg" id="weight_kg" class="form-control" placeholder="70 kg" value="<?= e($vitals['weight_kg'] ?? '') ?>" oninput="calcBmiLive()">
                    </div>
                    <div class="col-4">
                        <label class="form-label small text-muted">الطول (Height cm)</label>
                        <input type="number" step="0.1" name="height_cm" id="height_cm" class="form-control" placeholder="175 cm" value="<?= e($vitals['height_cm'] ?? '') ?>" oninput="calcBmiLive()">
                    </div>
                    <div class="col-4">
                        <label class="form-label small text-muted">السكر العشوائي (RBS)</label>
                        <input type="number" name="random_blood_sugar" class="form-control" placeholder="110 mg/dL" value="<?= e($vitals['random_blood_sugar'] ?? '') ?>">
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted" id="liveBmiText">
                        <?= $vitals && $vitals['bmi'] ? "مؤشر الكتلة المحسوب: {$vitals['bmi']}" : '' ?>
                    </small>
                    <button type="submit" class="btn btn-sm btn-hms-primary">
                        <i data-lucide="save" style="width: 14px; height: 14px; display:inline-block; vertical-align:text-bottom;"></i>
                        حفظ العلامات الحيوية
                    </button>
                </div>
            </form>
        </div>

        <!-- Clinical Notes & Findings -->
        <div class="card card-hms p-4 bg-white">
            <h5 class="fw-bold text-navy border-bottom pb-2 mb-3 d-flex align-items-center gap-2">
                <i data-lucide="file-edit" class="text-green" style="width: 20px; height: 20px;"></i>
                الفحص السريري والملاحظات الطبية
            </h5>

            <form action="<?= url("/emr/encounter/{$encounter['id']}/notes") ?>" method="POST">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label class="form-label fw-semibold">الشكوى الرئيسية للمريض (Chief Complaint) <span class="text-danger">*</span></label>
                    <textarea name="chief_complaint" class="form-control" rows="2" required placeholder="مثال: حمى وصداع مستمر منذ 3 أيام..."><?= e($encounter['chief_complaint']) ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">القصة المرضية الحالية (History of Present Illness - HPI)</label>
                    <textarea name="history_present_illness" class="form-control" rows="2" placeholder="تفاصيل تطور الحالة والأعراض المصاحبة..."><?= e($encounter['history_present_illness']) ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">الفحص البدني والسريري (Physical Examination)</label>
                    <textarea name="physical_examination" class="form-control" rows="2" placeholder="المعاينة العامة، فحص الصدر، البطن، القلب..."><?= e($encounter['physical_examination']) ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">الخطة العلاجية والتوجيهات (Clinical Plan)</label>
                    <textarea name="clinical_plan" class="form-control" rows="2" placeholder="الراحة، الإكثار من السوائل، مراجعة بعد 3 أيام..."><?= e($encounter['clinical_plan']) ?></textarea>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-sm btn-hms-secondary">
                        <i data-lucide="save" style="width: 14px; height: 14px; display:inline-block; vertical-align:text-bottom;"></i>
                        حفظ الملاحظات الطبية
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- 2. Diagnoses, Prescriptions & Medical Orders Section -->
    <div class="col-lg-6">
        <!-- ICD-10 Diagnosis Card -->
        <div class="card card-hms p-4 bg-white mb-4">
            <h5 class="fw-bold text-navy border-bottom pb-2 mb-3 d-flex align-items-center justify-content-between">
                <span class="d-flex align-items-center gap-2">
                    <i data-lucide="clipboard-check" class="text-green" style="width: 20px; height: 20px;"></i>
                    التشخيص الطبي المعتمد (ICD-10)
                </span>
                <span class="badge bg-light text-navy border"><?= count($diagnoses) ?> تشخيص</span>
            </h5>

            <!-- List Existing Diagnoses -->
            <?php if (!empty($diagnoses)): ?>
                <div class="mb-3">
                    <?php foreach ($diagnoses as $d): ?>
                        <div class="p-2 border rounded bg-light-subtle mb-2 d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge <?= $d['type'] === 'primary' ? 'bg-primary' : 'bg-secondary' ?> me-1">
                                    <?= $d['type'] === 'primary' ? 'رئيسي' : 'ثانوي' ?>
                                </span>
                                <strong class="text-navy"><?= e($d['description_ar']) ?></strong>
                                <span class="text-muted small ms-1">(<?= e($d['description_en']) ?>)</span>
                                <span class="badge bg-light text-dark border font-monospace ms-2">ICD-10: <?= e($d['icd10_code']) ?></span>
                            </div>
                            <?php if ($d['is_infectious']): ?>
                                <span class="badge bg-danger">معدي</span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form action="<?= url("/emr/encounter/{$encounter['id']}/diagnosis") ?>" method="POST" class="row g-2">
                <?= csrf_field() ?>
                <div class="col-md-7">
                    <select name="diagnosis_catalog_id" class="form-select form-select-sm" required>
                        <option value="">-- اختر من دليل الأمراض العالمي ICD-10 --</option>
                        <?php foreach ($icd10Catalog as $item): ?>
                            <option value="<?= $item['id'] ?>">
                                [<?= e($item['icd10_code']) ?>] <?= e($item['description_ar']) ?> (<?= e($item['description_en']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="type" class="form-select form-select-sm">
                        <option value="primary" selected>تشخيص رئيسي</option>
                        <option value="secondary">تشخيص ثانوي</option>
                        <option value="provisional">مبدئي</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-sm btn-hms-primary w-100">إضافة</button>
                </div>
            </form>
        </div>

        <!-- Electronic Prescription (E-Prescription) -->
        <div class="card card-hms p-4 bg-white mb-4">
            <h5 class="fw-bold text-navy border-bottom pb-2 mb-3 d-flex align-items-center justify-content-between">
                <span class="d-flex align-items-center gap-2">
                    <i data-lucide="pill" class="text-accent" style="width: 20px; height: 20px;"></i>
                    الروشتة والوصفة الطبية الإلكترونية
                </span>
                <?php if (!empty($prescriptions)): ?>
                    <a href="<?= url("/emr/prescription/{$prescriptions[0]['id']}/print") ?>" target="_blank" class="btn btn-sm btn-outline-navy d-inline-flex align-items-center gap-1">
                        <i data-lucide="printer" style="width: 14px; height: 14px;"></i>
                        طباعة الروشتة المعتمدة
                    </a>
                <?php endif; ?>
            </h5>

            <!-- Existing Prescriptions -->
            <?php if (!empty($prescriptions)): ?>
                <div class="alert alert-success d-flex align-items-center justify-content-between p-2 mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <i data-lucide="file-check" style="width: 20px; height: 20px;"></i>
                        <span>تم تحرير روشتة برمز: <strong><?= e($prescriptions[0]['prescription_code']) ?></strong> (<?= $prescriptions[0]['items_count'] ?> أصناف)</span>
                    </div>
                    <a href="<?= url("/emr/prescription/{$prescriptions[0]['id']}/print") ?>" target="_blank" class="btn btn-sm btn-success">
                        معاينة وطباعة
                    </a>
                </div>
            <?php endif; ?>

            <form action="<?= url("/emr/encounter/{$encounter['id']}/prescription") ?>" method="POST" id="prescriptionForm">
                <?= csrf_field() ?>

                <div id="medicineRowsContainer">
                    <div class="medicine-row p-3 border rounded bg-light-subtle mb-3">
                        <div class="row g-2 mb-2">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">اسم الدواء والشكل الصيدلاني <span class="text-danger">*</span></label>
                                <input type="text" name="medicine_name[]" class="form-control form-control-sm" placeholder="مثال: Paracetamol 500mg, Amoxicillin 500mg..." required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">الجرعة</label>
                                <input type="text" name="dosage[]" class="form-control form-control-sm" placeholder="قرص واحد (1 Tab)" value="قرص واحد">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">التكرار</label>
                                <select name="frequency[]" class="form-select form-select-sm">
                                    <option value="مرتين يومياً (كل 12 ساعة)">مرتين يومياً (كل 12 ساعة)</option>
                                    <option value="3 مرات يومياً (كل 8 ساعات)">3 مرات يومياً (كل 8 ساعات)</option>
                                    <option value="مرة واحدة يومياً">مرة واحدة يومياً</option>
                                    <option value="عند اللزوم (PRN)">عند اللزوم (PRN)</option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">المدة (أيام)</label>
                                <input type="number" name="duration_days[]" class="form-control form-control-sm" value="5" min="1">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">الكمية الإجمالية</label>
                                <input type="number" name="total_quantity[]" class="form-control form-control-sm" value="10" min="1">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">تعليمات إضافية</label>
                                <input type="text" name="instructions[]" class="form-control form-control-sm" placeholder="بعد الأكل، شرب ماء وفير...">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addMedicineRow()">
                        <i data-lucide="plus" style="width: 14px; height: 14px; display:inline-block; vertical-align:text-bottom;"></i>
                        إضافة صنف دواء آخر
                    </button>
                    <button type="submit" class="btn btn-sm btn-hms-primary">
                        <i data-lucide="check" style="width: 14px; height: 14px; display:inline-block; vertical-align:text-bottom;"></i>
                        اعتماد وإصدار الروشتة
                    </button>
                </div>
            </form>
        </div>

        <!-- Medical Orders (Lab & Radiology) -->
        <div class="card card-hms p-4 bg-white">
            <h5 class="fw-bold text-navy border-bottom pb-2 mb-3 d-flex align-items-center justify-content-between">
                <span class="d-flex align-items-center gap-2">
                    <i data-lucide="flask-conical" class="text-green" style="width: 20px; height: 20px;"></i>
                    طلبات الفحوصات الطبية والأشعة (Orders)
                </span>
                <span class="badge bg-light text-navy border"><?= count($orders) ?> طلب</span>
            </h5>

            <!-- Existing Orders -->
            <?php if (!empty($orders)): ?>
                <div class="mb-3">
                    <?php foreach ($orders as $ord): ?>
                        <div class="p-2 border rounded bg-light-subtle mb-2 d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge <?= $ord['order_type'] === 'lab' ? 'bg-primary' : 'bg-info' ?> me-1">
                                    <?= $ord['order_type'] === 'lab' ? 'مختبر' : 'أشعة' ?>
                                </span>
                                <strong class="text-navy"><?= e($ord['order_description']) ?></strong>
                                <?php if (!empty($ord['clinical_indication'])): ?>
                                    <small class="text-muted ms-2">(دواعي: <?= e($ord['clinical_indication']) ?>)</small>
                                <?php endif; ?>
                            </div>
                            <span class="badge bg-warning-subtle text-warning border"><?= e($ord['status']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form action="<?= url("/emr/encounter/{$encounter['id']}/order") ?>" method="POST" class="row g-2">
                <?= csrf_field() ?>
                <div class="col-md-3">
                    <select name="order_type" class="form-select form-select-sm" required>
                        <option value="lab">فحص مختبر (Lab)</option>
                        <option value="radiology">تصوير أشعة (X-Ray/CT)</option>
                        <option value="procedure">إجراء تمريضي</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="text" name="order_description" class="form-control form-control-sm" placeholder="مثال: CBC, Malaria BF, X-Ray Chest..." required>
                </div>
                <div class="col-md-3">
                    <input type="text" name="clinical_indication" class="form-control form-control-sm" placeholder="دواعي الفحص...">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-sm btn-hms-primary w-100">طلب الفحص</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function calcBmiLive() {
        const w = parseFloat(document.getElementById('weight_kg').value);
        const h = parseFloat(document.getElementById('height_cm').value);
        if (w > 0 && h > 0) {
            const hm = h / 100.0;
            const bmi = (w / (hm * hm)).toFixed(1);
            document.getElementById('liveBmiText').innerText = 'مؤشر كتلة الجسم التقديري: ' + bmi + ' kg/m²';
        }
    }

    function addMedicineRow() {
        const container = document.getElementById('medicineRowsContainer');
        const firstRow = container.querySelector('.medicine-row');
        const clone = firstRow.cloneNode(true);
        // Clear inputs in clone
        clone.querySelectorAll('input').forEach(input => {
            if (input.name.includes('dosage')) input.value = 'قرص واحد';
            else if (input.name.includes('duration_days')) input.value = '5';
            else if (input.name.includes('total_quantity')) input.value = '10';
            else input.value = '';
        });
        container.appendChild(clone);
    }
</script>
