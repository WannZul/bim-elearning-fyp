<?php
require_once __DIR__ . '/../includes/app.php';
requireAuth('../login.php');
applyCameraSecurityHeaders();
require_once __DIR__ . '/../includes/quiz_bank.php';
require_once __DIR__ . '/../includes/sign_catalog.php';

$category = strtolower(trim((string) ($_GET['category'] ?? '')));
if (!in_array($category, BIM_SIGN_CATEGORIES, true)) {
    header('Location: quiz.php');
    exit;
}
$practicalTheme = practicalQuizThemes()[$category];
$attemptToken = '';
$targets = [];

$now = microtime(true);
$attempts = array_filter($_SESSION['practical_attempts'] ?? [], static function (mixed $attempt) use ($now): bool {
    if (!is_array($attempt)) return false;
    $deadline = $attempt['answer_deadline'] ?? null;
    if (is_numeric($deadline)) return (float) $deadline + 5.0 >= $now;
    return (float) ($attempt['created_at'] ?? 0) + 1800.0 >= $now;
});
$pool = cameraChallengeSigns($category);
shuffle($pool);
$selected = array_slice($pool, 0, 5);
if (count($selected) === 5) {
    $attemptToken = bin2hex(random_bytes(16));
    $targetSymbols = array_column($selected, 'symbol');
    $attempts[$attemptToken] = [
        'category' => $category,
        'targets' => $targetSymbols,
        'created_at' => $now,
        'started_at' => null,
        'answer_deadline' => null,
    ];
    foreach ($selected as $sign) {
        $targets[] = ['symbol' => $sign['symbol'], 'title' => t($sign['content_key'] . '.title')];
    }
}
$_SESSION['practical_attempts'] = array_slice($attempts, -5, null, true);

$pageTitle = t('practical.page_title', ['category' => $practicalTheme['short_title']]);
$basePath = '../';
$activePage = 'quiz';
include __DIR__ . '/../includes/header.php';
?>
<div class="page-shell practical-page"><div class="container-wide">
<?php if (count($targets) !== 5): ?>
    <section class="empty-state surface-card"><div class="icon-tile amber"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i></div><h1><?= e(t('quiz.cannot_start')) ?></h1><p><?= e(t('quiz.bank_incomplete')) ?></p><a class="btn-secondary-custom" href="quiz.php"><i class="bi bi-arrow-left" aria-hidden="true"></i> <?= e(t('common.back')) ?></a></section>
