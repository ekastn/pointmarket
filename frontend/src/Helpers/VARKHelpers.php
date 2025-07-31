<?php

namespace App\Helpers;

function getVARKLearningTips($dominantStyle) {
    $tips = [
        'Visual' => [
            'study_tips' => [
                'Gunakan diagram, chart, dan mind maps',
                'Highlight dengan warna-warna berbeda',
                'Buat flashcards dengan gambar',
                'Tonton video pembelajaran'
            ],
            'description' => 'Anda lebih mudah belajar melalui elemen visual seperti gambar, diagram, dan grafik.',
            'icon' => 'fas fa-eye'
        ],
        'Auditory' => [
            'study_tips' => [
                'Diskusikan materi dengan teman',
                'Rekam dan dengar kembali catatan',
                'Gunakan musik atau rhythm untuk mengingat',
                'Baca materi dengan suara keras'
            ],
            'description' => 'Anda lebih mudah belajar melalui mendengar dan berbicara.',
            'icon' => 'fas fa-volume-up'
        ],
        'Reading' => [
            'study_tips' => [
                'Buat catatan lengkap saat belajar',
                'Gunakan daftar dan bullet points',
                'Baca buku teks dan artikel',
                'Tulis ringkasan dengan kata-kata sendiri'
            ],
            'description' => 'Anda lebih mudah belajar melalui membaca dan menulis.',
            'icon' => 'fas fa-book-open'
        ],
        'Kinesthetic' => [
            'study_tips' => [
                'Praktikkan langsung apa yang dipelajari',
                'Gunakan model atau objek fisik',
                'Bergerak sambil belajar (walking study)',
                'Buat eksperimen dan simulasi'
            ],
            'description' => 'Anda lebih mudah belajar melalui pengalaman langsung dan praktik.',
            'icon' => 'fas fa-hand-rock'
        ],
        // Multi-modal styles (simplified descriptions)
        'Visual-Aural' => [
            'study_tips' => ['Gunakan kombinasi visual dan audio.'],
            'description' => 'Anda belajar terbaik dengan melihat dan mendengar.',
            'icon' => 'fas fa-eye-dropper'
        ],
        'Visual-Reading' => [
            'study_tips' => ['Kombinasikan visual dengan membaca/menulis.'],
            'description' => 'Anda belajar terbaik dengan melihat dan membaca/menulis.',
            'icon' => 'fas fa-book-reader'
        ],
        'Visual-Kinesthetic' => [
            'study_tips' => ['Gunakan visual dan praktik langsung.'],
            'description' => 'Anda belajar terbaik dengan melihat dan melakukan.',
            'icon' => 'fas fa-drafting-compass'
        ],
        'Aural-Reading' => [
            'study_tips' => ['Kombinasikan mendengar dengan membaca/menulis.'],
            'description' => 'Anda belajar terbaik dengan mendengar dan membaca/menulis.',
            'icon' => 'fas fa-file-audio'
        ],
        'Aural-Kinesthetic' => [
            'study_tips' => ['Kombinasikan mendengar dengan praktik langsung.'],
            'description' => 'Anda belajar terbaik dengan mendengar dan melakukan.',
            'icon' => 'fas fa-music'
        ],
        'Reading-Kinesthetic' => [
            'study_tips' => ['Kombinasikan membaca/menulis dengan praktik langsung.'],
            'description' => 'Anda belajar terbaik dengan membaca/menulis dan melakukan.',
            'icon' => 'fas fa-pen-ruler'
        ],
    ];
    return $tips[$dominantStyle] ?? ['study_tips' => ['Tidak ada tips spesifik.'], 'description' => 'Gaya belajar tidak teridentifikasi.', 'icon' => 'fas fa-question-circle'];
}
