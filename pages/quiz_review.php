<?php
require_once __DIR__ . '/../includes/app.php';
requireAuth('../login.php');

$resultToken = (string) ($_GET['result'] ?? '');
$result = $_SESSION['quiz_results'][$resultToken] ?? null;

if (!is_array($result) || (int) ($result['expires_at'] ?? 0) < time()) {
    unset($_SESSION['quiz_results'][$resultToken]);
    setFlash('info', 'Ulasan kuiz telah tamat. Mulakan cabaran baharu untuk melihat ulasan.');
    header('Location: quiz.php');
    exit;
}

$score = (int) $result['score'];
$total = (int) $result['total'];
$correctCount = intdiv($score, 10);
$incorrectCount = $total - $correctCount;

$pageTitle = 'Ulasan Kuiz';
$basePath = '../';
$activePage = 'quiz';
include __DIR__ . '/../includes/header.php';
?>
<div class="page-shell review-page">
    <div class="container-wide">
        <header class="review-summary surface-card" data-reveal>
            <div class="review-score-ring" style="--score-progress: <?= min(100, max(0, $score * 2)) ?>%"><div><strong><?= $score ?></strong><span>/ 50</span></div></div>
            <div class="review-summary-copy"><span class="eyebrow"><?= e($result['theme_title']) ?></span><h1><?= e(scoreLabel($score)) ?>, <?= e((string) ($_SESSION['username'] ?? 'Pelajar')) ?>!</h1><p>Anda menjawab <?= (int) $result['answered'] ?> daripada <?= $total ?> soalan dalam <?= e(formatDuration((int) $result['time_taken'])) ?>. Semak setiap jawapan di bawah untuk mengetahui bahagian yang perlu diperbaiki.</p><div class="review-stats"><span class="review-stat correct"><i class="bi bi-check-circle-fill"></i><strong><?= $correctCount ?></strong> betul</span><span class="review-stat incorrect"><i class="bi bi-x-circle-fill"></i><strong><?= $incorrectCount ?></strong> salah / kosong</span><span class="review-stat"><i class="bi bi-stopwatch-fill"></i><strong><?= e(formatDuration((int) $result['time_taken'])) ?></strong> masa</span></div></div>
            <div class="review-summary-actions"><a class="btn-primary-custom" href="quiz.php?theme=<?= urlencode($result['theme_key']) ?>"><i class="bi bi-arrow-repeat"></i> Cuba lagi</a><a class="btn-light-custom" href="leaderboard.php"><i class="bi bi-trophy"></i> Lihat ranking</a></div>
        </header>

        <div class="review-heading" data-reveal><div><span class="eyebrow">Ulasan jawapan</span><h2 class="section-title">Belajar daripada setiap pilihan</h2></div><span class="tag"><i class="bi bi-shield-check"></i> Jawapan disemak oleh pelayan</span></div>

        <section class="review-list" aria-label="Ulasan jawapan kuiz">
            <?php foreach ($result['items'] as $index => $item): $selected = (string) $item['selected']; $correct = (string) $item['correct']; ?>
            <article class="review-card surface-card <?= $item['is_correct'] ? 'is-correct' : 'is-incorrect' ?>" data-reveal>
                <div class="review-card-status"><span><?= $item['is_correct'] ? '<i class="bi bi-check-lg"></i>' : '<i class="bi bi-x-lg"></i>' ?></span><div><small>SOALAN <?= $index + 1 ?></small><strong><?= $item['is_correct'] ? 'Jawapan betul' : ($selected === '' ? 'Tidak dijawab' : 'Jawapan kurang tepat') ?></strong></div></div>
                <h3><?= e($item['question']) ?></h3>
                <div class="review-answer-grid">
                    <div class="review-answer <?= $item['is_correct'] ? 'answer-correct' : 'answer-wrong' ?>"><span>Jawapan anda</span><strong><?= $selected !== '' ? e($selected . '. ' . $item['options'][$selected]) : '— Tidak dijawab' ?></strong></div>
                    <?php if (!$item['is_correct']): ?><div class="review-answer answer-correct"><span>Jawapan betul</span><strong><?= e($correct . '. ' . $item['options'][$correct]) ?></strong></div><?php endif; ?>
                </div>
                <div class="review-explanation"><i class="bi bi-lightbulb-fill"></i><div><strong>Penjelasan</strong><p><?= e($item['explanation']) ?></p></div></div>
            </article>
            <?php endforeach; ?>
        </section>

        <div class="review-footer-actions" data-reveal><a class="btn-light-custom" href="quiz.php"><i class="bi bi-grid"></i> Pilih kuiz lain</a><a class="btn-secondary-custom" href="quiz.php?theme=<?= urlencode($result['theme_key']) ?>">Ulang cabaran <i class="bi bi-arrow-right"></i></a></div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
