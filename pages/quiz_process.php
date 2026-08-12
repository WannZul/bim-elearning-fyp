<?php
require_once __DIR__ . '/../includes/app.php';
requireAuth('../login.php');
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/database_schema.php';
require_once __DIR__ . '/../includes/quiz_bank.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: quiz.php');
    exit;
}

$postedToken = (string) ($_POST['attempt_token'] ?? '');
$attempt = $_SESSION['quiz_attempts'][$postedToken] ?? null;
$now = microtime(true);

if (!verifyCsrf($_POST['csrf_token'] ?? null) || !is_array($attempt) || $now > (float) ($attempt['answer_deadline'] ?? 0)) {
    unset($_SESSION['quiz_attempts'][$postedToken]);
    setFlash('error', 'flash.quiz_invalid');
    header('Location: quiz.php');
    exit;
}

$themeKey = (string) ($attempt['theme'] ?? '');
$themes = quizThemes();
$theme = $themes[$themeKey] ?? null;
$questionIds = array_values(array_filter($attempt['question_ids'] ?? [], 'is_string'));
$questions = quizQuestionsByIds($themeKey, $questionIds);

if (!$theme || count($questions) !== 5 || count($questionIds) !== 5) {
    unset($_SESSION['quiz_attempts'][$postedToken]);
    setFlash('error', 'flash.quiz_unverified');
    header('Location: quiz.php');
    exit;
}

$postedAnswers = is_array($_POST['q'] ?? null) ? $_POST['q'] : [];
$score = 0;
$answered = 0;
$selectedAnswers = [];
$correctAnswers = [];

foreach ($questions as $question) {
    $questionId = (string) $question['id'];
    $selectedLetter = strtoupper((string) ($postedAnswers[$questionId] ?? ''));
    if (!in_array($selectedLetter, ['A', 'B', 'C', 'D'], true)) $selectedLetter = '';
    $correctLetter = (string) $question['correct'];
    $selectedAnswers[$questionId] = $selectedLetter;
    $correctAnswers[$questionId] = $correctLetter;
    if ($selectedLetter !== '') $answered++;
    if ($selectedLetter !== '' && hash_equals($correctLetter, $selectedLetter)) $score += 10;
}

$serverElapsed = max(0.0, $now - (float) $attempt['started_at']);
$timeTaken = min(60, (int) floor($serverElapsed));
$userId = (int) $_SESSION['user_id'];

if (!quizTypeStorageReady($conn)) {
    unset($_SESSION['quiz_attempts'][$postedToken]);
    setFlash('error', 'flash.migration_required');
    header('Location: quiz.php');
    exit;
}

$insert = mysqli_prepare($conn, 'INSERT INTO quiz_scores (user_id, score, time_taken, quiz_type) VALUES (?, ?, ?, ?)');
if (!$insert) {
    unset($_SESSION['quiz_attempts'][$postedToken]);
    setFlash('error', 'flash.score_save_failed');
    header('Location: quiz.php?theme=' . urlencode($themeKey));
    exit;
}

mysqli_stmt_bind_param($insert, 'iiis', $userId, $score, $timeTaken, $themeKey);
$saved = mysqli_stmt_execute($insert);
mysqli_stmt_close($insert);
unset($_SESSION['quiz_attempts'][$postedToken]);

if (!$saved) {
    setFlash('error', 'flash.score_save_failed');
    header('Location: quiz.php?theme=' . urlencode($themeKey));
    exit;
}

$resultToken = bin2hex(random_bytes(16));
$storedResults = $_SESSION['quiz_results'] ?? [];
$storedResults = array_filter($storedResults, static fn(array $result): bool => (int) ($result['expires_at'] ?? 0) >= time());
$storedResults[$resultToken] = [
    'theme_key' => $themeKey,
    'question_ids' => $questionIds,
    'selected_answers' => $selectedAnswers,
    'correct_answers' => $correctAnswers,
    'score' => $score,
    'time_taken' => $timeTaken,
    'answered' => $answered,
    'total' => count($questions),
    'expires_at' => time() + 1800,
];
$_SESSION['quiz_results'] = array_slice($storedResults, -5, null, true);

header('Location: quiz_review.php?result=' . urlencode($resultToken));
exit;
