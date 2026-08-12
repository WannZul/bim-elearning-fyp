<?php
require_once __DIR__ . '/../includes/app.php';
requireAuth('../login.php');

$lessons = [
    ['symbol' => 'A', 'group' => 'abjad'],
    ['symbol' => '1', 'group' => 'nombor'],
    ['symbol' => '2', 'group' => 'nombor'],
    ['symbol' => '3', 'group' => 'nombor'],
    ['symbol' => '4', 'group' => 'nombor'],
    ['symbol' => '5', 'group' => 'nombor', 'ai_enabled' => false],
];
foreach ($lessons as &$lesson) {
    $itemKey = 'learn.items.' . $lesson['symbol'];
    $lesson['title'] = t($itemKey . '.title');
    $lesson['description'] = t($itemKey . '.description');
    $lesson['tip'] = t($itemKey . '.tip');
}
unset($lesson);

$pageTitle = t('learn.title');
$basePath = '../';
$activePage = 'learn';
include __DIR__ . '/../includes/header.php';
?>
<div class="page-shell"><div class="container-wide">
    <header class="page-intro" data-reveal><div><span class="eyebrow"><?= e(t('learn.eyebrow')) ?></span><h1 class="page-title"><?= e(t('learn.heading')) ?></h1><p><?= e(t('learn.intro')) ?></p></div><div class="page-intro-actions"><a class="btn-light-custom" href="../index.php"><i class="bi bi-arrow-left"></i> <?= e(t('common.home')) ?></a><a class="btn-primary-custom" href="ai_tracking.php"><i class="bi bi-camera-video"></i> <?= e(t('learn.ai')) ?></a></div></header>
    <section class="learn-summary"><article class="path-card surface-card" data-reveal><span class="tag teal"><i class="bi bi-compass"></i> <?= e(t('learn.path')) ?></span><h2><?= e(t('learn.path_title')) ?></h2><p><?= e(t('learn.path_desc')) ?></p><div class="path-progress"><span class="tag teal"><i class="bi bi-collection-play"></i> <?= e(t('learn.lessons_available')) ?></span><span class="tag"><i class="bi bi-camera-video"></i> <?= e(t('learn.ai_verified')) ?></span></div></article><aside class="guidance-card surface-card" data-reveal><div class="icon-tile coral"><i class="bi bi-brightness-high"></i></div><div><h3><?= e(t('learn.before')) ?></h3><p><?= e(t('learn.before_desc')) ?></p></div></aside></section>
    <div class="section-heading" data-reveal><div><span class="eyebrow"><?= e(t('learn.collection')) ?></span><h2 class="section-title"><?= e(t('learn.first_signs')) ?></h2></div><span class="tag"><i class="bi bi-clock"></i> <?= e(t('learn.duration')) ?></span></div>
    <div class="lesson-tabs" aria-label="<?= e(t('learn.filter_label')) ?>"><button class="lesson-tab active" type="button" data-lesson-filter="all" aria-pressed="true"><?= e(t('learn.all')) ?></button><button class="lesson-tab" type="button" data-lesson-filter="abjad" aria-pressed="false"><?= e(t('learn.alphabet')) ?></button><button class="lesson-tab" type="button" data-lesson-filter="nombor" aria-pressed="false"><?= e(t('learn.numbers')) ?></button></div>
    <section class="lesson-grid" aria-label="<?= e(t('learn.list_label')) ?>">
        <?php foreach ($lessons as $index => $lesson): $number = str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT); ?>
        <article class="lesson-card surface-card card-hover" data-lesson-group="<?= e($lesson['group']) ?>" data-reveal><div class="lesson-visual"><span class="lesson-index"><?= e(t('learn.lesson', ['number' => $number])) ?></span><div class="lesson-symbol-large"><?= e($lesson['symbol']) ?></div></div><div class="lesson-content"><h3><?= e($lesson['title']) ?></h3><p><?= e($lesson['description']) ?></p><div class="lesson-footer"><span class="lesson-tip"><i class="bi bi-hand-index-thumb"></i> <?= e($lesson['tip']) ?></span><?php if (($lesson['ai_enabled'] ?? true) === true): ?><a class="btn-light-custom btn-sm-custom btn-icon" href="ai_tracking.php?target=<?= urlencode($lesson['symbol']) ?>" aria-label="<?= e(t('learn.practice_aria', ['title' => $lesson['title']])) ?>"><i class="bi bi-play-fill"></i></a><?php else: ?><span class="tag"><i class="bi bi-book"></i> <?= e(t('learn.reference')) ?></span><?php endif; ?></div></div></article>
        <?php endforeach; ?>
    </section>
    <section class="lesson-cta surface-card" data-reveal><div><h2><?= e(t('learn.ready')) ?></h2><p><?= e(t('learn.ready_desc')) ?></p></div><a class="btn-secondary-custom" href="ai_tracking.php"><i class="bi bi-camera-video"></i> <?= e(t('learn.open_ai')) ?></a></section>
    <p class="text-muted-custom mt-3 small"><i class="bi bi-info-circle me-1"></i> <?= e(t('learn.prototype')) ?></p>
</div></div>
<script>
document.querySelectorAll('[data-lesson-filter]').forEach((button) => {
    button.addEventListener('click', () => {
        document.querySelectorAll('[data-lesson-filter]').forEach((item) => { const isActive = item === button; item.classList.toggle('active', isActive); item.setAttribute('aria-pressed', String(isActive)); });
        const filter = button.dataset.lessonFilter;
        document.querySelectorAll('[data-lesson-group]').forEach((card) => { card.hidden = filter !== 'all' && card.dataset.lessonGroup !== filter; });
    });
});
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
