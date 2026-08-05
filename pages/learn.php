<?php
require_once __DIR__ . '/../includes/app.php';
requireAuth('../login.php');

$lessons = [
    ['symbol' => 'A', 'title' => 'Huruf A', 'description' => 'Genggam empat jari dengan kemas dan letakkan ibu jari di sisi genggaman.', 'tip' => 'Tapak menghadap hadapan', 'group' => 'abjad'],
    ['symbol' => '1', 'title' => 'Nombor Satu', 'description' => 'Angkat jari telunjuk tegak sementara jari lain digenggam dengan selesa.', 'tip' => 'Telunjuk lurus ke atas', 'group' => 'nombor'],
    ['symbol' => '2', 'title' => 'Nombor Dua', 'description' => 'Angkat jari telunjuk dan jari tengah, kemudian genggam jari yang lain.', 'tip' => 'Dua jari direnggangkan', 'group' => 'nombor'],
    ['symbol' => '3', 'title' => 'Nombor Tiga', 'description' => 'Angkat tiga jari tengah secara jelas dengan tangan dalam kedudukan stabil.', 'tip' => 'Pastikan semua jari terlihat', 'group' => 'nombor'],
    ['symbol' => '4', 'title' => 'Nombor Empat', 'description' => 'Angkat empat jari bersama-sama dan lipat ibu jari ke arah tapak tangan.', 'tip' => 'Empat jari selari', 'group' => 'nombor'],
    ['symbol' => '5', 'title' => 'Nombor Lima', 'description' => 'Buka kelima-lima jari dengan tapak tangan jelas menghadap kamera.', 'tip' => 'Jari terbuka dan santai', 'group' => 'nombor'],
];

$pageTitle = 'Pusat Pembelajaran';
$basePath = '../';
$activePage = 'learn';
include __DIR__ . '/../includes/header.php';
?>
<div class="page-shell">
    <div class="container-wide">
        <header class="page-intro" data-reveal>
            <div><span class="eyebrow">Pusat pembelajaran</span><h1 class="page-title">Asas yang kuat bermula di sini.</h1><p>Kenali bentuk tangan untuk isyarat statik asas. Baca panduan, cuba sendiri, kemudian gunakan Studio AI untuk mendapatkan maklum balas masa nyata.</p></div>
            <div class="page-intro-actions"><a class="btn-light-custom" href="../index.php"><i class="bi bi-arrow-left"></i> Utama</a><a class="btn-primary-custom" href="ai_tracking.php"><i class="bi bi-camera-video"></i> Latihan AI</a></div>
        </header>

        <section class="learn-summary">
            <article class="path-card surface-card" data-reveal>
                <span class="tag teal"><i class="bi bi-compass"></i> Laluan permulaan</span><h2>Abjad & nombor asas</h2><p>Enam isyarat statik yang membina koordinasi tangan dan keyakinan sebelum meneroka kosa kata BIM yang lebih luas.</p>
                <div class="path-progress"><span class="tag teal"><i class="bi bi-collection-play"></i> 6 pelajaran tersedia</span><span class="tag"><i class="bi bi-camera-video"></i> Boleh dilatih dengan AI</span></div>
            </article>
            <aside class="guidance-card surface-card" data-reveal><div class="icon-tile coral"><i class="bi bi-brightness-high"></i></div><div><h3>Sebelum anda bermula</h3><p>Gunakan tangan dominan, pastikan pencahayaan datang dari hadapan, dan kekalkan tangan pada paras dada.</p></div></aside>
        </section>

        <div class="section-heading" data-reveal><div><span class="eyebrow">Koleksi pelajaran</span><h2 class="section-title">Isyarat statik pertama anda</h2></div><span class="tag"><i class="bi bi-clock"></i> ± 2 minit setiap pelajaran</span></div>
        <div class="lesson-tabs" aria-label="Tapis pelajaran"><button class="lesson-tab active" type="button" data-lesson-filter="all" aria-pressed="true">Semua isyarat</button><button class="lesson-tab" type="button" data-lesson-filter="abjad" aria-pressed="false">Abjad</button><button class="lesson-tab" type="button" data-lesson-filter="nombor" aria-pressed="false">Nombor</button></div>

        <section class="lesson-grid" aria-label="Senarai pelajaran">
            <?php foreach ($lessons as $index => $lesson): ?>
            <article class="lesson-card surface-card card-hover" data-lesson-group="<?= e($lesson['group']) ?>" data-reveal>
                <div class="lesson-visual"><span class="lesson-index">PELAJARAN <?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span><div class="lesson-symbol-large"><?= e($lesson['symbol']) ?></div></div>
                <div class="lesson-content"><h3><?= e($lesson['title']) ?></h3><p><?= e($lesson['description']) ?></p><div class="lesson-footer"><span class="lesson-tip"><i class="bi bi-hand-index-thumb"></i> <?= e($lesson['tip']) ?></span><a class="btn-light-custom btn-sm-custom btn-icon" href="ai_tracking.php?target=<?= urlencode($lesson['symbol']) ?>" aria-label="Latih <?= e($lesson['title']) ?>"><i class="bi bi-play-fill"></i></a></div></div>
            </article>
            <?php endforeach; ?>
        </section>

        <section class="lesson-cta surface-card" data-reveal><div><h2>Sudah bersedia untuk mencuba?</h2><p>Pilih satu isyarat dan lihat bagaimana MediaPipe bersama Fingerpose membaca bentuk tangan anda.</p></div><a class="btn-secondary-custom" href="ai_tracking.php"><i class="bi bi-camera-video"></i> Buka Studio AI</a></section>
        <p class="text-muted-custom mt-3 small"><i class="bi bi-info-circle me-1"></i> Panduan visual ini ialah bahan prototaip FYP dan perlu disahkan dengan rujukan BIM atau tenaga pengajar bertauliah sebelum penggunaan rasmi.</p>
    </div>
</div>
<script>
document.querySelectorAll('[data-lesson-filter]').forEach((button) => {
    button.addEventListener('click', () => {
        document.querySelectorAll('[data-lesson-filter]').forEach((item) => {
            const isActive = item === button;
            item.classList.toggle('active', isActive);
            item.setAttribute('aria-pressed', String(isActive));
        });
        const filter = button.dataset.lessonFilter;
        document.querySelectorAll('[data-lesson-group]').forEach((card) => {
            card.hidden = filter !== 'all' && card.dataset.lessonGroup !== filter;
        });
    });
});
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
