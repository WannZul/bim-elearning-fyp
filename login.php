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
        $error = 'Sesi borang telah tamat. Sila muat semula halaman dan cuba lagi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        $error = 'Sila masukkan e-mel dan kata laluan yang sah.';
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
                setFlash('success', 'Selamat kembali! Pembelajaran anda sedia diteruskan.');
                header('Location: index.php');
                exit;
            }
        }

        $error = 'E-mel atau kata laluan tidak tepat. Sila cuba lagi.';
    }
}

$pageTitle = 'Log Masuk';
$basePath = '';
$bodyClass = 'auth-body';
$hideNavigation = true;
$hideFooter = true;
include __DIR__ . '/includes/header.php';
?>
<div class="auth-layout">
    <section class="auth-showcase" aria-label="Pengenalan BIMBoleh">
        <a class="brand" href="login.php">
            <span class="brand-mark"><svg viewBox="0 0 42 42" aria-hidden="true"><path d="M12.5 21.5v-8a2.5 2.5 0 0 1 5 0v5-9a2.5 2.5 0 0 1 5 0v9-7a2.5 2.5 0 0 1 5 0v8-4.5a2.5 2.5 0 0 1 5 0V24c0 8-4.6 12-11.2 12-5.8 0-9.6-3.1-12.6-8l-2.2-3.7a2.7 2.7 0 0 1 4.3-3.2l1.7 1.4Z"/></svg></span>
            <span class="brand-copy"><strong>BIM<span>Boleh</span></strong><small>Belajar. Isyarat. Yakin.</small></span>
        </a>
        <div class="auth-story">
            <span class="eyebrow">Komunikasi untuk semua</span>
            <h1>Setiap tangan ada <span>suara.</span></h1>
            <p>Platform pembelajaran Bahasa Isyarat Malaysia yang membantu anda belajar, berlatih dengan kamera, dan membina keyakinan—satu isyarat pada satu masa.</p>
            <div class="auth-points">
                <span class="auth-point"><i class="bi bi-camera-video"></i> Latihan AI masa nyata</span>
                <span class="auth-point"><i class="bi bi-lightning-charge"></i> Kuiz gamifikasi</span>
                <span class="auth-point"><i class="bi bi-trophy"></i> Papan kedudukan</span>
            </div>
        </div>
        <div class="auth-quote">Direka untuk pembelajaran inklusif dan praktikal.</div>
    </section>

    <section class="auth-panel">
        <div class="auth-form-wrap">
            <a class="brand mobile-brand" href="login.php"><span class="brand-mark"><i class="bi bi-hand-index-thumb"></i></span><span class="brand-copy"><strong>BIM<span>Boleh</span></strong><small>Belajar. Isyarat. Yakin.</small></span></a>
            <span class="eyebrow">Selamat kembali</span>
            <h2>Log masuk ke akaun</h2>
            <p>Teruskan perjalanan pembelajaran BIM anda.</p>

            <?php if ($error): ?><div class="form-alert error" role="alert"><i class="bi bi-exclamation-circle-fill"></i><span><?= e($error) ?></span></div><?php endif; ?>

            <form method="POST" action="login.php" data-submit-loading>
                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                <div class="form-group-custom">
                    <label for="email">Alamat e-mel</label>
                    <div class="input-shell"><i class="bi bi-envelope"></i><input class="input-control-custom" id="email" name="email" type="email" value="<?= e($email) ?>" placeholder="nama@contoh.com" autocomplete="email" required></div>
                </div>
                <div class="form-group-custom">
                    <label for="password">Kata laluan</label>
                    <div class="input-shell"><i class="bi bi-lock"></i><input class="input-control-custom" id="password" name="password" type="password" placeholder="Masukkan kata laluan" autocomplete="current-password" required><button class="password-toggle" type="button" data-password-toggle="password" aria-label="Tunjukkan kata laluan"><i class="bi bi-eye"></i></button></div>
                </div>
                <button class="btn-primary-custom btn-wide" type="submit">Log masuk <i class="bi bi-arrow-right"></i></button>
            </form>
            <p class="auth-switch">Belum mempunyai akaun? <a href="register.php">Daftar percuma</a></p>
            <div class="auth-trust"><i class="bi bi-shield-check"></i> Kata laluan anda dilindungi dengan penyulitan selamat</div>
        </div>
    </section>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
