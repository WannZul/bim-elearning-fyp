<?php

declare(strict_types=1);

function quizThemes(): array
{
    return [
        'numbers' => [
            'title' => t('quiz.themes.numbers.title'),
            'short_title' => t('quiz.themes.numbers.short_title'),
            'description' => t('quiz.themes.numbers.description'),
            'icon' => 'bi-123',
            'accent' => 'teal',
            'duration' => t('quiz.themes.numbers.duration'),
        ],
        'alphabet' => [
            'title' => t('quiz.themes.alphabet.title'),
            'short_title' => t('quiz.themes.alphabet.short_title'),
            'description' => t('quiz.themes.alphabet.description'),
            'icon' => 'bi-alphabet-uppercase',
            'accent' => 'blue',
            'duration' => t('quiz.themes.alphabet.duration'),
        ],
    ];
}

function practicalQuizThemes(): array
{
    $themes = quizThemes();
    return [
        'alphabet' => array_merge($themes['alphabet'], [
            'mode' => 'practical',
            'category' => 'alphabet',
            'duration' => t('quiz.practical_duration'),
        ]),
        'numbers' => array_merge($themes['numbers'], [
            'mode' => 'practical',
            'category' => 'numbers',
            'duration' => t('quiz.practical_duration'),
        ]),
    ];
}

function leaderboardThemes(): array
{
    $themes = [];
    foreach (quizThemes() as $type => $theme) {
        $themes[$type] = array_merge($theme, [
            'score_type' => $type,
            'mode' => 'theory',
            'category' => $type,
        ]);
    }
    return $themes;
}

function challengeUrlForScoreType(string $scoreType): string
{
    $theme = leaderboardThemes()[$scoreType] ?? null;
    if (!$theme) return 'quiz.php';
    return 'quiz.php?theme=' . urlencode($theme['category']);
}

function quizQuestionDefinitions(): array
{
    return [
        'numbers' => [
            ['id' => 'num-01', 'correct' => 'A'],
            ['id' => 'num-02', 'correct' => 'B'],
            ['id' => 'num-03', 'correct' => 'C'],
            ['id' => 'num-04', 'correct' => 'B'],
            ['id' => 'num-05', 'correct' => 'C'],
            ['id' => 'num-06', 'correct' => 'B'],
            ['id' => 'num-07', 'correct' => 'B'],
            ['id' => 'num-08', 'correct' => 'C'],
        ],
        'alphabet' => [
            ['id' => 'alpha-01', 'correct' => 'B'],
            ['id' => 'alpha-02', 'correct' => 'A'],
            ['id' => 'alpha-03', 'correct' => 'B'],
            ['id' => 'alpha-04', 'correct' => 'B'],
            ['id' => 'alpha-05', 'correct' => 'B'],
            ['id' => 'alpha-06', 'correct' => 'A'],
            ['id' => 'alpha-07', 'correct' => 'A'],
            ['id' => 'alpha-08', 'correct' => 'A'],
        ],
    ];
}

function localizedQuizQuestion(array $definition): array
{
    $id = (string) $definition['id'];
    $key = 'quiz.questions.' . $id;
    $options = [];
    foreach (['A', 'B', 'C', 'D'] as $letter) {
        $options[$letter] = t($key . '.options.' . $letter);
    }

    return [
        'id' => $id,
        'question' => t($key . '.question'),
        'options' => $options,
        'correct' => (string) $definition['correct'],
        'explanation' => t($key . '.explanation'),
    ];
}

function quizQuestionBank(): array
{
    $bank = [];
    foreach (quizQuestionDefinitions() as $theme => $definitions) {
        $bank[$theme] = array_map('localizedQuizQuestion', $definitions);
    }
    return $bank;
}

function quizQuestionsForTheme(string $theme): array
{
    return quizQuestionBank()[$theme] ?? [];
}

function quizQuestionsByIds(string $theme, array $ids): array
{
    $indexed = [];
    foreach (quizQuestionsForTheme($theme) as $question) {
        $indexed[$question['id']] = $question;
    }

    $ordered = [];
    foreach ($ids as $id) {
        if (isset($indexed[$id])) {
            $ordered[] = $indexed[$id];
        }
    }

    return $ordered;
}