<?php else: ?>
    <header class="practical-intro" data-reveal><div><a class="quiz-back-link" href="quiz.php"><i class="bi bi-arrow-left" aria-hidden="true"></i> <?= e(t('common.back')) ?></a><span class="eyebrow"><?= e(t('practical.eyebrow')) ?></span><h1 class="page-title"><?= e(t('practical.heading', ['category' => $practicalTheme['short_title']])) ?></h1><p><?= e(t('practical.intro')) ?></p></div><div class="prototype-warning" role="note"><i class="bi bi-shield-exclamation" aria-hidden="true"></i><strong><?= e(t('practical.trust')) ?></strong></div></header>
    <div class="practical-toolbar surface-card"><div><strong id="practical-progress"><?= e(t('practical.progress', ['current' => 1, 'total' => 5])) ?></strong><span id="practical-confirmed"><?= e(t('practical.confirmed_count', ['count' => 0, 'total' => 5])) ?></span></div><div class="timer-pill" id="practical-timer-pill"><i class="bi bi-stopwatch" aria-hidden="true"></i><span class="visually-hidden"><?= e(t('practical.time_remaining')) ?></span><strong id="practical-timer">01:30</strong></div></div>
    <div class="practical-layout">
        <section class="camera-card surface-card" aria-label="<?= e(t('practical.camera_label')) ?>" data-reveal>
            <div class="camera-topbar"><div class="camera-status"><span class="camera-status-dot" id="camera-status-dot" aria-hidden="true"></span><span id="practical-camera-status"><?= e(t('ai.not_started')) ?></span></div><button class="camera-icon-button" id="stop-practical-camera" type="button" aria-label="<?= e(t('practical.stop')) ?>" title="<?= e(t('practical.stop')) ?>" disabled><i class="bi bi-stop-fill" aria-hidden="true"></i></button></div>
            <div class="camera-stage"><video id="practical_video" playsinline muted aria-label="<?= e(t('ai.video_label')) ?>"></video><canvas id="practical_canvas" width="640" height="480" aria-hidden="true"></canvas><div class="camera-guide" aria-hidden="true"></div><div class="camera-placeholder" id="practical-placeholder"><div><div class="placeholder-icon" aria-hidden="true"><i class="bi bi-camera-video"></i></div><h2><?= e(t('practical.current_target')) ?>: <span id="placeholder-target"><?= e($targets[0]['symbol']) ?></span></h2><p><?= e(t('practical.waiting')) ?></p><button class="btn-primary-custom" id="start-practical" type="button"><i class="bi bi-camera-video" aria-hidden="true"></i> <?= e(t('practical.start')) ?></button></div></div></div>
            <div class="camera-feedback"><div class="detected-sign" id="practical-detected">—</div><div class="feedback-copy"><span><?= e(t('ai.detected')) ?></span><strong id="practical-feedback"><?= e(t('practical.waiting')) ?></strong></div><div class="confidence-block"><span id="practical-stability"><?= e(t('ai.stability_zero')) ?></span><div class="confidence-track" id="practical-confidence" role="progressbar" aria-label="<?= e(t('ai.confidence_label')) ?>" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="confidence-fill" id="practical-confidence-fill"></div></div></div></div>
            <p class="visually-hidden" id="practical-announcer" role="status" aria-live="polite" aria-atomic="true"></p>
        </section>
        <aside class="practical-target-panel surface-card" data-reveal><span class="eyebrow"><?= e(t('practical.current_target')) ?></span><div class="practical-target-symbol" id="practical-target-symbol"><?= e($targets[0]['symbol']) ?></div><h2 id="practical-target-title"><?= e($targets[0]['title']) ?></h2><ol class="practical-target-list" id="practical-target-list"><?php foreach ($targets as $index => $target): ?><li data-target-index="<?= $index ?>" <?= $index === 0 ? 'aria-current="step"' : '' ?>><span><?= $index + 1 ?></span><strong><?= e($target['symbol']) ?></strong><small><?= e($target['title']) ?></small><span class="visually-hidden" data-target-status><?= e(t('practical.status_pending')) ?></span></li><?php endforeach; ?></ol><button class="btn-light-custom btn-wide" id="skip-practical-target" type="button" disabled><i class="bi bi-skip-forward" aria-hidden="true"></i> <?= e(t('practical.skip')) ?></button></aside>
    </div>
    <div class="practical-privacy surface-card" role="note"><i class="bi bi-shield-lock-fill" aria-hidden="true"></i><p><strong><?= e(t('practical.trust')) ?></strong><br><?= e(t('practical.privacy')) ?></p></div>
    <form id="practical-form" method="POST" action="practical_quiz_process.php"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="attempt_token" value="<?= e($attemptToken) ?>"><input type="hidden" name="confirmations" id="practical-confirmations" value="[]"><button class="visually-hidden" type="submit"><?= e(t('practical.submit')) ?></button></form>
<?php endif; ?>
</div></div>
<?php if (count($targets) === 5): ?>
<script>window.BIM_PRACTICAL = <?= json_encode(['category' => $category, 'targets' => $targets, 'startUrl' => 'practical_quiz_start.php'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;</script>
<?php endif; ?>
<?php
$clientI18nKeys = count($targets) === 5 ? [
    'ai.js.show_hand', 'ai.js.unrecognized', 'ai.js.stabilizing', 'ai.js.match', 'ai.js.keep', 'ai.js.mismatch', 'ai.js.requesting', 'ai.js.active', 'ai.js.access_failed', 'ai.js.allow_camera', 'ai.js.stopped', 'ai.js.stability_zero',
    'practical.progress', 'practical.next_target', 'practical.confirmed_count', 'practical.confirmed', 'practical.skipped', 'practical.status_pending', 'practical.status_confirmed', 'practical.status_skipped', 'practical.timeout', 'practical.submitting', 'practical.waiting', 'practical.activating', 'practical.js.load_failed', 'practical.js.start_failed', 'practical.js.session_start_failed',
] : [];
$pageScripts = count($targets) === 5 ? <<<'HTML'
<script src="https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils@0.3.1675466862/camera_utils.js" integrity="sha384-q1KhAZhJcJXr3zfC3Tz07pBqQSabwFIZhXlmlUAB8s0zk4ETWERkIKGBCFQ5Jc3e" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/@mediapipe/drawing_utils@0.3.1675466124/drawing_utils.js" integrity="sha384-W/7NVG2tfN12ld8faSFVOZ/W4UHFHze98GqEUPTl8EjY9QDwCKQIzoCHp8/IlIIr" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/@mediapipe/hands@0.4.1675469240/hands.js" integrity="sha384-oHwoZ9HyKv5ark5VOH+XWdbNfmhYtptAOBuV8plz6mAfXvTA6d8fULuYllWouEK2" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/fingerpose@0.1.0/dist/fingerpose.min.js" integrity="sha384-f6Gl/lwyU1P/oralooN3bzpzmtUOS4dJrRV41/EQs02hPHQnnNu2zmvKBv8403ec" crossorigin="anonymous"></script>
<script src="../assets/js/gesture-recognizer.js"></script>
<script src="../assets/js/practical-quiz.js"></script>
HTML : '';
include __DIR__ . '/../includes/footer.php';
?>
