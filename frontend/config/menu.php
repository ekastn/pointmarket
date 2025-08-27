<?php

// Centralized menu configuration per role
// Each role maps to an array of sections, and each section has optional label and an items array
// Each item: ['path' => '/route', 'label' => 'Label', 'icon' => 'fas fa-icon']

return [
    'siswa' => [
        [
            'label' => null,
            'items' => [
                ['path' => '/dashboard', 'label' => 'Dashboard', 'icon' => 'fas fa-tachometer-alt'],
            ],
        ],
        [
            'label' => null,
            'items' => [
                ['path' => '/my-missions', 'label' => 'Misi Saya', 'icon' => 'fas fa-trophy'],
                ['path' => '/my-courses', 'label' => 'Kursus Saya', 'icon' => 'fas fa-book'],
                ['path' => '/my-badges', 'label' => 'Lencana Saya', 'icon' => 'fas fa-id-badge'],
                ['path' => '/products', 'label' => 'Marketplace', 'icon' => 'fas fa-store'],
                ['path' => '/assignments', 'label' => 'Tugas', 'icon' => 'fas fa-tasks'],
                ['path' => '/quiz', 'label' => 'Kuis', 'icon' => 'fas fa-question-circle'],
                ['path' => '/questionnaires', 'label' => 'Kuesioner', 'icon' => 'fas fa-clipboard-list'],
                ['path' => '/vark-correlation-analysis', 'label' => 'Analisis Korelasi VARK', 'icon' => 'fas fa-chart-pie'],
                ['path' => '/weekly-evaluations', 'label' => 'Evaluasi Mingguan', 'icon' => 'fas fa-calendar-check'],
            ],
        ],
        [
            'label' => 'Fitur AI',
            'items' => [
                ['path' => '/ai-explanation', 'label' => 'Cara Kerja AI', 'icon' => 'fas fa-graduation-cap'],
                ['path' => '/ai-recommendations', 'label' => 'Rekomendasi AI', 'icon' => 'fas fa-robot'],
                ['path' => '/nlp-demo', 'label' => 'Demo NLP', 'icon' => 'fas fa-brain'],
            ],
        ],
        [
            'label' => 'Support',
            'items' => [
                ['path' => '/help', 'label' => 'Bantuan', 'icon' => 'fas fa-info-circle'],
            ],
        ],
    ],
    'guru' => [
        [
            'label' => null,
            'items' => [
                ['path' => '/dashboard', 'label' => 'Dashboard', 'icon' => 'fas fa-tachometer-alt'],
            ],
        ],
        [
            'label' => null,
            'items' => [
                ['path' => '/missions', 'label' => 'Misi', 'icon' => 'fas fa-trophy'],
                ['path' => '/courses', 'label' => 'Kursus', 'icon' => 'fas fa-book-open'],
                ['path' => '/assignments', 'label' => 'Tugas', 'icon' => 'fas fa-tasks'],
                ['path' => '/teacher-evaluation-monitoring', 'label' => 'Monitoring Evaluasi', 'icon' => 'fas fa-chart-line'],
            ],
        ],
        [
            'label' => 'Fitur AI',
            'items' => [
                ['path' => '/ai-explanation', 'label' => 'Cara Kerja AI', 'icon' => 'fas fa-graduation-cap'],
                ['path' => '/ai-recommendations', 'label' => 'Rekomendasi AI', 'icon' => 'fas fa-robot'],
                ['path' => '/nlp-demo', 'label' => 'Demo NLP', 'icon' => 'fas fa-brain'],
            ],
        ],
        [
            'label' => 'Support',
            'items' => [
                ['path' => '/help', 'label' => 'Bantuan', 'icon' => 'fas fa-info-circle'],
            ],
        ],
    ],
    'admin' => [
        [
            'label' => null,
            'items' => [
                ['path' => '/dashboard', 'label' => 'Dashboard', 'icon' => 'fas fa-tachometer-alt'],
            ],
        ],
        [
            'label' => null,
            'items' => [
                ['path' => '/users', 'label' => 'Pengguna', 'icon' => 'fas fa-users-cog'],
                ['path' => '/students', 'label' => 'Siswa', 'icon' => 'fas fa-user-graduate'],
                ['path' => '/missions', 'label' => 'Misi', 'icon' => 'fas fa-trophy'],
                ['path' => '/courses', 'label' => 'Kursus', 'icon' => 'fas fa-book-open'],
                ['path' => '/badges', 'label' => 'Lencana', 'icon' => 'fas fa-id-badge'],
                ['path' => '/products', 'label' => 'Kelola Produk', 'icon' => 'fas fa-box-open'],
                ['path' => '/product-categories', 'label' => 'Kelola Kategori Produk', 'icon' => 'fas fa-tags'],
                ['path' => '/reports', 'label' => 'Laporan', 'icon' => 'fas fa-chart-bar'],
                ['path' => '/questionnaires', 'label' => 'Kuesioner', 'icon' => 'fas fa-clipboard-list'],
            ],
        ],
        [
            'label' => 'Fitur AI',
            'items' => [
                ['path' => '/ai-explanation', 'label' => 'Cara Kerja AI', 'icon' => 'fas fa-graduation-cap'],
                ['path' => '/ai-recommendations', 'label' => 'Rekomendasi AI', 'icon' => 'fas fa-robot'],
                ['path' => '/nlp-demo', 'label' => 'Demo NLP', 'icon' => 'fas fa-brain'],
            ],
        ],
        [
            'label' => 'Support',
            'items' => [
                ['path' => '/help', 'label' => 'Bantuan', 'icon' => 'fas fa-info-circle'],
            ],
        ],
    ],
];
