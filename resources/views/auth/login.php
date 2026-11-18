<div class="row justify-content-center">
    <div class="col-12 d-flex justify-content-center">
        <div class="auth-card">
            <div class="auth-header">
                <div class="d-inline-flex p-3 rounded-circle mb-3" style="background: rgba(22, 133, 109, 0.2);">
                    <i data-lucide="activity" style="width: 40px; height: 40px; color: #6EE7B7;"></i>
                </div>
                <h4 class="fw-bold mb-1"><?= e(config('app.name')) ?></h4>
                <small class="opacity-75">بوابة تسجيل الدخول الآمن للكوادر الطبية والإدارية</small>
            </div>

            <div class="p-4 p-md-5">
                <!-- Flash Messages -->
                <?php if (\App\Core\Session::hasFlash('error')): ?>
                    <div class="alert alert-danger d-flex align-items-center gap-2 mb-4" role="alert">
                        <i data-lucide="alert-circle" style="width: 20px; height: 20px; flex-shrink:0;"></i>
                        <div><?= e(\App\Core\Session::getFlash('error')) ?></div>
                    </div>
                <?php endif; ?>

                <?php if (\App\Core\Session::hasFlash('info')): ?>
                    <div class="alert alert-info d-flex align-items-center gap-2 mb-4" role="alert">
                        <i data-lucide="info" style="width: 20px; height: 20px; flex-shrink:0;"></i>
                        <div><?= e(\App\Core\Session::getFlash('info')) ?></div>
                    </div>
                <?php endif; ?>

                <form action="<?= url('/login') ?>" method="POST" autocomplete="off">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="username" class="form-label fw-semibold">اسم المستخدم أو البريد الإلكتروني</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted">
                                <i data-lucide="user" style="width: 18px; height: 18px;"></i>
                            </span>
                            <input type="text"
                                   class="form-control"
                                   id="username"
                                   name="username"
                                   value="<?= e(old('username')) ?>"
                                   required
                                   autofocus
                                   placeholder="أدخل اسم المستخدم (مثال: admin)">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label fw-semibold">كلمة المرور</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted">
                                <i data-lucide="lock" style="width: 18px; height: 18px;"></i>
                            </span>
                            <input type="password"
                                   class="form-control"
                                   id="password"
                                   name="password"
                                   required
                                   placeholder="••••••••">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-hms-primary d-flex align-items-center justify-content-center gap-2">
                        <i data-lucide="log-in" style="width: 20px; height: 20px;"></i>
                        تسجيل الدخول
                    </button>
                </form>

                <div class="mt-4 pt-3 border-top text-center">
                    <small class="text-muted d-block">
                        البيانات الافتراضية للتثبيت الأولي:
                    </small>
                    <code class="d-inline-block mt-1 p-1 bg-light rounded text-dark">admin / Password@123</code>
                </div>
            </div>
        </div>
    </div>
</div>
