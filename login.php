<?php
require_once __DIR__ . '/includes/app.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/includes/db_connect.php';
$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');

    if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
        $error = t('errors.csrf');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        $error = t('auth.login.invalid_fields');
    } else {
        $stmt = mysqli_prepare($conn, 'SELECT id, username, password FROM users WHERE email = ? LIMIT 1');
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 's', $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $user = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);

            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int) $user['id'];
                $_SESSION['username'] = (string) $user['username'];
                setFlash('success', 'flash.welcome_back');
                header('Location: index.php');
                exit;
            }
        }
        $error = t('auth.login.invalid_credentials');
    }
}

$pageTitle = t('auth.login.title');
$basePath = '';
$bodyClass = 'auth-body';
$hideNavigation = true;
$hideFooter = true;
$clientI18nKeys = ['common.show_password', 'common.hide_password', 'common.processing'];
include __DIR__ . '/includes/header.php';
?>
<div class="auth-layout">
    <section class="auth-showcase" aria-label="<?= e(t('auth.intro_label')) ?>">
        <a class="brand" href="login.php"><span class="brand-mark"><svg viewBox="0 0 42 42" aria-hidden="true"><path d="M12.5 21.5v-8a2.5 2.5 0 0 1 5 0v5-9a2.5 2.5 0 0 1 5 0v9-7a2.5 2.5 0 0 1 5 0v8-4.5a2.5 2.5 0 0 1 5 0V24c0 8-4.6 12-11.2 12-5.8 0-9.6-3.1-12.6-8l-2.2-3.7a2.7 2.7 0 0 1 4.3-3.2l1.7 1.4Z"/></svg></span><span class="brand-copy"><strong>BIM<span>Boleh</span></strong><small><?= e(t('common.brand_tagline')) ?></small></span></a>
        <div class="auth-story">
            <span class="eyebrow"><?= e(t('auth.login.eyebrow')) ?></span>
            <h1><?= e(t('auth.login.headline_before')) ?> <span><?= e(t('auth.login.headline_emphasis')) ?></span></h1>
            <p><?= e(t('auth.login.story')) ?></p>
            <div class="auth-points"><span class="auth-point"><i class="bi bi-camera-video"></i> <?= e(t('auth.login.point_ai')) ?></span><span class="auth-point"><i class="bi bi-lightning-charge"></i> <?= e(t('auth.login.point_quiz')) ?></span><span class="auth-point"><i class="bi bi-trophy"></i> <?= e(t('auth.login.point_board')) ?></span></div>
        </div>
        <div class="auth-quote"><?= e(t('auth.login.quote')) ?></div>
    </section>

    <section class="auth-panel">
        <div class="auth-form-wrap">
            <a class="brand mobile-brand" href="login.php"><span class="brand-mark"><i class="bi bi-hand-index-thumb"></i></span><span class="brand-copy"><strong>BIM<span>Boleh</span></strong><small><?= e(t('common.brand_tagline')) ?></small></span></a>
            <span class="eyebrow"><?= e(t('auth.login.welcome')) ?></span><h2><?= e(t('auth.login.heading')) ?></h2><p><?= e(t('auth.login.subheading')) ?></p>
            <?php if ($error): ?><div class="form-alert error" role="alert"><i class="bi bi-exclamation-circle-fill"></i><span><?= e($error) ?></span></div><?php endif; ?>
            <form method="POST" action="login.php" data-submit-loading>
                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                <div class="form-group-custom"><label for="email"><?= e(t('auth.email')) ?></label><div class="input-shell"><i class="bi bi-envelope"></i><input class="input-control-custom" id="email" name="email" type="email" value="<?= e($email) ?>" placeholder="<?= e(t('auth.email_placeholder')) ?>" autocomplete="email" required></div></div>
                <div class="form-group-custom"><label for="password"><?= e(t('auth.password')) ?></label><div class="input-shell"><i class="bi bi-lock"></i><input class="input-control-custom" id="password" name="password" type="password" placeholder="<?= e(t('auth.login.password_placeholder')) ?>" autocomplete="current-password" required><button class="password-toggle" type="button" data-password-toggle="password" aria-label="<?= e(t('common.show_password')) ?>"><i class="bi bi-eye"></i></button></div></div>
                <button class="btn-primary-custom btn-wide" type="submit"><?= e(t('auth.login.submit')) ?> <i class="bi bi-arrow-right"></i></button>
            </form>
            <p class="auth-switch"><?= e(t('auth.login.no_account')) ?> <a href="register.php"><?= e(t('auth.login.register')) ?></a></p>
            <div class="auth-trust"><i class="bi bi-shield-check"></i> <?= e(t('auth.trust_password')) ?></div>
        </div>
    </section>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
