<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Bahan Pembelajaran - BIM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../index.php">← Kembali ke Dashboard</a>
        </div>
    </nav>

    <div class="container mt-5">
        <h2 class="mb-4 text-center">📚 Modul Asas BIM: Abjad & Nombor</h2>
        <p class="text-center text-muted">Sila perhatikan gambar isyarat di bawah dan cuba praktikkannya di depan cermin.</p>

        <div class="row mt-4">
            <!-- Contoh Kad 1 -->
            <div class="col-md-3 mb-4">
                <div class="card shadow-sm h-100 text-center">
                    <div class="card-body">
                        <h3 class="text-primary">A</h3>
                        <p class="card-text">Kepalkan jari, ibu jari di depan.</p>
                        <!-- Nanti letak gambar: <img src="../assets/images/BIM_A.jpg" class="img-fluid" alt="Isyarat A"> -->
                    </div>
                </div>
            </div>
            <!-- Contoh Kad 2 -->
            <div class="col-md-3 mb-4">
                <div class="card shadow-sm h-100 text-center">
                    <div class="card-body">
                        <h3 class="text-primary">B</h3>
                        <p class="card-text">Empat jari tegak ke atas, ibu jari ke dalam.</p>
                    </div>
                </div>
            </div>
            <!-- Contoh Kad 3 -->
            <div class="col-md-3 mb-4">
                <div class="card shadow-sm h-100 text-center">
                    <div class="card-body">
                        <h3 class="text-primary">1</h3>
                        <p class="card-text">Angkat jari telunjuk sahaja.</p>
                    </div>
                </div>
            </div>
            <!-- Contoh Kad 4 -->
            <div class="col-md-3 mb-4">
                <div class="card shadow-sm h-100 text-center">
                    <div class="card-body">
                        <h3 class="text-primary">2</h3>
                        <p class="card-text">Angkat jari telunjuk dan jari tengah.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="quiz.php" class="btn btn-success btn-lg">✅ Saya Sudah Faham, Mula Kuiz!</a>
        </div>
    </div>
</body>
</html>