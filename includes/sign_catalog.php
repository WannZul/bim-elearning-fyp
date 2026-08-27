<?php

declare(strict_types=1);

const BIM_SIGN_REFERENCE_URL = 'https://www.bimsignbank.org';
const BIM_SIGN_CATEGORIES = ['alphabet', 'numbers'];

/**
 * Canonical BIM learning catalog. The classifier IDs are intentionally absent
 * for learning-only signs; they must never be offered to the camera prototype.
 */
function signCatalog(): array
{
    static $catalog = null;
    if ($catalog !== null) return $catalog;

    $cameraAlphabet = ['A', 'B', 'I', 'L', 'O', 'U', 'V', 'W', 'Y'];
    $cameraNumbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    $dynamicSigns = ['alphabet:J', 'alphabet:Z', 'numbers:10'];
    $alphabetCameraLimits = [
        'C' => 'orientation',
        'D' => 'finger_contact',
        'E' => 'finger_bend',
        'F' => 'finger_contact',
        'G' => 'orientation',
        'H' => 'orientation',
        'K' => 'thumb_placement',
        'M' => 'thumb_placement',
        'N' => 'thumb_placement',
        'P' => 'orientation',
        'Q' => 'orientation',
        'R' => 'finger_crossing',
        'S' => 'thumb_placement',
        'T' => 'thumb_placement',
        'X' => 'finger_bend',
    ];
    $catalog = [];

    foreach (range('A', 'Z') as $index => $symbol) {
        $cameraEligible = in_array($symbol, $cameraAlphabet, true);
        $qualifiedId = 'alphabet:' . $symbol;
        $motion = in_array($qualifiedId, $dynamicSigns, true) ? 'dynamic' : 'static';
        $catalog[] = [
            'symbol' => $symbol,
            'category' => 'alphabet',
            'sort_order' => $index + 1,
            'hands' => 1,
            'motion' => $motion,
            'camera_eligible' => $cameraEligible,
            'classifier_id' => $cameraEligible ? $qualifiedId : null,
            'content_key' => 'signs.entries.alphabet.' . $symbol,
            'unavailable_reason' => $motion === 'dynamic'
                ? 'dynamic'
                : ($cameraEligible ? null : ($alphabetCameraLimits[$symbol] ?? 'fine_detail')),
            'reference_url' => BIM_SIGN_REFERENCE_URL,
        ];
    }

    foreach (range(0, 10) as $number) {
        $symbol = (string) $number;
        $cameraEligible = in_array($symbol, $cameraNumbers, true);
        $qualifiedId = 'numbers:' . $symbol;
        $motion = in_array($qualifiedId, $dynamicSigns, true) ? 'dynamic' : 'static';
        $catalog[] = [
            'symbol' => $symbol,
            'category' => 'numbers',
            'sort_order' => $number,
            'hands' => 1,
            'motion' => $motion,
            'camera_eligible' => $cameraEligible,
            'classifier_id' => $cameraEligible ? $qualifiedId : null,
            'content_key' => 'signs.entries.numbers.' . $symbol,
            'unavailable_reason' => $motion === 'dynamic' ? 'dynamic' : ($cameraEligible ? null : 'fine_detail'),
            'reference_url' => BIM_SIGN_REFERENCE_URL,
        ];
    }

    return $catalog;
}

function signsForCategory(string $category, bool $cameraOnly = false): array
{
    if (!in_array($category, BIM_SIGN_CATEGORIES, true)) return [];

    return array_values(array_filter(signCatalog(), static fn(array $sign): bool =>
        $sign['category'] === $category && (!$cameraOnly || $sign['camera_eligible'])
    ));
}

function cameraApprovedSigns(string $category): array
{
    return signsForCategory($category, true);
}

/**
 * Temporary challenge candidates. A remains available in guided practice, but
 * is excluded here until side-thumb placement is tested against closed-fist
 * neighbours such as M, N, S, and T.
 */
function cameraChallengeSigns(string $category): array
{
    return array_values(array_filter(cameraApprovedSigns($category), static fn(array $sign): bool =>
        $sign['classifier_id'] !== 'alphabet:A'
    ));
}

function signCatalogEntry(string $category, string $symbol): ?array
{
    foreach (signCatalog() as $sign) {
        if ($sign['category'] === $category && $sign['symbol'] === $symbol) return $sign;
    }
    return null;
}

function signCategoryCounts(): array
{
    $counts = ['alphabet' => 0, 'numbers' => 0, 'all' => 0, 'camera' => 0];
    foreach (signCatalog() as $sign) {
        $counts[$sign['category']]++;
        $counts['all']++;
        if ($sign['camera_eligible']) $counts['camera']++;
    }
    return $counts;
}

function signRecognizerManifest(): array
{
    $manifest = [];
    foreach (BIM_SIGN_CATEGORIES as $category) {
        $manifest[$category] = array_map(static fn(array $sign): array => [
            'symbol' => $sign['symbol'],
            'classifierId' => $sign['classifier_id'],
        ], cameraApprovedSigns($category));
    }
    return $manifest;
}
