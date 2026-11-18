<div class="row g-4 justify-content-center">
    <div class="col-lg-10">
        <div class="card card-hms p-0 bg-white overflow-hidden shadow-sm">
            <div class="card-header bg-danger text-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 d-flex align-items-center gap-2">
                    <i data-lucide="ambulance" style="width: 22px; height: 22px;"></i>
                    استقبال وتسجيل حالة طوارئ وفرز سريري (Triage Registration)
                </h5>
                <a href="<?= url('/emergency') ?>" class="btn btn-sm btn-outline-light">رجوع للقائمة</a>
            </div>

            <form action="<?= url('/emergency') ?>" method="POST" class="p-4">
                <?= csrf_field() ?>

                <div class="row g-3">
                    <!-- Patient Selection or Temporary John Doe -->
                    <div class="col-12">
                        <h6 class="fw-bold text-navy border-bottom pb-2 mb-3">1. بيانات هوية المريض والوصول</h6>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">مريض مسجل مسبقاً في السجلات:</label>
                        <select name="patient_id" class="form-select">
                            <option value="">-- اختر مريض مسجل (أو اتركه فارغاً لحالة طوارئ غير مسجلة) --</option>
                            <?php foreach ($patients as $p): ?>
                                <option value="<?= $p['id'] ?>">
                                    <?= e($p['first_name'] . ' ' . $p['second_name'] . ' ' . $p['last_name']) ?> (<?= e($p['mrn']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">أو اسم تعريفي مؤقت (للحالات مجهولة الهوية / Trauma):</label>
                        <input type="text" name="temporary_patient_name" class="form-control" placeholder="مثال: مجهول - حادث سير بالخرطوم">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">طريقة الوصول (Arrival Mode):</label>
                        <select name="arrival_mode" class="form-select" required>
                            <option value="walk_in">مشياً (Walk-in)</option>
                            <option value="ambulance">سيارة إسعاف (Ambulance)</option>
                            <option value="police">الشرطة / النجدة (Police)</option>
                            <option value="transfer">محول من مشفى آخر (Transfer)</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">الطبيب المناوب المستلم:</label>
                        <select name="attending_doctor_id" class="form-select">
                            <option value="">-- اختياري (يحدد لاحقاً) --</option>
                            <?php foreach ($doctors as $d): ?>
                                <option value="<?= $d['id'] ?>">د. <?= e($d['employee_name'] ?? 'طبيب') ?> (<?= e($d['specialty_name'] ?? '') ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-danger">مستوى الفرز السريري (Triage Level): *</label>
                        <select name="triage_level" class="form-select border-danger fw-bold" required>
                            <option value="red" class="bg-danger text-white">🔴 أحمر (Red) - إنعاش فوري مهدد للحياة</option>
                            <option value="orange" class="bg-warning text-dark">🟠 برتقالي (Orange) - حالة حرجة جداً</option>
                            <option value="yellow" selected class="bg-info text-dark">🟡 أصفر (Yellow) - حالة عاجلة</option>
                            <option value="green" class="bg-success text-white">🟢 أخضر (Green) - مستقر / أقل إلحاحاً</option>
                            <option value="blue" class="bg-secondary text-white">🔵 أزرق (Blue) - غير عاجل</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">الشكوى الرئيسية وسبب الحضور: *</label>
                        <textarea name="chief_complaint" class="form-control" rows="2" placeholder="وصف موجز للأعراض، الإصابات، أو سبب الإسعاف..." required></textarea>
                    </div>

                    <!-- Triage Assessment Data -->
                    <div class="col-12 mt-4">
                        <h6 class="fw-bold text-navy border-bottom pb-2 mb-3">2. تقييم الفرز المبدئي والعلامات الحيوية (ABC & Vitals)</h6>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">مجرى الهواء (Airway):</label>
                        <select name="airway_status" class="form-select">
                            <option value="clear">سليم ومفتوح (Clear)</option>
                            <option value="threatened">مهدد بالانسداد (Threatened)</option>
                            <option value="obstructed">مسدود (Obstructed)</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">التنفس (Breathing):</label>
                        <select name="breathing_status" class="form-select">
                            <option value="normal">طبيعي (Normal)</option>
                            <option value="dyspnea">صعوبة تنفس (Dyspnea)</option>
                            <option value="apnea">توقف تنفس (Apnea)</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">الدورة الدموية (Circulation):</label>
                        <select name="circulation_status" class="form-select">
                            <option value="stable">مستقرة (Stable)</option>
                            <option value="weak">نبض ضعيف وخيطي (Weak)</option>
                            <option value="pulseless">انعدام النبض (Pulseless)</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">مقياس غلاسكو للوعي (GCS 3-15):</label>
                        <input type="number" name="gcs_score" class="form-control" min="3" max="15" placeholder="15">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">الضغط الانقباضي:</label>
                        <input type="number" name="systolic_bp" class="form-control" placeholder="120">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">الضغط الانبساطي:</label>
                        <input type="number" name="diastolic_bp" class="form-control" placeholder="80">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">معدل النبض (bpm):</label>
                        <input type="number" name="pulse_rate" class="form-control" placeholder="75">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">تشبع الأكسجين (%):</label>
                        <input type="number" name="oxygen_saturation" class="form-control" placeholder="98">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">الحرارة (°C):</label>
                        <input type="number" step="0.1" name="temperature" class="form-control" placeholder="37.0">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">مقياس الألم (1-10):</label>
                        <input type="number" name="pain_score" class="form-control" min="0" max="10" placeholder="5">
                    </div>

                    <div class="col-12">
                        <label class="form-label">ملاحظات ممرض الفرز وتوجيهات الطوارئ:</label>
                        <input type="text" name="triage_notes" class="form-control" placeholder="مثال: تم إدخاله لغرفة الإنعاش Resus 1 وتأمين أكسجين">
                    </div>

                    <div class="col-12 mt-4 text-end">
                        <button type="submit" class="btn btn-danger px-5 py-2 fw-bold shadow-sm">
                            حفظ وتسجيل الحالة في طابور الطوارئ
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
