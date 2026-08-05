<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit(); }
include __DIR__ . '/../includes/db_connect.php';

// Query untuk ambil Top 10 (Score tinggi dulu, kalau sama, masa singkat dulu)
$sql = "SELECT u.username, qs.score, qs.time_taken, qs.created_at 
        FROM quiz_scores qs 
        JOIN users u ON qs.user_id = u.id 
        ORDER BY qs.score DESC, qs.time_taken ASC 
        LIMIT 10";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Leaderboard - BIM E-Learning</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../index.php">← Kembali ke Dashboard</a>
        </div>
    </nav>

    <div class="container mt-5">
        <h2 class="text-center mb-4">🏆 Papan Leaderboard BIM</h2>
        
        <div class="card shadow-lg">
            <div class="card-body">
                <table class="table table-hover table-striped text-center">
                    <thead class="table-dark">
                        <tr>
                            <th>Ranking</th>
                            <th>Nama User</th>
                            <th>Markah</th>
                            <th>Masa (Saat)</th>
                            <th>Tarikh</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $rank = 1;
                        while ($row = mysqli_fetch_assoc($result)): 
                            // Highlight top 3
                            $rowClass = "";
                            if ($rank == 1) $rowClass = "table-warning fw-bold";
                            elseif ($rank == 2) $rowClass = "table-light fw-bold";
                            elseif ($rank == 3) $rowClass = "table-danger text-white fw-bold";
                        ?>
                        <tr class="<?php echo $rowClass; ?>">
                            <td>
                                <?php 
                                    if ($rank == 1) echo "🥇 ";
                                    elseif ($rank == 2) echo "🥈 ";
                                    elseif ($rank == 3) echo "🥉 ";
                                    echo "#" . $rank; 
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td><?php echo $row['score']; ?></td>
                            <td><?php echo $row['time_taken']; ?>s</td>
                            <td><?php echo date("d M Y", strtotime($row['created_at'])); ?></td>
                        </tr>
                        <?php 
                            $rank++;
                        endwhile; 
                        
                        if ($rank == 1) {
                            echo "<tr><td colspan='5' class='text-muted'>Belum ada rekod kuiz. Jadilah yang pertama!</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
                <div class="text-center mt-3">
                    <a href="quiz.php" class="btn btn-success">🔁 Cuba Kuiz Lagi</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>