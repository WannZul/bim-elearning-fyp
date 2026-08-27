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
$username = (string) ($_SESSION['username'] ?? t('common.student'));
$locale = currentLocale();
$returnTo = (string) ($_SERVER['REQUEST_URI'] ?? ($basePath . 'index.php'));
?>
<!DOCTYPE html>
<html lang="<?= e($locale) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= e(t('meta.description')) ?>">
    <meta name="theme-color" content="#071a2b">
    <title><?= e($pageTitle) ?> · BIMBoleh</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@600;700;800&family=Noto+Sans+SC:wght@400;500;600;700;800&family=Noto+Sans+Tamil:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="<?= e($basePath) ?>assets/js/accessibility-init.js"></script>
    <link href="<?= e($basePath) ?>assets/css/app.css" rel="stylesheet">
</head>
<body class="<?= e($bodyClass) ?>">
<a class="skip-link" href="#main-content"><?= e(t('common.skip')) ?></a>

<div class="accessibility-widget" id="accessibility-widget">
    <button class="accessibility-trigger" id="accessibility-trigger" type="button" aria-expanded="false" aria-controls="accessibility-panel">
        <i class="bi bi-universal-access" aria-hidden="true"></i><span><?= e(t('accessibility.open')) ?></span>
    </button>
    <section class="accessibility-panel" id="accessibility-panel" aria-labelledby="accessibility-title" aria-describedby="accessibility-description" tabindex="-1" hidden>
        <div class="accessibility-panel-header">
            <div><h2 id="accessibility-title" tabindex="-1"><?= e(t('accessibility.title')) ?></h2><p id="accessibility-description"><?= e(t('accessibility.description')) ?></p></div>
            <button class="accessibility-close" id="accessibility-close" type="button" aria-label="<?= e(t('accessibility.close')) ?>"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
        </div>
        <fieldset class="accessibility-fieldset">
            <legend><?= e(t('accessibility.text_size')) ?></legend>
            <div class="accessibility-radio-group">
                <label><input type="radio" name="accessibility-text-size" value="default"> <span><?= e(t('accessibility.text_default')) ?></span></label>
                <label><input type="radio" name="accessibility-text-size" value="large"> <span><?= e(t('accessibility.text_large')) ?></span></label>
                <label><input type="radio" name="accessibility-text-size" value="extra-large"> <span><?= e(t('accessibility.text_extra_large')) ?></span></label>
            </div>
        </fieldset>
        <label class="accessibility-check" for="accessibility-high-contrast"><input id="accessibility-high-contrast" type="checkbox" aria-describedby="accessibility-high-contrast-help"> <span><strong><?= e(t('accessibility.high_contrast')) ?></strong><small id="accessibility-high-contrast-help"><?= e(t('accessibility.high_contrast_help')) ?></small></span></label>
        <label class="accessibility-check" for="accessibility-reduce-motion"><input id="accessibility-reduce-motion" type="checkbox" aria-describedby="accessibility-reduce-motion-help"> <span><strong><?= e(t('accessibility.reduced_motion')) ?></strong><small id="accessibility-reduce-motion-help"><?= e(t('accessibility.reduced_motion_help')) ?></small></span></label>
        <div class="accessibility-actions">
            <button class="btn-light-custom btn-sm-custom" id="accessibility-reset" type="button"><i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i> <?= e(t('accessibility.reset')) ?></button>
            <button class="btn-secondary-custom btn-sm-custom" id="accessibility-close-action" type="button"><?= e(t('accessibility.close')) ?></button>
        </div>
        <p class="visually-hidden" id="accessibility-status" role="status" aria-live="polite" aria-atomic="true"></p>
    </section>
</div>

