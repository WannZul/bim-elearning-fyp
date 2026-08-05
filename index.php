<?php
require_once __DIR__ . '/includes/app.php';
requireAuth('login.php');
require_once __DIR__ . '/includes/db_connect.php';

$userId = (int) $_SESSION['user_id'];
$username = (string) ($_SESSION['username'] ?? 'Pelajar');
$bestScore = 0;
$totalAttempts = 0;
$bestTime = 0;

$stmt = mysqli_prepare($conn, 'SELECT COALESCE(MAX(score), 0) AS best_score, COUNT(*) AS total_attempts FROM quiz_scores WHERE user_id = ?');
if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $stats = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    $bestScore = (int) ($stats['best_score'] ?? 0);
    $totalAttempts = (int) ($stats['total_attempts'] ?? 0);
    mysqli_stmt_close($stmt);
}

$stmt = mysqli_prepare($conn, 'SELECT time_taken FROM quiz_scores WHERE user_id = ? ORDER BY score DESC, time_taken ASC LIMIT 1');
if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($row = mysqli_fetch_assoc($result)) {
        $bestTime = (int) $row['time_taken'];
    }
    mysqli_stmt_close($stmt);
}

$pageTitle = 'Ruang Pembelajaran';
$basePath = '';
$activePage = 'home';
include __DIR__ . '/includes/header.php';
?>
<section class="dashboard-hero">
    <div class="container-wide">
        <div class="hero-grid">
            <div class="hero-copy" data-reveal>
                <span class="eyebrow">Bahasa Isyarat Malaysia</span>
                <h1 class="display-title">Tangan anda boleh <span>membuka dunia.</span></h1>
                <p>Belajar isyarat asas BIM melalui pelajaran ringkas, latihan kamera masa nyata, dan cabaran yang menjadikan setiap kemajuan terasa bermakna.</p>
                <div class="hero-actions">
                    <a class="btn-primary-custom" href="pages/learn.php"><i class="bi bi-play-fill"></i> Mula belajar</a>
                    <a class="btn-light-custom" href="pages/ai_tracking.php"><i class="bi bi-camera-video"></i> Buka latihan AI</a>
                </div>
                <div class="hero-stat-row" aria-label="Statistik pembelajaran anda">
                    <div class="hero-stat"><strong><?= $bestScore ?>/50</strong><span>Skor terbaik</span></div>
                    <div class="hero-stat"><strong><?= $totalAttempts ?></strong><span>Kuiz diselesaikan</span></div>
                    <div class="hero-stat"><strong><?= $bestTime > 0 ? e(formatDuration($bestTime)) : '—' ?></strong><span>Masa terbaik</span></div>
                </div>
            </div>
            <div class="hero-visual" aria-hidden="true" data-reveal>
                <div class="gesture-orbit">
                    <div class="gesture-core"><i class="bi bi-hand-index-thumb"></i></div>
                    <span class="orbit-chip chip-one"><i class="bi bi-stars"></i> 21 titik tangan</span>
                    <span class="orbit-chip chip-two"><i class="bi bi-camera"></i> Kamera langsung</span>
                    <span class="orbit-chip chip-three"><i class="bi bi-check-circle"></i> Maklum balas segera</span>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="dashboard-content">
    <div class="container-wide">
        <div class="welcome-strip surface-card" data-reveal>
            <div class="welcome-user">
                <div class="profile-avatar"><?= e(initials($username)) ?></div>
                <div><h2>Selamat kembali, <?= e($username) ?>!</h2><p>Satu isyarat hari ini, satu hubungan baharu esok.</p></div>
            </div>
            <div class="streak-block"><i class="bi bi-fire"></i><div><strong>Teruskan momentum</strong><span>Lengkapkan satu aktiviti hari ini</span></div></div>
        </div>

        <div class="section-heading" data-reveal>
            <div><span class="eyebrow">Perjalanan anda</span><h2 class="section-title">Pilih cara anda mahu belajar</h2><p>Daripada memahami asas hingga menguji ketepatan tangan, setiap modul membawa anda selangkah ke hadapan.</p></div>
            <a class="link-arrow" href="pages/learn.php">Lihat semua pelajaran <i class="bi bi-arrow-right"></i></a>
        </div>

        <div class="module-grid">
            <article class="module-card featured surface-card card-hover" data-reveal>
                <span class="module-number">01</span><div class="icon-tile"><i class="bi bi-journal-richtext"></i></div>
                <h3>Kenali asas BIM</h3><p>Pelajari bentuk tangan, arah tapak tangan, dan isyarat abjad serta nombor yang paling berguna.</p>
                <div class="module-meta"><span class="tag teal"><i class="bi bi-collection-play"></i> 6 pelajaran</span><span class="tag"><i class="bi bi-clock"></i> 10 minit</span></div>
                <a class="link-arrow" href="pages/learn.php">Teruskan belajar <i class="bi bi-arrow-right"></i></a>
            </article>
            <article class="module-card surface-card card-hover" data-reveal>
                <span class="module-number">02</span><div class="icon-tile coral"><i class="bi bi-camera-video"></i></div>
                <h3>Studio latihan AI</h3><p>Tunjukkan isyarat statik di hadapan kamera dan terima pengesahan secara masa nyata.</p>
                <div class="module-meta"><span class="tag coral"><i class="bi bi-broadcast"></i> Langsung</span><span class="tag">A, 1–5</span></div>
                <a class="link-arrow" href="pages/ai_tracking.php">Buka kamera <i class="bi bi-arrow-right"></i></a>
            </article>
            <article class="module-card surface-card card-hover" data-reveal>
                <span class="module-number">03</span><div class="icon-tile amber"><i class="bi bi-lightning-charge"></i></div>
                <h3>Cabaran pantas</h3><p>Jawab lima soalan dalam 60 saat. Ketepatan didahulukan, masa memecahkan seri.</p>
                <div class="module-meta"><span class="tag amber"><i class="bi bi-stopwatch"></i> 60 saat</span><span class="tag">50 mata</span></div>
                <a class="link-arrow" href="pages/quiz.php">Mulakan kuiz <i class="bi bi-arrow-right"></i></a>
            </article>
        </div>

        <div class="insight-grid">
            <article class="continue-card surface-card card-hover" data-reveal>
                <div class="lesson-symbol">A</div>
                <div><span class="tag teal">Disyorkan</span><h3>Asas abjad & nombor</h3><p>Mulakan enam isyarat statik pertama dan berlatih mengikut kemampuan anda.</p><div class="module-meta"><span class="tag"><i class="bi bi-collection-play"></i> 6 pelajaran</span><span class="tag"><i class="bi bi-camera-video"></i> Latihan AI</span></div></div>
                <a class="btn-secondary-custom btn-sm-custom" href="pages/learn.php">Sambung <i class="bi bi-arrow-right"></i></a>
            </article>
            <aside class="tip-card surface-card" data-reveal>
                <div class="icon-tile"><i class="bi bi-lightbulb"></i></div><h3>Petua hari ini</h3><p>Pastikan tapak tangan kelihatan penuh dan gunakan pencahayaan dari hadapan untuk pengesanan lebih stabil.</p>
            </aside>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
