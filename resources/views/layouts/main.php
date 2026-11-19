<?php
$locale = \App\Core\Session::get('locale', config('app.locale', 'ar'));
$isRtl = ($locale === 'ar');
$dir = $isRtl ? 'rtl' : 'ltr';
$user = current_user();
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
            --hms-light-bg: #F3F6F9;
            --hms-gray: #DCE5EC;
            --hms-accent: #D49A36;
            --hms-font: <?= $isRtl ? "'Cairo', sans-serif" : "'Inter', -apple-system, sans-serif" ?>;
        }

        body {
            font-family: var(--hms-font);
            background-color: var(--hms-light-bg);
            color: #2D3748;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        [data-bs-theme="dark"] {
            --hms-light-bg: #0F172A;
            background-color: var(--hms-light-bg);
            color: #E2E8F0;
        }

        .hms-navbar {
            background-color: var(--hms-navy);
            border-bottom: 3px solid var(--hms-green);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
        }

        .hms-brand {
            font-weight: 700;
            color: #FFFFFF !important;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.15rem;
        }

        .hms-brand .badge-hms {
            background-color: var(--hms-green);
            font-size: 0.72rem;
            padding: 3px 7px;
            border-radius: 4px;
            font-weight: 600;
        }

        .nav-link-hms {
            color: rgba(255, 255, 255, 0.88) !important;
            font-weight: 600;
            font-size: 0.9rem;
            padding: 8px 12px !important;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.18s ease;
        }
        .nav-link-hms:hover, 
        .nav-link-hms.active,
        .nav-link-hms:focus {
            color: #FFFFFF !important;
            background-color: rgba(255, 255, 255, 0.14);
        }

        .dropdown-menu-hms {
            border: 1px solid var(--hms-gray);
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(23, 59, 89, 0.15);
            padding: 8px 0;
            min-width: 230px;
        }

        .dropdown-item-hms {
            padding: 9px 16px;
            font-weight: 500;
            font-size: 0.88rem;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #334155;
            transition: all 0.15s ease;
        }
        .dropdown-item-hms:hover {
            background-color: #F1F5F9;
            color: var(--hms-navy);
            padding-inline-start: 20px;
        }
        .dropdown-item-hms.active {
            background-color: var(--hms-green);
            color: #FFFFFF !important;
        }
        .dropdown-item-hms.active i {
            color: #FFFFFF !important;
        }

        [data-bs-theme="dark"] .dropdown-menu-hms {
            background-color: #1E293B;
            border-color: #334155;
        }
        [data-bs-theme="dark"] .dropdown-item-hms {
            color: #CBD5E1;
        }
        [data-bs-theme="dark"] .dropdown-item-hms:hover {
            background-color: #334155;
            color: #FFFFFF;
        }

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

        .search-trigger-btn {
            background: rgba(255, 255, 255, 0.12);
            color: #FFFFFF;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            padding: 6px 14px;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
        }
        .search-trigger-btn:hover {
            background: rgba(255, 255, 255, 0.22);
            color: #FFFFFF;
        }

        .breadcrumb-hms {
            background: transparent;
            padding: 0;
            margin-bottom: 1.25rem;
            font-size: 0.88rem;
        }
        .breadcrumb-hms a {
            text-decoration: none;
            color: var(--hms-green);
        }

        footer {
            margin-top: auto;
            background-color: #FFFFFF;
            border-top: 1px solid var(--hms-gray);
            padding: 15px 0;
            font-size: 0.875rem;
        }

        [data-bs-theme="dark"] footer {
            background-color: #1E293B;
            border-color: #334155;
        }
    </style>
