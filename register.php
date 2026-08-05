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
        $error = 'Sesi borang telah tamat. Sila muat semula halaman dan cuba lagi.';
    } elseif (strlen($username) < 2 || strlen($username) > 50) {
        $error = 'Nama pengguna mesti mengandungi antara 2 hingga 50 aksara.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Sila masukkan alamat e-mel yang sah.';
    } elseif (strlen($password) < 8) {
        $error = 'Kata laluan mesti mempunyai sekurang-kurangnya 8 aksara.';
    } else {
        $stmt = mysqli_prepare($conn, 'SELECT id FROM users WHERE email = ? LIMIT 1');
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $exists = mysqli_num_rows(mysqli_stmt_get_result($stmt)) > 0;
        mysqli_stmt_close($stmt);

        if ($exists) {
            $error = 'Alamat e-mel ini telah didaftarkan. Cuba log masuk.';
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conn, 'INSERT INTO users (username, email, password) VALUES (?, ?, ?)');
            mysqli_stmt_bind_param($stmt, 'sss', $username, $email, $passwordHash);

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                setFlash('success', 'Akaun berjaya dicipta. Log masuk untuk mula belajar!');
                header('Location: login.php');
                exit;
            }
            mysqli_stmt_close($stmt);
            $error = 'Akaun tidak dapat dicipta sekarang. Sila cuba sebentar lagi.';
        }
    }
}

$pageTitle = 'Daftar Akaun';
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
            <span class="eyebrow">Mulakan hari ini</span>
            <h1>Belajar untuk <span>memahami.</span></h1>
            <p>Bina kemahiran komunikasi yang benar-benar berguna. Pelajaran kami ringkas, visual, dan disokong latihan kamera interaktif.</p>
            <div class="auth-points"><span class="auth-point"><i class="bi bi-check2-circle"></i> Percuma untuk bermula</span><span class="auth-point"><i class="bi bi-shield-check"></i> Data dilindungi</span><span class="auth-point"><i class="bi bi-graph-up-arrow"></i> Jejak pencapaian</span></div>
        </div>
        <div class="auth-quote">Inklusiviti bermula apabila kita memilih untuk belajar.</div>
    </section>

    <section class="auth-panel">
        <div class="auth-form-wrap">
            <a class="brand mobile-brand" href="login.php"><span class="brand-mark"><i class="bi bi-hand-index-thumb"></i></span><span class="brand-copy"><strong>BIM<span>Boleh</span></strong><small>Belajar. Isyarat. Yakin.</small></span></a>
            <span class="eyebrow">Sertai BIMBoleh</span>
            <h2>Cipta akaun anda</h2>
            <p>Hanya satu minit untuk mula belajar BIM.</p>

            <?php if ($error): ?><div class="form-alert error" role="alert"><i class="bi bi-exclamation-circle-fill"></i><span><?= e($error) ?></span></div><?php endif; ?>

            <form method="POST" action="register.php" data-submit-loading>
                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                <div class="form-group-custom"><label for="username">Nama pengguna</label><div class="input-shell"><i class="bi bi-person"></i><input class="input-control-custom" id="username" name="username" type="text" value="<?= e($username) ?>" placeholder="Nama yang akan dipaparkan" minlength="2" maxlength="50" autocomplete="name" required></div></div>
                <div class="form-group-custom"><label for="email">Alamat e-mel</label><div class="input-shell"><i class="bi bi-envelope"></i><input class="input-control-custom" id="email" name="email" type="email" value="<?= e($email) ?>" placeholder="nama@contoh.com" autocomplete="email" required></div></div>
                <div class="form-group-custom"><label for="password">Kata laluan</label><div class="input-shell"><i class="bi bi-lock"></i><input class="input-control-custom" id="password" name="password" type="password" placeholder="Sekurang-kurangnya 8 aksara" minlength="8" autocomplete="new-password" required><button class="password-toggle" type="button" data-password-toggle="password" aria-label="Tunjukkan kata laluan"><i class="bi bi-eye"></i></button></div><small class="form-hint">Gunakan gabungan huruf, nombor, dan simbol untuk keselamatan lebih baik.</small></div>
                <label class="form-check-custom"><input type="checkbox" required><span>Saya bersetuju menggunakan platform ini untuk tujuan pembelajaran.</span></label>
                <button class="btn-primary-custom btn-wide" type="submit">Cipta akaun <i class="bi bi-arrow-right"></i></button>
            </form>
            <p class="auth-switch">Sudah mempunyai akaun? <a href="login.php">Log masuk</a></p>
            <div class="auth-trust"><i class="bi bi-shield-check"></i> Maklumat anda disimpan dengan selamat</div>
        </div>
    </section>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
