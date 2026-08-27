<?php
require_once __DIR__ . '/includes/app.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/includes/db_connect.php';
$error = '';
$username = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');

    if (!verifyCsrf($_POST['csrf_token'] ?? null)) {
        $error = t('errors.csrf');
    } elseif (strlen($username) < 2 || strlen($username) > 50) {
        $error = t('auth.register.username_error');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = t('auth.register.email_error');
    } elseif (strlen($password) < 8) {
        $error = t('auth.register.password_error');
    } else {
        $stmt = mysqli_prepare($conn, 'SELECT id FROM users WHERE email = ? LIMIT 1');
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $exists = mysqli_num_rows(mysqli_stmt_get_result($stmt)) > 0;
        mysqli_stmt_close($stmt);

        if ($exists) {
            $error = t('auth.register.email_exists');
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conn, 'INSERT INTO users (username, email, password) VALUES (?, ?, ?)');
            mysqli_stmt_bind_param($stmt, 'sss', $username, $email, $passwordHash);
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                setFlash('success', 'flash.account_created');
                header('Location: login.php');
                exit;
            }
            mysqli_stmt_close($stmt);
            $error = t('auth.register.create_failed');
        }
    }
}

$pageTitle = t('auth.register.title');
$basePath = '';
$bodyClass = 'auth-body';
$hideNavigation = true;
$hideFooter = true;
$clientI18nKeys = ['common.show_password', 'common.hide_password', 'common.processing'];
include __DIR__ . '/includes/header.php';
?>
<div class="auth-layout">
    <section class="auth-showcase" aria-label="<?= e(t('auth.intro_label')) ?>">
        <a class="brand" href="login.php"><span class="brand-mark" aria-hidden="true"><svg viewBox="0 0 42 42" aria-hidden="true"><path d="M12.5 21.5v-8a2.5 2.5 0 0 1 5 0v5-9a2.5 2.5 0 0 1 5 0v9-7a2.5 2.5 0 0 1 5 0v8-4.5a2.5 2.5 0 0 1 5 0V24c0 8-4.6 12-11.2 12-5.8 0-9.6-3.1-12.6-8l-2.2-3.7a2.7 2.7 0 0 1 4.3-3.2l1.7 1.4Z"/></svg></span><span class="brand-copy"><strong>BIM<span>Boleh</span></strong><small><?= e(t('common.brand_tagline')) ?></small></span></a>
        <div class="auth-story">
            <span class="eyebrow"><?= e(t('auth.register.eyebrow')) ?></span><p class="auth-story-title"><?= e(t('auth.register.headline_before')) ?> <span><?= e(t('auth.register.headline_emphasis')) ?></span></p><p><?= e(t('auth.register.story')) ?></p>
            <div class="auth-points"><span class="auth-point"><i aria-hidden="true" class="bi bi-check2-circle"></i> <?= e(t('auth.register.point_free')) ?></span><span class="auth-point"><i aria-hidden="true" class="bi bi-shield-check"></i> <?= e(t('auth.register.point_safe')) ?></span><span class="auth-point"><i aria-hidden="true" class="bi bi-graph-up-arrow"></i> <?= e(t('auth.register.point_track')) ?></span></div>
        </div>
        <div class="auth-quote"><?= e(t('auth.register.quote')) ?></div>
    </section>

    <section class="auth-panel">
        <div class="auth-form-wrap">
            <a class="brand mobile-brand" href="login.php"><span class="brand-mark" aria-hidden="true"><i aria-hidden="true" class="bi bi-hand-index-thumb"></i></span><span class="brand-copy"><strong>BIM<span>Boleh</span></strong><small><?= e(t('common.brand_tagline')) ?></small></span></a>
            <span class="eyebrow"><?= e(t('auth.register.join')) ?></span><h1><?= e(t('auth.register.heading')) ?></h1><p><?= e(t('auth.register.subheading')) ?></p>
            <?php if ($error): ?><div class="form-alert error" id="auth-form-alert" role="alert" tabindex="-1"><i aria-hidden="true" class="bi bi-exclamation-circle-fill"></i><span><?= e($error) ?></span></div><?php endif; ?>
            <form method="POST" action="register.php" data-submit-loading>
                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                <div class="form-group-custom"><label for="username"><?= e(t('auth.register.username')) ?></label><div class="input-shell"><i aria-hidden="true" class="bi bi-person"></i><input class="input-control-custom" id="username" name="username" type="text" value="<?= e($username) ?>" placeholder="<?= e(t('auth.register.username_placeholder')) ?>" minlength="2" maxlength="50" autocomplete="name" required></div></div>
                <div class="form-group-custom"><label for="email"><?= e(t('auth.email')) ?></label><div class="input-shell"><i aria-hidden="true" class="bi bi-envelope"></i><input class="input-control-custom" id="email" name="email" type="email" value="<?= e($email) ?>" placeholder="<?= e(t('auth.email_placeholder')) ?>" autocomplete="email" required></div></div>
                <div class="form-group-custom"><label for="password"><?= e(t('auth.password')) ?></label><div class="input-shell"><i aria-hidden="true" class="bi bi-lock"></i><input class="input-control-custom" id="password" name="password" type="password" placeholder="<?= e(t('auth.register.password_placeholder')) ?>" minlength="8" autocomplete="new-password" aria-describedby="register-password-hint" required><button class="password-toggle" type="button" data-password-toggle="password" aria-label="<?= e(t('common.show_password')) ?>"><i aria-hidden="true" class="bi bi-eye"></i></button></div><small class="form-hint" id="register-password-hint"><?= e(t('auth.register.password_hint')) ?></small></div>
                <label class="form-check-custom"><input type="checkbox" required><span><?= e(t('auth.register.consent')) ?></span></label>
                <button class="btn-primary-custom btn-wide" type="submit"><?= e(t('auth.register.submit')) ?> <i aria-hidden="true" class="bi bi-arrow-right"></i></button>
            </form>
            <p class="auth-switch"><?= e(t('auth.register.has_account')) ?> <a href="login.php"><?= e(t('auth.register.login')) ?></a></p>
            <div class="auth-trust"><i aria-hidden="true" class="bi bi-shield-check"></i> <?= e(t('auth.register.trust')) ?></div>
        </div>
    </section>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
