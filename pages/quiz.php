<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit(); }
include __DIR__ . '/../includes/db_connect.php';

// Ambil semua soalan secara rawak
$questions = [];
$result = mysqli_query($conn, "SELECT * FROM quiz_questions ORDER BY RAND() LIMIT 5");
while ($row = mysqli_fetch_assoc($result)) {
    $questions[] = $row;
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Kuiz BIM - E-Learning</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        #timer { font-size: 1.5rem; font-weight: bold; color: red; }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card shadow-lg p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>🏆 Kuiz Interaktif BIM</h3>
                <div>Masa Berbaki: <span id="timer">60</span> saat</div>
            </div>

            <form id="quizForm" action="quiz_process.php" method="POST">
                <input type="hidden" name="time_taken" id="time_taken" value="0">
                
                <?php foreach ($questions as $index => $q): ?>
                    <div class="mb-4">
                        <h5><?php echo ($index + 1) . ". " . $q['question_text']; ?></h5>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q<?php echo $q['id']; ?>" value="A" required>
                            <label class="form-check-label"><?php echo $q['option_a']; ?></label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q<?php echo $q['id']; ?>" value="B">
                            <label class="form-check-label"><?php echo $q['option_b']; ?></label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q<?php echo $q['id']; ?>" value="C">
                            <label class="form-check-label"><?php echo $q['option_c']; ?></label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="q<?php echo $q['id']; ?>" value="D">
                            <label class="form-check-label"><?php echo $q['option_d']; ?></label>
                        </div>
                    </div>
                <?php endforeach; ?>

                <button type="submit" class="btn btn-primary w-100 btn-lg">Hantar Jawapan</button>
            </form>
        </div>
    </div>

    <script>
        // Logik Timer JavaScript
        let timeLeft = 60;
        const timerDisplay = document.getElementById('timer');
        const timeTakenInput = document.getElementById('time_taken');
        const quizForm = document.getElementById('quizForm');

        const countdown = setInterval(() => {
            timeLeft--;
            timerDisplay.textContent = timeLeft;
            timeTakenInput.value = 60 - timeLeft; // Kira masa yang dah digunakan

            if (timeLeft <= 0) {
                clearInterval(countdown);
                alert("Masa Tamat! Kuiz akan dihantar secara automatik.");
                quizForm.submit(); // Auto submit bila masa habis
            }
        }, 1000);

        // Jika user submit manual, kira masa sebenar
        quizForm.addEventListener('submit', () => {
            clearInterval(countdown);
            timeTakenInput.value = 60 - timeLeft;
        });
    </script>
</body>
</html>