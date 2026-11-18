<div class="row g-4">
    <!-- Welcome Header Banner -->
    <div class="col-12">
        <div class="card card-hms p-4 text-white" style="background: linear-gradient(135deg, var(--hms-navy) 0%, #1a4f78 100%);">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <h2 class="fw-bold mb-2 d-flex align-items-center gap-2">
                        <i data-lucide="shield-check" style="width: 32px; height: 32px; color: #6EE7B7;"></i>
                        <?= __('messages.welcome_title') ?>
                    </h2>
                    <p class="mb-0 text-light opacity-75 fs-6"><?= __('messages.welcome_subtitle') ?></p>
                </div>
                <div>
                    <span class="badge bg-green px-3 py-2 fs-6 d-inline-flex align-items-center gap-1">
                        <i data-lucide="check-circle" style="width: 18px; height: 18px;"></i>
                        <?= __('messages.phase_1_status') ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Health Check Status Cards -->
    <div class="col-md-3 col-sm-6">
        <div class="card card-hms p-3 bg-white h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="p-3 rounded-3 <?= $info['db_status'] ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' ?>">
                    <i data-lucide="database" style="width: 28px; height: 28px;"></i>
                </div>
                <div>
                    <small class="text-muted d-block"><?= __('messages.operational_readiness') ?></small>
                    <span class="fw-bold fs-6 <?= $info['db_status'] ? 'text-success' : 'text-danger' ?>">
                        <?= $info['db_status'] ? __('messages.database_connected') : __('messages.database_failed') ?>
                    </span>
                    <small class="d-block text-secondary mt-1"><?= e($info['db_version']) ?></small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="card card-hms p-3 bg-white h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="p-3 rounded-3 bg-primary-subtle text-primary">
                    <i data-lucide="cpu" style="width: 28px; height: 28px;"></i>
                </div>
                <div>
                    <small class="text-muted d-block"><?= __('messages.php_version') ?></small>
                    <span class="fw-bold fs-6 text-navy">PHP <?= e($info['php_version']) ?></span>
                    <small class="d-block text-secondary mt-1">Native MVC Engine</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="card card-hms p-3 bg-white h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="p-3 rounded-3 bg-warning-subtle text-warning">
                    <i data-lucide="clock" style="width: 28px; height: 28px;"></i>
                </div>
                <div>
                    <small class="text-muted d-block"><?= __('messages.server_time') ?></small>
                    <span class="fw-bold fs-6 text-navy"><?= date('H:i:s') ?></span>
                    <small class="d-block text-secondary mt-1"><?= e($info['timezone']) ?></small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="card card-hms p-3 bg-white h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="p-3 rounded-3 bg-info-subtle text-info">
                    <i data-lucide="coins" style="width: 28px; height: 28px;"></i>
                </div>
                <div>
                    <small class="text-muted d-block"><?= __('messages.currency') ?></small>
                    <span class="fw-bold fs-6 text-navy"><?= e($info['currency']) ?> (الجنيه السوداني)</span>
                    <small class="d-block text-secondary mt-1"><?= format_currency(1000) ?></small>
                </div>
            </div>
        </div>
    </div>

    <!-- Architectural Components & Phase Status -->
    <div class="col-lg-8">
        <div class="card card-hms p-4 bg-white">
            <h5 class="fw-bold text-navy mb-3 d-flex align-items-center gap-2">
                <i data-lucide="layers" class="text-green" style="width: 22px; height: 22px;"></i>
                مكونات النواة المعمارية المفعلة (Core Architecture Status)
            </h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>المكون الفني (Component)</th>
                            <th>المسار البرمجي (Namespace / Path)</th>
                            <th>الحالة (Status)</th>
                            <th>الوظيفة الأساسية</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="fw-bold">Router & Dispatcher</td>
                            <td><code>App\Core\Router</code></td>
                            <td><span class="badge bg-success">يعمل بنجاح</span></td>
                            <td>توجيه المسارات، دعم Regex Parameters، وسلسلة Middleware</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Database Singleton PDO</td>
                            <td><code>App\Core\Database</code></td>
                            <td><span class="badge bg-success">متصل بنجاح</span></td>
                            <td>PDO Prepared Statements، Transactions ذرية</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Request & Response</td>
                            <td><code>App\Core\Request</code> / <code>Response</code></td>
                            <td><span class="badge bg-success">يعمل بنجاح</span></td>
                            <td>تجريد الطلبات والاستجابات، معالجة JSON والتعقيم</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Session & CSRF Guard</td>
                            <td><code>App\Core\Session</code> / <code>CsrfMiddleware</code></td>
                            <td><span class="badge bg-success">مفعل وآمن</span></td>
                            <td>حماية من هجمات التزوير، تجديد المعرفات، Flash Messages</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">View Engine & Layouts</td>
                            <td><code>App\Core\View</code></td>
                            <td><span class="badge bg-success">يعمل بنجاح</span></td>
                            <td>دعم القوالب، التعريب (AR/EN)، والهروب الآمن XSS</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Active Record Base Model</td>
                            <td><code>App\Core\Model</code></td>
                            <td><span class="badge bg-success">جاهز للربط</span></td>
                            <td>إدارة استعلامات CRUD، الفلترة، والترقيم الموزع</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Next Phase Roadmap Callout -->
    <div class="col-lg-4">
        <div class="card card-hms p-4 bg-white h-100">
            <h5 class="fw-bold text-navy mb-3 d-flex align-items-center gap-2">
                <i data-lucide="milestone" class="text-accent" style="width: 22px; height: 22px;"></i>
                خارطة التنفيذ المعتمدة
            </h5>
            <ul class="list-group list-group-flush mb-4">
                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <div>
                        <strong class="d-block text-success">✓ PHASE 0</strong>
                        <small class="text-muted">التحليل، المخطط ERD، والتصميم المعماري</small>
                    </div>
                    <span class="badge bg-success rounded-pill">معتمد</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <div>
                        <strong class="d-block text-primary">✓ PHASE 1</strong>
                        <small class="text-muted">إعداد المشروع، MVC Core، وقاعدة البيانات</small>
                    </div>
                    <span class="badge bg-primary rounded-pill">مكتمل الآن</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <div>
                        <strong class="d-block text-secondary">⏳ PHASE 2 (التالي)</strong>
                        <small class="text-muted">المصادقة، المستخدمون، الصلاحيات، التدقيق</small>
                    </div>
                    <span class="badge bg-secondary rounded-pill">قيد الانتظار</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <div>
                        <strong class="d-block text-muted">PHASE 3</strong>
                        <small class="text-muted">الهيكل التنظيمي، الأقسام، الأطباء، العيادات</small>
                    </div>
                    <span class="badge bg-light text-dark rounded-pill">مجدول</span>
                </li>
            </ul>

            <div class="alert alert-info d-flex align-items-center gap-2 mb-0" role="alert">
                <i data-lucide="info" style="width: 20px; height: 20px; flex-shrink: 0;"></i>
                <small class="fw-semibold"><?= __('messages.next_phase') ?></small>
            </div>
        </div>
    </div>
</div>
