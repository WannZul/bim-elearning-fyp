<?php
require_once __DIR__ . '/includes/app.php';
requireAuth('login.php');
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/database_schema.php';
require_once __DIR__ . '/includes/quiz_bank.php';

$userId = (int) $_SESSION['user_id'];
$username = (string) ($_SESSION['username'] ?? t('common.student'));
$themes = leaderboardThemes();
$storageReady = quizTypeStorageReady($conn);
$typeStats = [];
$recentAttempts = [];
$bestScore = 0;
$totalAttempts = 0;
$bestTime = 0;

foreach ($themes as $type => $theme) {
    $typeStats[$type] = ['title' => $theme['short_title'], 'icon' => $theme['icon'], 'accent' => $theme['accent'], 'best_score' => 0, 'attempts' => 0, 'best_time' => 0];
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
            if ($row = mysqli_fetch_assoc(mysqli_stmt_get_result($timeStmt))) $typeStats[$type]['best_time'] = (int) $row['time_taken'];
        }
    }
    if ($statsStmt) mysqli_stmt_close($statsStmt);
    if ($timeStmt) mysqli_stmt_close($timeStmt);
    foreach ($typeStats as $stats) $totalAttempts += $stats['attempts'];

    $overallStmt = mysqli_prepare($conn, "SELECT score, time_taken FROM quiz_scores WHERE user_id = ? AND quiz_type IN ('numbers', 'alphabet') ORDER BY score DESC, time_taken ASC LIMIT 1");
    if ($overallStmt) {
        mysqli_stmt_bind_param($overallStmt, 'i', $userId);
        mysqli_stmt_execute($overallStmt);
        if ($overall = mysqli_fetch_assoc(mysqli_stmt_get_result($overallStmt))) { $bestScore = (int) $overall['score']; $bestTime = (int) $overall['time_taken']; }
        mysqli_stmt_close($overallStmt);
    }
    $historyStmt = mysqli_prepare($conn, "SELECT quiz_type, score, time_taken, created_at FROM quiz_scores WHERE user_id = ? AND quiz_type IN ('numbers', 'alphabet') ORDER BY created_at DESC LIMIT 5");
    if ($historyStmt) {
        mysqli_stmt_bind_param($historyStmt, 'i', $userId);
        mysqli_stmt_execute($historyStmt);
        $historyResult = mysqli_stmt_get_result($historyStmt);
        while ($row = mysqli_fetch_assoc($historyResult)) $recentAttempts[] = $row;
        mysqli_stmt_close($historyStmt);
    }
}

$pageTitle = t('dashboard.title');
$basePath = '';
$activePage = 'home';
include __DIR__ . '/includes/header.php';
?>
<section class="dashboard-hero"><div class="container-wide"><div class="hero-grid">
    <div class="hero-copy" data-reveal><span class="eyebrow"><?= e(t('dashboard.eyebrow')) ?></span><h1 class="display-title"><?= e(t('dashboard.headline_before')) ?> <span><?= e(t('dashboard.headline_emphasis')) ?></span></h1><p><?= e(t('dashboard.intro')) ?></p>
        <div class="hero-actions"><a class="btn-primary-custom" href="pages/learn.php"><i class="bi bi-play-fill"></i> <?= e(t('dashboard.start')) ?></a><a class="btn-light-custom" href="pages/ai_tracking.php"><i class="bi bi-camera-video"></i> <?= e(t('dashboard.open_ai')) ?></a></div>
        <div class="hero-stat-row" aria-label="<?= e(t('dashboard.stats_label')) ?>"><div class="hero-stat"><strong><?= $bestScore ?>/50</strong><span><?= e(t('dashboard.best_score')) ?></span></div><div class="hero-stat"><strong><?= $totalAttempts ?></strong><span><?= e(t('dashboard.completed')) ?></span></div><div class="hero-stat"><strong><?= $bestTime > 0 ? e(formatDuration($bestTime)) : '—' ?></strong><span><?= e(t('dashboard.best_time')) ?></span></div></div>
    </div>
    <div class="hero-visual" aria-hidden="true" data-reveal><div class="gesture-orbit"><div class="gesture-core"><i class="bi bi-hand-index-thumb"></i></div><span class="orbit-chip chip-one"><i class="bi bi-stars"></i> <?= e(t('dashboard.landmarks')) ?></span><span class="orbit-chip chip-two"><i class="bi bi-camera"></i> <?= e(t('dashboard.live_camera')) ?></span><span class="orbit-chip chip-three"><i class="bi bi-check-circle"></i> <?= e(t('dashboard.instant_feedback')) ?></span></div></div>
