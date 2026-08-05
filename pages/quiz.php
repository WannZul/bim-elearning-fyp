<?php
require_once __DIR__ . '/../includes/app.php';
requireAuth('../login.php');
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/database_schema.php';
require_once __DIR__ . '/../includes/quiz_bank.php';

$storageReady = quizTypeStorageReady($conn);
$themes = quizThemes();
$themeKey = strtolower(trim((string) ($_GET['theme'] ?? '')));
$selectedTheme = $themes[$themeKey] ?? null;
$questions = [];
$attemptToken = '';
$remainingMilliseconds = 60000;

if ($selectedTheme && $storageReady) {
    $availableQuestions = quizQuestionsForTheme($themeKey);
    shuffle($availableQuestions);
    $questions = array_slice($availableQuestions, 0, 5);

    if (count($questions) === 5) {
        $attemptToken = bin2hex(random_bytes(16));
        $now = microtime(true);
        $activeAttempts = $_SESSION['quiz_attempts'] ?? [];
        $activeAttempts = array_filter($activeAttempts, static fn(array $attempt): bool => (float) ($attempt['answer_deadline'] ?? 0) >= $now);
        $activeAttempts[$attemptToken] = [
            'theme' => $themeKey,
            'question_ids' => array_column($questions, 'id'),
            'started_at' => $now,
            'answer_deadline' => $now + 60.0,
        ];
        $_SESSION['quiz_attempts'] = array_slice($activeAttempts, -5, null, true);
        $remainingMilliseconds = max(0, (int) round(($activeAttempts[$attemptToken]['answer_deadline'] - microtime(true)) * 1000));
    }
}

