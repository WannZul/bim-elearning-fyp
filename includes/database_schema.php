<?php

declare(strict_types=1);

function quizTypeStorageReady(mysqli $conn): bool
{
    static $isReady = null;

    if ($isReady !== null) {
        return $isReady;
    }

    $result = mysqli_query($conn, "SHOW COLUMNS FROM quiz_scores LIKE 'quiz_type'");
    $isReady = $result instanceof mysqli_result && mysqli_num_rows($result) === 1;

    if ($result instanceof mysqli_result) {
        mysqli_free_result($result);
    }

    return $isReady;
}

function quizTypeMigrationMessage(): string
{
    return t('flash.migration_required');
}
