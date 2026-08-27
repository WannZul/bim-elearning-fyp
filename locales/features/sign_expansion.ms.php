<?php
require_once __DIR__ . '/../../includes/sign_catalog.php';

$copy = [
    'dashboard' => [
        'completed' => 'Kuiz diselesaikan', 'progress_intro' => 'Skor teori disimpan berasingan untuk kuiz abjad dan nombor. Keputusan latihan kamera eksperimen tidak disimpan.',
        'six_lessons' => '37 isyarat pembelajaran', 'ten_minutes' => 'A–Z dan 0–10', 'five_signs' => '19 isyarat serasi', 'five_ai' => '19 latihan kamera',
        'module1_desc' => 'Pelajari kurikulum lengkap abjad BIM A–Z dan nombor 0–10 menggunakan satu tangan.',
        'module2_desc' => 'Latih 19 isyarat statik yang serasi dengan prototaip titik tangan ini.',
        'basics_desc' => 'Pelajari semua 37 isyarat, kemudian gunakan kamera untuk subset 19 isyarat yang serasi.',
        'recent' => 'Cubaan teori terkini', 'recent_intro' => 'Skor teori, kategori, masa, dan tarikh direkodkan.',
        'no_records' => 'Belum ada rekod teori', 'no_records_desc' => 'Lengkapkan kuiz teori abjad atau nombor untuk mula menjejak kemajuan.', 'quiz_type' => ':type',
    ],
    'learn' => [
        'intro' => 'Pelajari kesemua 37 isyarat abjad dan nombor BIM satu tangan. Latihan kamera hanya tersedia untuk subset statik yang serasi.',
        'path_desc' => 'Kurikulum lengkap A–Z dan 0–10 dengan panduan pergerakan dan keserasian kamera.',
        'lessons_available' => '37 isyarat tersedia', 'ai_verified' => '19 isyarat serasi kamera', 'first_signs' => 'Semua isyarat abjad dan nombor BIM', 'duration' => '37 kad rujukan ringkas',
        'all_count' => 'Semua · 37', 'alphabet_count' => 'Abjad · 26', 'numbers_count' => 'Nombor · 11',
        'static_badge' => 'Statik', 'dynamic_badge' => 'Dinamik', 'camera_badge' => 'Serasi kamera', 'reference_badge' => 'Rujukan sahaja', 'one_hand_badge' => 'Satu tangan',
        'unavailable_label' => 'Kamera tidak tersedia:',
        'unavailable' => [
            'dynamic' => 'Isyarat ini menggunakan pergerakan; prototaip hanya menyemak bentuk tangan statik.',
            'orientation' => 'Isyarat ini bergantung pada arah tapak tangan atau jari yang tepat, yang belum dapat dibezakan dengan andal oleh titik kamera web.',
            'finger_contact' => 'Isyarat ini bergantung pada sentuhan kecil hujung jari atau ibu jari yang boleh terlindung atau kelihatan kabur pada kamera web.',
            'finger_bend' => 'Isyarat ini bergantung pada lenturan jari yang tepat dan mungkin dikelirukan dengan jari terlipat oleh prototaip.',
            'thumb_placement' => 'Isyarat ini bergantung pada kedudukan ibu jari yang tepat atau separa terlindung, yang belum dapat disahkan dengan andal.',
            'finger_crossing' => 'Isyarat ini bergantung pada kedalaman jari bersilang, yang belum dapat disahkan dengan andal oleh titik kamera rata.',
            'fine_detail' => 'Titik tangan belum dapat mengesahkan bentuk tangan halus isyarat ini dengan andal.',
        ],
        'movement' => ['J' => 'Jejak bentuk J dengan tangan.', 'Z' => 'Jejak bentuk Z dengan tangan.', '10' => 'Putarkan bentuk ibu jari ke atas.'],
        'ready_desc' => 'Latih 19 isyarat statik terpilih menggunakan titik satu tangan MediaPipe dan Fingerpose.',
        'prototype' => 'Rujukan pembelajaran: Bank Isyarat BIM MFD. Imej pihak ketiga tidak disalin kerana repositori sumber tiada lesen.',
        'reference_link' => 'Buka Bank Isyarat BIM MFD', 'practice_aria' => 'Latih :title dengan kamera',
    ],
    'ai' => [
        'intro' => 'Pilih abjad atau nombor, kemudian latih satu daripada 19 isyarat prototaip statik yang dipilih. Pengelasan mengikut kategori dipilih.',
        'category_label' => 'Pilih kategori isyarat', 'alphabet' => 'Abjad', 'numbers' => 'Nombor', 'alphabet_count' => '9 huruf serasi', 'numbers_count' => '10 nombor serasi',
        'target_heading' => 'Sasaran :category', 'target_count' => ':count sasaran tersedia',
        'equivalent_note' => 'Sesetengah isyarat abjad dan nombor mempunyai bentuk yang sama. Kategori dipilih menentukan tafsiran prototaip.',
        'validation_note' => 'A dan nombor 1–4 menerima ujian pengguna yang positif. Isyarat baharu kekal sebagai calon eksperimen sehingga diuji dengan lebih ramai pengguna, pencahayaan, jarak, latar, dan kedua-dua tangan dominan.',
        'instruction' => 'Bentuk isyarat statik dipilih dengan jelas dan tahan sehingga pelayar mengesahkannya.',
        'js' => ['category_changed' => 'Kategori ditukar kepada :category', 'title_sign' => ':category :target'],
    ],
    'quiz' => [
        'hub_intro' => 'Pilih kuiz teori 60 saat atau praktikal kamera berisiko rendah 90 saat.', 'theory_heading' => 'Kuiz teori', 'theory_intro' => 'Jawab lima soalan pengetahuan rawak.',
        'practical_heading' => 'Prototaip latihan kamera', 'practical_intro' => 'Tunjukkan lima isyarat serasi yang unik. Keputusan ialah latihan peribadi berisiko rendah, bukan penilaian formal selamat atau bukti ranking.',
        'practical_card_meta' => '5 sasaran kamera', 'practical_choose' => 'Mulakan latihan kamera', 'practical_duration' => '90 saat',
        'scope' => 'Kurikulum meliputi semua A–Z dan 0–10. Latihan kamera berpandu menawarkan 19 calon terpilih; cabaran sementara tanpa skor mengecualikan A sehingga ujian kekeliruan bentuk genggaman didokumenkan.',
    ],
    'practical' => [
        'page_title' => 'Praktikal Kamera :category', 'eyebrow' => 'Pengesahan pelayar berisiko rendah', 'heading' => 'Cabaran praktikal :category',
        'intro' => 'Sahkan lima isyarat unik dalam 90 saat. Mulakan kamera sekali, tahan setiap sasaran, atau langkau dan teruskan.',
        'trust' => 'Cabaran titik tangan eksperimen ini hanya untuk pembelajaran peribadi. Keputusannya sementara, tidak disimpan, dan bukan bukti selamat untuk penilaian formal.',
        'privacy' => 'Aplikasi tidak sengaja memuat naik atau menyimpan video atau imej. Pengecaman berjalan dalam pelayar menggunakan skrip MediaPipe dan Fingerpose bertetap yang dimuatkan daripada jsDelivr; hanya nama sasaran disahkan dihantar untuk ulasan sementara.',
        'camera_label' => 'Kamera cabaran praktikal', 'progress' => 'Sasaran :current daripada :total', 'next_target' => 'Sasaran :current daripada :total: :title (:target).', 'confirmed_count' => ':count daripada :total disahkan', 'time_remaining' => 'Masa berbaki', 'current_target' => 'Sasaran semasa',
        'start' => 'Mulakan kamera dan cabaran', 'stop' => 'Hentikan kamera', 'skip' => 'Langkau sasaran ini', 'submit' => 'Tamat dan lihat ulasan', 'waiting' => 'Mulakan kamera apabila bersedia.', 'activating' => 'Kamera sedia. Memulakan pemasa 90 saat…',
        'confirmed' => ':target disahkan. Beralih ke sasaran seterusnya.', 'skipped' => ':target dilangkau.', 'timeout' => 'Masa tamat. Menghantar isyarat yang disahkan.', 'submitting' => 'Menghantar keputusan praktikal…',
        'camera_required' => 'Akses kamera diperlukan untuk praktikal ini.', 'invalid' => 'Sesi praktikal ini tidak sah atau tamat. Mulakan cubaan baharu.',
        'review_title' => 'Ulasan Latihan', 'review_heading' => 'Keputusan latihan peribadi yang dilaporkan pelayar', 'review_summary' => ':confirmed daripada :total sasaran disahkan dalam :time.',
        'review_list' => 'Ulasan sasaran praktikal', 'status_confirmed' => 'Disahkan pelayar', 'status_skipped' => 'Tidak disahkan', 'status_pending' => 'Belum dicuba',
        'review_note' => 'Pengesahan bermaksud prototaip memadankan titik tangan stabil; ia tidak membuktikan kemahiran bahasa isyarat formal. Keputusan sementara ini tidak disimpan.',
        'retry' => 'Cuba kategori ini lagi', 'choose_other' => 'Pilih cabaran lain', 'view_progress' => 'Lihat kemajuan peribadi',
        'js' => ['load_failed' => 'Pengecaman kamera gagal dimuatkan. Kembali atau muat semula halaman; tiada skor direkodkan.', 'start_failed' => 'Akses kamera gagal. Semak kebenaran pelayar dan cuba lagi.', 'session_start_failed' => 'Sesi bermasa gagal dimulakan. Tiada skor direkodkan; semak sambungan dan cuba lagi.'],
    ],
    'leaderboard' => ['eyebrow' => 'Papan kedudukan teori', 'intro' => 'Kuiz teori abjad dan nombor mempunyai ranking berasingan. Skor tertinggi didahulukan dan masa terpantas memecahkan seri.', 'scope_note' => 'Hanya keputusan jenis kuiz teori yang tepat ini mempengaruhi ranking.', 'try_practical' => 'Cuba praktikal :theme', 'mode_theory' => 'TEORI', 'mode_practical' => 'PRAKTIKAL KAMERA'],
    'flash' => ['practical_invalid' => 'Sesi praktikal tidak sah atau tamat. Sila mulakan semula.', 'practical_unverified' => 'Sasaran praktikal yang dikeluarkan tidak dapat disahkan.', 'practical_save_failed' => 'Skor praktikal tidak dapat disimpan. Sila cuba lagi.', 'practical_review_expired' => 'Ulasan praktikal telah tamat. Mulakan cubaan baharu untuk melihat ulasan lain.'],
    'signs' => ['category' => ['alphabet' => 'Huruf', 'numbers' => 'Nombor']],
];

