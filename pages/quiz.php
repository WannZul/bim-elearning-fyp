<?php
require_once __DIR__ . '/../includes/app.php';
requireAuth('../login.php');
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/database_schema.php';
require_once __DIR__ . '/../includes/quiz_bank.php';

$storageReady = quizTypeStorageReady($conn);
$themes = quizThemes();
$practicalThemes = practicalQuizThemes();
$themeKey = strtolower(trim((string) ($_GET['theme'] ?? '')));
$selectedTheme = $themes[$themeKey] ?? null;
$questions = [];
$attemptToken = '';
$remainingMilliseconds = 60000;
$resumeAnswers = [];

if ($selectedTheme && $storageReady) {
    $now = microtime(true);
    $activeAttempts = $_SESSION['quiz_attempts'] ?? [];
    $activeAttempts = array_filter($activeAttempts, static fn(array $attempt): bool => (float) ($attempt['answer_deadline'] ?? 0) >= $now);
    $resumeToken = (string) ($_SESSION['resume_quiz_attempt'] ?? '');
    unset($_SESSION['resume_quiz_attempt']);
    $resumeAttempt = $activeAttempts[$resumeToken] ?? null;

    if (is_array($resumeAttempt) && ($resumeAttempt['theme'] ?? null) === $themeKey) {
        $resumeQuestionIds = array_values(array_filter($resumeAttempt['question_ids'] ?? [], 'is_string'));
        $questions = quizQuestionsByIds($themeKey, $resumeQuestionIds);
        if (count($questions) === 5) {
            $attemptToken = $resumeToken;
            $resumeAnswers = is_array($resumeAttempt['selected_answers'] ?? null) ? $resumeAttempt['selected_answers'] : [];
            $remainingMilliseconds = max(0, (int) round(((float) $resumeAttempt['answer_deadline'] - microtime(true)) * 1000));
        }
    }

    if (count($questions) !== 5) {
        $availableQuestions = quizQuestionsForTheme($themeKey);
        shuffle($availableQuestions);
        $questions = array_slice($availableQuestions, 0, 5);
        if (count($questions) === 5) {
            $attemptToken = bin2hex(random_bytes(16));
            $activeAttempts[$attemptToken] = ['theme' => $themeKey, 'question_ids' => array_column($questions, 'id'), 'started_at' => $now, 'answer_deadline' => $now + 60.0];
            $remainingMilliseconds = max(0, (int) round(($activeAttempts[$attemptToken]['answer_deadline'] - microtime(true)) * 1000));
        }
    }
    $_SESSION['quiz_attempts'] = array_slice($activeAttempts, -5, null, true);
}

$initialAnswered = count(array_filter($resumeAnswers, static fn(mixed $letter): bool => in_array($letter, ['A', 'B', 'C', 'D'], true)));
$localeSwitchAttemptToken = $attemptToken;

$pageTitle = $selectedTheme ? $selectedTheme['title'] : t('quiz.choose_title');
$basePath = '../';
$activePage = 'quiz';
include __DIR__ . '/../includes/header.php';
?>
<div class="page-shell quiz-shell"><div class="container-wide">
<?php if (!$selectedTheme): ?>
    <?php if (!$storageReady): ?><section class="schema-alert surface-card" role="alert" data-reveal><div class="icon-tile amber"><i class="bi bi-database-exclamation"></i></div><div><h2><?= e(t('quiz.schema_heading')) ?></h2><p><?= e(quizTypeMigrationMessage()) ?></p></div></section><?php endif; ?>
    <header class="quiz-hub-hero" data-reveal><div><span class="eyebrow"><?= e(t('quiz.hub_eyebrow')) ?></span><h1 class="page-title"><?= e(t('quiz.hub_title')) ?></h1><p><?= e(t('quiz.hub_intro')) ?></p></div><div class="quiz-hub-score"><i class="bi bi-lightning-charge-fill"></i><strong>50</strong><span><?= e(t('quiz.max_points')) ?></span></div></header>
    <div class="challenge-section-heading" data-reveal><div><span class="eyebrow"><?= e(t('quiz.theory_heading')) ?></span><h2><?= e(t('quiz.theory_heading')) ?></h2><p><?= e(t('quiz.theory_intro')) ?></p></div></div>
    <section class="quiz-type-grid" aria-label="<?= e(t('quiz.types_label')) ?>">
        <?php foreach ($themes as $key => $theme): ?><article class="quiz-type-card surface-card card-hover accent-<?= e($theme['accent']) ?>" data-reveal><div class="quiz-type-icon"><i class="bi <?= e($theme['icon']) ?>"></i></div><div class="quiz-type-number">0<?= array_search($key, array_keys($themes), true) + 1 ?></div><span class="tag"><i class="bi bi-stopwatch"></i> <?= e($theme['duration']) ?></span><h2><?= e($theme['title']) ?></h2><p><?= e($theme['description']) ?></p><div class="quiz-type-meta"><span><i class="bi bi-list-check"></i> <?= e(t('quiz.random_questions')) ?></span><span><i class="bi bi-chat-square-text"></i> <?= e(t('quiz.answer_review')) ?></span></div><?php if ($storageReady): ?><a class="btn-secondary-custom btn-wide" href="quiz.php?theme=<?= urlencode($key) ?>"><?= e(t('quiz.choose')) ?> <i class="bi bi-arrow-right"></i></a><?php else: ?><span class="btn-light-custom btn-wide" aria-disabled="true"><i class="bi bi-lock"></i> <?= e(t('quiz.migration')) ?></span><?php endif; ?></article><?php endforeach; ?>
    </section>
    <div class="challenge-section-heading practical-section-heading" data-reveal><div><span class="eyebrow"><?= e(t('leaderboard.mode_practical')) ?></span><h2><?= e(t('quiz.practical_heading')) ?></h2><p><?= e(t('quiz.practical_intro')) ?></p></div></div>
    <section class="quiz-type-grid practical-type-grid" aria-label="<?= e(t('quiz.practical_heading')) ?>">
        <?php foreach ($practicalThemes as $key => $theme): ?><article class="quiz-type-card practical-type-card surface-card card-hover accent-<?= e($theme['accent']) ?>" data-reveal><div class="quiz-type-icon"><i class="bi bi-camera-video" aria-hidden="true"></i></div><div class="quiz-type-number"><i class="bi bi-hand-index-thumb" aria-hidden="true"></i></div><span class="tag coral"><i class="bi bi-stopwatch" aria-hidden="true"></i> <?= e($theme['duration']) ?></span><h2><?= e($theme['title']) ?></h2><p><?= e(t('quiz.practical_intro')) ?></p><div class="quiz-type-meta"><span><i class="bi bi-shuffle" aria-hidden="true"></i> <?= e(t('quiz.practical_card_meta')) ?></span><span><i class="bi bi-shield-exclamation" aria-hidden="true"></i> <?= e(t('practical.trust')) ?></span></div><a class="btn-secondary-custom btn-wide" href="practical_quiz.php?category=<?= urlencode($key) ?>"><?= e(t('quiz.practical_choose')) ?> <i class="bi bi-arrow-right" aria-hidden="true"></i></a></article><?php endforeach; ?>
    </section>
    <div class="quiz-hub-note" data-reveal><i class="bi bi-bullseye"></i><p><strong><?= e(t('quiz.scope_label')) ?></strong> <?= e(t('quiz.scope')) ?></p></div>
