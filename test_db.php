<?php
require_once __DIR__ . '/includes/app.php';
requireAuth('login.php');
require_once __DIR__ . '/includes/db_connect.php';

$tables = [];
$result = mysqli_query($conn, 'SHOW TABLES');
if ($result) {
    while ($row = mysqli_fetch_array($result)) {
        $tables[] = (string) $row[0];
    }
}

$pageTitle = 'Diagnostik Pangkalan Data';
$basePath = '';
$activePage = '';
include __DIR__ . '/includes/header.php';
?>
<div class="page-shell"><div class="container-wide"><section class="surface-card empty-state"><div class="icon-tile"><i class="bi bi-database-check"></i></div><h1 class="section-title">Sambungan pangkalan data berjaya</h1><p><?= count($tables) ?> jadual ditemui. Halaman diagnostik ini hanya boleh dilihat selepas log masuk.</p><div class="module-meta justify-content-center"><?php foreach ($tables as $table): ?><span class="tag"><?= e($table) ?></span><?php endforeach; ?></div><a class="btn-secondary-custom" href="index.php"><i class="bi bi-arrow-left"></i> Kembali ke utama</a></section></div></div>
<?php include __DIR__ . '/includes/footer.php'; ?>
