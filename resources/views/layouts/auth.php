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

    <!-- Google Fonts -->
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
            align-items: center;
            justify-content: center;
        }

        .auth-card {
            border: 1px solid var(--hms-gray);
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(23, 59, 89, 0.08);
            background-color: #FFFFFF;
            overflow: hidden;
            width: 100%;
            max-width: 440px;
        }

        .auth-header {
            background-color: var(--hms-navy);
            border-bottom: 4px solid var(--hms-green);
            padding: 30px 20px;
            text-align: center;
            color: #FFFFFF;
        }

        .btn-hms-primary {
            background-color: var(--hms-green);
            color: #FFFFFF;
            font-weight: 600;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            transition: all 0.2s ease;
            width: 100%;
        }
        .btn-hms-primary:hover {
            background-color: #126e5a;
            color: #FFFFFF;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <?= $content ?>
    </div>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>lucide.createIcons();</script>
</body>
</html>
