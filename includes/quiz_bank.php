<?php

declare(strict_types=1);

function quizThemes(): array
{
    return [
        'numbers' => [
            'title' => 'Isyarat Nombor',
            'short_title' => 'Nombor',
            'description' => 'Uji bentuk tangan dan perbezaan nombor 1 hingga 5.',
            'icon' => 'bi-123',
            'accent' => 'teal',
            'duration' => '60 saat',
        ],
        'alphabet' => [
            'title' => 'Isyarat Abjad',
            'short_title' => 'Abjad',
            'description' => 'Kenali bentuk tangan dan asas ejaan jari BIM.',
            'icon' => 'bi-alphabet-uppercase',
            'accent' => 'blue',
            'duration' => '60 saat',
        ],
    ];
}

function quizQuestionBank(): array
{
    return [
        'numbers' => [
            ['id' => 'num-01', 'question' => 'Jari manakah perlu diluruskan untuk membentuk nombor 1?', 'options' => ['A' => 'Jari telunjuk sahaja', 'B' => 'Ibu jari sahaja', 'C' => 'Jari tengah sahaja', 'D' => 'Semua jari'], 'correct' => 'A', 'explanation' => 'Nombor 1 menggunakan jari telunjuk yang tegak sementara jari lain digenggam.'],
            ['id' => 'num-02', 'question' => 'Apakah gabungan jari untuk nombor 2 dalam latihan ini?', 'options' => ['A' => 'Ibu jari dan kelingking', 'B' => 'Telunjuk dan jari tengah', 'C' => 'Jari manis dan kelingking', 'D' => 'Telunjuk dan ibu jari'], 'correct' => 'B', 'explanation' => 'Nombor 2 dibentuk dengan meluruskan telunjuk dan jari tengah.'],
            ['id' => 'num-03', 'question' => 'Sistem mengesan empat jari lurus dan ibu jari dilipat. Apakah nombornya?', 'options' => ['A' => '2', 'B' => '3', 'C' => '4', 'D' => '5'], 'correct' => 'C', 'explanation' => 'Empat jari selain ibu jari yang terbuka mewakili nombor 4.'],
            ['id' => 'num-04', 'question' => 'Apakah perbezaan utama antara nombor 4 dan nombor 5?', 'options' => ['A' => 'Arah telunjuk', 'B' => 'Kedudukan ibu jari', 'C' => 'Kedudukan pergelangan', 'D' => 'Tiada perbezaan'], 'correct' => 'B', 'explanation' => 'Nombor 5 membuka ibu jari bersama empat jari lain, manakala nombor 4 melipat ibu jari.'],
            ['id' => 'num-05', 'question' => 'Berapa banyak jari bukan ibu jari yang lurus untuk nombor 3?', 'options' => ['A' => 'Satu', 'B' => 'Dua', 'C' => 'Tiga', 'D' => 'Empat'], 'correct' => 'C', 'explanation' => 'Latihan nombor 3 menggunakan telunjuk, jari tengah, dan jari manis.'],
            ['id' => 'num-06', 'question' => 'Mengapakah tapak tangan perlu jelas menghadap kamera?', 'options' => ['A' => 'Untuk menambah markah', 'B' => 'Supaya titik sendi lebih mudah dikesan', 'C' => 'Untuk menukar nombor', 'D' => 'Supaya video disimpan'], 'correct' => 'B', 'explanation' => 'Kedudukan yang jelas membantu MediaPipe melihat sendi dan hujung jari dengan stabil.'],
            ['id' => 'num-07', 'question' => 'Jika telunjuk dan jari tengah lurus tetapi jari lain dilipat, sistem patut membaca…', 'options' => ['A' => '1', 'B' => '2', 'C' => '3', 'D' => '5'], 'correct' => 'B', 'explanation' => 'Corak dua jari tersebut ialah sasaran nombor 2.'],
            ['id' => 'num-08', 'question' => 'Apakah tindakan terbaik jika bacaan nombor berubah-ubah?', 'options' => ['A' => 'Gerakkan tangan lebih laju', 'B' => 'Tutup kamera', 'C' => 'Stabilkan tangan dalam bingkai', 'D' => 'Gunakan dua tangan'], 'correct' => 'C', 'explanation' => 'Tangan yang stabil dan terang menghasilkan bacaan titik tangan yang lebih konsisten.'],
        ],
        'alphabet' => [
            ['id' => 'alpha-01', 'question' => 'Bagaimanakah bentuk asas huruf A dalam modul ini?', 'options' => ['A' => 'Tangan terbuka', 'B' => 'Genggaman dengan ibu jari di sisi', 'C' => 'Dua jari lurus', 'D' => 'Telunjuk menunjuk ke bawah'], 'correct' => 'B', 'explanation' => 'Huruf A menggunakan genggaman yang kemas dengan ibu jari berada di sisi.'],
            ['id' => 'alpha-02', 'question' => 'Apakah nama kaedah mengeja perkataan menggunakan bentuk tangan huruf demi huruf?', 'options' => ['A' => 'Ejaan jari', 'B' => 'Bacaan bibir', 'C' => 'Kod nombor', 'D' => 'Gerakan bebas'], 'correct' => 'A', 'explanation' => 'Ejaan jari menggunakan bentuk abjad tangan untuk membina nama atau perkataan.'],
            ['id' => 'alpha-03', 'question' => 'Semasa mempelajari abjad statik, apakah yang paling penting?', 'options' => ['A' => 'Kelajuan tangan', 'B' => 'Bentuk dan kedudukan jari', 'C' => 'Warna pakaian', 'D' => 'Jarak dari papan kekunci'], 'correct' => 'B', 'explanation' => 'Isyarat statik dibezakan terutama melalui bentuk tangan, jari, dan orientasi tapak.'],
            ['id' => 'alpha-04', 'question' => 'Mengapakah huruf yang kelihatan hampir sama perlu dilatih secara berasingan?', 'options' => ['A' => 'Untuk memanjangkan kuiz', 'B' => 'Perbezaan kecil jari boleh mengubah huruf', 'C' => 'Semua huruf adalah sama', 'D' => 'Kamera memerlukan warna berbeza'], 'correct' => 'B', 'explanation' => 'Satu jari yang terbuka atau terlipat boleh menghasilkan huruf yang berbeza.'],
            ['id' => 'alpha-05', 'question' => 'Apakah latar terbaik untuk latihan ejaan jari dengan kamera?', 'options' => ['A' => 'Latar sibuk dan gelap', 'B' => 'Latar ringkas dengan cahaya hadapan', 'C' => 'Cahaya dari belakang', 'D' => 'Bilik tanpa cahaya'], 'correct' => 'B', 'explanation' => 'Latar ringkas dan cahaya hadapan memudahkan kamera membezakan tangan.'],
            ['id' => 'alpha-06', 'question' => 'Apakah langkah pertama apabila bentuk huruf tidak dikenali?', 'options' => ['A' => 'Semak kedudukan setiap jari', 'B' => 'Tukar akaun', 'C' => 'Tambah masa kuiz', 'D' => 'Gunakan tiga tangan'], 'correct' => 'A', 'explanation' => 'Bandingkan bentuk tangan dengan panduan dan betulkan jari satu demi satu.'],
            ['id' => 'alpha-07', 'question' => 'Huruf A dan nombor yang menggunakan jari terbuka dibezakan terutamanya oleh…', 'options' => ['A' => 'Bentuk bukaan jari', 'B' => 'Nama pengguna', 'C' => 'Saiz skrin', 'D' => 'Masa log masuk'], 'correct' => 'A', 'explanation' => 'Huruf A ialah genggaman, manakala nombor menggunakan corak jari lurus tertentu.'],
            ['id' => 'alpha-08', 'question' => 'Mengapa perlu menahan bentuk huruf seketika semasa latihan AI?', 'options' => ['A' => 'Supaya sistem mendapat beberapa bacaan stabil', 'B' => 'Supaya kamera mengambil gambar', 'C' => 'Untuk menghentikan masa', 'D' => 'Untuk menyimpan video'], 'correct' => 'A', 'explanation' => 'Beberapa bacaan yang konsisten mengurangkan keputusan yang berkelip atau tersalah.'],
        ],
    ];
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
