<div class="row g-4 justify-content-center">
    <div class="col-lg-8">
        <!-- Profile Card -->
        <div class="card card-hms p-4 bg-white mb-4">
            <div class="d-flex align-items-center gap-3 border-bottom pb-3 mb-4">
                <div class="p-3 rounded-circle bg-navy text-white">
                    <i data-lucide="user-check" style="width: 32px; height: 32px;"></i>
                </div>
                <div>
                    <h4 class="fw-bold mb-1 text-navy"><?= e($user['full_name']) ?></h4>
                    <span class="badge bg-green"><?= e($user['user_type'] === 'staff' ? 'كادر وظيفي' : 'مريض') ?></span>
                    <span class="text-muted ms-2">@<?= e($user['username']) ?></span>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-sm-6">
                    <label class="text-muted small d-block">البريد الإلكتروني</label>
                    <span class="fw-semibold"><?= e($user['email']) ?></span>
                </div>
                <div class="col-sm-6">
                    <label class="text-muted small d-block">رقم الهاتف</label>
                    <span class="fw-semibold"><?= e($user['phone'] ?? 'غير محدد') ?></span>
                </div>
                <div class="col-sm-6">
                    <label class="text-muted small d-block">تاريخ التسجيل</label>
                    <span class="fw-semibold"><?= e($user['created_at']) ?></span>
                </div>
                <div class="col-sm-6">
                    <label class="text-muted small d-block">آخر تسجيل دخول</label>
                    <span class="fw-semibold"><?= e($user['last_login_at'] ?? 'الجلسة الأولى') ?></span>
                </div>
                <div class="col-12 mt-3">
                    <label class="text-muted small d-block mb-1">الأدوار والمجموعات الممنوحة</label>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($roles as $r): ?>
                            <span class="badge bg-navy py-2 px-3"><?= e($r['display_name_ar']) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Change Password Card -->
        <div class="card card-hms p-4 bg-white">
            <h5 class="fw-bold text-navy mb-3 d-flex align-items-center gap-2">
                <i data-lucide="key" class="text-accent" style="width: 22px; height: 22px;"></i>
                تغيير كلمة المرور
            </h5>

            <form action="<?= url('/profile/password') ?>" method="POST">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label for="old_password" class="form-label fw-semibold">كلمة المرور الحالية</label>
                    <input type="password" class="form-control" id="old_password" name="old_password" required>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="new_password" class="form-label fw-semibold">كلمة المرور الجديدة</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" minlength="8" required placeholder="8 أحرف على الأقل">
                    </div>
                    <div class="col-md-6">
                        <label for="confirm_password" class="form-label fw-semibold">تأكيد كلمة المرور الجديدة</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" minlength="8" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-hms-primary">
                    <i data-lucide="save" style="width: 18px; height: 18px; display:inline-block; vertical-align:text-bottom;"></i>
                    حفظ كلمة المرور الجديدة
                </button>
            </form>
        </div>
    </div>
</div>
