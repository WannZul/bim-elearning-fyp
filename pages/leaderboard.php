<?php
require_once __DIR__ . '/../includes/app.php';
requireAuth('../login.php');
require_once __DIR__ . '/../includes/db_connect.php';

$currentUserId = (int) $_SESSION['user_id'];
$lastResult = $_SESSION['last_quiz_result'] ?? null;
unset($_SESSION['last_quiz_result']);

$rankings = [];
$sql = "SELECT u.id AS user_id, u.username, qs.score, qs.time_taken, MAX(qs.created_at) AS created_at
        FROM users u
        INNER JOIN quiz_scores qs ON qs.user_id = u.id
        WHERE qs.score = (SELECT MAX(s2.score) FROM quiz_scores s2 WHERE s2.user_id = u.id)
          AND qs.time_taken = (SELECT MIN(s3.time_taken) FROM quiz_scores s3 WHERE s3.user_id = u.id AND s3.score = qs.score)
        GROUP BY u.id, u.username, qs.score, qs.time_taken
        ORDER BY qs.score DESC, qs.time_taken ASC, u.username ASC";
$query = mysqli_query($conn, $sql);
if ($query) {
    while ($row = mysqli_fetch_assoc($query)) {
        $rankings[] = $row;
    }
}

$currentRank = null;
foreach ($rankings as $index => $entry) {
    if ((int) $entry['user_id'] === $currentUserId) {
        $currentRank = $index + 1;
        break;
    }
}
$topTen = array_slice($rankings, 0, 10);
$podiumEntries = array_slice($rankings, 0, 3);

$pageTitle = 'Papan Kedudukan';
$basePath = '../';
$activePage = 'leaderboard';
include __DIR__ . '/../includes/header.php';
?>
<section class="leaderboard-hero">
    <div class="container-wide" data-reveal><span class="eyebrow">Papan kedudukan · semua tema</span><h1 class="page-title">Kehebatan bermula dengan latihan.</h1><p>Ranking ini menggabungkan cabaran nombor, abjad, dan haiwan. Setiap cabaran mempunyai lima soalan bernilai 50 mata; skor tertinggi menang dan masa terpantas memecahkan seri.</p></div>
</section>
<div class="leaderboard-content">
    <div class="container-wide">
        <?php if (is_array($lastResult)): ?>
        <section class="result-banner surface-card" data-reveal><div class="result-badge"><i class="bi bi-stars"></i></div><div><span class="eyebrow">Keputusan terkini</span><h2><?= e(scoreLabel((int) $lastResult['score'])) ?> — <?= (int) $lastResult['score'] ?>/50 mata</h2><p><?= (int) $lastResult['answered'] ?> daripada <?= (int) $lastResult['total'] ?> soalan dijawab dalam <?= e(formatDuration((int) $lastResult['time_taken'])) ?>.</p></div><a class="btn-primary-custom" href="quiz.php"><i class="bi bi-arrow-repeat"></i> Cuba lagi</a></section>
        <?php endif; ?>

        <?php if ($podiumEntries): ?>
        <section class="podium" aria-label="Tiga kedudukan teratas">
            <?php $displayOrder = count($podiumEntries) >= 2 ? [1, 0, 2] : [0]; foreach ($displayOrder as $podiumIndex): if (!isset($podiumEntries[$podiumIndex])) continue; $entry = $podiumEntries[$podiumIndex]; $rank = $podiumIndex + 1; ?>
            <article class="podium-card surface-card <?= $rank === 1 ? 'first' : '' ?>"><div class="podium-rank"><?= $rank === 1 ? '<i class="bi bi-trophy-fill"></i>' : $rank ?></div><div class="podium-avatar"><?= e(initials($entry['username'])) ?></div><h2><?= e($entry['username']) ?></h2><div class="podium-score"><strong><?= (int) $entry['score'] ?></strong> / 50 mata · <?= e(formatDuration((int) $entry['time_taken'])) ?></div></article>
            <?php endforeach; ?>
        </section>
        <?php endif; ?>

        <section class="ranking-card surface-card" data-reveal>
            <div class="ranking-header"><div><h2>10 pelajar terbaik</h2><span>Dikemas kini daripada rekod kuiz terbaik</span></div><?php if ($currentRank): ?><span class="tag teal"><i class="bi bi-person-check"></i> Kedudukan anda: #<?= $currentRank ?></span><?php endif; ?></div>
            <?php if ($topTen): ?>
            <table class="ranking-table"><thead><tr><th>Kedudukan</th><th>Pelajar</th><th>Skor terbaik</th><th>Masa</th><th>Pencapaian</th></tr></thead><tbody>
                <?php foreach ($topTen as $index => $entry): $rank = $index + 1; ?>
                <tr class="<?= (int) $entry['user_id'] === $currentUserId ? 'is-current' : '' ?>"><td><span class="rank-number"><?= $rank ?></span></td><td><div class="player-cell"><div class="player-avatar"><?= e(initials($entry['username'])) ?></div><div><strong><?= e($entry['username']) ?><?= (int) $entry['user_id'] === $currentUserId ? ' (Anda)' : '' ?></strong><span><?= e(date('d M Y', strtotime($entry['created_at']))) ?></span></div></div></td><td class="score-cell"><strong><?= (int) $entry['score'] ?> / 50</strong></td><td><?= e(formatDuration((int) $entry['time_taken'])) ?></td><td><span class="tag <?= (int) $entry['score'] >= 40 ? 'teal' : '' ?>"><?= e(scoreLabel((int) $entry['score'])) ?></span></td></tr>
                <?php endforeach; ?>
            </tbody></table>
            <div class="ranking-footer"><p><i class="bi bi-info-circle me-1"></i> Hanya skor terbaik setiap pengguna dipaparkan.</p><a class="btn-secondary-custom btn-sm-custom" href="quiz.php">Sertai cabaran <i class="bi bi-arrow-right"></i></a></div>
            <?php else: ?>
            <div class="empty-state"><div class="icon-tile amber"><i class="bi bi-trophy"></i></div><h2>Jadilah yang pertama</h2><p>Belum ada skor direkodkan. Selesaikan cabaran BIM untuk membuka papan kedudukan.</p><a class="btn-primary-custom" href="quiz.php"><i class="bi bi-lightning-charge"></i> Mulakan cabaran</a></div>
            <?php endif; ?>
        </section>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
