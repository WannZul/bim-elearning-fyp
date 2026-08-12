<?php
require_once __DIR__ . '/../includes/app.php';
requireAuth('../login.php');

$allowedTargets = ['A', '1', '2', '3', '4'];
$initialTarget = strtoupper((string) ($_GET['target'] ?? 'A'));
if (!in_array($initialTarget, $allowedTargets, true)) $initialTarget = 'A';
$initialTargetTitle = $initialTarget === 'A' ? t('ai.target_title_A') : t('ai.target_title_number', ['target' => $initialTarget]);

$pageTitle = t('ai.title');
$basePath = '../';
$activePage = 'practice';
include __DIR__ . '/../includes/header.php';
?>
<div class="page-shell"><div class="container-wide">
    <header class="page-intro" data-reveal><div><span class="eyebrow"><?= e(t('ai.eyebrow')) ?></span><h1 class="page-title"><?= e(t('ai.title')) ?></h1><p><?= e(t('ai.intro')) ?></p></div><span class="ai-intro-badge"><span class="pulse-dot"></span> <?= e(t('ai.browser_processing')) ?></span></header>
    <div class="ai-layout">
        <section class="camera-card surface-card" data-reveal aria-label="<?= e(t('ai.camera_label')) ?>">
            <div class="camera-topbar"><div class="camera-status"><span class="camera-status-dot" id="camera-status-dot"></span><span id="ai-status" aria-live="polite"><?= e(t('ai.not_started')) ?></span></div><div class="camera-actions"><button class="camera-icon-button" id="stop-camera" type="button" title="<?= e(t('ai.stop')) ?>" aria-label="<?= e(t('ai.stop')) ?>" disabled><i class="bi bi-stop-fill"></i></button></div></div>
            <div class="camera-stage"><video id="input_video" playsinline muted aria-label="<?= e(t('ai.video_label')) ?>"></video><canvas id="output_canvas" width="640" height="480" aria-label="<?= e(t('ai.canvas_label')) ?>"></canvas><div class="camera-guide" aria-hidden="true"></div><div class="camera-placeholder" id="camera-placeholder"><div><div class="placeholder-icon"><i class="bi bi-camera-video"></i></div><h2><?= e(t('ai.ready')) ?></h2><p><?= e(t('ai.ready_desc')) ?></p><button class="btn-primary-custom" id="start-camera" type="button"><i class="bi bi-camera-video"></i> <?= e(t('ai.start')) ?></button></div></div></div>
            <div class="camera-feedback" aria-live="polite"><div class="detected-sign" id="gesture-result">—</div><div class="feedback-copy"><span><?= e(t('ai.detected')) ?></span><strong id="feedback-message"><?= e(t('ai.waiting_camera')) ?></strong></div><div class="confidence-block"><span id="confidence-label"><?= e(t('ai.stability_zero')) ?></span><div class="confidence-track"><div class="confidence-fill" id="confidence-fill"></div></div></div></div>
        </section>
        <aside class="practice-panel">
            <section class="target-card surface-card" data-reveal><div class="target-card-header"><span><?= e(t('ai.target')) ?></span><span class="tag teal" id="practice-state"><i class="bi bi-hourglass-split"></i> <?= e(t('ai.js.not_started')) ?></span></div><div class="target-display"><strong id="target-symbol"><?= e($initialTarget) ?></strong></div><h2 id="target-title"><?= e($initialTargetTitle) ?></h2><p id="target-instruction"><?= e(t('ai.instruction')) ?></p><div class="target-selector" aria-label="<?= e(t('ai.target_label')) ?>"><?php foreach ($allowedTargets as $target): ?><button class="target-button <?= $target === $initialTarget ? 'active' : '' ?>" type="button" data-target="<?= e($target) ?>" aria-pressed="<?= $target === $initialTarget ? 'true' : 'false' ?>"><?= e($target) ?></button><?php endforeach; ?></div></section>
            <section class="instruction-card surface-card" data-reveal><h3><?= e(t('ai.best_reading')) ?></h3><div class="instruction-list"><div class="instruction-step"><span>1</span><p><?= e(t('ai.step1')) ?></p></div><div class="instruction-step"><span>2</span><p><?= e(t('ai.step2')) ?></p></div><div class="instruction-step"><span>3</span><p><?= e(t('ai.step3')) ?></p></div></div></section>
            <div class="privacy-note"><i class="bi bi-shield-lock-fill"></i><span><?= e(t('ai.privacy')) ?></span></div>
        </aside>
    </div>
</div></div>
<?php
$clientI18nKeys = [
    'ai.js.load_failed', 'ai.js.reload', 'ai.js.show_hand', 'ai.js.waiting_sign', 'ai.js.form_hold', 'ai.js.start_to_practice',
    'ai.js.unrecognized', 'ai.js.match', 'ai.js.stabilizing', 'ai.js.keep', 'ai.js.almost', 'ai.js.confirmed', 'ai.js.success',
    'ai.js.steady', 'ai.js.mismatch', 'ai.js.trying', 'ai.js.requesting', 'ai.js.active', 'ai.js.access_failed', 'ai.js.allow_camera',
    'ai.js.stopped', 'ai.js.not_started', 'ai.js.stability_zero', 'ai.js.title_A', 'ai.js.title_number',
];
$pageScripts = <<<'HTML'
<script src="https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils@0.3.1675466862/camera_utils.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/@mediapipe/drawing_utils@0.3.1675466124/drawing_utils.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/@mediapipe/hands@0.4.1675469240/hands.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/fingerpose@0.1.0/dist/fingerpose.min.js" crossorigin="anonymous"></script>
<script src="../assets/js/ai-tracking.js"></script>
HTML;
include __DIR__ . '/../includes/footer.php';
?>
