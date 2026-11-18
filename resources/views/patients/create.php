<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-navy mb-1 d-flex align-items-center gap-2">
                    <i data-lucide="user-plus" class="text-green" style="width: 28px; height: 28px;"></i>
                    فتح ملف مريض جديد (Patient Registration)
                </h3>
                <p class="text-muted mb-0">توليد الرقم الطبي الموحد والتحقق من عدم تكرار السجلات</p>
            </div>
            <a href="<?= url('/patients') ?>" class="btn btn-outline-secondary btn-sm">
                <i data-lucide="arrow-right" style="width: 16px; height: 16px; display:inline-block; vertical-align:text-bottom;"></i>
                العودة لدليل المرضى
            </a>
        </div>

        <div class="card card-hms p-4 bg-white">
            <form action="<?= url('/patients') ?>" method="POST">
                <?= csrf_field() ?>

                <!-- MRN Banner -->
                <div class="alert alert-info d-flex align-items-center justify-content-between mb-4">
                    <div class="d-flex align-items-center gap-2">
                        <i data-lucide="fingerprint" style="width: 24px; height: 24px;"></i>
                        <span class="fw-bold">الرقم الطبي الموحد المتولد تلقائياً (MRN):</span>
                    </div>
                    <span class="badge bg-navy fs-5 font-monospace px-3 py-2"><?= e($nextMrn) ?></span>
                </div>

                <!-- Section 1: Demographics -->
                <h5 class="fw-bold text-navy border-bottom pb-2 mb-3">1. البيانات الشخصية والديموغرافية</h5>
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">الاسم الأول <span class="text-danger">*</span></label>
                        <input type="text" name="first_name" class="form-control" value="<?= e(old('first_name')) ?>" required placeholder="مثال: محمد">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">اسم الأب <span class="text-danger">*</span></label>
                        <input type="text" name="second_name" class="form-control" value="<?= e(old('second_name')) ?>" required placeholder="مثال: أحمد">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">اسم الجد</label>
                        <input type="text" name="third_name" class="form-control" value="<?= e(old('third_name')) ?>" placeholder="مثال: علي">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">اسم العائلة / اللقب <span class="text-danger">*</span></label>
                        <input type="text" name="last_name" class="form-control" value="<?= e(old('last_name')) ?>" required placeholder="مثال: البشير">
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">الرقم الوطني السوداني</label>
                        <input type="text" name="national_id" class="form-control" value="<?= e(old('national_id')) ?>" placeholder="1100..." maxlength="20">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">تاريخ الميلاد <span class="text-danger">*</span></label>
                        <input type="date" name="date_of_birth" class="form-control" value="<?= e(old('date_of_birth')) ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">النوع <span class="text-danger">*</span></label>
                        <select name="gender" class="form-select" required>
                            <option value="male" <?= old('gender') === 'male' ? 'selected' : '' ?>>ذكر (Male)</option>
                            <option value="female" <?= old('gender') === 'female' ? 'selected' : '' ?>>أنثى (Female)</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">فصيلة الدم</label>
                        <select name="blood_group" class="form-select">
                            <option value="unknown">غير محدد (Unknown)</option>
                            <option value="A+">A+</option>
                            <option value="A-">A-</option>
                            <option value="B+">B+</option>
                            <option value="B-">B-</option>
                            <option value="AB+">AB+</option>
                            <option value="AB-">AB-</option>
                            <option value="O+">O+</option>
                            <option value="O-">O-</option>
                        </select>
                    </div>
                </div>

                <!-- Section 2: Contact & Address -->
                <h5 class="fw-bold text-navy border-bottom pb-2 mb-3">2. بيانات الاتصال والعنوان في السودان</h5>
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">الهاتف الأساسي <span class="text-danger">*</span></label>
                        <input type="text" name="phone_primary" class="form-control" value="<?= e(old('phone_primary')) ?>" placeholder="+249 9..." required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">هاتف إضافي / واتساب</label>
                        <input type="text" name="phone_secondary" class="form-control" value="<?= e(old('phone_secondary')) ?>" placeholder="+249...">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">الحالة الاجتماعية</label>
                        <select name="marital_status" class="form-select">
                            <option value="single">أعزب / عزباء</option>
                            <option value="married">متزوج / متزوجة</option>
                            <option value="divorced">مطلق / مطلقة</option>
                            <option value="widowed">أرمل / أرملة</option>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">الولاية <span class="text-danger">*</span></label>
                        <select name="address_state" class="form-select" required>
                            <option value="ولاية الخرطوم">ولاية الخرطوم</option>
                            <option value="ولاية الجزيرة">ولاية الجزيرة</option>
                            <option value="ولاية نهر النيل">ولاية نهر النيل</option>
                            <option value="ولاية البحر الأحمر">ولاية البحر الأحمر</option>
                            <option value="الولاية الشمالية">الولاية الشمالية</option>
                            <option value="ولاية كسلا">ولاية كسلا</option>
                            <option value="ولاية القضارف">ولاية القضارف</option>
                            <option value="ولاية سنار">ولاية سنار</option>
                            <option value="ولاية النيل الأبيض">ولاية النيل الأبيض</option>
                            <option value="ولاية النيل الأزرق">ولاية النيل الأزرق</option>
                            <option value="ولايات شمال/جنوب كردفان">ولايات شمال/جنوب كردفان</option>
                            <option value="ولايات دارفور">ولايات دارفور</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">المدينة / الحي / المربع <span class="text-danger">*</span></label>
                        <input type="text" name="address_city" class="form-control" value="<?= e(old('address_city')) ?>" placeholder="مثال: الخرطوم - حي الرياض، شارع الستين" required>
                    </div>
                </div>

                <!-- Section 3: Emergency Contact -->
                <h5 class="fw-bold text-navy border-bottom pb-2 mb-3">3. جهة الاتصال في حالات الطوارئ</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">اسم جهة الطوارئ <span class="text-danger">*</span></label>
                        <input type="text" name="emergency_contact_name" class="form-control" value="<?= e(old('emergency_contact_name')) ?>" required placeholder="مثال: عثمان علي (الأخ)">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">هاتف جهة الطوارئ <span class="text-danger">*</span></label>
                        <input type="text" name="emergency_contact_phone" class="form-control" value="<?= e(old('emergency_contact_phone')) ?>" required placeholder="+249 9...">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">صلة القرابة</label>
                        <input type="text" name="emergency_contact_relation" class="form-control" value="<?= e(old('emergency_contact_relation')) ?>" placeholder="مثال: زوج، أب، أخ، ابن">
                    </div>
                </div>

                <!-- Section 4: Initial Clinical Allergies -->
                <h5 class="fw-bold text-navy border-bottom pb-2 mb-3">4. التحسس الطبي الأولي (Allergies) - اختياري</h5>
                <div class="row g-3 mb-4 p-3 border rounded bg-light-subtle">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">هل يعاني المريض من تحسس دوائي أو غذائي؟</label>
                        <input type="text" name="initial_allergy" class="form-control" placeholder="مثال: Penicillin, Aspirin, بيض، فول سوداني">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">نوع الحساسية</label>
                        <select name="allergy_type" class="form-select">
                            <option value="drug">دوائية (Drug Allergy)</option>
                            <option value="food">غذائية (Food Allergy)</option>
                            <option value="environmental">بيئية (Environmental)</option>
                            <option value="other">أخرى (Other)</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">درجة الخطورة</label>
                        <select name="allergy_severity" class="form-select">
                            <option value="life_threatening" class="text-danger fw-bold">مهددة للحياة (Life-Threatening)</option>
                            <option value="severe" selected>شديدة (Severe)</option>
                            <option value="moderate">متوسطة (Moderate)</option>
                            <option value="mild">خفيفة (Mild)</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="<?= url('/patients') ?>" class="btn btn-outline-secondary">إلغاء</a>
                    <button type="submit" class="btn btn-hms-primary px-4 py-2">
                        <i data-lucide="check" style="width: 18px; height: 18px; display:inline-block; vertical-align:text-bottom;"></i>
                        فتح الملف وحفظ البيانات
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
