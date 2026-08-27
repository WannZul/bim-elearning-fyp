<?php
require_once __DIR__ . '/../includes/app.php';
requireAuth('../login.php');
applyCameraSecurityHeaders();
require_once __DIR__ . '/../includes/sign_catalog.php';

$category = strtolower(trim((string) ($_GET['category'] ?? 'alphabet')));
if (!in_array($category, BIM_SIGN_CATEGORIES, true)) $category = 'alphabet';
$availableSigns = cameraApprovedSigns($category);
$requestedTarget = trim((string) ($_GET['target'] ?? ''));
if ($category === 'alphabet') $requestedTarget = strtoupper($requestedTarget);
$initialSign = signCatalogEntry($category, $requestedTarget);
if (!$initialSign || !$initialSign['camera_eligible']) $initialSign = $availableSigns[0];
$initialTarget = $initialSign['symbol'];
$initialTargetTitle = t($initialSign['content_key'] . '.title');

$practiceManifest = signRecognizerManifest();
foreach ($practiceManifest as $manifestCategory => &$manifestSigns) {
    foreach ($manifestSigns as &$manifestSign) {
        $entry = signCatalogEntry($manifestCategory, $manifestSign['symbol']);
        $manifestSign['title'] = $entry ? t($entry['content_key'] . '.title') : $manifestSign['symbol'];
    }
    unset($manifestSign);
}
unset($manifestSigns);

$pageTitle = t('ai.title');
$basePath = '../';
$activePage = 'practice';
include __DIR__ . '/../includes/header.php';
?>
<div class="page-shell"><div class="container-wide">
    <header class="page-intro" data-reveal><div><span class="eyebrow"><?= e(t('ai.eyebrow')) ?></span><h1 class="page-title"><?= e(t('ai.title')) ?></h1><p><?= e(t('ai.intro')) ?></p></div><span class="ai-intro-badge"><span class="pulse-dot" aria-hidden="true"></span> <?= e(t('ai.browser_processing')) ?></span></header>
    <div class="practice-category-bar surface-card" data-reveal><div><strong><?= e(t('ai.category_label')) ?></strong><span id="category-count"><?= e(t('ai.' . $category . '_count')) ?></span></div><div class="practice-category-controls" role="group" aria-label="<?= e(t('ai.category_label')) ?>"><?php foreach (BIM_SIGN_CATEGORIES as $categoryOption): ?><button class="lesson-tab <?= $categoryOption === $category ? 'active' : '' ?>" type="button" data-category="<?= e($categoryOption) ?>" aria-pressed="<?= $categoryOption === $category ? 'true' : 'false' ?>"><?= e(t('ai.' . $categoryOption)) ?></button><?php endforeach; ?></div></div>
    <p class="category-scope-note" data-reveal><i class="bi bi-intersect" aria-hidden="true"></i> <?= e(t('ai.equivalent_note')) ?></p>
    <p class="category-scope-note" data-reveal><i class="bi bi-clipboard2-pulse" aria-hidden="true"></i> <?= e(t('ai.validation_note')) ?></p>
    <div class="ai-layout">
        <section class="camera-card surface-card" data-reveal aria-label="<?= e(t('ai.camera_label')) ?>">
            <div class="camera-topbar"><div class="camera-status"><span class="camera-status-dot" id="camera-status-dot" aria-hidden="true"></span><span id="ai-status"><?= e(t('ai.not_started')) ?></span></div><div class="camera-actions"><button class="camera-icon-button" id="stop-camera" type="button" title="<?= e(t('ai.stop')) ?>" aria-label="<?= e(t('ai.stop')) ?>" disabled><i class="bi bi-stop-fill" aria-hidden="true"></i></button></div></div>
            <div class="camera-stage"><video id="input_video" playsinline muted aria-label="<?= e(t('ai.video_label')) ?>"></video><canvas id="output_canvas" width="640" height="480" aria-hidden="true"></canvas><div class="camera-guide" aria-hidden="true"></div><div class="camera-placeholder" id="camera-placeholder"><div><div class="placeholder-icon" aria-hidden="true"><i class="bi bi-camera-video"></i></div><h2><?= e(t('ai.ready')) ?></h2><p><?= e(t('ai.ready_desc')) ?></p><button class="btn-primary-custom" id="start-camera" type="button"><i class="bi bi-camera-video" aria-hidden="true"></i> <?= e(t('ai.start')) ?></button></div></div></div>
            <div class="camera-feedback"><div class="detected-sign" id="gesture-result">—</div><div class="feedback-copy"><span><?= e(t('ai.detected')) ?></span><strong id="feedback-message"><?= e(t('ai.waiting_camera')) ?></strong></div><div class="confidence-block"><span id="confidence-label"><?= e(t('ai.stability_zero')) ?></span><div class="confidence-track" id="confidence-progress" role="progressbar" aria-label="<?= e(t('ai.confidence_label')) ?>" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="confidence-fill" id="confidence-fill"></div></div></div></div>
            <p class="visually-hidden" id="camera-announcer" role="status" aria-live="polite" aria-atomic="true"></p><p class="visually-hidden" id="detection-announcer" role="status" aria-live="polite" aria-atomic="true"></p>
        </section>
        <aside class="practice-panel">
            <section class="target-card surface-card" data-reveal><div class="target-card-header"><span id="target-heading"><?= e(t('ai.target_heading', ['category' => t('ai.' . $category)])) ?></span><span class="tag teal" id="practice-state"><i class="bi bi-hourglass-split" aria-hidden="true"></i> <?= e(t('ai.js.not_started')) ?></span></div><div class="target-display"><strong id="target-symbol"><?= e($initialTarget) ?></strong></div><h2 id="target-title"><?= e($initialTargetTitle) ?></h2><p id="target-instruction"><?= e(t('ai.instruction')) ?></p><div class="target-selector categorized-target-selector" id="target-selector" role="group" aria-label="<?= e(t('ai.target_label')) ?>"><?php foreach ($availableSigns as $sign): ?><button class="target-button <?= $sign['symbol'] === $initialTarget ? 'active' : '' ?>" type="button" data-target="<?= e($sign['symbol']) ?>" aria-pressed="<?= $sign['symbol'] === $initialTarget ? 'true' : 'false' ?>"><?= e($sign['symbol']) ?></button><?php endforeach; ?></div></section>
            <section class="instruction-card surface-card" data-reveal><h3><?= e(t('ai.best_reading')) ?></h3><div class="instruction-list"><div class="instruction-step"><span>1</span><p><?= e(t('ai.step1')) ?></p></div><div class="instruction-step"><span>2</span><p><?= e(t('ai.step2')) ?></p></div><div class="instruction-step"><span>3</span><p><?= e(t('ai.step3')) ?></p></div></div></section>
            <div class="privacy-note"><i class="bi bi-shield-lock-fill" aria-hidden="true"></i><span><?= e(t('ai.privacy')) ?></span></div>
        </aside>
    </div>
