<?php
require_once __DIR__ . '/includes/app.php';

if (appIsProduction() || !configBool('BIM_ENABLE_DIAGNOSTICS', true)) {
    http_response_code(404);
    exit;
}

requireAuth('login.php');
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/database_schema.php';
require_once __DIR__ . '/includes/security.php';

$tables = [];
$result = mysqli_query($conn, 'SHOW TABLES');
if ($result) while ($row = mysqli_fetch_array($result)) $tables[] = (string) $row[0];
$quizStorageReady = quizTypeStorageReady($conn);
$loginProtectionReady = loginThrottleStorageReady($conn);
$storageReady = $quizStorageReady && $loginProtectionReady;

$pageTitle = t('diagnostics.title');
$basePath = '';
$activePage = '';
include __DIR__ . '/includes/header.php';
?>
<div class="page-shell"><div class="container-wide"><section class="surface-card empty-state"><div class="icon-tile"><i class="bi bi-database-check"></i></div><h1 class="section-title"><?= e(t('diagnostics.success')) ?></h1><p><?= e(t('diagnostics.summary', ['count' => count($tables)])) ?></p><?php if (!$storageReady): ?><div class="schema-alert text-start"><div class="icon-tile amber"><i class="bi bi-database-exclamation"></i></div><div><h2><?= e(t('schema.migration')) ?></h2><p><?= e(quizTypeMigrationMessage()) ?></p></div></div><?php endif; ?><div class="module-meta justify-content-center"><span class="tag <?= $quizStorageReady ? 'teal' : 'amber' ?>"><?= e(t('diagnostics.quiz_storage')) ?>: <?= e($quizStorageReady ? t('diagnostics.ready') : t('diagnostics.not_ready')) ?></span><span class="tag <?= $loginProtectionReady ? 'teal' : 'amber' ?>"><?= e(t('diagnostics.login_protection')) ?>: <?= e($loginProtectionReady ? t('diagnostics.ready') : t('diagnostics.not_ready')) ?></span></div><a class="btn-secondary-custom" href="index.php"><i class="bi bi-arrow-left"></i> <?= e(t('diagnostics.back')) ?></a></section></div></div>
<?php include __DIR__ . '/includes/footer.php'; ?>
