<?php
require_once __DIR__ . '/includes/app.php';
requireAuth('login.php');
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/database_schema.php';
require_once __DIR__ . '/includes/quiz_bank.php';

$userId = (int) $_SESSION['user_id'];
$username = (string) ($_SESSION['username'] ?? 'Pelajar');
$themes = quizThemes();
$storageReady = quizTypeStorageReady($conn);
$typeStats = [];
$recentAttempts = [];
$bestScore = 0;
$totalAttempts = 0;
$bestTime = 0;

foreach ($themes as $type => $theme) {
    $typeStats[$type] = [
        'title' => $theme['short_title'],
        'icon' => $theme['icon'],
        'accent' => $theme['accent'],
        'best_score' => 0,
        'attempts' => 0,
        'best_time' => 0,
    ];
}

if ($storageReady) {
    $statsStmt = mysqli_prepare($conn, 'SELECT COALESCE(MAX(score), 0) AS best_score, COUNT(*) AS total_attempts FROM quiz_scores WHERE user_id = ? AND quiz_type = ?');
    $timeStmt = mysqli_prepare($conn, 'SELECT time_taken FROM quiz_scores WHERE user_id = ? AND quiz_type = ? ORDER BY score DESC, time_taken ASC LIMIT 1');

    foreach (array_keys($themes) as $type) {
        if ($statsStmt) {
            mysqli_stmt_bind_param($statsStmt, 'is', $userId, $type);
            mysqli_stmt_execute($statsStmt);
            $stats = mysqli_fetch_assoc(mysqli_stmt_get_result($statsStmt));
            $typeStats[$type]['best_score'] = (int) ($stats['best_score'] ?? 0);
            $typeStats[$type]['attempts'] = (int) ($stats['total_attempts'] ?? 0);
        }
        if ($timeStmt) {
            mysqli_stmt_bind_param($timeStmt, 'is', $userId, $type);
            mysqli_stmt_execute($timeStmt);
            if ($row = mysqli_fetch_assoc(mysqli_stmt_get_result($timeStmt))) {
                $typeStats[$type]['best_time'] = (int) $row['time_taken'];
            }
        }
    }

    if ($statsStmt) mysqli_stmt_close($statsStmt);
    if ($timeStmt) mysqli_stmt_close($timeStmt);

    foreach ($typeStats as $stats) {
        $totalAttempts += $stats['attempts'];
    }

    $overallStmt = mysqli_prepare($conn, "SELECT score, time_taken FROM quiz_scores WHERE user_id = ? AND quiz_type IN ('numbers', 'alphabet') ORDER BY score DESC, time_taken ASC LIMIT 1");
    if ($overallStmt) {
        mysqli_stmt_bind_param($overallStmt, 'i', $userId);
        mysqli_stmt_execute($overallStmt);
        if ($overall = mysqli_fetch_assoc(mysqli_stmt_get_result($overallStmt))) {
            $bestScore = (int) $overall['score'];
            $bestTime = (int) $overall['time_taken'];
        }
        mysqli_stmt_close($overallStmt);
    }

    $historyStmt = mysqli_prepare($conn, "SELECT quiz_type, score, time_taken, created_at FROM quiz_scores WHERE user_id = ? AND quiz_type IN ('numbers', 'alphabet') ORDER BY created_at DESC LIMIT 5");
    if ($historyStmt) {
        mysqli_stmt_bind_param($historyStmt, 'i', $userId);
        mysqli_stmt_execute($historyStmt);
        $historyResult = mysqli_stmt_get_result($historyStmt);
        while ($row = mysqli_fetch_assoc($historyResult)) {
            $recentAttempts[] = $row;
        }
        mysqli_stmt_close($historyStmt);
    }
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
                <p>Belajar asas BIM melalui pelajaran ringkas, latihan kamera masa nyata, dan cabaran interaktif—direka untuk orang awam serta petugas barisan hadapan.</p>
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

        <?php if (!$storageReady): ?>
        <section class="schema-alert surface-card" role="alert" data-reveal><div class="icon-tile amber"><i class="bi bi-database-exclamation"></i></div><div><h2>Kemas kini pangkalan data diperlukan</h2><p><?= e(quizTypeMigrationMessage()) ?></p></div></section>
        <?php else: ?>
        <section class="dashboard-progress" data-reveal>
            <div class="section-heading"><div><span class="eyebrow">Kemajuan sebenar</span><h2 class="section-title">Pencapaian mengikut kategori</h2><p>Skor dan cubaan disimpan dalam MySQL supaya anda boleh melihat perkembangan bagi abjad dan nombor secara berasingan.</p></div></div>
            <div class="progress-type-grid">
                <?php foreach ($typeStats as $type => $stats): ?>
                <article class="progress-type-card surface-card">
                    <div class="icon-tile <?= e($stats['accent']) ?>"><i class="bi <?= e($stats['icon']) ?>"></i></div>
                    <div class="progress-type-copy"><span>Kuiz <?= e($stats['title']) ?></span><strong><?= (int) $stats['best_score'] ?><small>/50</small></strong><p><?= (int) $stats['attempts'] ?> cubaan · Masa terbaik <?= $stats['best_time'] > 0 ? e(formatDuration((int) $stats['best_time'])) : '—' ?></p></div>
                    <a class="btn-light-custom btn-sm-custom" href="pages/leaderboard.php?type=<?= urlencode($type) ?>">Lihat ranking <i class="bi bi-arrow-right"></i></a>
                </article>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

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
                <div class="module-meta"><span class="tag coral"><i class="bi bi-broadcast"></i> Langsung</span><span class="tag">A, 1–4 · 5 isyarat</span></div>
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
                <div><span class="tag teal">Disyorkan</span><h3>Asas abjad & nombor</h3><p>Mulakan bahan asas BIM dan latih lima isyarat statik terpilih dengan kamera.</p><div class="module-meta"><span class="tag"><i class="bi bi-collection-play"></i> 6 pelajaran</span><span class="tag"><i class="bi bi-camera-video"></i> 5 latihan AI</span></div></div>
                <a class="btn-secondary-custom btn-sm-custom" href="pages/learn.php">Sambung <i class="bi bi-arrow-right"></i></a>
            </article>
            <aside class="tip-card surface-card" data-reveal>
                <div class="icon-tile"><i class="bi bi-people"></i></div><h3>Untuk komunikasi inklusif</h3><p>Asas ejaan jari dan nombor membantu petugas kaunter, hospital, bank, dan peruncitan berinteraksi dengan lebih yakin.</p>
            </aside>
        </div>

        <?php if ($storageReady): ?>
        <section class="history-section" data-reveal>
            <div class="section-heading"><div><span class="eyebrow">Rekod pembelajaran</span><h2 class="section-title">Cubaan kuiz terkini</h2><p>Setiap markah, kategori, masa, dan tarikh direkodkan untuk menunjukkan perkembangan anda.</p></div><a class="link-arrow" href="pages/quiz.php">Ambil kuiz baharu <i class="bi bi-arrow-right"></i></a></div>
            <div class="history-card surface-card">
                <?php if ($recentAttempts): ?>
                <div class="history-list">
                    <?php foreach ($recentAttempts as $attempt): $attemptTheme = $themes[$attempt['quiz_type']] ?? null; if (!$attemptTheme) continue; ?>
                    <div class="history-row">
                        <span class="icon-tile <?= e($attemptTheme['accent']) ?>"><i class="bi <?= e($attemptTheme['icon']) ?>"></i></span>
                        <div class="history-main"><strong>Kuiz <?= e($attemptTheme['short_title']) ?></strong><span><?= e(date('d M Y, H:i', strtotime($attempt['created_at']))) ?></span></div>
                        <div class="history-metric"><strong><?= (int) $attempt['score'] ?>/50</strong><span><?= e(scoreLabel((int) $attempt['score'])) ?></span></div>
                        <div class="history-metric history-time"><strong><?= e(formatDuration((int) $attempt['time_taken'])) ?></strong><span>Masa</span></div>
                        <a class="btn-light-custom btn-sm-custom" href="pages/leaderboard.php?type=<?= urlencode($attempt['quiz_type']) ?>">Ranking</a>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="history-empty"><i class="bi bi-clock-history"></i><div><strong>Belum ada rekod kuiz</strong><p>Lengkapkan kuiz abjad atau nombor untuk mula menjejak kemajuan.</p></div></div>
                <?php endif; ?>
            </div>
        </section>
        <?php endif; ?>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