$pageTitle = $selectedTheme ? $selectedTheme['title'] : 'Pilih Cabaran';
$basePath = '../';
$activePage = 'quiz';
include __DIR__ . '/../includes/header.php';
?>
<div class="page-shell quiz-shell">
    <div class="container-wide">
        <?php if (!$selectedTheme): ?>
        <?php if (!$storageReady): ?><section class="schema-alert surface-card" role="alert" data-reveal><div class="icon-tile amber"><i class="bi bi-database-exclamation"></i></div><div><h2>Kemas kini pangkalan data sebelum mengambil kuiz</h2><p><?= e(quizTypeMigrationMessage()) ?></p></div></section><?php endif; ?>
        <header class="quiz-hub-hero" data-reveal>
            <div><span class="eyebrow">Pusat cabaran BIM</span><h1 class="page-title">Pilih fokus. Uji kefahaman.</h1><p>Setiap cabaran mempunyai lima soalan rawak, masa 60 saat, dan ulasan jawapan lengkap selepas selesai.</p></div>
            <div class="quiz-hub-score"><i class="bi bi-lightning-charge-fill"></i><strong>50</strong><span>mata maksimum</span></div>
        </header>

        <section class="quiz-type-grid" aria-label="Jenis kuiz tersedia">
            <?php foreach ($themes as $key => $theme): ?>
            <article class="quiz-type-card surface-card card-hover accent-<?= e($theme['accent']) ?>" data-reveal>
                <div class="quiz-type-icon"><i class="bi <?= e($theme['icon']) ?>"></i></div>
                <div class="quiz-type-number">0<?= array_search($key, array_keys($themes), true) + 1 ?></div>
                <span class="tag"><i class="bi bi-stopwatch"></i> <?= e($theme['duration']) ?></span>
                <h2><?= e($theme['title']) ?></h2>
                <p><?= e($theme['description']) ?></p>
                <div class="quiz-type-meta"><span><i class="bi bi-list-check"></i> 5 soalan rawak</span><span><i class="bi bi-chat-square-text"></i> Ulasan jawapan</span></div>
                <?php if ($storageReady): ?><a class="btn-secondary-custom btn-wide" href="quiz.php?theme=<?= urlencode($key) ?>">Pilih cabaran <i class="bi bi-arrow-right"></i></a><?php else: ?><span class="btn-light-custom btn-wide" aria-disabled="true"><i class="bi bi-lock"></i> Migrasi diperlukan</span><?php endif; ?>
            </article>
            <?php endforeach; ?>
        </section>
        <div class="quiz-hub-note" data-reveal><i class="bi bi-bullseye"></i><p><strong>Selaras dengan skop projek:</strong> Cabaran rasmi memberi tumpuan khusus kepada asas abjad dan nombor BIM untuk orang awam serta petugas barisan hadapan.</p></div>

        <?php elseif (!$storageReady): ?>
        <section class="empty-state surface-card"><div class="icon-tile amber"><i class="bi bi-database-exclamation"></i></div><h2>Kemas kini pangkalan data diperlukan</h2><p><?= e(quizTypeMigrationMessage()) ?></p><a class="btn-secondary-custom" href="quiz.php"><i class="bi bi-arrow-left"></i> Kembali</a></section>
        <?php elseif (count($questions) === 5): ?>
        <div class="quiz-toolbar surface-card">
            <div class="quiz-progress-info"><strong><?= e($selectedTheme['title']) ?></strong><span><span id="answered-count">0</span> / 5 dijawab</span></div>
            <div class="timer-pill" id="timer-pill" aria-live="polite"><i class="bi bi-stopwatch"></i><strong id="timer">01:00</strong></div>
            <div class="quiz-progress-info"><strong>50 mata</strong><span>10 mata setiap jawapan</span></div>
        </div>
        <div class="quiz-content">
            <header class="quiz-intro" data-reveal><a class="quiz-back-link" href="quiz.php"><i class="bi bi-arrow-left"></i> Tukar jenis kuiz</a><span class="eyebrow"><?= e($selectedTheme['short_title']) ?></span><h1>Pantas, tepat, dan yakin.</h1><p>Jawab semua soalan sebelum masa tamat. Selepas menghantar, anda akan melihat jawapan dan penjelasan.</p></header>
            <form id="quizForm" method="POST" action="quiz_process.php" data-remaining-ms="<?= $remainingMilliseconds ?>">
                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                <input type="hidden" name="attempt_token" value="<?= e($attemptToken) ?>">
                <?php foreach ($questions as $index => $question): ?>
                <section class="question-card surface-card" data-question-card data-reveal>
                    <div class="question-number"><span>Soalan <?= $index + 1 ?> daripada 5</span><span class="answered-marker"><i class="bi bi-check-circle-fill"></i> Dijawab</span></div>
                    <h2><?= e($question['question']) ?></h2>
                    <div class="option-grid">
                        <?php foreach ($question['options'] as $letter => $option): $inputId = e($question['id'] . '_' . $letter); ?>
                        <label class="quiz-option" for="<?= $inputId ?>"><input id="<?= $inputId ?>" type="radio" name="q[<?= e($question['id']) ?>]" value="<?= e($letter) ?>"><span class="option-letter"><?= e($letter) ?></span><span class="option-text"><?= e($option) ?></span></label>
                        <?php endforeach; ?>
                    </div>
                </section>
                <?php endforeach; ?>
                <div class="quiz-submit-bar surface-card"><p><strong>Sudah selesai?</strong><br>Soalan tidak dijawab akan ditanda sebagai salah.</p><button class="btn-primary-custom" type="submit"><i class="bi bi-send-check"></i> Hantar & lihat ulasan</button></div>
            </form>
        </div>
        <?php else: ?>
        <section class="empty-state surface-card"><div class="icon-tile amber"><i class="bi bi-exclamation-triangle"></i></div><h2>Cabaran tidak dapat dimulakan</h2><p>Bank soalan untuk tema ini belum lengkap. Sila pilih cabaran lain.</p><a class="btn-secondary-custom" href="quiz.php"><i class="bi bi-arrow-left"></i> Pilih cabaran</a></section>
        <?php endif; ?>
    </div>
</div>
<?php
$pageScripts = $selectedTheme && $storageReady && count($questions) === 5 ? '<script src="../assets/js/quiz.js"></script>' : '';
include __DIR__ . '/../includes/footer.php';
?>
