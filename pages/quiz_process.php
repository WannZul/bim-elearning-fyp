<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit(); }
include __DIR__ . '/../includes/db_connect.php';

$user_id = $_SESSION['user_id'];
$time_taken = (int)$_POST['time_taken'];
$score = 0;

// Ambil semua soalan untuk semak jawapan betul
$result = mysqli_query($conn, "SELECT id, correct_answer FROM quiz_questions");
while ($q = mysqli_fetch_assoc($result)) {
    $q_id = $q['id'];
    $correct_ans = $q['correct_answer'];
    
    // Semak jika user jawab soalan ini
    if (isset($_POST['q' . $q_id]) && $_POST['q' . $q_id] == $correct_ans) {
        $score += 10; // 10 markah setiap soalan betul
    }
}

// Simpan markah ke database
$stmt = mysqli_prepare($conn, "INSERT INTO quiz_scores (user_id, score, time_taken) VALUES (?, ?, ?)");
mysqli_stmt_bind_param($stmt, "iii", $user_id, $score, $time_taken);
mysqli_stmt_execute($stmt);

// Redirect ke Leaderboard
header("Location: leaderboard.php");
exit();
?>