</div></div></section>

<section class="dashboard-content"><div class="container-wide">
    <div class="welcome-strip surface-card" data-reveal><div class="welcome-user"><div class="profile-avatar"><?= e(initials($username)) ?></div><div><h2><?= e(t('dashboard.welcome', ['name' => $username])) ?></h2><p><?= e(t('dashboard.welcome_note')) ?></p></div></div><div class="streak-block"><i class="bi bi-fire"></i><div><strong><?= e(t('dashboard.momentum')) ?></strong><span><?= e(t('dashboard.momentum_note')) ?></span></div></div></div>

    <?php if (!$storageReady): ?><section class="schema-alert surface-card" role="alert" data-reveal><div class="icon-tile amber"><i class="bi bi-database-exclamation"></i></div><div><h2><?= e(t('schema.title')) ?></h2><p><?= e(quizTypeMigrationMessage()) ?></p></div></section>
    <?php else: ?><section class="dashboard-progress" data-reveal><div class="section-heading"><div><span class="eyebrow"><?= e(t('dashboard.progress_eyebrow')) ?></span><h2 class="section-title"><?= e(t('dashboard.progress_title')) ?></h2><p><?= e(t('dashboard.progress_intro')) ?></p></div></div><div class="progress-type-grid">
        <?php foreach ($typeStats as $type => $stats): $bestTimeText = $stats['best_time'] > 0 ? formatDuration((int) $stats['best_time']) : '—'; ?>
        <article class="progress-type-card surface-card"><div class="icon-tile <?= e($stats['accent']) ?>"><i class="bi <?= e($stats['icon']) ?>"></i></div><div class="progress-type-copy"><span><?= e(t('dashboard.quiz_type', ['type' => $stats['title']])) ?></span><strong><?= (int) $stats['best_score'] ?><small>/50</small></strong><p><?= e(t('dashboard.attempt_summary', ['count' => $stats['attempts'], 'time' => $bestTimeText])) ?></p></div><a class="btn-light-custom btn-sm-custom" href="pages/leaderboard.php?type=<?= urlencode($type) ?>"><?= e(t('dashboard.view_ranking')) ?> <i class="bi bi-arrow-right"></i></a></article>
        <?php endforeach; ?>
    </div></section><?php endif; ?>

    <div class="section-heading" data-reveal><div><span class="eyebrow"><?= e(t('dashboard.journey')) ?></span><h2 class="section-title"><?= e(t('dashboard.choose')) ?></h2><p><?= e(t('dashboard.choose_intro')) ?></p></div><a class="link-arrow" href="pages/learn.php"><?= e(t('dashboard.all_lessons')) ?> <i class="bi bi-arrow-right"></i></a></div>
    <div class="module-grid">
        <article class="module-card featured surface-card card-hover" data-reveal><span class="module-number">01</span><div class="icon-tile"><i class="bi bi-journal-richtext"></i></div><h3><?= e(t('dashboard.module1_title')) ?></h3><p><?= e(t('dashboard.module1_desc')) ?></p><div class="module-meta"><span class="tag teal"><i class="bi bi-collection-play"></i> <?= e(t('dashboard.six_lessons')) ?></span><span class="tag"><i class="bi bi-clock"></i> <?= e(t('dashboard.ten_minutes')) ?></span></div><a class="link-arrow" href="pages/learn.php"><?= e(t('dashboard.continue_learning')) ?> <i class="bi bi-arrow-right"></i></a></article>
        <article class="module-card surface-card card-hover" data-reveal><span class="module-number">02</span><div class="icon-tile coral"><i class="bi bi-camera-video"></i></div><h3><?= e(t('dashboard.module2_title')) ?></h3><p><?= e(t('dashboard.module2_desc')) ?></p><div class="module-meta"><span class="tag coral"><i class="bi bi-broadcast"></i> <?= e(t('dashboard.live')) ?></span><span class="tag"><?= e(t('dashboard.five_signs')) ?></span></div><a class="link-arrow" href="pages/ai_tracking.php"><?= e(t('dashboard.open_camera')) ?> <i class="bi bi-arrow-right"></i></a></article>
        <article class="module-card surface-card card-hover" data-reveal><span class="module-number">03</span><div class="icon-tile amber"><i class="bi bi-lightning-charge"></i></div><h3><?= e(t('dashboard.module3_title')) ?></h3><p><?= e(t('dashboard.module3_desc')) ?></p><div class="module-meta"><span class="tag amber"><i class="bi bi-stopwatch"></i> <?= e(t('dashboard.sixty_seconds')) ?></span><span class="tag"><?= e(t('dashboard.fifty_points')) ?></span></div><a class="link-arrow" href="pages/quiz.php"><?= e(t('dashboard.start_quiz')) ?> <i class="bi bi-arrow-right"></i></a></article>
    </div>
    <div class="insight-grid"><article class="continue-card surface-card card-hover" data-reveal><div class="lesson-symbol">A</div><div><span class="tag teal"><?= e(t('dashboard.recommended')) ?></span><h3><?= e(t('dashboard.basics_title')) ?></h3><p><?= e(t('dashboard.basics_desc')) ?></p><div class="module-meta"><span class="tag"><i class="bi bi-collection-play"></i> <?= e(t('dashboard.six_lessons')) ?></span><span class="tag"><i class="bi bi-camera-video"></i> <?= e(t('dashboard.five_ai')) ?></span></div></div><a class="btn-secondary-custom btn-sm-custom" href="pages/learn.php"><?= e(t('dashboard.continue')) ?> <i class="bi bi-arrow-right"></i></a></article><aside class="tip-card surface-card" data-reveal><div class="icon-tile"><i class="bi bi-people"></i></div><h3><?= e(t('dashboard.inclusive_title')) ?></h3><p><?= e(t('dashboard.inclusive_desc')) ?></p></aside></div>

    <?php if ($storageReady): ?><section class="history-section" data-reveal><div class="section-heading"><div><span class="eyebrow"><?= e(t('dashboard.record')) ?></span><h2 class="section-title"><?= e(t('dashboard.recent')) ?></h2><p><?= e(t('dashboard.recent_intro')) ?></p></div><a class="link-arrow" href="pages/quiz.php"><?= e(t('dashboard.new_quiz')) ?> <i class="bi bi-arrow-right"></i></a></div><div class="history-card surface-card">
        <?php if ($recentAttempts): ?><div class="history-list"><?php foreach ($recentAttempts as $attempt): $attemptTheme = $themes[$attempt['quiz_type']] ?? null; if (!$attemptTheme) continue; ?><div class="history-row"><span class="icon-tile <?= e($attemptTheme['accent']) ?>"><i class="bi <?= e($attemptTheme['icon']) ?>"></i></span><div class="history-main"><strong><?= e(t('dashboard.quiz_type', ['type' => $attemptTheme['short_title']])) ?></strong><span><?= e(localizedDate($attempt['created_at'], true)) ?></span></div><div class="history-metric"><strong><?= (int) $attempt['score'] ?>/50</strong><span><?= e(scoreLabel((int) $attempt['score'])) ?></span></div><div class="history-metric history-time"><strong><?= e(formatDuration((int) $attempt['time_taken'])) ?></strong><span><?= e(t('common.time')) ?></span></div><a class="btn-light-custom btn-sm-custom" href="pages/leaderboard.php?type=<?= urlencode($attempt['quiz_type']) ?>"><?= e(t('dashboard.ranking')) ?></a></div><?php endforeach; ?></div>
        <?php else: ?><div class="history-empty"><i class="bi bi-clock-history"></i><div><strong><?= e(t('dashboard.no_records')) ?></strong><p><?= e(t('dashboard.no_records_desc')) ?></p></div></div><?php endif; ?>
    </div></section><?php endif; ?>
</div></section>
<?php include __DIR__ . '/includes/footer.php'; ?>
