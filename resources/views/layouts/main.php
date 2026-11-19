<?php
$locale = \App\Core\Session::get('locale', config('app.locale', 'ar'));
$isRtl = ($locale === 'ar');
$dir = $isRtl ? 'rtl' : 'ltr';
$user = current_user();

$hospitalName = \App\Models\SystemSetting::get('hospital_name_ar', config('app.name', 'مستشفى الأمل التخصصي'));
$bankakAccount = \App\Models\SystemSetting::get('bankak_account_number', '1820499');
$emergencyPhone = \App\Models\SystemSetting::get('emergency_hotline', '999');
?>
<!DOCTYPE html>
<html lang="<?= e($locale) ?>" dir="<?= e($dir) ?>" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' | ' : '' ?><?= e(config('app.name')) ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <?php if ($isRtl): ?>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <?php else: ?>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <?php endif; ?>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        :root {
            --hms-navy: #173B59;
            --hms-navy-dark: #10293D;
            --hms-green: #16856D;
            --hms-green-dark: #126e5a;
            --hms-light-bg: #F4F7FA;
            --hms-gray: #E2E8F0;
            --hms-accent: #D49A36;
            --hms-sidebar-width: 275px;
            --hms-sidebar-collapsed-width: 74px;
            --hms-topbar-height: 64px;
            --hms-font: <?= $isRtl ? "'Cairo', sans-serif" : "'Inter', -apple-system, sans-serif" ?>;
        }

        body {
            font-family: var(--hms-font);
            background-color: var(--hms-light-bg);
            color: #2D3748;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        [data-bs-theme="dark"] {
            --hms-light-bg: #0F172A;
            --hms-navy: #1E293B;
            --hms-navy-dark: #0F172A;
            --hms-gray: #334155;
            background-color: var(--hms-light-bg);
            color: #E2E8F0;
        }

        /* ---------------- App Layout Containers ---------------- */
        #app-layout {
            display: flex;
            min-height: 100vh;
            width: 100%;
        }

        /* ---------------- Sidebar Styling ---------------- */
        #hms-sidebar {
            width: var(--hms-sidebar-width);
            background-color: var(--hms-navy-dark);
            color: #F8FAFC;
            position: fixed;
            top: 0;
            bottom: 0;
            <?= $isRtl ? 'right: 0;' : 'left: 0;' ?>
            z-index: 1040;
            display: flex;
            flex-direction: column;
            box-shadow: <?= $isRtl ? '-3px 0 15px rgba(0,0,0,0.1)' : '3px 0 15px rgba(0,0,0,0.1)' ?>;
            transition: width 0.24s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }

        .sidebar-brand {
            min-height: 72px;
            display: flex;
            align-items: center;
            padding: 12px 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            background-color: rgba(0, 0, 0, 0.15);
            text-decoration: none;
            color: #FFFFFF !important;
            gap: 12px;
            flex-shrink: 0;
        }

        .sidebar-brand .brand-icon-box {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--hms-green) 0%, #10b981 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .sidebar-brand .brand-title {
            font-weight: 700;
            font-size: 0.90rem;
            line-height: 1.35;
            white-space: normal;
            overflow: visible;
        }

        .sidebar-nav-container {
            flex-grow: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 12px 10px 28px;
        }

        /* Scrollbar */
        .sidebar-nav-container::-webkit-scrollbar {
            width: 5px;
        }
        .sidebar-nav-container::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 4px;
        }

        .sidebar-group-label {
            font-size: 0.72rem;
            text-transform: uppercase;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.45);
            padding: 14px 12px 6px;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }

        .nav-item-hms {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 9px 14px;
            color: rgba(255, 255, 255, 0.82) !important;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.88rem;
            margin-bottom: 2px;
            white-space: nowrap;
            transition: all 0.15s ease-in-out;
            position: relative;
        }

        .nav-item-hms:hover {
            color: #FFFFFF !important;
            background-color: rgba(255, 255, 255, 0.1);
        }

        .nav-item-hms.active {
            color: #FFFFFF !important;
            background: linear-gradient(90deg, rgba(22, 133, 109, 0.9) 0%, rgba(22, 133, 109, 0.6) 100%);
            box-shadow: 0 2px 6px rgba(22, 133, 109, 0.3);
        }

        .nav-item-hms i {
            flex-shrink: 0;
            width: 18px;
            height: 18px;
        }

        .sidebar-footer {
            padding: 12px 16px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            background-color: rgba(0, 0, 0, 0.15);
            flex-shrink: 0;
        }

        /* ---------------- Collapsed State (Mini-Sidebar) ---------------- */
        body.sidebar-collapsed #hms-sidebar {
            width: var(--hms-sidebar-collapsed-width);
        }

        body.sidebar-collapsed .sidebar-brand .brand-title,
        body.sidebar-collapsed .sidebar-brand .badge-hms,
        body.sidebar-collapsed .sidebar-group-label,
        body.sidebar-collapsed .nav-item-hms span,
        body.sidebar-collapsed .nav-item-hms .badge,
        body.sidebar-collapsed .sidebar-footer .user-details {
            display: none !important;
        }

        body.sidebar-collapsed .nav-item-hms {
            justify-content: center;
            padding: 12px 0;
        }

        /* ---------------- Main Content Canvas ---------------- */
        #hms-main-wrapper {
            flex-grow: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            <?= $isRtl ? 'margin-right: var(--hms-sidebar-width);' : 'margin-left: var(--hms-sidebar-width);' ?>
            transition: <?= $isRtl ? 'margin-right' : 'margin-left' ?> 0.24s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body.sidebar-collapsed #hms-main-wrapper {
            <?= $isRtl ? 'margin-right: var(--hms-sidebar-collapsed-width);' : 'margin-left: var(--hms-sidebar-collapsed-width);' ?>
        }

        /* ---------------- Sleek Topbar ---------------- */
        #hms-topbar {
            height: var(--hms-topbar-height);
            background-color: #FFFFFF;
            border-bottom: 1px solid var(--hms-gray);
            position: sticky;
            top: 0;
            z-index: 1030;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
        }

        [data-bs-theme="dark"] #hms-topbar {
            background-color: #1E293B;
            border-bottom-color: #334155;
        }

        .topbar-toggle-btn {
            background: transparent;
            border: 1px solid var(--hms-gray);
            color: #334155;
            width: 38px;
            height: 38px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .topbar-toggle-btn:hover {
            background-color: #F1F5F9;
            color: var(--hms-green);
        }
        [data-bs-theme="dark"] .topbar-toggle-btn {
            border-color: #334155;
            color: #CBD5E1;
        }
        [data-bs-theme="dark"] .topbar-toggle-btn:hover {
            background-color: #334155;
            color: #FFFFFF;
        }

        .topbar-search-btn {
            background-color: var(--hms-light-bg);
            border: 1px solid var(--hms-gray);
            color: #64748B;
            border-radius: 8px;
            padding: 7px 14px;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .topbar-search-btn:hover {
            border-color: var(--hms-green);
            color: var(--hms-navy);
            background-color: #FFFFFF;
        }
        [data-bs-theme="dark"] .topbar-search-btn {
            background-color: #0F172A;
            border-color: #334155;
            color: #94A3B8;
        }

        /* ---------------- Standard Component Styles ---------------- */
        .card-hms {
            border: 1px solid var(--hms-gray);
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(23, 59, 89, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .text-navy { color: var(--hms-navy) !important; }
        .text-green { color: var(--hms-green) !important; }
        .text-accent { color: var(--hms-accent) !important; }

        .bg-navy { background-color: var(--hms-navy) !important; color: #fff; }
        .bg-green { background-color: var(--hms-green) !important; color: #fff; }
        .bg-accent { background-color: var(--hms-accent) !important; color: #fff; }

        .btn-hms-primary {
            background-color: var(--hms-green);
            color: #FFFFFF;
            font-weight: 600;
            border: none;
            padding: 8px 18px;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        .btn-hms-primary:hover {
            background-color: var(--hms-green-dark);
            color: #FFFFFF;
        }

        .btn-hms-secondary {
            background-color: var(--hms-navy);
            color: #FFFFFF;
            font-weight: 600;
            border: none;
            padding: 8px 18px;
            border-radius: 8px;
        }
        .btn-hms-secondary:hover {
            background-color: var(--hms-navy-dark);
            color: #FFFFFF;
        }

        footer {
            margin-top: auto;
            background-color: #FFFFFF;
            border-top: 1px solid var(--hms-gray);
            padding: 14px 20px;
            font-size: 0.85rem;
        }
        [data-bs-theme="dark"] footer {
            background-color: #1E293B;
            border-color: #334155;
        }

        /* ---------------- Responsive Drawer for Mobile ---------------- */
        @media (max-width: 991.98px) {
            #hms-sidebar {
                <?= $isRtl ? 'right: calc(-1 * var(--hms-sidebar-width));' : 'left: calc(-1 * var(--hms-sidebar-width));' ?>
            }
            body.mobile-sidebar-open #hms-sidebar {
                <?= $isRtl ? 'right: 0;' : 'left: 0;' ?>
            }
            #hms-main-wrapper {
                <?= $isRtl ? 'margin-right: 0 !important;' : 'margin-left: 0 !important;' ?>
            }
            #sidebar-backdrop {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 1035;
            }
            body.mobile-sidebar-open #sidebar-backdrop {
                display: block;
            }
        }
    </style>
</head>
<body>

    <div id="app-layout">
        <!-- Backdrop for mobile drawer -->
        <div id="sidebar-backdrop"></div>

        <!-- ---------------- Modern Sidebar Navigation ---------------- -->
        <aside id="hms-sidebar">
            <!-- Sidebar Header / Brand -->
            <a href="<?= url('/') ?>" class="sidebar-brand">
                <div class="brand-icon-box shadow-sm">
                    <i data-lucide="activity" style="color: #FFFFFF; width: 22px; height: 22px;"></i>
                </div>
                <div class="brand-title">
                    <span class="fw-bold text-white d-block lh-sm mb-1"><?= e($hospitalName) ?></span>
                    <span class="text-light opacity-75 font-monospace fw-normal d-block" style="font-size: 0.68rem; letter-spacing: 0.5px;">Hospital HMS v2.0</span>
                </div>
            </a>

            <!-- Scrollable Navigation Items -->
            <div class="sidebar-nav-container">
                <!-- Group 1: اللوحة المركزية -->
                <div class="sidebar-group-label">القيادة والعمليات</div>
                <a href="<?= url('/') ?>" class="nav-item-hms <?= active_route('/') ?>" title="الرئيسية">
                    <i data-lucide="layout-dashboard" style="color: #6EE7B7;"></i>
                    <span>لوحة القيادة اليومية</span>
                </a>

                <?php if (auth()->check()): ?>
                    <!-- Group 2: الاستقبال والمرضى -->
                    <div class="sidebar-group-label">الاستقبال وسجلات المرضى</div>
                    <a href="<?= url('/reception') ?>" class="nav-item-hms <?= active_route('/reception') ?>" title="الاستقبال والمكتب الأمامي">
                        <i data-lucide="bell" style="color: #93C5FD;"></i>
                        <span>الاستقبال والمكتب الأمامي</span>
                    </a>
                    <a href="<?= url('/patients') ?>" class="nav-item-hms <?= active_route('/patients') ?>" title="سجل المرضى الموحد">
                        <i data-lucide="folder-heart" style="color: #60A5FA;"></i>
                        <span>سجل المرضى الموحد (MRN)</span>
                    </a>
                    <a href="<?= url('/appointments') ?>" class="nav-item-hms <?= active_route('/appointments') ?>" title="المواعيد والحجوزات">
                        <i data-lucide="calendar" style="color: #FCD34D;"></i>
                        <span>المواعيد والجدولة</span>
                    </a>
                    <a href="<?= url('/patients/create') ?>" class="nav-item-hms" title="فتح ملف مريض جديد">
                        <i data-lucide="user-plus" style="color: #34D399;"></i>
                        <span>فتح ملف جديد (+)</span>
                    </a>

                    <!-- Group 3: الخدمات السريرية والتنويم -->
                    <div class="sidebar-group-label">الخدمات السريرية والتنويم</div>
                    <a href="<?= url('/emr') ?>" class="nav-item-hms <?= active_route('/emr') ?>" title="عيادة الطبيب السريرية">
                        <i data-lucide="file-heart" style="color: #34D399;"></i>
                        <span>عيادة الطبيب EMR</span>
                    </a>
                    <a href="<?= url('/emergency') ?>" class="nav-item-hms <?= active_route('/emergency') ?>" title="الحوادث والطوارئ">
                        <i data-lucide="ambulance" style="color: #F87171;"></i>
                        <span>الحوادث والطوارئ ER</span>
                    </a>
                    <a href="<?= url('/admissions') ?>" class="nav-item-hms <?= active_route('/admissions') ?>" title="التنويم والأجنحة">
                        <i data-lucide="bed-double" style="color: #38BDF8;"></i>
                        <span>التنويم والأجنحة IPD</span>
                    </a>
                    <a href="<?= url('/nursing') ?>" class="nav-item-hms <?= active_route('/nursing') ?>" title="محطة التمريض MAR">
                        <i data-lucide="clipboard-check" style="color: #A78BFA;"></i>
                        <span>محطة التمريض والجرعات</span>
                    </a>
                    <a href="<?= url('/surgery') ?>" class="nav-item-hms <?= active_route('/surgery') ?>" title="العمليات الجراحية">
                        <i data-lucide="scissors" style="color: #CBD5E1;"></i>
                        <span>العمليات الجراحية OR</span>
                    </a>
                    <a href="<?= url('/icu') ?>" class="nav-item-hms <?= active_route('/icu') ?>" title="العناية المركزة">
                        <i data-lucide="heart-pulse" style="color: #EF4444;"></i>
                        <span>العناية المركزة ICU</span>
                    </a>

                    <!-- Group 4: التشخيص والإمداد الدوائي -->
                    <div class="sidebar-group-label">التشخيص والصيدلية والمخازن</div>
                    <a href="<?= url('/lab') ?>" class="nav-item-hms <?= active_route('/lab') ?>" title="المختبرات الطبية LIS">
                        <i data-lucide="flask-conical" style="color: #FBBF24;"></i>
                        <span>المختبرات الطبية LIS</span>
                    </a>
                    <a href="<?= url('/radiology') ?>" class="nav-item-hms <?= active_route('/radiology') ?>" title="الأشعة والتصوير الطبي RIS">
                        <i data-lucide="scan" style="color: #60A5FA;"></i>
                        <span>الأشعة والتصوير RIS</span>
                    </a>
                    <a href="<?= url('/pharmacy') ?>" class="nav-item-hms <?= active_route('/pharmacy') ?>" title="صيدلية المستشفى">
                        <i data-lucide="pill" style="color: #10B981;"></i>
                        <span>صيدلية المستشفى وصرف العلاج</span>
                    </a>
                    <a href="<?= url('/inventory') ?>" class="nav-item-hms <?= active_route('/inventory') ?>" title="المستودعات الطبية FEFO">
                        <i data-lucide="boxes" style="color: #94A3B8;"></i>
                        <span>المستودعات الطبية FEFO</span>
                    </a>

                    <!-- Group 5: المالية والتأمين -->
                    <div class="sidebar-group-label">المالية والتأمين الصحي</div>
                    <a href="<?= url('/billing') ?>" class="nav-item-hms <?= active_route('/billing') ?>" title="الفواتير وسندات القبض">
                        <i data-lucide="receipt" style="color: #34D399;"></i>
                        <span>الفواتير والتحصيل</span>
                    </a>
                    <a href="<?= url('/cashboxes') ?>" class="nav-item-hms <?= active_route('/cashboxes') ?>" title="ورديات الصناديق">
                        <i data-lucide="wallet" style="color: #FBBF24;"></i>
                        <span>ورديات الصناديق والخزينة</span>
                    </a>
                    <a href="<?= url('/insurance') ?>" class="nav-item-hms <?= active_route('/insurance') ?>" title="التأمين الطبي">
                        <i data-lucide="shield-check" style="color: #60A5FA;"></i>
                        <span>التأمين الصحي والمطالبات</span>
                    </a>

                    <!-- Group 6: الموارد البشرية والرواتب -->
                    <div class="sidebar-group-label">الموارد البشرية والكوادر</div>
                    <a href="<?= url('/hr') ?>" class="nav-item-hms <?= active_route('/hr') ?>" title="لوحة الموارد البشرية">
                        <i data-lucide="briefcase" style="color: #F59E0B;"></i>
                        <span>لوحة الموارد البشرية</span>
                    </a>
                    <a href="<?= url('/employees') ?>" class="nav-item-hms <?= active_route('/employees') ?>" title="دليل الموظفين">
                        <i data-lucide="users" style="color: #38BDF8;"></i>
                        <span>سجل الكوادر والموظفين</span>
                    </a>
                    <a href="<?= url('/hr?tab=payroll') ?>" class="nav-item-hms" title="مسيرات الرواتب SDG">
                        <i data-lucide="coins" style="color: #10B981;"></i>
                        <span>مسيرات الرواتب (SDG)</span>
                    </a>

                    <!-- Group 7: التقارير والإدارة -->
                    <div class="sidebar-group-label">الإدارة والتقارير والنظام</div>
                    <a href="<?= url('/reports') ?>" class="nav-item-hms <?= active_route('/reports') ?>" title="التقارير والمؤشرات">
                        <i data-lucide="bar-chart-2" style="color: #10B981;"></i>
                        <span>التقارير والإحصائيات</span>
                    </a>
                    <a href="<?= url('/beds') ?>" class="nav-item-hms <?= active_route('/beds') ?>" title="خريطة الأسرة">
                        <i data-lucide="bed" style="color: #38BDF8;"></i>
                        <span>خريطة الأسرة والأجنحة</span>
                    </a>
                    <a href="<?= url('/departments') ?>" class="nav-item-hms <?= active_route('/departments') ?>" title="الأقسام والعيادات">
                        <i data-lucide="building" style="color: #94A3B8;"></i>
                        <span>الأقسام والعيادات</span>
                    </a>
                    <a href="<?= url('/doctors') ?>" class="nav-item-hms <?= active_route('/doctors') ?>" title="الأطباء">
                        <i data-lucide="user-check" style="color: #60A5FA;"></i>
                        <span>دليل الأطباء</span>
                    </a>

                    <?php if (can('users.view')): ?>
                        <a href="<?= url('/users') ?>" class="nav-item-hms <?= active_route('/users') ?>" title="المستخدمين">
                            <i data-lucide="user-cog" style="color: #94A3B8;"></i>
                            <span>المستخدمين والحسابات</span>
                        </a>
                    <?php endif; ?>

                    <?php if (can('roles.manage')): ?>
                        <a href="<?= url('/roles') ?>" class="nav-item-hms <?= active_route('/roles') ?>" title="الأدوار والصلاحيات">
                            <i data-lucide="shield-alert" style="color: #F87171;"></i>
                            <span>الأدوار والصلاحيات RBAC</span>
                        </a>
                    <?php endif; ?>

                    <?php if (can('audit.view')): ?>
                        <a href="<?= url('/audit-logs') ?>" class="nav-item-hms <?= active_route('/audit-logs') ?>" title="سجل التدقيق">
                            <i data-lucide="clipboard-list" style="color: #FBBF24;"></i>
                            <span>سجل التدقيق الأمني</span>
                        </a>
                    <?php endif; ?>

                    <a href="<?= url('/settings') ?>" class="nav-item-hms <?= active_route('/settings') ?>" title="إعدادات النظام">
                        <i data-lucide="settings" style="color: #38BDF8;"></i>
                        <span>إعدادات وهوية المستشفى</span>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Sidebar User Profile Footer -->
            <?php if (auth()->check()): ?>
                <div class="sidebar-footer">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2 overflow-hidden">
                            <div class="position-relative flex-shrink-0">
                                <div class="p-2 rounded-circle bg-white bg-opacity-10 text-white">
                                    <i data-lucide="user" style="width: 16px; height: 16px;"></i>
                                </div>
                                <span class="position-absolute bottom-0 start-0 p-1 bg-success border border-dark rounded-circle" title="متصل الآن"></span>
                            </div>
                            <div class="user-details overflow-hidden">
                                <strong class="d-block text-white text-truncate" style="font-size: 0.84rem;"><?= e($user['full_name'] ?? 'مستخدم') ?></strong>
                                <small class="text-light opacity-75 font-monospace" style="font-size: 0.7rem;">@<?= e($user['username']) ?></small>
                            </div>
                        </div>
                        <form action="<?= url('/logout') ?>" method="POST" class="m-0 user-details">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-link text-danger p-1" title="تسجيل الخروج">
                                <i data-lucide="log-out" style="width: 16px; height: 16px;"></i>
                            </button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </aside>

        <!-- ---------------- Main Canvas & Sleek Topbar ---------------- -->
        <div id="hms-main-wrapper">
            <!-- Topbar Header -->
            <header id="hms-topbar">
                <div class="d-flex align-items-center gap-3">
                    <!-- Toggle Sidebar Button -->
                    <button class="topbar-toggle-btn" id="sidebarToggleBtn" title="طي / توسيع القائمة الجانبية">
                        <i data-lucide="menu" style="width: 20px; height: 20px;"></i>
                    </button>

                    <?php if (auth()->check()): ?>
                        <!-- Global Quick Search Trigger -->
                        <button class="topbar-search-btn shadow-sm" type="button" data-bs-toggle="modal" data-bs-target="#quickSearchModal">
                            <i data-lucide="search" style="width: 16px; height: 16px; color: var(--hms-green);"></i>
                            <span class="d-none d-sm-inline">بحث سريع عن مريض...</span>
                            <kbd class="small bg-white text-dark border px-1 rounded shadow-sm d-none d-md-inline">Ctrl+K</kbd>
                        </button>
                    <?php endif; ?>
                </div>

                <!-- Topbar Right Tools -->
                <div class="d-flex align-items-center gap-2 gap-sm-3">
                    <!-- Quick Emergency & Bankak Badges -->
                    <div class="d-none d-lg-flex align-items-center gap-2 small">
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle py-1 px-2 d-flex align-items-center gap-1">
                            <i data-lucide="phone-call" style="width: 12px; height: 12px;"></i>
                            <span>طوارئ: <?= e($emergencyPhone) ?></span>
                        </span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle py-1 px-2 d-flex align-items-center gap-1">
                            <i data-lucide="landmark" style="width: 12px; height: 12px;"></i>
                            <span>بنكك: <?= e($bankakAccount) ?></span>
                        </span>
                    </div>

                    <!-- Theme Toggle -->
                    <button class="topbar-toggle-btn" id="themeToggleBtn" title="تبديل الوضع الليلي / النهاري">
                        <i data-lucide="moon" id="themeIcon" style="width: 18px; height: 18px;"></i>
                    </button>

                    <!-- Language Switcher -->
                    <?php if ($locale === 'ar'): ?>
                        <a href="<?= url('/lang?lang=en') ?>" class="btn btn-sm btn-outline-secondary fw-bold px-2 py-1 fs-7">EN</a>
                    <?php else: ?>
                        <a href="<?= url('/lang?lang=ar') ?>" class="btn btn-sm btn-outline-secondary fw-bold px-2 py-1 fs-7">عربي</a>
                    <?php endif; ?>

                    <!-- User Dropdown or Login -->
                    <?php if (auth()->check()): ?>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light border dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                                <i data-lucide="user-check" style="width: 16px; height: 16px; color: var(--hms-green);"></i>
                                <span class="d-none d-md-inline fw-semibold"><?= e($user['full_name'] ?? 'المستخدم') ?></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="border-radius: 10px;">
                                <li class="px-3 py-2 border-bottom">
                                    <strong class="d-block text-navy"><?= e($user['full_name'] ?? 'مستخدم') ?></strong>
                                    <small class="text-muted">@<?= e($user['username']) ?></small>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="<?= url('/profile') ?>">
                                        <i data-lucide="user" style="width: 15px; height: 15px;"></i>
                                        <span>الملف الشخصي وكلمة المرور</span>
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider my-0"></li>
                                <li>
                                    <form action="<?= url('/logout') ?>" method="POST" class="m-0">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="dropdown-item text-danger d-flex align-items-center gap-2 py-2">
                                            <i data-lucide="log-out" style="width: 15px; height: 15px;"></i>
                                            <span>تسجيل الخروج</span>
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="<?= url('/login') ?>" class="btn btn-sm btn-hms-primary fw-bold px-3">
                            <i data-lucide="log-in" style="width: 16px; height: 16px; display:inline-block; vertical-align:text-bottom;"></i>
                            تسجيل الدخول
                        </a>
                    <?php endif; ?>
                </div>
            </header>

            <!-- Main Body Container -->
            <main class="py-4 flex-grow-1">
                <div class="container-fluid px-3 px-xl-4">
                    <!-- Standard Breadcrumbs -->
                    <?php if (isset($breadcrumbs) && is_array($breadcrumbs)): ?>
                        <nav aria-label="breadcrumb" class="mb-3">
                            <ol class="breadcrumb small">
                                <?php foreach ($breadcrumbs as $index => $crumb): ?>
                                    <?php if ($index === count($breadcrumbs) - 1 || empty($crumb['url'])): ?>
                                        <li class="breadcrumb-item active fw-bold text-navy" aria-current="page"><?= e($crumb['label']) ?></li>
                                    <?php else: ?>
                                        <li class="breadcrumb-item"><a href="<?= e($crumb['url']) ?>" class="text-decoration-none text-green"><?= e($crumb['label']) ?></a></li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ol>
                        </nav>
                    <?php endif; ?>

                    <!-- Flash Notifications -->
                    <?php if (\App\Core\Session::hasFlash('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-4 shadow-sm" role="alert">
                            <i data-lucide="check-circle" style="width: 20px; height: 20px; flex-shrink: 0;"></i>
                            <div><?= e(\App\Core\Session::getFlash('success')) ?></div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (\App\Core\Session::hasFlash('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-4 shadow-sm" role="alert">
                            <i data-lucide="alert-octagon" style="width: 20px; height: 20px; flex-shrink: 0;"></i>
                            <div><?= e(\App\Core\Session::getFlash('error')) ?></div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (\App\Core\Session::hasFlash('info')): ?>
                        <div class="alert alert-info alert-dismissible fade show d-flex align-items-center gap-2 mb-4 shadow-sm" role="alert">
                            <i data-lucide="info" style="width: 20px; height: 20px; flex-shrink: 0;"></i>
                            <div><?= e(\App\Core\Session::getFlash('info')) ?></div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?= $content ?>
                </div>
            </main>

            <!-- Footer -->
            <footer class="text-muted">
                <div class="container-fluid px-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <strong class="text-navy"><?= e($hospitalName) ?></strong> &bull; نظام الإدارة الطبية المتكاملة
                    </div>
                    <div class="small">
                        <span>الجمهورية السودانية &bull; العملة: <strong>SDG</strong> &bull; PHP <?= PHP_VERSION ?> &bull; <?= date('Y') ?></span>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Quick Search Modal -->
    <?php if (auth()->check()): ?>
        <div class="modal fade" id="quickSearchModal" tabindex="-1" aria-labelledby="quickSearchModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content shadow border-0" style="border-radius: 14px; overflow: hidden;">
                    <div class="modal-header bg-navy text-white py-3 border-0">
                        <div class="d-flex align-items-center gap-2">
                            <i data-lucide="search" style="width: 20px; height: 20px; color: #6EE7B7;"></i>
                            <h5 class="modal-title fw-bold" id="quickSearchModalLabel">البحث السريع عن ملف مريض</h5>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4 bg-light">
                        <div class="input-group input-group-lg shadow-sm mb-3">
                            <span class="input-group-text bg-white border-end-0">
                                <i data-lucide="user-search" class="text-green" style="width: 22px; height: 22px;"></i>
                            </span>
                            <input type="text" id="globalPatientSearchInput" class="form-control border-start-0 ps-0" placeholder="ابحث بالاسم، رقم الملف MRN، الهاتف، أو الرقم الوطني..." autocomplete="off">
                            <button class="btn btn-outline-secondary" type="button" id="clearQuickSearchBtn">مسح</button>
                        </div>

                        <div id="quickSearchResults" class="d-flex flex-column gap-2" style="max-height: 380px; overflow-y: auto;">
                            <div class="text-center py-4 text-muted">
                                <i data-lucide="keyboard" style="width: 36px; height: 36px; opacity: 0.5;" class="d-block mx-auto mb-2"></i>
                                اكتب حرفين على الأقل لبدء البحث الفوري
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-white border-top py-2 d-flex justify-content-between align-items-center">
                        <div class="small text-muted">
                            <kbd class="bg-light text-dark border px-1 rounded">Esc</kbd> للإغلاق &bull; 
                            <kbd class="bg-light text-dark border px-1 rounded">Ctrl+K</kbd> لفتح البحث في أي صفحة
                        </div>
                        <a href="<?= url('/patients/create') ?>" class="btn btn-sm btn-hms-primary d-flex align-items-center gap-1">
                            <i data-lucide="user-plus" style="width: 16px; height: 16px;"></i>
                            <span>فتح ملف جديد</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        lucide.createIcons();

        // ---------------- Sidebar Toggle & Persistence ----------------
        const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
        const backdrop = document.getElementById('sidebar-backdrop');
        const isMobile = () => window.innerWidth < 992;

        // Restore desktop collapse state
        const savedSidebarState = localStorage.getItem('hms_sidebar_state');
        if (!isMobile() && savedSidebarState === 'collapsed') {
            document.body.classList.add('sidebar-collapsed');
        }

        if (sidebarToggleBtn) {
            sidebarToggleBtn.addEventListener('click', () => {
                if (isMobile()) {
                    document.body.classList.toggle('mobile-sidebar-open');
                } else {
                    document.body.classList.toggle('sidebar-collapsed');
                    const currentState = document.body.classList.contains('sidebar-collapsed') ? 'collapsed' : 'expanded';
                    localStorage.setItem('hms_sidebar_state', currentState);
                }
            });
        }

        if (backdrop) {
            backdrop.addEventListener('click', () => {
                document.body.classList.remove('mobile-sidebar-open');
            });
        }

        // Close mobile drawer on link click
        document.querySelectorAll('.nav-item-hms').forEach(link => {
            link.addEventListener('click', () => {
                if (isMobile()) {
                    document.body.classList.remove('mobile-sidebar-open');
                }
            });
        });

        // ---------------- Theme Toggle ----------------
        const themeToggleBtn = document.getElementById('themeToggleBtn');
        const themeIcon = document.getElementById('themeIcon');
        const htmlElement = document.documentElement;

        const currentTheme = localStorage.getItem('hms_theme') || 'light';
        htmlElement.setAttribute('data-bs-theme', currentTheme);
        updateThemeIcon(currentTheme);

        if (themeToggleBtn) {
            themeToggleBtn.addEventListener('click', () => {
                const newTheme = htmlElement.getAttribute('data-bs-theme') === 'light' ? 'dark' : 'light';
                htmlElement.setAttribute('data-bs-theme', newTheme);
                localStorage.setItem('hms_theme', newTheme);
                updateThemeIcon(newTheme);
            });
        }

        function updateThemeIcon(theme) {
            if (!themeIcon) return;
            if (theme === 'dark') {
                themeIcon.setAttribute('data-lucide', 'sun');
            } else {
                themeIcon.setAttribute('data-lucide', 'moon');
            }
            lucide.createIcons();
        }

        // ---------------- Global Shortcut: Ctrl+K / Cmd+K ----------------
        const searchModalEl = document.getElementById('quickSearchModal');
        let searchModal = null;
        if (searchModalEl) {
            searchModal = new bootstrap.Modal(searchModalEl);
        }

        window.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                e.preventDefault();
                if (searchModal) {
                    searchModal.show();
                }
            }
        });

        // ---------------- Quick Search AJAX Implementation ----------------
        const searchInput = document.getElementById('globalPatientSearchInput');
        const searchResults = document.getElementById('quickSearchResults');
        const clearBtn = document.getElementById('clearQuickSearchBtn');

        if (searchModalEl && searchInput) {
            searchModalEl.addEventListener('shown.bs.modal', () => {
                searchInput.focus();
                searchInput.select();
            });

            let debounceTimer = null;
            searchInput.addEventListener('input', (e) => {
                clearTimeout(debounceTimer);
                const query = e.target.value.trim();

                if (query.length < 2) {
                    searchResults.innerHTML = `
                        <div class="text-center py-4 text-muted">
                            <i data-lucide="keyboard" style="width: 36px; height: 36px; opacity: 0.5;" class="d-block mx-auto mb-2"></i>
                            اكتب حرفين على الأقل لبدء البحث الفوري
                        </div>`;
                    lucide.createIcons();
                    return;
                }

                searchResults.innerHTML = `
                    <div class="text-center py-4 text-muted">
                        <div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>
                        <div>جاري البحث في قاعدة البيانات...</div>
                    </div>`;

                debounceTimer = setTimeout(() => {
                    fetch('<?= url("/patients/quick-search") ?>?q=' + encodeURIComponent(query))
                        .then(res => res.json())
                        .then(res => {
                            const patients = res.data || [];
                            if (patients.length === 0) {
                                searchResults.innerHTML = `
                                    <div class="alert alert-light text-center py-4">
                                        <i data-lucide="user-x" style="width: 32px; height: 32px; color: #94A3B8;" class="d-block mx-auto mb-2"></i>
                                        لم يتم العثور على مريض مطابق لـ "<strong>${escapeHtml(query)}</strong>"
                                    </div>`;
                                lucide.createIcons();
                                return;
                            }

                            let html = '';
                            patients.forEach(pt => {
                                html += `
                                    <div class="p-3 bg-white border rounded-3 shadow-sm d-flex flex-wrap justify-content-between align-items-center gap-2 hover-shadow">
                                        <div>
                                            <div class="d-flex align-items-center gap-2">
                                                <strong class="text-navy fs-6">${escapeHtml(pt.name)}</strong>
                                                <span class="badge bg-primary-subtle text-primary fw-bold font-monospace">${escapeHtml(pt.mrn)}</span>
                                                <span class="badge bg-light text-dark border small">${pt.gender === 'male' ? 'ذكر' : 'أنثى'} ${pt.age ? '• ' + pt.age + ' سنة' : ''}</span>
                                            </div>
                                            <div class="small text-muted mt-1">
                                                <span>هاتف: <strong>${escapeHtml(pt.phone || 'غير مسجل')}</strong></span>
                                                ${pt.national_id ? `<span class="ms-3">الرقم الوطني: <strong>${escapeHtml(pt.national_id)}</strong></span>` : ''}
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center gap-1">
                                            <a href="${pt.url}" class="btn btn-sm btn-outline-primary">
                                                <i data-lucide="folder-heart" style="width: 14px; height: 14px; display:inline-block; vertical-align:text-bottom;"></i>
                                                الملف الطبي
                                            </a>
                                            <a href="<?= url('/emr') ?>" class="btn btn-sm btn-outline-success">
                                                <i data-lucide="stethoscope" style="width: 14px; height: 14px; display:inline-block; vertical-align:text-bottom;"></i>
                                                العيادة EMR
                                            </a>
                                        </div>
                                    </div>`;
                            });

                            searchResults.innerHTML = html;
                            lucide.createIcons();
                        })
                        .catch(err => {
                            searchResults.innerHTML = `
                                <div class="alert alert-danger py-2 small">
                                    حدث خطأ أثناء إجراء البحث. يرجى المحاولة مرة أخرى.
                                </div>`;
                        });
                }, 250);
            });

            if (clearBtn) {
                clearBtn.addEventListener('click', () => {
                    searchInput.value = '';
                    searchInput.focus();
                    searchResults.innerHTML = `
                        <div class="text-center py-4 text-muted">
                            <i data-lucide="keyboard" style="width: 36px; height: 36px; opacity: 0.5;" class="d-block mx-auto mb-2"></i>
                            اكتب حرفين على الأقل لبدء البحث الفوري
                        </div>`;
                    lucide.createIcons();
                });
            }
        }

        function escapeHtml(text) {
            if (!text) return '';
            return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
        }
    </script>
</body>
</html>
