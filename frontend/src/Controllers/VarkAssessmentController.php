<?php

namespace App\Controllers;

use App\Core\ApiClient;

class VarkAssessmentController extends BaseController
{
    public function __construct(ApiClient $apiClient)
    {
        parent::__construct($apiClient);
    }

    public function show(): void
    {
        session_start();
        $user = $_SESSION['user_data'] ?? null;

        // This should ideally be handled by AuthMiddleware, but as a fallback
        if (!$user) {
            $userProfileResponse = $this->apiClient->getUserProfile();
            if ($userProfileResponse['success']) {
                $user = $userProfileResponse['data'];
                $_SESSION['user_data'] = $user;
            } else {
                $_SESSION['messages'] = ['error' => $userProfileResponse['error'] ?? 'Gagal memuat profil pengguna.'];
                session_destroy();
                $this->redirect('/login');
                return;
            }
        }

        $varkQuestions = [];
        $existingResult = null;
        $show_result = false;
        $result_data = null;
        $messages = $_SESSION['messages'] ?? [];
        unset($_SESSION['messages']);

        // Check if the user explicitly wants to retake the assessment
        $retake = isset($_GET['retake']) && $_GET['retake'] === 'true';

        // If not retaking, try to get the latest VARK result first
        if (!$retake) {
            $latestResultResponse = $this->apiClient->getLatestVARKResult();
            if ($latestResultResponse['success'] && !empty($latestResultResponse['data'])) {
                $existingResult = $latestResultResponse['data'];
                $show_result = true;
                $result_data = [
                    'scores' => [
                        'Visual' => $existingResult['visual_score'],
                        'Auditory' => $existingResult['auditory_score'],
                        'Reading' => $existingResult['reading_score'],
                        'Kinesthetic' => $existingResult['kinesthetic_score'],
                    ],
                    'dominant_style' => $existingResult['dominant_style'],
                    'learning_preference' => $existingResult['learning_preference'],
                ];
            }
        }

        // If no existing result (or retake is true), fetch questions for a new assessment
        if (!$show_result || $retake) { // This condition ensures questions are fetched if no result or if retake is true
            $questionsResponse = $this->apiClient->getVARKAssessment();
            if ($questionsResponse['success']) {
                $varkQuestions = $questionsResponse['data']['questions'] ?? [];
            } else {
                $_SESSION['messages'] = ['error' => $questionsResponse['error'] ?? 'Failed to load VARK questions.'];
            }
        }

        $this->render('siswa/vark-assessment', [
            'title' => 'VARK Assessment',
            'user' => $user,
            'varkQuestions' => $varkQuestions,
            'existingResult' => $existingResult, // Still pass existing result for display if not retaking
            'messages' => $messages,
            'show_result' => $show_result && !$retake, // Only show result if not retaking
            'result_data' => $result_data,
        ]);
    }

    public function submit(): void
    {
        session_start();
        $user = $_SESSION['user_data'] ?? null;

        // This should ideally be handled by AuthMiddleware, but as a fallback
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        $this->apiClient->setJwtToken($_SESSION['jwt_token']);
        
        $answers = $_POST['answers'] ?? []; // Assuming answers are now passed as answers[question_id] = option_letter

        if (empty($answers) || count($answers) !== 16) { // Assuming 16 questions for VARK
            $_SESSION['messages'] = ['error' => 'Please answer all questions.'];
            $this->redirect('/vark-assessment');
            return;
        }

        $response = $this->apiClient->submitVARK($answers);

        if ($response['success']) {
            $_SESSION['messages'] = ['success' => 'VARK assessment completed successfully!'];
            // Store the result data in session to display immediately after redirect
            $_SESSION['vark_result_display'] = [
                'scores' => [
                    'Visual' => $response['data']['visual_score'],
                    'Auditory' => $response['data']['auditory_score'],
                    'Reading' => $response['data']['reading_score'],
                    'Kinesthetic' => $response['data']['kinesthetic_score'],
                ],
                'dominant_style' => $response['data']['dominant_style'], // Corrected to dominant_style
                'learning_preference' => $response['data']['learning_preference'], // Corrected to learning_preference
            ];
            $this->redirect('/vark-assessment');
        } else {
            $_SESSION['messages'] = ['error' => $response['error'] ?? 'Failed to submit VARK assessment.'];
            $this->redirect('/vark-assessment');
        }
    }
}