<?php if (!$hideNavigation && isLoggedIn()): ?>
<header class="app-header" id="appHeader">
    <div class="container-wide header-inner">
        <a class="brand" href="<?= e($basePath) ?>index.php" aria-label="<?= e(t('nav.brand_home')) ?>">
            <span class="brand-mark" aria-hidden="true"><svg viewBox="0 0 42 42"><path d="M12.5 21.5v-8a2.5 2.5 0 0 1 5 0v5-9a2.5 2.5 0 0 1 5 0v9-7a2.5 2.5 0 0 1 5 0v8-4.5a2.5 2.5 0 0 1 5 0V24c0 8-4.6 12-11.2 12-5.8 0-9.6-3.1-12.6-8l-2.2-3.7a2.7 2.7 0 0 1 4.3-3.2l1.7 1.4Z"/></svg></span>
            <span class="brand-copy"><strong>BIM<span>Boleh</span></strong><small><?= e(t('common.brand_tagline')) ?></small></span>
        </a>

        <button class="nav-toggle" type="button" aria-controls="primaryNav" aria-expanded="false" aria-label="<?= e(t('nav.open')) ?>"><span></span><span></span><span></span></button>

        <nav class="primary-nav" id="primaryNav" aria-label="<?= e(t('nav.primary')) ?>">
            <a class="nav-link-custom <?= $activePage === 'home' ? 'active' : '' ?>" href="<?= e($basePath) ?>index.php"<?= $activePage === 'home' ? ' aria-current="page"' : '' ?>><i class="bi bi-grid" aria-hidden="true"></i><span><?= e(t('nav.home')) ?></span></a>
            <a class="nav-link-custom <?= $activePage === 'learn' ? 'active' : '' ?>" href="<?= e($basePath) ?>pages/learn.php"<?= $activePage === 'learn' ? ' aria-current="page"' : '' ?>><i class="bi bi-journal-richtext" aria-hidden="true"></i><span><?= e(t('nav.learn')) ?></span></a>
            <a class="nav-link-custom <?= $activePage === 'practice' ? 'active' : '' ?>" href="<?= e($basePath) ?>pages/ai_tracking.php"<?= $activePage === 'practice' ? ' aria-current="page"' : '' ?>><i class="bi bi-camera-video" aria-hidden="true"></i><span><?= e(t('nav.ai')) ?></span></a>
            <a class="nav-link-custom <?= $activePage === 'quiz' ? 'active' : '' ?>" href="<?= e($basePath) ?>pages/quiz.php"<?= $activePage === 'quiz' ? ' aria-current="page"' : '' ?>><i class="bi bi-lightning-charge" aria-hidden="true"></i><span><?= e(t('nav.quiz')) ?></span></a>
            <a class="nav-link-custom <?= $activePage === 'leaderboard' ? 'active' : '' ?>" href="<?= e($basePath) ?>pages/leaderboard.php"<?= $activePage === 'leaderboard' ? ' aria-current="page"' : '' ?>><i class="bi bi-trophy" aria-hidden="true"></i><span><?= e(t('nav.ranking')) ?></span></a>
        </nav>

        <form class="locale-form" method="POST" action="<?= e($basePath) ?>language.php">
            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
            <input type="hidden" name="return_to" value="<?= e($returnTo) ?>">
            <?php if (!empty($localeSwitchAttemptToken)): ?><input type="hidden" name="attempt_token" value="<?= e($localeSwitchAttemptToken) ?>"><?php endif; ?>
            <label class="visually-hidden" for="header-locale"><?= e(t('locale.label')) ?></label>
            <i class="bi bi-globe2" aria-hidden="true"></i>
            <select id="header-locale" name="locale" aria-label="<?= e(t('locale.label')) ?>">
                <?php foreach (localeOptions() as $code => $label): ?><option value="<?= e($code) ?>" <?= $locale === $code ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?>
            </select>
            <button type="submit"><i class="bi bi-arrow-repeat" aria-hidden="true"></i><span><?= e(t('locale.switch')) ?></span></button>
        </form>

        <div class="profile-menu">
            <div class="profile-avatar" aria-hidden="true"><?= e(initials($username)) ?></div>
            <div class="profile-copy"><span><?= e(t('nav.greeting')) ?></span><strong><?= e($username) ?></strong></div>
            <a class="logout-link" href="<?= e($basePath) ?>logout.php" title="<?= e(t('nav.logout')) ?>" aria-label="<?= e(t('nav.logout')) ?>"><i class="bi bi-box-arrow-right" aria-hidden="true"></i></a>
        </div>
    </div>
</header>
<?php elseif ($hideNavigation): ?>
<div class="auth-locale-control">
    <form class="locale-form" method="POST" action="<?= e($basePath) ?>language.php">
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
        <input type="hidden" name="return_to" value="<?= e($returnTo) ?>">
        <label class="visually-hidden" for="auth-locale"><?= e(t('locale.label')) ?></label>
        <i class="bi bi-globe2" aria-hidden="true"></i>
        <select id="auth-locale" name="locale" aria-label="<?= e(t('locale.label')) ?>">
            <?php foreach (localeOptions() as $code => $label): ?><option value="<?= e($code) ?>" <?= $locale === $code ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?>
        </select>
        <button type="submit"><?= e(t('locale.switch')) ?></button>
    </form>
</div>
<?php endif; ?>

<?php if ($flash): ?>
<div class="toast-notice toast-<?= e($flash['type'] ?? 'info') ?>" role="status" aria-live="polite">
    <i class="bi bi-info-circle-fill" aria-hidden="true"></i><span><?= e(flashMessage($flash)) ?></span>
    <button type="button" aria-label="<?= e(t('common.close_notice')) ?>"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
</div>
<?php endif; ?>

<main id="main-content" tabindex="-1">