</div></div>
<script>window.BIM_SIGN_PRACTICE = <?= json_encode(['category' => $category, 'target' => $initialTarget, 'manifest' => $practiceManifest, 'categoryLabels' => ['alphabet' => t('ai.alphabet'), 'numbers' => t('ai.numbers')], 'categoryCounts' => ['alphabet' => t('ai.alphabet_count'), 'numbers' => t('ai.numbers_count')]], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;</script>
<?php
$clientI18nKeys = [
    'ai.js.load_failed', 'ai.js.reload', 'ai.js.show_hand', 'ai.js.waiting_sign', 'ai.js.form_hold', 'ai.js.start_to_practice',
    'ai.js.unrecognized', 'ai.js.match', 'ai.js.stabilizing', 'ai.js.keep', 'ai.js.almost', 'ai.js.confirmed', 'ai.js.success',
    'ai.js.steady', 'ai.js.mismatch', 'ai.js.trying', 'ai.js.requesting', 'ai.js.active', 'ai.js.access_failed', 'ai.js.allow_camera',
    'ai.js.stopped', 'ai.js.not_started', 'ai.js.stability_zero', 'ai.js.target_changed', 'ai.js.category_changed',
    'ai.target_heading',
];
$pageScripts = <<<'HTML'
<script src="https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils@0.3.1675466862/camera_utils.js" integrity="sha384-q1KhAZhJcJXr3zfC3Tz07pBqQSabwFIZhXlmlUAB8s0zk4ETWERkIKGBCFQ5Jc3e" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/@mediapipe/drawing_utils@0.3.1675466124/drawing_utils.js" integrity="sha384-W/7NVG2tfN12ld8faSFVOZ/W4UHFHze98GqEUPTl8EjY9QDwCKQIzoCHp8/IlIIr" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/@mediapipe/hands@0.4.1675469240/hands.js" integrity="sha384-oHwoZ9HyKv5ark5VOH+XWdbNfmhYtptAOBuV8plz6mAfXvTA6d8fULuYllWouEK2" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/fingerpose@0.1.0/dist/fingerpose.min.js" integrity="sha384-f6Gl/lwyU1P/oralooN3bzpzmtUOS4dJrRV41/EQs02hPHQnnNu2zmvKBv8403ec" crossorigin="anonymous"></script>
<script src="../assets/js/gesture-recognizer.js"></script>
<script src="../assets/js/ai-tracking.js"></script>
HTML;
include __DIR__ . '/../includes/footer.php';
?>