</head>
<body>

    <!-- Main Navigation Bar -->
    <nav class="navbar navbar-expand-xl hms-navbar py-2 sticky-top">
        <div class="container-fluid px-3 px-xl-4">
            <!-- Brand -->
            <a class="navbar-brand hms-brand" href="<?= url('/') ?>">
                <i data-lucide="activity" style="color: #6EE7B7; width: 28px; height: 28px;"></i>
                <span class="d-none d-sm-inline"><?= e(config('app.name')) ?></span>
                <span class="badge-hms">Core v1.0</span>
            </a>

            <!-- Mobile Navbar Toggler -->
            <button class="navbar-toggler text-white border-0" type="button" data-bs-toggle="collapse" data-bs-target="#hmsNavbar">
                <i data-lucide="menu"></i>
            </button>

            <!-- Nav Links Collapse -->
            <div class="collapse navbar-collapse" id="hmsNavbar">
                <ul class="navbar-nav me-auto mb-2 mb-xl-0 gap-1">
                    <!-- 1. الرئيسية -->
                    <li class="nav-item">
                        <a class="nav-link nav-link-hms <?= active_route('/') ?>" href="<?= url('/') ?>">
                            <i data-lucide="layout-dashboard" style="width: 17px; height: 17px;"></i>
                            <span>الرئيسية</span>
                        </a>
                    </li>

                    <?php if (auth()->check()): ?>
                        <!-- 2. الاستقبال والمرضى (Patient Services) -->
                        <li class="nav-item dropdown">
                            <a class="nav-link nav-link-hms dropdown-toggle <?= active_group(['/reception', '/patients', '/appointments']) ?>" href="#" role="button" data-bs-toggle="dropdown">
                                <i data-lucide="users" style="width: 17px; height: 17px; color: #93C5FD;"></i>
                                <span>الاستقبال وسجلات المرضى</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-hms shadow">
                                <li>
                                    <a class="dropdown-item dropdown-item-hms <?= active_route('/reception') ?>" href="<?= url('/reception') ?>">
                                        <i data-lucide="bell" style="width: 16px; height: 16px; color: #16856D;"></i>
                                        <span>الاستقبال والمكتب الأمامي</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item dropdown-item-hms <?= active_route('/patients') ?>" href="<?= url('/patients') ?>">
                                        <i data-lucide="folder-heart" style="width: 16px; height: 16px; color: #2563EB;"></i>
                                        <span>سجل المرضى الموحد</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item dropdown-item-hms <?= active_route('/appointments') ?>" href="<?= url('/appointments') ?>">
                                        <i data-lucide="calendar" style="width: 16px; height: 16px; color: #D97706;"></i>
                                        <span>حجز وإدارة المواعيد</span>
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider my-1"></li>
                                <li>
                                    <a class="dropdown-item dropdown-item-hms text-success fw-bold" href="<?= url('/patients/create') ?>">
                                        <i data-lucide="user-plus" style="width: 16px; height: 16px;"></i>
                                        <span>+ فتح ملف مريض جديد (MRN)</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- 3. العيادات والتنويم (Clinical & IPD) -->
                        <li class="nav-item dropdown">
                            <a class="nav-link nav-link-hms dropdown-toggle <?= active_group(['/emr', '/emergency', '/admissions', '/nursing', '/surgery', '/icu']) ?>" href="#" role="button" data-bs-toggle="dropdown">
                                <i data-lucide="stethoscope" style="width: 17px; height: 17px; color: #6EE7B7;"></i>
                                <span>الخدمات السريرية والتنويم</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-hms shadow">
                                <li>
                                    <a class="dropdown-item dropdown-item-hms <?= active_route('/emr') ?>" href="<?= url('/emr') ?>">
                                        <i data-lucide="file-heart" style="width: 16px; height: 16px; color: #16856D;"></i>
                                        <span>عيادة الطبيب السريرية EMR</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item dropdown-item-hms <?= active_route('/emergency') ?>" href="<?= url('/emergency') ?>">
                                        <i data-lucide="ambulance" style="width: 16px; height: 16px; color: #EF4444;"></i>
                                        <span>طوارئ وحوادث وفرز ER</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item dropdown-item-hms <?= active_route('/admissions') ?>" href="<?= url('/admissions') ?>">
                                        <i data-lucide="bed-double" style="width: 16px; height: 16px; color: #0284C7;"></i>
                                        <span>التنويم الداخلي والأجنحة IPD</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item dropdown-item-hms <?= active_route('/nursing') ?>" href="<?= url('/nursing') ?>">
                                        <i data-lucide="clipboard-check" style="width: 16px; height: 16px; color: #8B5CF6;"></i>
                                        <span>محطة التمريض والجرعات MAR</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item dropdown-item-hms <?= active_route('/surgery') ?>" href="<?= url('/surgery') ?>">
                                        <i data-lucide="scissors" style="width: 16px; height: 16px; color: #475569;"></i>
                                        <span>العمليات الجراحية والتخدير OR</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item dropdown-item-hms <?= active_route('/icu') ?>" href="<?= url('/icu') ?>">
                                        <i data-lucide="heart-pulse" style="width: 16px; height: 16px; color: #DC2626;"></i>
                                        <span>العناية المركزة والحرجة ICU</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- 4. التشخيص والصيدلية (Diagnostics & Pharmacy) -->
                        <li class="nav-item dropdown">
                            <a class="nav-link nav-link-hms dropdown-toggle <?= active_group(['/lab', '/radiology', '/pharmacy', '/inventory']) ?>" href="#" role="button" data-bs-toggle="dropdown">
                                <i data-lucide="test-tube-2" style="width: 17px; height: 17px; color: #FBBF24;"></i>
                                <span>التشخيص والصيدلية</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-hms shadow">
                                <li>
                                    <a class="dropdown-item dropdown-item-hms <?= active_route('/lab') ?>" href="<?= url('/lab') ?>">
                                        <i data-lucide="flask-conical" style="width: 16px; height: 16px; color: #D97706;"></i>
                                        <span>المختبرات والتحاليل LIS</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item dropdown-item-hms <?= active_route('/radiology') ?>" href="<?= url('/radiology') ?>">
                                        <i data-lucide="scan" style="width: 16px; height: 16px; color: #2563EB;"></i>
                                        <span>الأشعة والتصوير الطبي RIS</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item dropdown-item-hms <?= active_route('/pharmacy') ?>" href="<?= url('/pharmacy') ?>">
                                        <i data-lucide="pill" style="width: 16px; height: 16px; color: #16856D;"></i>
                                        <span>صيدلية المستشفى وصرف العلاج</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item dropdown-item-hms <?= active_route('/inventory') ?>" href="<?= url('/inventory') ?>">
                                        <i data-lucide="boxes" style="width: 16px; height: 16px; color: #475569;"></i>
                                        <span>المستودعات الدوائية FEFO</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- 5. المالية والتأمين (Finance & Insurance) -->
                        <li class="nav-item dropdown">
                            <a class="nav-link nav-link-hms dropdown-toggle <?= active_group(['/billing', '/cashboxes', '/insurance']) ?>" href="#" role="button" data-bs-toggle="dropdown">
                                <i data-lucide="wallet" style="width: 17px; height: 17px; color: #34D399;"></i>
                                <span>المالية والتأمين</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-hms shadow">
                                <li>
                                    <a class="dropdown-item dropdown-item-hms <?= active_route('/billing') ?>" href="<?= url('/billing') ?>">
                                        <i data-lucide="receipt" style="width: 16px; height: 16px; color: #16856D;"></i>
                                        <span>الفواتير والتحصيل وسندات القبض</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item dropdown-item-hms <?= active_route('/cashboxes') ?>" href="<?= url('/cashboxes') ?>">
                                        <i data-lucide="landmark" style="width: 16px; height: 16px; color: #D97706;"></i>
                                        <span>ورديات الخزينة والصناديق</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item dropdown-item-hms <?= active_route('/insurance') ?>" href="<?= url('/insurance') ?>">
                                        <i data-lucide="shield-check" style="width: 16px; height: 16px; color: #2563EB;"></i>
                                        <span>التأمين الصحي والمطالبات</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- 6. الموارد البشرية والرواتب (HR & Workforce) -->
                        <li class="nav-item dropdown">
                            <a class="nav-link nav-link-hms dropdown-toggle <?= active_group(['/hr', '/employees']) ?>" href="#" role="button" data-bs-toggle="dropdown">
                                <i data-lucide="briefcase" style="width: 17px; height: 17px; color: #F59E0B;"></i>
                                <span>الموارد البشرية</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-hms shadow">
                                <li>
                                    <a class="dropdown-item dropdown-item-hms <?= active_route('/hr') ?>" href="<?= url('/hr') ?>">
                                        <i data-lucide="layout-grid" style="width: 16px; height: 16px; color: #F59E0B;"></i>
                                        <span>لوحة تحكم الموارد البشرية</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item dropdown-item-hms <?= active_route('/employees') ?>" href="<?= url('/employees') ?>">
                                        <i data-lucide="users" style="width: 16px; height: 16px; color: #2563EB;"></i>
                                        <span>سجل الكوادر والموظفين</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item dropdown-item-hms" href="<?= url('/hr?tab=rosters') ?>">
                                        <i data-lucide="calendar-check" style="width: 16px; height: 16px; color: #16856D;"></i>
                                        <span>جداول المناوبات والورديات</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item dropdown-item-hms" href="<?= url('/hr?tab=leaves') ?>">
                                        <i data-lucide="calendar-off" style="width: 16px; height: 16px; color: #EF4444;"></i>
                                        <span>إدارة طلبات الإجازات</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item dropdown-item-hms" href="<?= url('/hr?tab=payroll') ?>">
                                        <i data-lucide="coins" style="width: 16px; height: 16px; color: #10B981;"></i>
                                        <span>مسيرات الرواتب الشهرية (SDG)</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- 7. الإدارة والتقارير (Admin & Settings) -->
                        <li class="nav-item dropdown">
                            <a class="nav-link nav-link-hms dropdown-toggle <?= active_group(['/reports', '/departments', '/clinics', '/doctors', '/beds', '/users', '/roles', '/audit-logs', '/settings']) ?>" href="#" role="button" data-bs-toggle="dropdown">
                                <i data-lucide="settings" style="width: 17px; height: 17px; color: #94A3B8;"></i>
                                <span>الإدارة والتقارير</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-hms shadow">
                                <li>
                                    <a class="dropdown-item dropdown-item-hms <?= active_route('/reports') ?>" href="<?= url('/reports') ?>">
                                        <i data-lucide="bar-chart-2" style="width: 16px; height: 16px; color: #10B981;"></i>
                                        <span>لوحة التقارير والمؤشرات</span>
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider my-1"></li>
                                <li class="dropdown-header small text-muted">الهيكل الطبي والتنظيمي</li>
                                <li>
                                    <a class="dropdown-item dropdown-item-hms <?= active_route('/beds') ?>" href="<?= url('/beds') ?>">
                                        <i data-lucide="bed" style="width: 16px; height: 16px; color: #0284C7;"></i>
                                        <span>خريطة الأجنحة والأسرة</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item dropdown-item-hms <?= active_route('/departments') ?>" href="<?= url('/departments') ?>">
                                        <i data-lucide="building" style="width: 16px; height: 16px; color: #475569;"></i>
                                        <span>الأقسام الطبية والإدارية</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item dropdown-item-hms <?= active_route('/clinics') ?>" href="<?= url('/clinics') ?>">
                                        <i data-lucide="door-open" style="width: 16px; height: 16px; color: #16856D;"></i>
                                        <span>العيادات التخصصية</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item dropdown-item-hms <?= active_route('/doctors') ?>" href="<?= url('/doctors') ?>">
                                        <i data-lucide="user-check" style="width: 16px; height: 16px; color: #2563EB;"></i>
                                        <span>دليل الأطباء والاستشاريين</span>
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider my-1"></li>
                                <li class="dropdown-header small text-muted">إدارة النظام والأمان</li>
                                <?php if (can('users.view')): ?>
                                    <li>
                                        <a class="dropdown-item dropdown-item-hms <?= active_route('/users') ?>" href="<?= url('/users') ?>">
                                            <i data-lucide="user-cog" style="width: 16px; height: 16px; color: #475569;"></i>
                                            <span>المستخدمين والحسابات</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <?php if (can('roles.manage')): ?>
                                    <li>
                                        <a class="dropdown-item dropdown-item-hms <?= active_route('/roles') ?>" href="<?= url('/roles') ?>">
                                            <i data-lucide="shield-check" style="width: 16px; height: 16px; color: #DC2626;"></i>
                                            <span>الأدوار والصلاحيات RBAC</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <?php if (can('audit.view')): ?>
                                    <li>
                                        <a class="dropdown-item dropdown-item-hms <?= active_route('/audit-logs') ?>" href="<?= url('/audit-logs') ?>">
                                            <i data-lucide="clipboard-list" style="width: 16px; height: 16px; color: #D97706;"></i>
                                            <span>سجل التدقيق الأمني Audit</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <li>
                                    <a class="dropdown-item dropdown-item-hms <?= active_route('/settings') ?>" href="<?= url('/settings') ?>">
                                        <i data-lucide="sliders" style="width: 16px; height: 16px; color: #60A5FA;"></i>
                                        <span>إعدادات وهوية المنشأة</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>

                <!-- Navbar Right Tools -->
                <div class="d-flex align-items-center gap-2 mt-2 mt-xl-0">
                    <?php if (auth()->check()): ?>
                        <!-- Quick Search Trigger -->
                        <button class="search-trigger-btn" type="button" data-bs-toggle="modal" data-bs-target="#quickSearchModal" title="بحث سريع عن مريض (Ctrl+K)">
                            <i data-lucide="search" style="width: 16px; height: 16px;"></i>
                            <span class="d-none d-md-inline">بحث مريض...</span>
                            <kbd class="small bg-white bg-opacity-25 text-white px-1 rounded d-none d-lg-inline">Ctrl+K</kbd>
                        </button>
                    <?php endif; ?>

                    <!-- Theme Mode Toggle -->
                    <button class="btn btn-sm btn-outline-light d-flex align-items-center p-2 rounded-circle" id="themeToggleBtn" title="تبديل الوضع الليلي/النهاري">
                        <i data-lucide="moon" id="themeIcon" style="width: 16px; height: 16px;"></i>
                    </button>

                    <!-- Language Switcher -->
                    <?php if ($locale === 'ar'): ?>
                        <a href="<?= url('/lang?lang=en') ?>" class="btn btn-sm btn-light fw-bold px-2 py-1 fs-7">EN</a>
                    <?php else: ?>
                        <a href="<?= url('/lang?lang=ar') ?>" class="btn btn-sm btn-light fw-bold px-2 py-1 fs-7">عربي</a>
                    <?php endif; ?>

                    <!-- User Dropdown or Login Button -->
                    <?php if (auth()->check()): ?>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-light dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                                <i data-lucide="user-check" style="width: 16px; height: 16px;"></i>
                                <span class="d-none d-md-inline"><?= e($user['full_name'] ?? 'المستخدم') ?></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-hms shadow">
                                <li>
                                    <div class="px-3 py-1">
                                        <strong class="d-block text-navy"><?= e($user['full_name'] ?? 'مستخدم') ?></strong>
                                        <small class="text-muted">@<?= e($user['username']) ?></small>
                                    </div>
                                </li>
                                <li><hr class="dropdown-divider my-1"></li>
                                <li>
                                    <a class="dropdown-item dropdown-item-hms" href="<?= url('/profile') ?>">
                                        <i data-lucide="user" style="width: 16px; height: 16px;"></i>
                                        <span>الملف الشخصي وكلمة المرور</span>
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider my-1"></li>
                                <li>
                                    <form action="<?= url('/logout') ?>" method="POST" class="m-0">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="dropdown-item dropdown-item-hms text-danger">
                                            <i data-lucide="log-out" style="width: 16px; height: 16px;"></i>
                                            <span>تسجيل الخروج</span>
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="<?= url('/login') ?>" class="btn btn-sm btn-light fw-bold px-3">
                            <i data-lucide="log-in" style="width: 16px; height: 16px; display:inline-block; vertical-align:text-bottom;"></i>
                            تسجيل الدخول
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Body Container -->
    <main class="py-4 flex-grow-1">
        <div class="container-fluid px-3 px-xl-4">
            <!-- Breadcrumbs -->
            <?php if (isset($breadcrumbs) && is_array($breadcrumbs)): ?>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-hms">
                        <?php foreach ($breadcrumbs as $index => $crumb): ?>
                            <?php if ($index === count($breadcrumbs) - 1 || empty($crumb['url'])): ?>
                                <li class="breadcrumb-item active" aria-current="page"><?= e($crumb['label']) ?></li>
                            <?php else: ?>
                                <li class="breadcrumb-item"><a href="<?= e($crumb['url']) ?>"><?= e($crumb['label']) ?></a></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ol>
                </nav>
            <?php endif; ?>

            <!-- Flash Notifications -->
            <?php if (\App\Core\Session::hasFlash('success')): ?>
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
                    <i data-lucide="check-circle" style="width: 20px; height: 20px; flex-shrink: 0;"></i>
                    <div><?= e(\App\Core\Session::getFlash('success')) ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (\App\Core\Session::hasFlash('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
                    <i data-lucide="alert-octagon" style="width: 20px; height: 20px; flex-shrink: 0;"></i>
                    <div><?= e(\App\Core\Session::getFlash('error')) ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (\App\Core\Session::hasFlash('info')): ?>
                <div class="alert alert-info alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
                    <i data-lucide="info" style="width: 20px; height: 20px; flex-shrink: 0;"></i>
                    <div><?= e(\App\Core\Session::getFlash('info')) ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?= $content ?>
        </div>
    </main>

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

    <!-- Footer -->
    <footer class="text-center text-muted">
        <div class="container-fluid px-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <strong class="text-navy"><?= e(config('app.name')) ?></strong> &bull; <?= __('messages.footer_text') ?>
            </div>
            <div class="small">
                <span>الجمهورية السودانية &bull; العملة: <strong>SDG</strong> &bull; PHP <?= PHP_VERSION ?> &bull; <?= date('Y') ?></span>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        lucide.createIcons();

        // Theme Toggle
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

        // Global Shortcut: Ctrl+K / Cmd+K for Quick Search
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

        // Quick Search AJAX Implementation
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
                                            <a href="${pt.emr_url}" class="btn btn-sm btn-outline-success">
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
