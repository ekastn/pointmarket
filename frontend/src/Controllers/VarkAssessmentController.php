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

        // In a real application, you would fetch these from the Go backend
        $varkQuestions = []; // Placeholder
        $existingResult = null; // Placeholder
        $messages = $_SESSION['messages'] ?? [];
        unset($_SESSION['messages']);

        $this->render('vark-assessment', [
            'title' => 'VARK Assessment',
            'user' => $user,
            'varkQuestions' => $varkQuestions,
            'existingResult' => $existingResult,
            'messages' => $messages,
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
        
        $answers = [];
        for ($i = 1; $i <= 16; $i++) {
            if (isset($_POST["question_$i"])) {
                $answers[$i] = $_POST["question_$i"];
            }
        }

        if (count($answers) == 16) {
            $response = $this->apiClient->submitVARK($answers);

            if ($response['success']) {
                $_SESSION['messages'] = ['success' => 'VARK assessment completed successfully!'];
                // Redirect to a results page or back to the assessment page to show results
                $this->redirect('/vark-assessment');
            } else {
                $_SESSION['messages'] = ['error' => $response['error']];
                $this->redirect('/vark-assessment');
            }
        } else {
            $_SESSION['messages'] = ['error' => 'Please answer all questions.'];
            $this->redirect('/vark-assessment');
        }
    }
}