$approved = [
    'alphabet' => [
        'A' => ['Genggam empat jari; letak ibu jari di sisi dan jauh daripada hujung telunjuk.', 'Genggaman dengan ibu jari sisi'],
        'B' => ['Angkat empat jari bersama dan lipat ibu jari pada tapak.', 'Empat jari bersama'],
        'C' => ['Lengkungkan jari dan ibu jari sehingga ruang berbentuk C kekal terbuka.', 'Pastikan bukaan melengkung terlihat'],
        'D' => ['Angkat telunjuk; lengkungkan jari lain ke arah ibu jari.', 'Telunjuk ke atas dengan dasar bulat'],
        'E' => ['Bengkokkan jari ke arah tapak dan rapatkan ibu jari di bawahnya.', 'Tunjukkan bentuk bengkok yang padat'],
        'F' => ['Sentuhkan hujung telunjuk pada ibu jari dan angkat tiga jari lain.', 'Sentuhan kecil ibu jari–telunjuk'],
        'G' => ['Halakan telunjuk dan ibu jari ke sisi sambil jari lain dilipat.', 'Semak arah ke sisi'],
        'H' => ['Rentangkan telunjuk dan jari tengah bersama ke sisi; lipat jari lain.', 'Dua jari menghala ke sisi'],
        'I' => ['Angkat kelingking sahaja dan lipat ibu jari.', 'Kelingking sahaja'],
        'K' => ['Angkat dan jarakkan telunjuk dengan jari tengah; letakkan ibu jari di antaranya.', 'Ibu jari di antara dua jari'],
        'L' => ['Angkat telunjuk dan rentangkan ibu jari membentuk L.', 'Sudut tepat jelas'],
        'M' => ['Lipat tiga jari di atas ibu jari sehingga ibu jari terlihat di bawahnya.', 'Ibu jari di bawah tiga jari'],
        'N' => ['Lipat dua jari di atas ibu jari sehingga ibu jari terlihat di bawahnya.', 'Ibu jari di bawah dua jari'],
        'O' => ['Lengkungkan jari dan sentuhkan ibu jari pada telunjuk membentuk O.', 'Sentuhan ibu jari–telunjuk'],
        'P' => ['Gunakan bentuk tangan K dan condongkannya ke bawah.', 'Bentuk K menghadap bawah'],
        'Q' => ['Gunakan bentuk tangan G dan halakannya ke bawah.', 'Bentuk G menghadap bawah'],
        'R' => ['Angkat telunjuk dan jari tengah lalu silangkan satu di atas yang lain.', 'Kekalkan dua jari bersilang'],
        'S' => ['Genggam jari dan letakkan ibu jari melintang di bahagian hadapan.', 'Semak kedudukan ibu jari hadapan'],
        'T' => ['Genggam tangan dengan ibu jari di antara telunjuk dan jari tengah.', 'Ibu jari di antara dua jari'],
        'U' => ['Angkat telunjuk dan jari tengah rapat bersama.', 'Dua jari dirapatkan'],
        'V' => ['Angkat telunjuk dan jari tengah dengan jarak jelas.', 'Buka bentuk V'],
        'W' => ['Angkat telunjuk, jari tengah, dan jari manis.', 'Tiga jari terlihat'],
        'X' => ['Angkat telunjuk dengan sendi atas dibengkokkan seperti cangkuk; lipat jari lain.', 'Kekalkan telunjuk bercangkuk'],
        'Y' => ['Angkat kelingking dan rentangkan ibu jari.', 'Ibu jari dan kelingking'],
    ],
    'numbers' => [
        '0' => ['Lengkungkan jari dan sentuhkan ibu jari pada telunjuk membentuk sifar.', 'Sentuhan ibu jari–telunjuk'], '1' => ['Angkat telunjuk sahaja.', 'Telunjuk sahaja'],
        '2' => ['Angkat telunjuk dan jari tengah.', 'Dua jari diangkat'], '3' => ['Angkat telunjuk, jari tengah, dan jari manis tanpa sentuhan ibu jari–kelingking.', 'Tiga jari diangkat'],
        '4' => ['Angkat empat jari dan lipat ibu jari.', 'Ibu jari dilipat'], '5' => ['Angkat empat jari dan rentangkan ibu jari.', 'Tangan terbuka'],
        '6' => ['Angkat tiga jari dan sentuhkan ibu jari pada kelingking.', 'Sentuhan ibu jari–kelingking'], '7' => ['Angkat telunjuk, jari tengah, dan kelingking; sentuh ibu jari pada jari manis.', 'Sentuhan ibu jari–jari manis'],
        '8' => ['Angkat telunjuk, jari manis, dan kelingking; sentuh ibu jari pada jari tengah.', 'Sentuhan ibu jari–jari tengah'], '9' => ['Angkat jari tengah, jari manis, dan kelingking; sentuh ibu jari pada telunjuk.', 'Sentuhan ibu jari–telunjuk'],
    ],
];

foreach (signCatalog() as $sign) {
    $symbol = $sign['symbol']; $category = $sign['category'];
    $title = ($category === 'alphabet' ? 'Huruf ' : 'Nombor ') . $symbol;
    if (isset($approved[$category][$symbol])) [$description, $tip] = $approved[$category][$symbol];
    elseif ($sign['motion'] === 'dynamic') { $description = $copy['learn']['movement'][$symbol] . ' Ikut pergerakan dalam Bank Isyarat BIM MFD.'; $tip = 'Pergerakan untuk pembelajaran sahaja'; }
    else { $description = 'Gunakan satu tangan untuk membentuk ' . $title . '. Semak arah dan kedudukan jari tepat dalam Bank Isyarat BIM MFD.'; $tip = 'Bandingkan bentuk tangan halus'; }
    $copy['signs']['entries'][$category][$symbol] = ['title' => $title, 'description' => $description, 'tip' => $tip];
}
return $copy;
