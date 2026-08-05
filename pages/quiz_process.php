<?php
require_once __DIR__ . '/../includes/app.php';
requireAuth('../login.php');
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/quiz_bank.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: quiz.php');
    exit;
}

$postedToken = (string) ($_POST['attempt_token'] ?? '');
$attempt = $_SESSION['quiz_attempts'][$postedToken] ?? null;
$now = microtime(true);

if (!verifyCsrf($_POST['csrf_token'] ?? null)
    || !is_array($attempt)
    || $now > (float) ($attempt['answer_deadline'] ?? 0)) {
    unset($_SESSION['quiz_attempts'][$postedToken]);
    setFlash('error', 'Sesi kuiz tidak sah atau telah tamat. Sila mulakan semula.');
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
    setFlash('error', 'Soalan kuiz tidak dapat disahkan. Sila cuba lagi.');
    header('Location: quiz.php');
    exit;
}

$postedAnswers = is_array($_POST['q'] ?? null) ? $_POST['q'] : [];
$score = 0;
$answered = 0;
$reviewItems = [];

foreach ($questions as $question) {
    $selectedLetter = strtoupper((string) ($postedAnswers[$question['id']] ?? ''));
    if (!array_key_exists($selectedLetter, $question['options'])) {
        $selectedLetter = '';
    }

    $isCorrect = $selectedLetter !== '' && hash_equals($question['correct'], $selectedLetter);
    if ($selectedLetter !== '') {
        $answered++;
    }
    if ($isCorrect) {
        $score += 10;
    }

    $reviewItems[] = [
        'question' => $question['question'],
        'options' => $question['options'],
        'selected' => $selectedLetter,
        'correct' => $question['correct'],
        'is_correct' => $isCorrect,
        'explanation' => $question['explanation'],
    ];
}

$serverElapsed = max(0.0, $now - (float) $attempt['started_at']);
$timeTaken = min(60, (int) floor($serverElapsed));
$userId = (int) $_SESSION['user_id'];
$insert = mysqli_prepare($conn, 'INSERT INTO quiz_scores (user_id, score, time_taken) VALUES (?, ?, ?)');

if (!$insert) {
    unset($_SESSION['quiz_attempts'][$postedToken]);
    setFlash('error', 'Skor tidak dapat disimpan. Sila cuba lagi.');
    header('Location: quiz.php?theme=' . urlencode($themeKey));
    exit;
}

mysqli_stmt_bind_param($insert, 'iii', $userId, $score, $timeTaken);
$saved = mysqli_stmt_execute($insert);
mysqli_stmt_close($insert);
unset($_SESSION['quiz_attempts'][$postedToken]);

if (!$saved) {
    setFlash('error', 'Skor tidak dapat disimpan. Sila cuba lagi.');
    header('Location: quiz.php?theme=' . urlencode($themeKey));
    exit;
}

$resultToken = bin2hex(random_bytes(16));
$storedResults = $_SESSION['quiz_results'] ?? [];
$storedResults = array_filter($storedResults, static fn(array $result): bool => (int) ($result['expires_at'] ?? 0) >= time());
$storedResults[$resultToken] = [
    'theme_key' => $themeKey,
    'theme_title' => $theme['title'],
    'score' => $score,
    'time_taken' => $timeTaken,
    'answered' => $answered,
    'total' => count($questions),
    'items' => $reviewItems,
    'expires_at' => time() + 1800,
];
$_SESSION['quiz_results'] = array_slice($storedResults, -5, null, true);

header('Location: quiz_review.php?result=' . urlencode($resultToken));
exit;
