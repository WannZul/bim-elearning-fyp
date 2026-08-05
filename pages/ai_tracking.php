<?php
require_once __DIR__ . '/../includes/app.php';
requireAuth('../login.php');

$allowedTargets = ['A', '1', '2', '3', '4'];
$initialTarget = strtoupper((string) ($_GET['target'] ?? 'A'));
if (!in_array($initialTarget, $allowedTargets, true)) {
    $initialTarget = 'A';
}

$pageTitle = 'Studio Latihan AI';
$basePath = '../';
$activePage = 'practice';
include __DIR__ . '/../includes/header.php';
?>
<div class="page-shell">
    <div class="container-wide">
        <header class="page-intro" data-reveal>
            <div><span class="eyebrow">Latihan berasaskan kamera</span><h1 class="page-title">Studio Latihan AI</h1><p>Proof of concept ini mengesahkan lima isyarat statik sahaja: A dan nombor 1 hingga 4. MediaPipe memetakan 21 titik tangan dan Fingerpose membantu mengelaskan kedudukan jari.</p></div>
            <span class="ai-intro-badge"><span class="pulse-dot"></span> Pemprosesan dalam pelayar</span>
        </header>

        <div class="ai-layout">
            <section class="camera-card surface-card" data-reveal aria-label="Paparan kamera latihan">
                <div class="camera-topbar"><div class="camera-status"><span class="camera-status-dot" id="camera-status-dot"></span><span id="ai-status" aria-live="polite">Kamera belum dimulakan</span></div><div class="camera-actions"><button class="camera-icon-button" id="stop-camera" type="button" title="Hentikan kamera" aria-label="Hentikan kamera" disabled><i class="bi bi-stop-fill"></i></button></div></div>
                <div class="camera-stage">
                    <video id="input_video" playsinline muted aria-label="Paparan kamera anda"></video>
                    <canvas id="output_canvas" width="640" height="480" aria-label="Titik pengesanan tangan"></canvas>
                    <div class="camera-guide" aria-hidden="true"></div>
                    <div class="camera-placeholder" id="camera-placeholder">
                        <div><div class="placeholder-icon"><i class="bi bi-camera-video"></i></div><h2>Sedia untuk berlatih?</h2><p>Benarkan akses kamera, letakkan satu tangan di dalam bingkai, dan tahan isyarat selama kira-kira dua saat.</p><button class="btn-primary-custom" id="start-camera" type="button"><i class="bi bi-camera-video"></i> Mulakan kamera</button></div>
                    </div>
                </div>
                <div class="camera-feedback" aria-live="polite"><div class="detected-sign" id="gesture-result">—</div><div class="feedback-copy"><span>Isyarat dikesan</span><strong id="feedback-message">Menunggu kamera</strong></div><div class="confidence-block"><span id="confidence-label">Kestabilan 0%</span><div class="confidence-track"><div class="confidence-fill" id="confidence-fill"></div></div></div></div>
            </section>

            <aside class="practice-panel">
                <section class="target-card surface-card" data-reveal>
                    <div class="target-card-header"><span>Sasaran latihan</span><span class="tag teal" id="practice-state"><i class="bi bi-hourglass-split"></i> Belum bermula</span></div>
                    <div class="target-display"><strong id="target-symbol"><?= e($initialTarget) ?></strong></div>
                    <h2 id="target-title"><?= $initialTarget === 'A' ? 'Huruf A' : 'Nombor ' . e($initialTarget) ?></h2><p id="target-instruction">Bentuk isyarat dengan jelas dan tahan sehingga sistem mengesahkannya.</p>
                    <div class="target-selector" aria-label="Pilih isyarat sasaran">
                        <?php foreach ($allowedTargets as $target): ?><button class="target-button <?= $target === $initialTarget ? 'active' : '' ?>" type="button" data-target="<?= e($target) ?>" aria-pressed="<?= $target === $initialTarget ? 'true' : 'false' ?>"><?= e($target) ?></button><?php endforeach; ?>
                    </div>
                </section>
                <section class="instruction-card surface-card" data-reveal><h3>Cara mendapatkan bacaan terbaik</h3><div class="instruction-list"><div class="instruction-step"><span>1</span><p>Pastikan seluruh tangan berada dalam bingkai panduan.</p></div><div class="instruction-step"><span>2</span><p>Hadapkan tapak tangan kepada kamera dan elakkan latar yang sibuk.</p></div><div class="instruction-step"><span>3</span><p>Tahan isyarat dengan stabil sehingga pengesahan muncul.</p></div></div></section>
                <div class="privacy-note"><i class="bi bi-shield-lock-fill"></i><span>Video kamera tidak dimuat naik atau disimpan. Semua pengesanan berlaku terus dalam pelayar anda.</span></div>
            </aside>
        </div>
    </div>
</div>
<?php
$pageScripts = <<<'HTML'
<script src="https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils@0.3.1675466862/camera_utils.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/@mediapipe/drawing_utils@0.3.1675466124/drawing_utils.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/@mediapipe/hands@0.4.1675469240/hands.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/fingerpose@0.1.0/dist/fingerpose.min.js" crossorigin="anonymous"></script>
<script src="../assets/js/ai-tracking.js"></script>
HTML;
include __DIR__ . '/../includes/footer.php';
?>
