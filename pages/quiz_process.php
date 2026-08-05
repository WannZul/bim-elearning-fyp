<?php
require_once __DIR__ . '/../includes/app.php';
requireAuth('../login.php');
require_once __DIR__ . '/../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: quiz.php');
    exit;
}

$postedToken = (string) ($_POST['attempt_token'] ?? '');
$attempt = $_SESSION['quiz_attempts'][$postedToken] ?? null;

if (!verifyCsrf($_POST['csrf_token'] ?? null)
    || !is_array($attempt)
    || microtime(true) > (float) ($attempt['expires_at'] ?? 0)) {
    unset($_SESSION['quiz_attempts'][$postedToken]);
    setFlash('error', 'Sesi kuiz tidak sah atau telah tamat. Sila mulakan semula.');
    header('Location: quiz.php');
    exit;
}

$questionIds = array_values(array_filter(array_map('intval', $attempt['question_ids'] ?? []), static fn(int $id): bool => $id > 0));
if (!$questionIds) {
    unset($_SESSION['quiz_attempts'][$postedToken]);
    setFlash('error', 'Soalan kuiz tidak dapat disahkan. Sila cuba lagi.');
    header('Location: quiz.php');
    exit;
}

$safeIds = implode(',', $questionIds);
$query = mysqli_query($conn, "SELECT id, correct_answer FROM quiz_questions WHERE id IN ($safeIds)");
$score = 0;
$answered = 0;
$answerKeysFound = 0;

if ($query) {
    while ($question = mysqli_fetch_assoc($query)) {
        $answerKeysFound++;
        $answer = strtoupper((string) ($_POST['q' . (int) $question['id']] ?? ''));
        if (in_array($answer, ['A', 'B', 'C', 'D'], true)) {
            $answered++;
            if (hash_equals(strtoupper((string) $question['correct_answer']), $answer)) {
                $score += 10;
            }
        }
    }
}

if (!$query || $answerKeysFound !== count($questionIds)) {
    unset($_SESSION['quiz_attempts'][$postedToken]);
    setFlash('error', 'Jawapan kuiz tidak dapat disahkan. Sila mulakan semula.');
    header('Location: quiz.php');
    exit;
}

$serverElapsed = max(0.0, microtime(true) - (float) $attempt['started_at']);
$timeTaken = min(60, (int) floor($serverElapsed));
$userId = (int) $_SESSION['user_id'];
$insert = mysqli_prepare($conn, 'INSERT INTO quiz_scores (user_id, score, time_taken) VALUES (?, ?, ?)');

if (!$insert) {
    unset($_SESSION['quiz_attempts'][$postedToken]);
    setFlash('error', 'Skor tidak dapat disimpan. Sila cuba lagi.');
    header('Location: quiz.php');
    exit;
}

mysqli_stmt_bind_param($insert, 'iii', $userId, $score, $timeTaken);
$saved = mysqli_stmt_execute($insert);
mysqli_stmt_close($insert);
unset($_SESSION['quiz_attempts'][$postedToken]);

if (!$saved) {
    setFlash('error', 'Skor tidak dapat disimpan. Sila cuba lagi.');
    header('Location: quiz.php');
    exit;
}

$_SESSION['last_quiz_result'] = [
    'score' => $score,
    'time_taken' => $timeTaken,
    'answered' => $answered,
    'total' => count($questionIds),
];
setFlash('success', 'Cabaran selesai! Keputusan anda telah direkodkan.');
header('Location: leaderboard.php');
exit;
