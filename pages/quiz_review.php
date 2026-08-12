<?php
require_once __DIR__ . '/../includes/app.php';
requireAuth('../login.php');
require_once __DIR__ . '/../includes/quiz_bank.php';

$resultToken = (string) ($_GET['result'] ?? '');
$result = $_SESSION['quiz_results'][$resultToken] ?? null;
$themeKey = is_array($result) ? (string) ($result['theme_key'] ?? '') : '';
$questionIds = is_array($result) ? array_values(array_filter($result['question_ids'] ?? [], 'is_string')) : [];
$questions = $themeKey !== '' ? quizQuestionsByIds($themeKey, $questionIds) : [];
$themes = quizThemes();
$theme = $themes[$themeKey] ?? null;

if (!is_array($result) || (int) ($result['expires_at'] ?? 0) < time() || !$theme || count($questions) !== 5) {
    unset($_SESSION['quiz_results'][$resultToken]);
    setFlash('info', 'flash.review_expired');
    header('Location: quiz.php');
    exit;
}

$selectedAnswers = is_array($result['selected_answers'] ?? null) ? $result['selected_answers'] : [];
$storedCorrectAnswers = is_array($result['correct_answers'] ?? null) ? $result['correct_answers'] : [];
$reviewItems = [];
foreach ($questions as $question) {
    $questionId = (string) $question['id'];
    $selected = in_array($selectedAnswers[$questionId] ?? '', ['A', 'B', 'C', 'D'], true) ? $selectedAnswers[$questionId] : '';
    $correct = (string) $question['correct'];
    if (($storedCorrectAnswers[$questionId] ?? null) !== $correct) {
        unset($_SESSION['quiz_results'][$resultToken]);
        setFlash('info', 'flash.review_expired');
        header('Location: quiz.php');
        exit;
    }
    $reviewItems[] = ['question' => $question, 'selected' => $selected, 'correct' => $correct, 'is_correct' => $selected !== '' && hash_equals($correct, $selected)];
}

$score = (int) $result['score'];
$total = (int) $result['total'];
$correctCount = intdiv($score, 10);
$incorrectCount = $total - $correctCount;

$pageTitle = t('review.title');
$basePath = '../';
$activePage = 'quiz';
include __DIR__ . '/../includes/header.php';
?>
<div class="page-shell review-page"><div class="container-wide">
    <header class="review-summary surface-card" data-reveal><div class="review-score-ring" style="--score-progress: <?= min(100, max(0, $score * 2)) ?>%"><div><strong><?= $score ?></strong><span>/ 50</span></div></div><div class="review-summary-copy"><span class="eyebrow"><?= e($theme['title']) ?></span><h1><?= e(scoreLabel($score)) ?>, <?= e((string) ($_SESSION['username'] ?? t('common.student'))) ?>!</h1><p><?= e(t('review.summary', ['answered' => $result['answered'], 'total' => $total, 'time' => formatDuration((int) $result['time_taken'])])) ?></p><div class="review-stats"><span class="review-stat correct"><i class="bi bi-check-circle-fill"></i><strong><?= $correctCount ?></strong> <?= e(t('review.correct')) ?></span><span class="review-stat incorrect"><i class="bi bi-x-circle-fill"></i><strong><?= $incorrectCount ?></strong> <?= e(t('review.incorrect')) ?></span><span class="review-stat"><i class="bi bi-stopwatch-fill"></i><strong><?= e(formatDuration((int) $result['time_taken'])) ?></strong> <?= e(t('review.time')) ?></span></div></div><div class="review-summary-actions"><a class="btn-primary-custom" href="quiz.php?theme=<?= urlencode($themeKey) ?>"><i class="bi bi-arrow-repeat"></i> <?= e(t('review.retry')) ?></a><a class="btn-light-custom" href="leaderboard.php?type=<?= urlencode($themeKey) ?>"><i class="bi bi-trophy"></i> <?= e(t('review.view_ranking', ['theme' => $theme['title']])) ?></a></div></header>
    <div class="review-heading" data-reveal><div><span class="eyebrow"><?= e(t('review.eyebrow')) ?></span><h2 class="section-title"><?= e(t('review.heading')) ?></h2></div><span class="tag"><i class="bi bi-shield-check"></i> <?= e(t('review.server_checked')) ?></span></div>
    <section class="review-list" aria-label="<?= e(t('review.list_label')) ?>">
        <?php foreach ($reviewItems as $index => $item): $question = $item['question']; $selected = $item['selected']; $correct = $item['correct']; ?>
        <article class="review-card surface-card <?= $item['is_correct'] ? 'is-correct' : 'is-incorrect' ?>" data-reveal><div class="review-card-status"><span><?= $item['is_correct'] ? '<i class="bi bi-check-lg"></i>' : '<i class="bi bi-x-lg"></i>' ?></span><div><small><?= e(t('review.question', ['number' => $index + 1])) ?></small><strong><?= e($item['is_correct'] ? t('review.answer_correct') : ($selected === '' ? t('review.not_answered') : t('review.answer_incorrect'))) ?></strong></div></div><h3><?= e($question['question']) ?></h3><div class="review-answer-grid"><div class="review-answer <?= $item['is_correct'] ? 'answer-correct' : 'answer-wrong' ?>"><span><?= e(t('review.your_answer')) ?></span><strong><?= $selected !== '' ? e($selected . '. ' . $question['options'][$selected]) : e(t('review.not_answered_value')) ?></strong></div><?php if (!$item['is_correct']): ?><div class="review-answer answer-correct"><span><?= e(t('review.correct_answer')) ?></span><strong><?= e($correct . '. ' . $question['options'][$correct]) ?></strong></div><?php endif; ?></div><div class="review-explanation"><i class="bi bi-lightbulb-fill"></i><div><strong><?= e(t('review.explanation')) ?></strong><p><?= e($question['explanation']) ?></p></div></div></article>
        <?php endforeach; ?>
    </section>
    <div class="review-footer-actions" data-reveal><a class="btn-light-custom" href="quiz.php"><i class="bi bi-grid"></i> <?= e(t('review.choose_other')) ?></a><a class="btn-secondary-custom" href="quiz.php?theme=<?= urlencode($themeKey) ?>"><?= e(t('review.repeat')) ?> <i class="bi bi-arrow-right"></i></a></div>
</div></div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
