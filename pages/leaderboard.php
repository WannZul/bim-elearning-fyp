<?php
require_once __DIR__ . '/../includes/app.php';
requireAuth('../login.php');
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/database_schema.php';
require_once __DIR__ . '/../includes/quiz_bank.php';

$currentUserId = (int) $_SESSION['user_id'];
$themes = quizThemes();
$selectedType = strtolower(trim((string) ($_GET['type'] ?? 'numbers')));
if (!isset($themes[$selectedType])) $selectedType = 'numbers';
$selectedTheme = $themes[$selectedType];
$storageReady = quizTypeStorageReady($conn);
$rankings = [];

if ($storageReady) {
    $sql = "SELECT u.id AS user_id, u.username, qs.score, qs.time_taken, MAX(qs.created_at) AS created_at
            FROM users u INNER JOIN quiz_scores qs ON qs.user_id = u.id
            WHERE qs.quiz_type = ?
              AND qs.score = (SELECT MAX(s2.score) FROM quiz_scores s2 WHERE s2.user_id = u.id AND s2.quiz_type = ?)
              AND qs.time_taken = (SELECT MIN(s3.time_taken) FROM quiz_scores s3 WHERE s3.user_id = u.id AND s3.quiz_type = ? AND s3.score = qs.score)
            GROUP BY u.id, u.username, qs.score, qs.time_taken
            ORDER BY qs.score DESC, qs.time_taken ASC, u.username ASC";
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'sss', $selectedType, $selectedType, $selectedType);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) $rankings[] = $row;
        mysqli_stmt_close($stmt);
    }
}
$currentRank = null;
foreach ($rankings as $index => $entry) if ((int) $entry['user_id'] === $currentUserId) { $currentRank = $index + 1; break; }
$topTen = array_slice($rankings, 0, 10);
$podiumEntries = array_slice($rankings, 0, 3);

$pageTitle = t('leaderboard.page_title', ['theme' => $selectedTheme['short_title']]);
$basePath = '../';
$activePage = 'leaderboard';
include __DIR__ . '/../includes/header.php';
?>
<section class="leaderboard-hero"><div class="container-wide" data-reveal><span class="eyebrow"><?= e(t('leaderboard.eyebrow')) ?></span><h1 class="page-title"><?= e(t('leaderboard.heading', ['theme' => $selectedTheme['title']])) ?></h1><p><?= e(t('leaderboard.intro')) ?></p></div></section>
<div class="leaderboard-content"><div class="container-wide">
    <nav class="ranking-type-tabs surface-card" aria-label="<?= e(t('leaderboard.tabs_label')) ?>" data-reveal><?php foreach ($themes as $type => $theme): ?><a class="ranking-type-tab <?= $type === $selectedType ? 'active' : '' ?>" href="leaderboard.php?type=<?= urlencode($type) ?>" aria-current="<?= $type === $selectedType ? 'page' : 'false' ?>"><span class="icon-tile <?= e($theme['accent']) ?>"><i class="bi <?= e($theme['icon']) ?>"></i></span><span><small><?= e(t('leaderboard.ranking')) ?></small><strong><?= e($theme['short_title']) ?></strong></span></a><?php endforeach; ?></nav>
    <?php if (!$storageReady): ?><section class="schema-alert surface-card" role="alert" data-reveal><div class="icon-tile amber"><i class="bi bi-database-exclamation"></i></div><div><h2><?= e(t('schema.title')) ?></h2><p><?= e(quizTypeMigrationMessage()) ?></p></div></section>
    <?php elseif ($podiumEntries): ?><section class="podium" aria-label="<?= e(t('leaderboard.podium_label', ['theme' => $selectedTheme['short_title']])) ?>"><?php $displayOrder = count($podiumEntries) >= 2 ? [1, 0, 2] : [0]; foreach ($displayOrder as $podiumIndex): if (!isset($podiumEntries[$podiumIndex])) continue; $entry = $podiumEntries[$podiumIndex]; $rank = $podiumIndex + 1; ?><article class="podium-card surface-card <?= $rank === 1 ? 'first' : '' ?>"><div class="podium-rank"><?= $rank === 1 ? '<i class="bi bi-trophy-fill"></i>' : $rank ?></div><div class="podium-avatar"><?= e(initials($entry['username'])) ?></div><h2><?= e($entry['username']) ?></h2><div class="podium-score"><strong><?= (int) $entry['score'] ?></strong> / 50 <?= e(t('common.points')) ?> · <?= e(formatDuration((int) $entry['time_taken'])) ?></div></article><?php endforeach; ?></section><?php endif; ?>

    <?php if ($storageReady): ?><section class="ranking-card surface-card" data-reveal><div class="ranking-header"><div><h2><?= e(t('leaderboard.top_ten', ['theme' => $selectedTheme['short_title']])) ?></h2><span><?= e(t('leaderboard.best_only')) ?></span></div><?php if ($currentRank): ?><span class="tag teal"><i class="bi bi-person-check"></i> <?= e(t('leaderboard.your_rank', ['rank' => $currentRank])) ?></span><?php endif; ?></div>
        <?php if ($topTen): ?><table class="ranking-table"><thead><tr><th><?= e(t('leaderboard.position')) ?></th><th><?= e(t('leaderboard.student')) ?></th><th><?= e(t('leaderboard.best_score')) ?></th><th><?= e(t('leaderboard.time')) ?></th><th><?= e(t('leaderboard.achievement')) ?></th></tr></thead><tbody><?php foreach ($topTen as $index => $entry): $rank = $index + 1; ?><tr class="<?= (int) $entry['user_id'] === $currentUserId ? 'is-current' : '' ?>"><td><span class="rank-number"><?= $rank ?></span></td><td><div class="player-cell"><div class="player-avatar"><?= e(initials($entry['username'])) ?></div><div><strong><?= e($entry['username']) ?><?= (int) $entry['user_id'] === $currentUserId ? ' (' . e(t('leaderboard.you')) . ')' : '' ?></strong><span><?= e(localizedDate($entry['created_at'])) ?></span></div></div></td><td class="score-cell"><strong><?= (int) $entry['score'] ?> / 50</strong></td><td><?= e(formatDuration((int) $entry['time_taken'])) ?></td><td><span class="tag <?= (int) $entry['score'] >= 40 ? 'teal' : '' ?>"><?= e(scoreLabel((int) $entry['score'])) ?></span></td></tr><?php endforeach; ?></tbody></table>
        <div class="ranking-footer"><p><i class="bi bi-info-circle me-1"></i> <?= e(t('leaderboard.scope_note')) ?></p><a class="btn-secondary-custom btn-sm-custom" href="quiz.php?theme=<?= urlencode($selectedType) ?>"><?= e(t('leaderboard.try_quiz', ['theme' => $selectedTheme['short_title']])) ?> <i class="bi bi-arrow-right"></i></a></div>
        <?php else: ?><div class="empty-state"><div class="icon-tile amber"><i class="bi bi-trophy"></i></div><h2><?= e(t('leaderboard.be_first')) ?></h2><p><?= e(t('leaderboard.empty', ['theme' => $selectedTheme['short_title']])) ?></p><a class="btn-primary-custom" href="quiz.php?theme=<?= urlencode($selectedType) ?>"><i class="bi bi-lightning-charge"></i> <?= e(t('leaderboard.start')) ?></a></div><?php endif; ?>
    </section><?php endif; ?>
</div></div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
