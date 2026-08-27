<?php
require_once __DIR__ . '/../../includes/sign_catalog.php';

$copy = [
    'dashboard' => [
        'completed' => 'Quizzes completed', 'progress_intro' => 'Theory scores are stored separately for alphabet and number quizzes. Experimental camera-practice results are not saved.',
        'six_lessons' => '37 learning signs', 'ten_minutes' => 'A–Z and 0–10', 'five_signs' => '19 compatible signs', 'five_ai' => '19 camera practices',
        'module1_desc' => 'Learn the complete one-hand BIM alphabet A–Z and numbers 0–10 curriculum.',
        'module2_desc' => 'Practise the 19 static signs compatible with this landmark prototype.',
        'basics_desc' => 'Study all 37 signs, then use the camera with the compatible 19-sign subset.',
        'recent' => 'Recent theory attempts', 'recent_intro' => 'Theory scores, categories, times, and dates are recorded.',
        'no_records' => 'No theory records yet', 'no_records_desc' => 'Complete an alphabet or number theory quiz to begin tracking progress.',
        'quiz_type' => ':type',
    ],
    'learn' => [
        'intro' => 'Study all 37 one-hand BIM alphabet and number signs. Camera practice is offered only for the compatible static subset.',
        'path_desc' => 'The complete A–Z and 0–10 curriculum, with movement and camera-compatibility guidance.',
        'lessons_available' => '37 signs available', 'ai_verified' => '19 camera-compatible signs',
        'first_signs' => 'All BIM alphabet and number signs', 'duration' => '37 concise reference cards',
        'all_count' => 'All · 37', 'alphabet_count' => 'Alphabet · 26', 'numbers_count' => 'Numbers · 11',
        'static_badge' => 'Static', 'dynamic_badge' => 'Dynamic', 'camera_badge' => 'Camera compatible', 'reference_badge' => 'Reference only', 'one_hand_badge' => 'One hand',
        'unavailable_label' => 'Camera unavailable:',
        'unavailable' => [
            'dynamic' => 'This sign uses movement; the prototype checks static handshapes only.',
            'orientation' => 'This sign depends on precise palm or finger direction, which the webcam landmarks cannot distinguish reliably.',
            'finger_contact' => 'This sign depends on a small fingertip or thumb contact that can be hidden or look ambiguous on webcam.',
            'finger_bend' => 'This sign depends on a precise finger bend that the landmark prototype may confuse with a folded finger.',
            'thumb_placement' => 'This sign depends on exact or partly hidden thumb placement, which the webcam landmarks cannot verify reliably.',
            'finger_crossing' => 'This sign depends on crossed-finger depth, which flat webcam landmarks cannot verify reliably.',
            'fine_detail' => 'Landmarks cannot reliably verify this sign’s fine handshape detail.',
        ],
        'movement' => [
            'J' => 'Trace a J with the handshape.', 'Z' => 'Trace a Z with the handshape.', '10' => 'Rotate a thumbs-up handshape.',
        ],
        'ready_desc' => 'Practise 19 selected static signs using one-hand MediaPipe landmarks and Fingerpose.',
        'prototype' => 'Learning reference: MFD BIM Sign Bank. No third-party images are copied because the source repository provides no licence.',
        'reference_link' => 'Open MFD BIM Sign Bank', 'practice_aria' => 'Practise :title with the camera',
    ],
    'ai' => [
        'intro' => 'Choose alphabet or numbers, then practise one of the 19 selected static prototype signs. Classification is scoped to the selected category.',
        'category_label' => 'Choose sign category', 'alphabet' => 'Alphabet', 'numbers' => 'Numbers',
        'alphabet_count' => '9 compatible letters', 'numbers_count' => '10 compatible numbers',
        'target_heading' => ':category target', 'target_count' => ':count targets available',
        'equivalent_note' => 'Some alphabet and number signs have the same visible shape. The selected category tells the prototype how to interpret it.',
        'validation_note' => 'A and numbers 1–4 received positive user trials. The newly added signs remain experimental candidates until they are tested across more users, lighting, distance, backgrounds, and handedness.',
        'instruction' => 'Form the selected static sign clearly and hold it until the browser confirms it.',
        'js' => ['category_changed' => 'Category changed to :category', 'title_sign' => ':category :target'],
    ],
    'quiz' => [
        'hub_intro' => 'Choose a 60-second theory quiz or a 90-second low-stakes camera practical.',
        'theory_heading' => 'Theory quizzes', 'theory_intro' => 'Answer five randomized knowledge questions.',
        'practical_heading' => 'Camera practice prototypes', 'practical_intro' => 'Show five unique compatible signs. Results are low-stakes personal practice, not secure formal assessment or leaderboard evidence.',
        'practical_card_meta' => '5 camera targets', 'practical_choose' => 'Start camera practice', 'practical_duration' => '90 seconds',
        'scope' => 'The curriculum covers all A–Z and 0–10 signs. Guided camera practice offers 19 selected candidates; the temporary unscored challenge excludes A until closed-fist confusion testing is documented.',
    ],
    'practical' => [
        'page_title' => ':category Camera Practical', 'eyebrow' => 'Low-stakes browser verification', 'heading' => ':category practical challenge',
        'intro' => 'Confirm five unique signs in 90 seconds. Start the camera once, hold each target, or skip and continue.',
        'trust' => 'This experimental landmark challenge supports personal learning only. Its result is temporary, is not saved, and is not secure evidence for formal assessment.',
        'privacy' => 'The application does not intentionally upload or store video or images. Recognition runs in your browser using pinned MediaPipe and Fingerpose scripts loaded from jsDelivr; only confirmed target names are submitted for the temporary review.',
        'camera_label' => 'Practical challenge camera', 'progress' => 'Target :current of :total', 'next_target' => 'Target :current of :total: :title (:target).', 'confirmed_count' => ':count of :total confirmed',
        'time_remaining' => 'Time remaining', 'current_target' => 'Current target', 'start' => 'Start camera and challenge', 'stop' => 'Stop camera',
        'skip' => 'Skip this target', 'submit' => 'Finish and view review', 'waiting' => 'Start the camera when ready.', 'activating' => 'Camera ready. Starting the 90-second timer…',
        'confirmed' => ':target confirmed. Moving to the next target.', 'skipped' => ':target skipped.', 'timeout' => 'Time is up. Submitting your confirmed signs.',
        'submitting' => 'Submitting practical result…', 'camera_required' => 'Camera access is needed for this practical.', 'invalid' => 'This practical session is invalid or has expired. Start a new attempt.',
        'review_title' => 'Practice Review', 'review_heading' => 'Browser-reported personal practice result',
        'review_summary' => ':confirmed of :total targets were confirmed in :time.', 'review_list' => 'Practical target review',
        'status_confirmed' => 'Browser confirmed', 'status_skipped' => 'Not confirmed', 'status_pending' => 'Pending', 'review_note' => 'A confirmation means the prototype matched stable landmarks; it does not prove formal sign-language proficiency. This temporary result is not saved.',
        'retry' => 'Try this category again', 'choose_other' => 'Choose another challenge', 'view_progress' => 'View personal progress',
        'js' => ['load_failed' => 'Camera recognition could not load. Go back or reload the page; no score has been recorded.', 'start_failed' => 'Camera access failed. Check browser permission and try again.', 'session_start_failed' => 'The timed session could not start. No score was recorded; check your connection and try again.'],
    ],
    'leaderboard' => [
        'eyebrow' => 'Theory leaderboard', 'intro' => 'Alphabet and number theory quizzes have separate rankings. Highest score comes first; fastest time breaks a tie.',
        'scope_note' => 'Only results from this exact theory quiz type affect the ranking.',
        'try_practical' => 'Try the :theme practical', 'mode_theory' => 'THEORY', 'mode_practical' => 'CAMERA PRACTICAL',
    ],
    'flash' => ['practical_invalid' => 'Your practical session is invalid or expired. Please start again.', 'practical_unverified' => 'The issued practical targets could not be verified.', 'practical_save_failed' => 'Your practical score could not be saved. Please try again.', 'practical_review_expired' => 'This practical review has expired. Start a new attempt to view another review.'],
    'signs' => ['category' => ['alphabet' => 'Letter', 'numbers' => 'Number']],
];

