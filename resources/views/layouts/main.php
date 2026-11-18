<?php
$locale = \App\Core\Session::get('locale', config('app.locale', 'ar'));
$isRtl = ($locale === 'ar');
$dir = $isRtl ? 'rtl' : 'ltr';
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

    <!-- Google Fonts: Cairo for Arabic, Inter for English -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        :root {
            --hms-navy: #173B59;
            --hms-green: #16856D;
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

        /* Top Navbar */
        .hms-navbar {
            background-color: var(--hms-navy);
            border-bottom: 3px solid var(--hms-green);
        }

        .hms-brand {
            font-weight: 700;
            color: #FFFFFF !important;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .hms-brand .badge-hms {
            background-color: var(--hms-green);
            font-size: 0.75rem;
            padding: 4px 8px;
            border-radius: 4px;
        }

        /* Cards & Components */
        .card-hms {
            border: 1px solid var(--hms-gray);
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(23, 59, 89, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card-hms:hover {
            box-shadow: 0 8px 25px rgba(23, 59, 89, 0.1);
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
            background-color: #126e5a;
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
            background-color: #10293d;
            color: #FFFFFF;
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
    <nav class="navbar navbar-expand-lg hms-navbar py-2">
        <div class="container-fluid px-4">
            <a class="navbar-brand hms-brand" href="<?= url('/') ?>">
                <i data-lucide="activity" style="color: #6EE7B7; width: 28px; height: 28px;"></i>
                <span><?= e(config('app.name')) ?></span>
                <span class="badge-hms">Core v1.0</span>
            </a>

            <div class="d-flex align-items-center gap-3">
                <!-- Theme Mode Toggle -->
                <button class="btn btn-sm btn-outline-light d-flex align-items-center gap-1" id="themeToggleBtn" title="تبديل الوضع">
                    <i data-lucide="moon" id="themeIcon" style="width: 18px; height: 18px;"></i>
                </button>

                <!-- Language Switcher -->
                <?php if ($locale === 'ar'): ?>
                    <a href="<?= url('/lang?lang=en') ?>" class="btn btn-sm btn-light fw-bold px-3">
                        <i data-lucide="globe" style="width: 16px; height: 16px; display:inline-block; vertical-align:text-bottom;"></i>
                        English
                    </a>
                <?php else: ?>
                    <a href="<?= url('/lang?lang=ar') ?>" class="btn btn-sm btn-light fw-bold px-3">
                        <i data-lucide="globe" style="width: 16px; height: 16px; display:inline-block; vertical-align:text-bottom;"></i>
                        العربية
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Main Body Container -->
    <main class="py-4 flex-grow-1">
        <div class="container-fluid px-4">
            <?= $content ?>
        </div>
    </main>

    <!-- Footer -->
    <footer class="text-center text-muted">
        <div class="container">
            <p class="mb-1"><?= __('messages.footer_text') ?></p>
            <small class="text-secondary"><?= config('app.name') ?> &bull; PHP <?= PHP_VERSION ?> &bull; <?= date('Y') ?></small>
        </div>
    </footer>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Initialize Lucide Icons
        lucide.createIcons();

        // Dark/Light Mode Switcher
        const themeToggleBtn = document.getElementById('themeToggleBtn');
        const themeIcon = document.getElementById('themeIcon');
        const htmlElement = document.documentElement;

        // Check stored theme preference
        const currentTheme = localStorage.getItem('hms_theme') || 'light';
        htmlElement.setAttribute('data-bs-theme', currentTheme);
        updateThemeIcon(currentTheme);

        themeToggleBtn.addEventListener('click', () => {
            const newTheme = htmlElement.getAttribute('data-bs-theme') === 'light' ? 'dark' : 'light';
            htmlElement.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('hms_theme', newTheme);
            updateThemeIcon(newTheme);
        });

        function updateThemeIcon(theme) {
            if (theme === 'dark') {
                themeIcon.setAttribute('data-lucide', 'sun');
            } else {
                themeIcon.setAttribute('data-lucide', 'moon');
            }
            lucide.createIcons();
        }
    </script>
</body>
</html>
