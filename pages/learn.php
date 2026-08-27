<?php
require_once __DIR__ . '/../includes/app.php';
requireAuth('../login.php');
require_once __DIR__ . '/../includes/sign_catalog.php';

$lessons = signCatalog();
foreach ($lessons as &$lesson) {
    $contentKey = $lesson['content_key'];
    $lesson['title'] = t($contentKey . '.title');
    $lesson['description'] = t($contentKey . '.description');
    $lesson['tip'] = t($contentKey . '.tip');
}
unset($lesson);

$pageTitle = t('learn.title');
$basePath = '../';
$activePage = 'learn';
include __DIR__ . '/../includes/header.php';
?>
<div class="page-shell"><div class="container-wide">
    <header class="page-intro" data-reveal><div><span class="eyebrow"><?= e(t('learn.eyebrow')) ?></span><h1 class="page-title"><?= e(t('learn.heading')) ?></h1><p><?= e(t('learn.intro')) ?></p></div><div class="page-intro-actions"><a class="btn-light-custom" href="../index.php"><i class="bi bi-arrow-left" aria-hidden="true"></i> <?= e(t('common.home')) ?></a><a class="btn-primary-custom" href="ai_tracking.php?category=alphabet"><i class="bi bi-camera-video" aria-hidden="true"></i> <?= e(t('learn.ai')) ?></a></div></header>
    <section class="learn-summary"><article class="path-card surface-card" data-reveal><span class="tag teal"><i class="bi bi-compass" aria-hidden="true"></i> <?= e(t('learn.path')) ?></span><h2><?= e(t('learn.path_title')) ?></h2><p><?= e(t('learn.path_desc')) ?></p><div class="path-progress"><span class="tag teal"><i class="bi bi-collection-play" aria-hidden="true"></i> <?= e(t('learn.lessons_available')) ?></span><span class="tag"><i class="bi bi-camera-video" aria-hidden="true"></i> <?= e(t('learn.ai_verified')) ?></span></div></article><aside class="guidance-card surface-card" data-reveal><div class="icon-tile coral"><i class="bi bi-brightness-high" aria-hidden="true"></i></div><div><h3><?= e(t('learn.before')) ?></h3><p><?= e(t('learn.before_desc')) ?></p></div></aside></section>
    <div class="section-heading" data-reveal><div><span class="eyebrow"><?= e(t('learn.collection')) ?></span><h2 class="section-title"><?= e(t('learn.first_signs')) ?></h2></div><span class="tag"><i class="bi bi-grid-3x3-gap" aria-hidden="true"></i> <?= e(t('learn.duration')) ?></span></div>
    <div class="lesson-tabs" role="group" aria-label="<?= e(t('learn.filter_label')) ?>" aria-controls="sign-catalog"><button class="lesson-tab active" type="button" data-lesson-filter="all" aria-pressed="true"><?= e(t('learn.all_count')) ?></button><button class="lesson-tab" type="button" data-lesson-filter="alphabet" aria-pressed="false"><?= e(t('learn.alphabet_count')) ?></button><button class="lesson-tab" type="button" data-lesson-filter="numbers" aria-pressed="false"><?= e(t('learn.numbers_count')) ?></button></div>
    <section class="lesson-grid sign-catalog-grid" id="sign-catalog" aria-label="<?= e(t('learn.list_label')) ?>">
        <?php foreach ($lessons as $index => $lesson): $number = str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT); ?>
        <article class="lesson-card sign-card surface-card card-hover" data-lesson-group="<?= e($lesson['category']) ?>" data-reveal>
            <div class="lesson-visual"><span class="lesson-index"><?= e(t('learn.lesson', ['number' => $number])) ?></span><div class="lesson-symbol-large"><?= e($lesson['symbol']) ?></div></div>
            <div class="lesson-content"><div class="sign-badges"><span class="tag"><i class="bi bi-hand-index-thumb" aria-hidden="true"></i> <?= e(t('learn.one_hand_badge')) ?></span><span class="tag <?= $lesson['motion'] === 'dynamic' ? 'amber' : 'teal' ?>"><i class="bi <?= $lesson['motion'] === 'dynamic' ? 'bi-arrow-repeat' : 'bi-pause-fill' ?>" aria-hidden="true"></i> <?= e(t('learn.' . $lesson['motion'] . '_badge')) ?></span><span class="tag <?= $lesson['camera_eligible'] ? 'teal' : '' ?>"><i class="bi <?= $lesson['camera_eligible'] ? 'bi-camera-video' : 'bi-book' ?>" aria-hidden="true"></i> <?= e(t($lesson['camera_eligible'] ? 'learn.camera_badge' : 'learn.reference_badge')) ?></span></div>
                <h3><?= e($lesson['title']) ?></h3><p><?= e($lesson['description']) ?></p><div class="lesson-tip"><i class="bi bi-hand-index-thumb" aria-hidden="true"></i> <?= e($lesson['tip']) ?></div>
                <?php if (!$lesson['camera_eligible']): ?><p class="camera-unavailable"><strong><?= e(t('learn.unavailable_label')) ?></strong> <?= e(t('learn.unavailable.' . $lesson['unavailable_reason'])) ?></p><?php endif; ?>
                <div class="lesson-footer"><?php if ($lesson['camera_eligible']): ?><a class="btn-light-custom btn-sm-custom" href="ai_tracking.php?category=<?= urlencode($lesson['category']) ?>&amp;target=<?= urlencode($lesson['symbol']) ?>" aria-label="<?= e(t('learn.practice_aria', ['title' => $lesson['title']])) ?>"><i class="bi bi-camera-video" aria-hidden="true"></i> <?= e(t('learn.camera_badge')) ?></a><?php else: ?><span class="tag"><i class="bi bi-book" aria-hidden="true"></i> <?= e(t('learn.reference_badge')) ?></span><?php endif; ?><a class="sign-reference-link" href="<?= e($lesson['reference_url']) ?>" target="_blank" rel="noopener noreferrer"><?= e(t('learn.reference_link')) ?> <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i></a></div>
            </div>
        </article>
        <?php endforeach; ?>
    </section>
    <section class="lesson-cta surface-card" data-reveal><div><h2><?= e(t('learn.ready')) ?></h2><p><?= e(t('learn.ready_desc')) ?></p></div><a class="btn-secondary-custom" href="ai_tracking.php?category=alphabet"><i class="bi bi-camera-video" aria-hidden="true"></i> <?= e(t('learn.open_ai')) ?></a></section>
    <p class="reference-disclaimer"><i class="bi bi-info-circle" aria-hidden="true"></i> <span><?= e(t('learn.prototype')) ?></span> <a href="<?= e(BIM_SIGN_REFERENCE_URL) ?>" target="_blank" rel="noopener noreferrer"><?= e(t('learn.reference_link')) ?></a></p>
</div></div>
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
