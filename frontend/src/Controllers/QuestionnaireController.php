<?php

namespace App\Controllers;

use App\Core\ApiClient;
use App\Services\QuestionnaireService;

class QuestionnaireController extends BaseController
{
    protected QuestionnaireService $questionnaireService;

    public function __construct(ApiClient $apiClient, QuestionnaireService $questionnaireService)
    {
        parent::__construct($apiClient);
        $this->questionnaireService = $questionnaireService;
    }

    public function index(): void
    {
        session_start();
        $user = $_SESSION['user_data'] ?? null;

        $questionnaires = [];
        $history = [];
        $stats = [];
        $varkResult = null;
        $pendingEvaluations = [];
        $messages = $_SESSION['messages'] ?? [];
        unset($_SESSION['messages']);

        // Fetch all questionnaires
        $questionnaires = $this->questionnaireService->getAllQuestionnaires();
        if ($questionnaires === null) {
            $_SESSION['messages']['error'] = 'Failed to fetch questionnaires.';
            $questionnaires = [];
        }

        // Fetch questionnaire history
        $history = $this->questionnaireService->getQuestionnaireHistory();
        if ($history === null) {
            $_SESSION['messages']['error'] = 'Failed to fetch questionnaire history.';
            $history = [];
        }

        // Fetch questionnaire stats
        $stats = $this->questionnaireService->getQuestionnaireStats();
        if ($stats === null) {
            $_SESSION['messages']['error'] = 'Failed to fetch questionnaire statistics.';
            $stats = [];
        }

        // Fetch VARK result
        $varkResult = $this->questionnaireService->getLatestVARKResult();
        if ($varkResult === null) {
            // Error already handled by service, just ensure it's null
            $varkResult = null;
        }

        $this->render('siswa/questionnaire', [
            'title' => 'Questionnaires',
            'user' => $user,
            'questionnaires' => $questionnaires,
            'history' => $history,
            'stats' => $stats,
            'pendingEvaluations' => $pendingEvaluations,
            'varkResult' => $varkResult,
            'messages' => $messages,
        ]);
    }

    public function startQuestionnairePage(int $id): void
    {
        session_start();
        $user = $_SESSION['user_data'] ?? null;
        $messages = $_SESSION['messages'] ?? [];
        unset($_SESSION['messages']);

        $questionnaireData = $this->questionnaireService->getQuestionnaire($id);

        if (! $questionnaireData) {
            $_SESSION['messages']['error'] = 'Questionnaire not found or failed to load.';
            header('Location: /questionnaires');
            exit();
        }

        $questionnaire = $questionnaireData['questionnaire'];
        $questions = $questionnaireData['questions'];

        $weeklyEvaluationId = $_GET['weekly_evaluation_id'] ?? null;

        if ($questionnaire['type'] === 'VARK') {
            $this->render('siswa/questionnaire-vark', [
                'title' => $questionnaire['name'],
                'user' => $user,
                'questionnaire' => $questionnaire,
                'questions' => $questions,
                'messages' => $messages,
            ]);
        } else { // Assuming MSLQ, AMS are Likert types
            $this->render('siswa/questionnaire-likert', [
                'title' => $questionnaire['name'],
                'user' => $user,
                'questionnaire' => $questionnaire,
                'questions' => $questions,
                'messages' => $messages,
                'weeklyEvaluationId' => $weeklyEvaluationId,
            ]);
        }
    }

    public function submitLikertQuestionnaire(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);

        $questionnaireId = $input['questionnaire_id'] ?? null;
        $weeklyEvaluationId = $input['weekly_evaluation_id'] ?? null;
        $answers = $input['answers'] ?? [];
        $weekNumber = date('W'); // Current week number
        $year = date('Y'); // Current year

        if (! $questionnaireId || empty($answers)) {
            echo json_encode(['success' => false, 'message' => 'Invalid submission data.']);
            exit();
        }

        $result = $this->questionnaireService->submitQuestionnaire($questionnaireId, $answers, $weekNumber, $year, $weeklyEvaluationId);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Questionnaire submitted successfully!', 'data' => $result]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to submit questionnaire. Please try again.']);
        }
    }

    public function submitVARKQuestionnaire(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);

        $questionnaireId = $input['questionnaire_id'] ?? null;
        $answers = $input['answers'] ?? [];
        $nlpText = $input['text'] ?? '';

        if (! $questionnaireId || empty($answers) || empty($nlpText)) {
            echo json_encode(['success' => false, 'message' => 'Invalid submission data for VARK/NLP.']);
            exit();
        }

        if (str_word_count($nlpText) < 100) {
            echo json_encode(['success' => false, 'message' => 'NLP text must be at least 100 words.']);
            exit();
        }

        $result = $this->questionnaireService->submitVARK($questionnaireId, $answers, $nlpText);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'VARK/NLP analysis submitted successfully!', 'data' => $result]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to submit VARK/NLP analysis. Please try again.']);
        }
    }
}
