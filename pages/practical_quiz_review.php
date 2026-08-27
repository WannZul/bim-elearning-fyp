<?php
require_once __DIR__ . '/../includes/app.php';
requireAuth('../login.php');
require_once __DIR__ . '/../includes/quiz_bank.php';
require_once __DIR__ . '/../includes/sign_catalog.php';

$resultToken = (string) ($_GET['result'] ?? '');
$result = $_SESSION['practical_results'][$resultToken] ?? null;
$category = is_array($result) ? (string) ($result['category'] ?? '') : '';
$issuedTargets = is_array($result) ? array_values(array_filter($result['issued_targets'] ?? [], 'is_string')) : [];
$confirmedTargets = is_array($result) ? array_values(array_filter($result['confirmed_targets'] ?? [], 'is_string')) : [];
$approvedTargets = array_column(cameraChallengeSigns($category), 'symbol');
$confirmedPositions = [];
foreach ($confirmedTargets as $confirmedTarget) {
    $position = array_search($confirmedTarget, $issuedTargets, true);
    if ($position !== false) $confirmedPositions[] = $position;
}
$sortedConfirmedPositions = $confirmedPositions;
sort($sortedConfirmedPositions);
$valid = is_array($result)
    && (int) ($result['expires_at'] ?? 0) >= time()
    && in_array($category, BIM_SIGN_CATEGORIES, true)
    && count($issuedTargets) === 5
    && count(array_unique($issuedTargets)) === 5
    && count(array_diff($issuedTargets, $approvedTargets)) === 0
    && count($confirmedTargets) <= 5
    && count(array_unique($confirmedTargets)) === count($confirmedTargets)
    && count(array_diff($confirmedTargets, $issuedTargets)) === 0
    && count($confirmedPositions) === count($confirmedTargets)
    && $confirmedPositions === $sortedConfirmedPositions
    && (int) ($result['time_taken'] ?? -1) >= 0
    && (int) ($result['time_taken'] ?? -1) <= 90;
if (!$valid) {
    unset($_SESSION['practical_results'][$resultToken]);
    setFlash('info', 'flash.practical_review_expired');
    header('Location: quiz.php');
    exit;
}

$theme = practicalQuizThemes()[$category];
$confirmedCount = count($confirmedTargets);
$pageTitle = t('practical.review_title');
$basePath = '../';
$activePage = 'quiz';
include __DIR__ . '/../includes/header.php';
?>
<div class="page-shell review-page practical-review"><div class="container-wide">
    <header class="review-summary surface-card" data-reveal><div class="review-score-ring" style="--score-progress: <?= min(100, max(0, $confirmedCount * 20)) ?>%"><div><strong><?= $confirmedCount ?></strong><span>/ 5</span></div></div><div class="review-summary-copy"><span class="eyebrow"><?= e($theme['short_title']) ?> · <?= e(t('leaderboard.mode_practical')) ?></span><h1><?= e(t('practical.review_heading')) ?></h1><p><?= e(t('practical.review_summary', ['confirmed' => $confirmedCount, 'total' => 5, 'time' => formatDuration((int) $result['time_taken'])])) ?></p><div class="review-stats"><span class="review-stat correct"><i class="bi bi-camera-video" aria-hidden="true"></i><strong><?= $confirmedCount ?></strong> <?= e(t('practical.status_confirmed')) ?></span><span class="review-stat incorrect"><i class="bi bi-skip-forward" aria-hidden="true"></i><strong><?= 5 - $confirmedCount ?></strong> <?= e(t('practical.status_skipped')) ?></span><span class="review-stat"><i class="bi bi-stopwatch-fill" aria-hidden="true"></i><strong><?= e(formatDuration((int) $result['time_taken'])) ?></strong> <?= e(t('review.time')) ?></span></div></div><div class="review-summary-actions"><a class="btn-primary-custom" href="practical_quiz.php?category=<?= urlencode($category) ?>"><i class="bi bi-arrow-repeat" aria-hidden="true"></i> <?= e(t('practical.retry')) ?></a><a class="btn-light-custom" href="../index.php"><i class="bi bi-graph-up-arrow" aria-hidden="true"></i> <?= e(t('practical.view_progress')) ?></a></div></header>
    <div class="prototype-warning practical-review-warning" role="note" data-reveal><i class="bi bi-shield-exclamation" aria-hidden="true"></i><strong><?= e(t('practical.review_note')) ?></strong></div>
    <section class="practical-review-list" aria-label="<?= e(t('practical.review_list')) ?>">
        <?php foreach ($issuedTargets as $index => $symbol): $sign = signCatalogEntry($category, $symbol); $isConfirmed = in_array($symbol, $confirmedTargets, true); ?>
        <article class="practical-review-item surface-card <?= $isConfirmed ? 'is-confirmed' : 'is-skipped' ?>" data-reveal><span class="practical-review-number"><?= $index + 1 ?></span><div class="lesson-symbol-large"><?= e($symbol) ?></div><div><h2><?= e($sign ? t($sign['content_key'] . '.title') : $symbol) ?></h2><p><i class="bi <?= $isConfirmed ? 'bi-check-circle-fill' : 'bi-dash-circle' ?>" aria-hidden="true"></i> <?= e(t($isConfirmed ? 'practical.status_confirmed' : 'practical.status_skipped')) ?></p></div></article>
        <?php endforeach; ?>
    </section>
    <div class="review-footer-actions" data-reveal><a class="btn-light-custom" href="quiz.php"><i class="bi bi-grid" aria-hidden="true"></i> <?= e(t('practical.choose_other')) ?></a><a class="btn-secondary-custom" href="practical_quiz.php?category=<?= urlencode($category) ?>"><?= e(t('practical.retry')) ?> <i class="bi bi-arrow-right" aria-hidden="true"></i></a></div>
</div></div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
