<?php
require_once __DIR__ . '/../includes/app.php';
requireAuth('../login.php');
require_once __DIR__ . '/../includes/db_connect.php';

$questions = [];
$query = mysqli_query($conn, 'SELECT id, question_text, option_a, option_b, option_c, option_d FROM quiz_questions ORDER BY RAND() LIMIT 5');
if ($query) {
    while ($row = mysqli_fetch_assoc($query)) {
        $questions[] = $row;
    }
}

if (count($questions) < 5) {
    $questions = [];
}

$attemptToken = bin2hex(random_bytes(16));
if ($questions) {
    $now = microtime(true);
    $activeAttempts = $_SESSION['quiz_attempts'] ?? [];
    $activeAttempts = array_filter($activeAttempts, static fn(array $attempt): bool => (float) ($attempt['expires_at'] ?? 0) >= $now);
    $activeAttempts[$attemptToken] = [
        'question_ids' => array_map(static fn(array $question): int => (int) $question['id'], $questions),
        'started_at' => $now,
        'expires_at' => $now + 60.0,
    ];
    $_SESSION['quiz_attempts'] = array_slice($activeAttempts, -3, null, true);
}

$pageTitle = 'Cabaran BIM';
$basePath = '../';
$activePage = 'quiz';
include __DIR__ . '/../includes/header.php';
?>
<div class="page-shell quiz-shell">
    <div class="container-wide">
        <?php if ($questions): ?>
        <div class="quiz-toolbar surface-card">
            <div class="quiz-progress-info"><strong>Cabaran BIM</strong><span><span id="answered-count">0</span> / <?= count($questions) ?> dijawab</span></div>
            <div class="timer-pill" id="timer-pill" aria-live="polite"><i class="bi bi-stopwatch"></i><strong id="timer">01:00</strong></div>
            <div class="quiz-progress-info"><strong>50 mata</strong><span>10 mata setiap jawapan</span></div>
        </div>
        <div class="quiz-content">
            <header class="quiz-intro" data-reveal><span class="eyebrow">Uji pengetahuan</span><h1>Pantas, tepat, dan yakin.</h1><p>Jawab semua soalan sebelum masa tamat. Masa hanya digunakan untuk memecahkan seri.</p></header>
            <form id="quizForm" method="POST" action="quiz_process.php" data-deadline-ms="<?= (int) round($_SESSION['quiz_attempts'][$attemptToken]['expires_at'] * 1000) ?>">
                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                <input type="hidden" name="attempt_token" value="<?= e($attemptToken) ?>">
                <input type="hidden" name="time_taken" id="time_taken" value="0">
                <?php foreach ($questions as $index => $question): ?>
                <section class="question-card surface-card" data-question-card data-reveal>
                    <div class="question-number"><span>Soalan <?= $index + 1 ?> daripada <?= count($questions) ?></span><span class="answered-marker"><i class="bi bi-check-circle-fill"></i> Dijawab</span></div>
                    <h2><?= e($question['question_text']) ?></h2>
                    <div class="option-grid">
                        <?php foreach (['A', 'B', 'C', 'D'] as $letter): $field = 'option_' . strtolower($letter); $inputId = 'q' . (int) $question['id'] . '_' . $letter; ?>
                        <label class="quiz-option" for="<?= e($inputId) ?>"><input id="<?= e($inputId) ?>" type="radio" name="q<?= (int) $question['id'] ?>" value="<?= $letter ?>" <?= $letter === 'A' ? 'required' : '' ?>><span class="option-letter"><?= $letter ?></span><span class="option-text"><?= e($question[$field]) ?></span></label>
                        <?php endforeach; ?>
                    </div>
                </section>
                <?php endforeach; ?>
                <div class="quiz-submit-bar surface-card"><p><strong>Sudah selesai?</strong><br>Semak pilihan anda sebelum menghantar.</p><button class="btn-primary-custom" type="submit"><i class="bi bi-send-check"></i> Hantar jawapan</button></div>
            </form>
        </div>
        <?php else: ?>
        <section class="empty-state surface-card"><div class="icon-tile amber"><i class="bi bi-question-circle"></i></div><h2>Soalan belum tersedia</h2><p>Tambahkan sekurang-kurangnya lima rekod ke jadual <code>quiz_questions</code> untuk memulakan cabaran.</p><a class="btn-secondary-custom" href="../index.php"><i class="bi bi-arrow-left"></i> Kembali ke utama</a></section>
        <?php endif; ?>
    </div>
</div>
<?php
$pageScripts = $questions ? '<script src="../assets/js/quiz.js"></script>' : '';
include __DIR__ . '/../includes/footer.php';
?>
