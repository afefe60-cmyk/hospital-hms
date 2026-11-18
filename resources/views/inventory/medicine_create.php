<div class="row g-4 justify-content-center">
    <div class="col-lg-8">
        <div class="card card-hms p-0 bg-white overflow-hidden shadow-sm">
            <div class="card-header bg-navy text-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 d-flex align-items-center gap-2">
                    <i data-lucide="plus-circle" style="width: 22px; height: 22px; color: #6EE7B7;"></i>
                    إضافة صنف دوائي ومستحضر طبي جديد (New Medicine)
                </h5>
                <a href="<?= url('/inventory/medicines') ?>" class="btn btn-sm btn-outline-light">رجوع للقائمة</a>
            </div>

            <form action="<?= url('/inventory/medicines') ?>" method="POST" class="p-4">
                <?= csrf_field() ?>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">كود الصنف الدوائي: *</label>
                        <input type="text" name="code" class="form-control font-monospace" placeholder="مثال: MED-AMX-500" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">الاسم التجاري (Commercial): *</label>
                        <input type="text" name="commercial_name" class="form-control" placeholder="مثال: Amoxil 500mg" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">الاسم العلمي (Generic): *</label>
                        <input type="text" name="scientific_name" class="form-control" placeholder="مثال: Amoxicillin Trihydrate" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">التصنيف الدوائي: *</label>
                        <select name="category_id" class="form-select" required>
                            <option value="">-- اختر التصنيف --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= e($cat['name_ar']) ?> (<?= e($cat['name_en']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">الشكل الصيدلاني (Form):</label>
                        <select name="dosage_form" class="form-select">
                            <option value="tablet">أقراص (Tablet)</option>
                            <option value="capsule">كبسولات (Capsule)</option>
                            <option value="syrup">شراب (Syrup)</option>
                            <option value="injection">حقن / فيال (Injection/Vial)</option>
                            <option value="infusion">محلول وريدي (Infusion)</option>
                            <option value="drops">قطرات (Drops)</option>
                            <option value="ointment">مرهم / كريم (Ointment)</option>
                            <option value="inhaler">بخاخ استنشاق (Inhaler)</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">التركيز (Strength): *</label>
                        <input type="text" name="strength" class="form-control" placeholder="مثال: 500mg أو 250mg/5ml" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">وحدة الصرف (Dispense Unit):</label>
                        <input type="text" name="unit" class="form-control" value="strip" placeholder="مثال: strip, box, bottle, vial">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-danger">حد التنبيه الأدنى (Min Alert):</label>
                        <input type="number" name="minimum_stock_alert" class="form-control" value="20" min="1">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-primary">مستوى إعادة الطلب (Reorder):</label>
                        <input type="number" name="reorder_level" class="form-control" value="50" min="1">
                    </div>

                    <div class="col-12 mt-3">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="is_prescription_required" id="rxReq" value="1" checked>
                            <label class="form-check-label" for="rxReq">يتطلب روشتة طبية للصرف (Rx Required)</label>
                        </div>
                        <div class="form-check form-check-inline ms-3">
                            <input class="form-check-input" type="checkbox" name="is_narcotic" id="narcotic" value="1">
                            <label class="form-check-label text-danger fw-bold" for="narcotic">دواء مقيد / مخدر خاضع للرقابة (Controlled / Narcotic)</label>
                        </div>
                    </div>

                    <div class="col-12 mt-4 text-end">
                        <button type="submit" class="btn btn-primary px-5 py-2 fw-bold shadow-sm">
                            حفظ الصنف الدوائي
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
