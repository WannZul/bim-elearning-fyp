<?php
session_start();
include 'includes/db_connect.php';

// Semak jika user belum login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - BIM E-Learning</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">BIM E-Learning</a>
            <div class="d-flex">
                <span class="navbar-text me-3">Selamat Datang, <strong><?php echo $username; ?></strong></span>
                <a href="logout.php" class="btn btn-outline-light btn-sm">Log Keluar</a>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h2 class="mb-4">Papan Pemuka Pembelajaran</h2>
        
        <div class="row">
            <!-- Modul Pembelajaran -->
            <div class="col-md-4 mb-3">
                <div class="card h-100 text-center shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">📚 Bahan Pembelajaran</h5>
                        <p class="card-text">Belajar asas abjad dan nombor BIM.</p>
                        <a href="pages/learn.php" class="btn btn-primary">Mula Belajar</a>
                    </div>
                </div>
            </div>

            <!-- Modul Kuiz & Leaderboard -->
            <div class="col-md-4 mb-3">
                <div class="card h-100 text-center shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">🏆 Kuiz & Leaderboard</h5>
                        <p class="card-text">Uji kefahaman dan lihat ranking anda.</p>
                        <a href="pages/quiz.php" class="btn btn-success">Mula Kuiz</a>
                    </div>
                </div>
            </div>

            <!-- Modul AI Hand Tracking -->
            <div class="col-md-4 mb-3">
                <div class="card h-100 text-center shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">🤖 AI Hand Tracking</h5>
                        <p class="card-text">Imbas isyarat tangan guna webcam (MediaPipe).</p>
                        <a href="pages/ai_tracking.php" class="btn btn-warning">Buka Webcam</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>