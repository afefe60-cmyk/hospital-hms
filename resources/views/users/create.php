<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold text-navy mb-0 d-flex align-items-center gap-2">
                <i data-lucide="user-plus" class="text-green" style="width: 28px; height: 28px;"></i>
                إضافة مستخدم جديد
            </h3>
            <a href="<?= url('/users') ?>" class="btn btn-outline-secondary btn-sm">
                <i data-lucide="arrow-right" style="width: 16px; height: 16px; display:inline-block; vertical-align:text-bottom;"></i>
                العودة للقائمة
            </a>
        </div>

        <div class="card card-hms p-4 bg-white">
            <form action="<?= url('/users') ?>" method="POST">
                <?= csrf_field() ?>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="full_name" class="form-label fw-semibold">الاسم الكامل <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="full_name" name="full_name" value="<?= e(old('full_name')) ?>" required placeholder="مثال: د. أحمد محمد علي">
                    </div>
                    <div class="col-md-6">
                        <label for="username" class="form-label fw-semibold">اسم الدخول الفريد <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="username" name="username" value="<?= e(old('username')) ?>" required placeholder="مثال: drahmed">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="email" class="form-label fw-semibold">البريد الإلكتروني <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= e(old('email')) ?>" required placeholder="name@hospital.sd">
                    </div>
                    <div class="col-md-6">
                        <label for="phone" class="form-label fw-semibold">رقم الهاتف</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="<?= e(old('phone')) ?>" placeholder="+249 9...">
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="password" class="form-label fw-semibold">كلمة المرور الابتدائية <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password" name="password" minlength="8" required placeholder="8 أحرف كحد أدنى">
                    </div>
                    <div class="col-md-6">
                        <label for="user_type" class="form-label fw-semibold">نوع الحساب</label>
                        <select class="form-select" id="user_type" name="user_type">
                            <option value="staff" selected>كادر وظيفي (Staff)</option>
                            <option value="patient">مريض (Patient Portal)</option>
                        </select>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold d-block mb-2">الأدوار والمجموعات الوظيفية (Roles)</label>
                    <div class="row g-2 border rounded p-3 bg-light">
                        <?php foreach ($roles as $role): ?>
                            <div class="col-md-4 col-sm-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="roles[]" value="<?= $role['id'] ?>" id="role_<?= $role['id'] ?>">
                                    <label class="form-check-label fw-semibold text-navy" for="role_<?= $role['id'] ?>">
                                        <?= e($role['display_name_ar']) ?>
                                    </label>
                                    <small class="d-block text-muted" style="font-size: 0.75rem;"><?= e($role['name']) ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                    <label class="form-check-label fw-semibold" for="is_active">تفعيل الحساب فوراً للعمل</label>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="<?= url('/users') ?>" class="btn btn-outline-secondary">إلغاء</a>
                    <button type="submit" class="btn btn-hms-primary px-4">
                        <i data-lucide="check" style="width: 18px; height: 18px; display:inline-block; vertical-align:text-bottom;"></i>
                        حفظ وإنشاء الحساب
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