$approvedDescriptions = [
    'alphabet' => [
        'A' => ['Close four fingers; keep the thumb at the side and away from the index fingertip.', 'Fist with side thumb'],
        'B' => ['Raise four fingers together and fold the thumb across the palm.', 'Four fingers together'],
        'C' => ['Curve the fingers and thumb to leave an open C-shaped space.', 'Keep the curved opening visible'],
        'D' => ['Raise the index finger; curve the other fingers toward the thumb.', 'Index up with a rounded base'],
        'E' => ['Bend the fingers toward the palm and keep the thumb close beneath them.', 'Show the compact bent shape'],
        'F' => ['Touch the index fingertip to the thumb and raise the other three fingers.', 'Small thumb–index contact'],
        'G' => ['Point the index finger and thumb sideways while the other fingers stay folded.', 'Check the sideways direction'],
        'H' => ['Extend the index and middle fingers together sideways; fold the other fingers.', 'Two fingers point sideways'],
        'I' => ['Raise only the little finger and keep the thumb folded.', 'Little finger only'],
        'K' => ['Raise and separate the index and middle fingers; place the thumb between them.', 'Thumb between two raised fingers'],
        'L' => ['Raise the index finger and extend the thumb to form an L.', 'Clear right angle'],
        'M' => ['Fold three fingers over the thumb so the thumb appears beneath them.', 'Thumb beneath three fingers'],
        'N' => ['Fold two fingers over the thumb so the thumb appears beneath them.', 'Thumb beneath two fingers'],
        'O' => ['Curve the fingers and touch thumb to index to form O.', 'Thumb–index contact'],
        'P' => ['Use the K handshape and angle it downward.', 'K handshape facing down'],
        'Q' => ['Use the G handshape and point it downward.', 'G handshape facing down'],
        'R' => ['Raise the index and middle fingers and cross one over the other.', 'Keep the two fingers crossed'],
        'S' => ['Close the fingers into a fist and place the thumb across the front.', 'Check the front thumb position'],
        'T' => ['Close the hand with the thumb placed between the index and middle fingers.', 'Thumb between two fingers'],
        'U' => ['Raise index and middle fingers close together.', 'Keep two fingers close'],
        'V' => ['Raise index and middle fingers with a clear spread.', 'Open the V shape'],
        'W' => ['Raise index, middle, and ring fingers.', 'Three fingers visible'],
        'X' => ['Raise the index finger with its upper joints bent like a hook; fold the other fingers.', 'Keep the index finger hooked'],
        'Y' => ['Raise the little finger and extend the thumb.', 'Thumb and little finger'],
    ],
    'numbers' => [
        '0' => ['Curve the fingers and touch thumb to index to form zero.', 'Thumb–index contact'],
        '1' => ['Raise only the index finger.', 'Index finger only'],
        '2' => ['Raise index and middle fingers.', 'Two fingers raised'],
        '3' => ['Raise index, middle, and ring fingers without thumb–little contact.', 'Three fingers raised'],
        '4' => ['Raise four fingers and fold the thumb.', 'Thumb folded'],
        '5' => ['Raise four fingers and extend the thumb.', 'Open hand'],
        '6' => ['Raise three fingers and touch thumb to little finger.', 'Thumb–little contact'],
        '7' => ['Raise index, middle, and little fingers; touch thumb to ring finger.', 'Thumb–ring contact'],
        '8' => ['Raise index, ring, and little fingers; touch thumb to middle finger.', 'Thumb–middle contact'],
        '9' => ['Raise middle, ring, and little fingers; touch thumb to index finger.', 'Thumb–index contact'],
    ],
];

foreach (signCatalog() as $sign) {
    $symbol = $sign['symbol'];
    $category = $sign['category'];
    $title = ($category === 'alphabet' ? 'Letter ' : 'Number ') . $symbol;
    if (isset($approvedDescriptions[$category][$symbol])) {
        [$description, $tip] = $approvedDescriptions[$category][$symbol];
    } elseif ($sign['motion'] === 'dynamic') {
        $description = $copy['learn']['movement'][$symbol] . ' Follow the movement shown in the MFD BIM Sign Bank.';
        $tip = 'Movement is learning-only';
    } else {
        $description = 'Use one hand to form ' . $title . '. Check the exact orientation and finger placement in the MFD BIM Sign Bank.';
        $tip = 'Compare the fine handshape';
    }
    $copy['signs']['entries'][$category][$symbol] = ['title' => $title, 'description' => $description, 'tip' => $tip];
}

return $copy;
