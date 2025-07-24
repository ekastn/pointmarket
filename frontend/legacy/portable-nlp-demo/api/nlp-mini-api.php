<?php
/**
 * Minimal NLP API for Testing
 * 
 * This file provides a minimal implementation of the NLP API
 * that always returns valid JSON, regardless of session or other requirements.
 */

// Set content type
header('Content-Type: application/json');

// Enable CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Default mock data for analysis
$mockAnalysis = [
    'success' => true,
    'message' => 'Analysis completed successfully',
    'sentiment' => [
        'score' => 0.75,
        'label' => 'Positive'
    ],
    'complexity' => [
        'score' => 0.65,
        'label' => 'Medium-High'
    ],
    'coherence' => [
        'score' => 0.82,
        'label' => 'High'
    ],
    'keywords' => [
        'teknologi', 'pendidikan', 'pembelajaran', 'inovasi', 'digital'
    ],
    'keySentences' => [
        'Teknologi dalam pendidikan memainkan peran yang sangat penting dalam meningkatkan kualitas pembelajaran.',
        'Aplikasi pembelajaran interaktif memungkinkan siswa untuk belajar dengan cara yang lebih menarik dan efektif.'
    ],
    'stats' => [
        'wordCount' => 85,
        'sentenceCount' => 5,
        'avgWordLength' => 6.2,
        'readingTime' => 30
    ]
];

// Default mock data for stats
$mockStats = [
    'success' => true,
    'data' => [
        'overall' => [
            'total_analyses' => 15,
            'average_score' => 75.5,
            'best_score' => 92.0,
            'avg_grammar' => 79.2
        ],
        'recent' => [
            [
                'date' => date('Y-m-d H:i:s', strtotime('-2 hours')),
                'score' => 82.5,
                'context' => 'assignment'
            ],
            [
                'date' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'score' => 76.0,
                'context' => 'quiz'
            ]
        ],
        'note' => 'Data ini adalah contoh untuk keperluan debugging.'
    ]
];

// Handle request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get input data
    $input = json_decode(file_get_contents('php://input'), true);
    $text = $input['text'] ?? 'No text provided';
    
    // Return mock analysis with the actual text
    $response = $mockAnalysis;
    $response['text'] = $text;
    $response['timestamp'] = date('Y-m-d H:i:s');
    
    echo json_encode($response);
} else {
    // GET requests
    $action = $_GET['action'] ?? 'test';
    
    switch ($action) {
        case 'statistics':
            echo json_encode($mockStats);
            break;
        case 'test':
        default:
            echo json_encode([
                'success' => true,
                'message' => 'API is working properly',
                'timestamp' => date('Y-m-d H:i:s'),
                'endpoint' => 'nlp-mini-api.php'
            ]);
            break;
    }
}
?>