<?php elseif (!$storageReady): ?>
    <section class="empty-state surface-card"><div class="icon-tile amber"><i class="bi bi-database-exclamation"></i></div><h2><?= e(t('schema.title')) ?></h2><p><?= e(quizTypeMigrationMessage()) ?></p><a class="btn-secondary-custom" href="quiz.php"><i class="bi bi-arrow-left"></i> <?= e(t('common.back')) ?></a></section>
<?php elseif (count($questions) === 5): ?>
    <div class="quiz-toolbar surface-card"><div class="quiz-progress-info"><strong><?= e($selectedTheme['title']) ?></strong><span><?= str_replace('__COUNT__', '<span id="answered-count">' . $initialAnswered . '</span>', e(t('quiz.answered_count', ['count' => '__COUNT__']))) ?></span></div><div class="timer-pill" id="timer-pill" aria-live="polite"><i class="bi bi-stopwatch"></i><strong id="timer">01:00</strong></div><div class="quiz-progress-info"><strong><?= e(t('dashboard.fifty_points')) ?></strong><span><?= e(t('quiz.ten_each')) ?></span></div></div>
    <div class="quiz-content"><header class="quiz-intro" data-reveal><a class="quiz-back-link" href="quiz.php"><i class="bi bi-arrow-left"></i> <?= e(t('quiz.change_type')) ?></a><span class="eyebrow"><?= e($selectedTheme['short_title']) ?></span><h1><?= e(t('quiz.challenge_title')) ?></h1><p><?= e(t('quiz.challenge_intro')) ?></p></header>
        <form id="quizForm" method="POST" action="quiz_process.php" data-remaining-ms="<?= $remainingMilliseconds ?>"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="attempt_token" value="<?= e($attemptToken) ?>">
        <?php foreach ($questions as $index => $question): ?><section class="question-card surface-card" data-question-card data-reveal><div class="question-number"><span><?= e(t('quiz.question_number', ['number' => $index + 1])) ?></span><span class="answered-marker"><i class="bi bi-check-circle-fill"></i> <?= e(t('quiz.answered')) ?></span></div><h2><?= e($question['question']) ?></h2><div class="option-grid"><?php foreach ($question['options'] as $letter => $option): $inputId = $question['id'] . '_' . $letter; ?><label class="quiz-option" for="<?= e($inputId) ?>"><input id="<?= e($inputId) ?>" type="radio" name="q[<?= e($question['id']) ?>]" value="<?= e($letter) ?>" <?= ($resumeAnswers[$question['id']] ?? '') === $letter ? 'checked' : '' ?>><span class="option-letter"><?= e($letter) ?></span><span class="option-text"><?= e($option) ?></span></label><?php endforeach; ?></div></section><?php endforeach; ?>
        <div class="quiz-submit-bar surface-card"><p><strong><?= e(t('quiz.finished')) ?></strong><br><?= e(t('quiz.unanswered_wrong')) ?></p><button class="btn-primary-custom" type="submit"><i class="bi bi-send-check"></i> <?= e(t('quiz.submit')) ?></button></div></form>
    </div>
<?php else: ?>
    <section class="empty-state surface-card"><div class="icon-tile amber"><i class="bi bi-exclamation-triangle"></i></div><h2><?= e(t('quiz.cannot_start')) ?></h2><p><?= e(t('quiz.bank_incomplete')) ?></p><a class="btn-secondary-custom" href="quiz.php"><i class="bi bi-arrow-left"></i> <?= e(t('quiz.choose')) ?></a></section>
<?php endif; ?>
</div></div>
<?php
$clientI18nKeys = $selectedTheme && $storageReady && count($questions) === 5 ? ['quiz.checking'] : [];
$pageScripts = $selectedTheme && $storageReady && count($questions) === 5 ? '<script src="../assets/js/quiz.js"></script>' : '';
include __DIR__ . '/../includes/footer.php';
?>
