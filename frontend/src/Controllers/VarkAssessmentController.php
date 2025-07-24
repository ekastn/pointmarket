<?php

namespace App\Controllers;

use App\Services\ApiClient;

class VarkAssessmentController extends BaseController
{
    public function __construct(ApiClient $apiClient)
    {
        parent::__construct($apiClient);
    }

    public function show(): void
    {
        session_start();
        $jwt = $_SESSION['jwt_token'] ?? null;

        if (!$jwt) {
            $this->redirect('/login');
            return;
        }

        $this->apiClient->setJwtToken($jwt);
        $userProfileResponse = $this->apiClient->getUserProfile();

        if (!$userProfileResponse['success']) {
            session_destroy();
            $this->redirect('/login');
            return;
        }

        $user = $userProfileResponse['data'];

        $varkQuestions = [];
        $existingResult = null;
        $show_result = false;
        $result_data = null;
        $messages = $_SESSION['messages'] ?? [];
        unset($_SESSION['messages']);

        // Try to get the latest VARK result first
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
                'dominant_style' => $existingResult['dominant_style'], // Corrected to dominant_style
                'learning_preference' => $existingResult['learning_preference'], // Corrected to learning_preference
            ];
        } else {
            // If no existing result, fetch questions for a new assessment
            $questionsResponse = $this->apiClient->getVARKAssessment();
            if ($questionsResponse['success']) {
                $varkQuestions = $questionsResponse['data']['questions'] ?? []; // Access 'questions' key
            } else {
                $_SESSION['messages'] = ['error' => $questionsResponse['error'] ?? 'Failed to load VARK questions.'];
            }
        }

        $this->render('vark-assessment', [
            'title' => 'VARK Assessment',
            'user' => $user,
            'varkQuestions' => $varkQuestions,
            'existingResult' => $existingResult,
            'messages' => $messages,
            'show_result' => $show_result,
            'result_data' => $result_data,
        ]);
    }

    public function submit(): void
    {
        session_start();
        $jwt = $_SESSION['jwt_token'] ?? null;

        if (!$jwt) {
            $this->redirect('/login');
            return;
        }

        $this->apiClient->setJwtToken($jwt);
        $userProfileResponse = $this->apiClient->getUserProfile();

        if (!$userProfileResponse['success']) {
            session_destroy();
            $this->redirect('/login');
            return;
        }

        $user = $userProfileResponse['data'];
        
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
