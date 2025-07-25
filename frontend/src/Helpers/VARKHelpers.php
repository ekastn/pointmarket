<?php

namespace App\Helpers;

class VARKHelpers
{
    public static function getVARKLearningTips($dominantStyle) {
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
            'Visual/Auditory' => [
                'study_tips' => ['Gunakan kombinasi visual dan audio.'],
                'description' => 'Anda belajar terbaik dengan melihat dan mendengar.',
                'icon' => 'fas fa-eye fas fa-volume-up'
            ],
            'Visual/Reading' => [
                'study_tips' => ['Kombinasikan visual dengan membaca/menulis.'],
                'description' => 'Anda belajar terbaik dengan melihat dan membaca/menulis.',
                'icon' => 'fas fa-eye fas fa-book-open'
            ],
            'Visual/Kinesthetic' => [
                'study_tips' => ['Gunakan visual dan praktik langsung.'],
                'description' => 'Anda belajar terbaik dengan melihat dan melakukan.',
                'icon' => 'fas fa-eye fas fa-hand-rock'
            ],
            'Auditory/Reading' => [
                'study_tips' => ['Kombinasikan mendengar dengan membaca/menulis.'],
                'description' => 'Anda belajar terbaik dengan mendengar dan membaca/menulis.',
                'icon' => 'fas fa-volume-up fas fa-book-open'
            ],
            'Auditory/Kinesthetic' => [
                'study_tips' => ['Kombinasikan mendengar dengan praktik langsung.'],
                'description' => 'Anda belajar terbaik dengan mendengar dan melakukan.',
                'icon' => 'fas fa-volume-up fas fa-hand-rock'
            ],
            'Reading/Kinesthetic' => [
                'study_tips' => ['Kombinasikan membaca/menulis dengan praktik langsung.'],
                'description' => 'Anda belajar terbaik dengan membaca/menulis dan melakukan.',
                'icon' => 'fas fa-book-open fas fa-hand-rock'
            ],
            'Visual/Auditory/Reading' => [
                'study_tips' => ['Gunakan visual, audio, dan membaca/menulis.'],
                'description' => 'Anda adalah pembelajar multimodal yang kuat dalam melihat, mendengar, dan membaca/menulis.',
                'icon' => 'fas fa-eye fas fa-volume-up fas fa-book-open'
            ],
            'Visual/Auditory/Kinesthetic' => [
                'study_tips' => ['Gunakan visual, audio, dan praktik langsung.'],
                'description' => 'Anda adalah pembelajar multimodal yang kuat dalam melihat, mendengar, dan melakukan.',
                'icon' => 'fas fa-eye fas fa-volume-up fas fa-hand-rock'
            ],
            'Visual/Reading/Kinesthetic' => [
                'study_tips' => ['Gunakan visual, membaca/menulis, dan praktik langsung.'],
                'description' => 'Anda adalah pembelajar multimodal yang kuat dalam melihat, membaca/menulis, dan melakukan.',
                'icon' => 'fas fa-eye fas fa-book-open fas fa-hand-rock'
            ],
            'Auditory/Reading/Kinesthetic' => [
                'study_tips' => ['Gunakan audio, membaca/menulis, dan praktik langsung.'],
                'description' => 'Anda adalah pembelajar multimodal yang kuat dalam mendengar, membaca/menulis, dan melakukan.',
                'icon' => 'fas fa-volume-up fas fa-book-open fas fa-hand-rock'
            ],
            'Visual/Auditory/Reading/Kinesthetic' => [
                'study_tips' => ['Gunakan semua modalitas pembelajaran.'],
                'description' => 'Anda adalah pembelajar multimodal yang efektif dengan semua gaya belajar.',
                'icon' => 'fas fa-brain'
            ],
        ];
        return $tips[$dominantStyle] ?? ['study_tips' => ['Tidak ada tips spesifik.'], 'description' => 'Gaya belajar tidak teridentifikasi.', 'icon' => 'fas fa-question-circle'];
    }
}
