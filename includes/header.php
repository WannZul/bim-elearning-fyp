<?php
/** @var string $pageTitle */
/** @var string $basePath */
/** @var string $activePage */
$pageTitle = $pageTitle ?? 'BIMBoleh';
$basePath = $basePath ?? '';
$activePage = $activePage ?? '';
$bodyClass = $bodyClass ?? '';
$hideNavigation = $hideNavigation ?? false;
$flash = getFlash();
$username = (string) ($_SESSION['username'] ?? 'Pelajar');
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="BIMBoleh — platform pembelajaran Bahasa Isyarat Malaysia yang interaktif dengan latihan kamera AI.">
    <meta name="theme-color" content="#071a2b">
    <title><?= e($pageTitle) ?> · BIMBoleh</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= e($basePath) ?>assets/css/app.css" rel="stylesheet">
</head>
<body class="<?= e($bodyClass) ?>">
<a class="skip-link" href="#main-content">Langkau ke kandungan utama</a>

<?php if (!$hideNavigation && isLoggedIn()): ?>
<header class="app-header" id="appHeader">
    <div class="container-wide header-inner">
        <a class="brand" href="<?= e($basePath) ?>index.php" aria-label="BIMBoleh — Halaman utama">
            <span class="brand-mark" aria-hidden="true">
                <svg viewBox="0 0 42 42" role="img"><path d="M12.5 21.5v-8a2.5 2.5 0 0 1 5 0v5-9a2.5 2.5 0 0 1 5 0v9-7a2.5 2.5 0 0 1 5 0v8-4.5a2.5 2.5 0 0 1 5 0V24c0 8-4.6 12-11.2 12-5.8 0-9.6-3.1-12.6-8l-2.2-3.7a2.7 2.7 0 0 1 4.3-3.2l1.7 1.4Z"/></svg>
            </span>
            <span class="brand-copy"><strong>BIM<span>Boleh</span></strong><small>Belajar. Isyarat. Yakin.</small></span>
        </a>

        <button class="nav-toggle" type="button" aria-controls="primaryNav" aria-expanded="false" aria-label="Buka menu navigasi">
            <span></span><span></span><span></span>
        </button>

        <nav class="primary-nav" id="primaryNav" aria-label="Navigasi utama">
            <a class="nav-link-custom <?= $activePage === 'home' ? 'active' : '' ?>" href="<?= e($basePath) ?>index.php"><i class="bi bi-grid"></i><span>Utama</span></a>
            <a class="nav-link-custom <?= $activePage === 'learn' ? 'active' : '' ?>" href="<?= e($basePath) ?>pages/learn.php"><i class="bi bi-journal-richtext"></i><span>Belajar</span></a>
            <a class="nav-link-custom <?= $activePage === 'practice' ? 'active' : '' ?>" href="<?= e($basePath) ?>pages/ai_tracking.php"><i class="bi bi-camera-video"></i><span>Latihan AI</span></a>
            <a class="nav-link-custom <?= $activePage === 'quiz' ? 'active' : '' ?>" href="<?= e($basePath) ?>pages/quiz.php"><i class="bi bi-lightning-charge"></i><span>Kuiz</span></a>
            <a class="nav-link-custom <?= $activePage === 'leaderboard' ? 'active' : '' ?>" href="<?= e($basePath) ?>pages/leaderboard.php"><i class="bi bi-trophy"></i><span>Ranking</span></a>
        </nav>

        <div class="profile-menu">
            <div class="profile-avatar" aria-hidden="true"><?= e(initials($username)) ?></div>
            <div class="profile-copy"><span>Selamat belajar,</span><strong><?= e($username) ?></strong></div>
            <a class="logout-link" href="<?= e($basePath) ?>logout.php" title="Log keluar" aria-label="Log keluar"><i class="bi bi-box-arrow-right"></i></a>
        </div>
    </div>
</header>
<?php endif; ?>

<?php if ($flash): ?>
<div class="toast-notice toast-<?= e($flash['type'] ?? 'info') ?>" role="status" aria-live="polite">
    <i class="bi bi-info-circle-fill"></i>
    <span><?= e($flash['message'] ?? '') ?></span>
    <button type="button" aria-label="Tutup pemberitahuan"><i class="bi bi-x-lg"></i></button>
</div>
<?php endif; ?>

<main id="main-content">
