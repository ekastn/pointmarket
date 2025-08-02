<?php
// Database configuration
class Database {
    private $host = "127.0.0.1";
    private $db_name = "pointmarket";
    private $username = "lab";
    private $password = "password";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password,
                array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            die("Connection error: " . $exception->getMessage());
        }
        return $this->conn;
    }
}

// Session management
function startSession() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

// Check if user is logged in
function isLoggedIn() {
    startSession();
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Get current user
function getCurrentUser() {
    startSession();
    if (isLoggedIn()) {
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'name' => $_SESSION['name'],
            'email' => $_SESSION['email'],
            'role' => $_SESSION['role']
        ];
    }
    return null;
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

// Redirect if not specific role
function requireRole($role) {
    requireLogin();
    $user = getCurrentUser();
    if ($user['role'] !== $role) {
        header("Location: dashboard.php");
        exit();
    }
}

// Sanitize input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Format date
function formatDate($date) {
    return date('d/m/Y H:i', strtotime($date));
}

// Format points
function formatPoints($points) {
    return number_format($points, 0, ',', '.');
}

// Generate CSRF token
function generateCSRFToken() {
    startSession();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verifyCSRFToken($token) {
    startSession();
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// JSON response helper
function jsonResponse($data, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

// Success message
function setSuccessMessage($message) {
    startSession();
    $_SESSION['success_message'] = $message;
}

// Error message
function setErrorMessage($message) {
    startSession();
    $_SESSION['error_message'] = $message;
}

// Get and clear messages
function getMessages() {
    startSession();
    $messages = [];
    
    if (isset($_SESSION['success_message'])) {
        $messages['success'] = $_SESSION['success_message'];
        unset($_SESSION['success_message']);
    }
    
    if (isset($_SESSION['error_message'])) {
        $messages['error'] = $_SESSION['error_message'];
        unset($_SESSION['error_message']);
    }
    
    return $messages;
}

// Password hashing
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Password verification
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Calculate student statistics
function getStudentStats($student_id, $pdo) {
    try {
        $stmt = $pdo->prepare("SELECT total_points, completed_assignments FROM student_stats WHERE student_id = ?");
        $stmt->execute([$student_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'total_points' => $result ? $result['total_points'] : 0,
            'completed_assignments' => $result ? $result['completed_assignments'] : 0
        ];
    } catch (Exception $e) {
        error_log("Error getting student stats: " . $e->getMessage());
        return [
            'total_points' => 0,
            'completed_assignments' => 0
        ];
    }
}

// Get questionnaire scores
function getQuestionnaireScores($student_id, $pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT q.type, qr.total_score 
            FROM questionnaire_results qr 
            JOIN questionnaires q ON qr.questionnaire_id = q.id 
            WHERE qr.student_id = ? 
            ORDER BY qr.completed_at DESC
        ");
        $stmt->execute([$student_id]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $scores = ['mslq' => null, 'ams' => null];
        foreach ($results as $result) {
            if ($scores[$result['type']] === null) {
                $scores[$result['type']] = $result['total_score'];
            }
        }
        
        return $scores;
    } catch (Exception $e) {
        error_log("Error getting questionnaire scores: " . $e->getMessage());
        return ['mslq' => null, 'ams' => null];
    }
}

// Log activity
function logActivity($user_id, $action, $description, $pdo) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO activity_log (user_id, action, description, created_at) 
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$user_id, $action, $description]);
    } catch (Exception $e) {
        error_log("Error logging activity: " . $e->getMessage());
    }
}

// Weekly evaluation functions
function getCurrentWeekNumber() {
    return date('W');
}

function getCurrentYear() {
    return date('Y');
}

// Check if student has pending weekly evaluations
function hasPendingWeeklyEvaluations($student_id, $pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as pending_count 
            FROM weekly_evaluations 
            WHERE student_id = ? AND status = 'pending' AND due_date <= CURDATE()
        ");
        $stmt->execute([$student_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['pending_count'] > 0;
    } catch (Exception $e) {
        error_log("Error checking pending evaluations: " . $e->getMessage());
        return false;
    }
}

// Get pending weekly evaluations for student
function getPendingWeeklyEvaluations($student_id, $pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT we.*, q.name as questionnaire_name, q.type as questionnaire_type
            FROM weekly_evaluations we
            JOIN questionnaires q ON we.questionnaire_id = q.id
            WHERE we.student_id = ? AND we.status = 'pending' AND we.due_date <= CURDATE()
            AND q.type != 'vark'
            ORDER BY we.due_date ASC
        ");
        $stmt->execute([$student_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error getting pending evaluations: " . $e->getMessage());
        return [];
    }
}

// Generate weekly evaluations for all students
function generateWeeklyEvaluations($pdo) {
    try {
        $current_week = getCurrentWeekNumber();
        $current_year = getCurrentYear();
        
        // Get all active students
        $stmt = $pdo->query("SELECT id FROM users WHERE role = 'siswa'");
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get all active questionnaires (exclude VARK)
        $stmt = $pdo->query("SELECT id FROM questionnaires WHERE status = 'active' AND type != 'vark'");
        $questionnaires = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $insertStmt = $pdo->prepare("
            INSERT IGNORE INTO weekly_evaluations 
            (student_id, questionnaire_id, week_number, year, due_date) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        foreach ($students as $student) {
            foreach ($questionnaires as $questionnaire) {
                // Calculate due date (end of current week - Sunday)
                $due_date = date('Y-m-d', strtotime('next sunday'));
                
                $insertStmt->execute([
                    $student['id'],
                    $questionnaire['id'],
                    $current_week,
                    $current_year,
                    $due_date
                ]);
            }
        }
        
        return true;
    } catch (Exception $e) {
        error_log("Error generating weekly evaluations: " . $e->getMessage());
        return false;
    }
}

// Mark weekly evaluation as completed
function completeWeeklyEvaluation($student_id, $questionnaire_id, $week_number, $year, $pdo) {
    try {
        $stmt = $pdo->prepare("
            UPDATE weekly_evaluations 
            SET status = 'completed', completed_at = NOW() 
            WHERE student_id = ? AND questionnaire_id = ? AND week_number = ? AND year = ?
        ");
        $stmt->execute([$student_id, $questionnaire_id, $week_number, $year]);
        return true;
    } catch (Exception $e) {
        error_log("Error completing weekly evaluation: " . $e->getMessage());
        return false;
    }
}

// Get weekly evaluation progress for student
function getWeeklyEvaluationProgress($student_id, $pdo, $weeks = 8) {
    try {
        $current_week = getCurrentWeekNumber();
        $current_year = getCurrentYear();
        
        $stmt = $pdo->prepare("
            SELECT 
                we.week_number,
                we.year,
                q.type as questionnaire_type,
                q.name as questionnaire_name,
                we.status,
                we.due_date,
                we.completed_at,
                qr.total_score
            FROM weekly_evaluations we
            JOIN questionnaires q ON we.questionnaire_id = q.id
            LEFT JOIN questionnaire_results qr ON (
                qr.student_id = we.student_id 
                AND qr.questionnaire_id = we.questionnaire_id 
                AND qr.week_number = we.week_number 
                AND qr.year = we.year
            )
            WHERE we.student_id = ? 
            AND q.type != 'vark'
            AND ((we.year = ? AND we.week_number >= ?) OR we.year > ?)
            ORDER BY we.year DESC, we.week_number DESC, q.type
            LIMIT ?
        ");
        
        $start_week = max(1, $current_week - $weeks + 1);
        $limit = $weeks * 2; // 2 questionnaires per week
        
        $stmt->execute([$student_id, $current_year, $start_week, $current_year, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error getting weekly evaluation progress: " . $e->getMessage());
        return [];
    }
}

// Update overdue evaluations
function updateOverdueEvaluations($pdo) {
    try {
        $stmt = $pdo->prepare("
            UPDATE weekly_evaluations 
            SET status = 'overdue' 
            WHERE status = 'pending' AND due_date < CURDATE()
        ");
        $stmt->execute();
        return true;
    } catch (Exception $e) {
        error_log("Error updating overdue evaluations: " . $e->getMessage());
        return false;
    }
}

// VARK Learning Style Functions
function getVARKQuestions($pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT qq.id, qq.question_number, qq.question_text, qq.subscale,
                   vo.option_letter, vo.option_text, vo.learning_style
            FROM questionnaire_questions qq
            LEFT JOIN vark_answer_options vo ON qq.id = vo.question_id
            WHERE qq.questionnaire_id = 3
            ORDER BY qq.question_number, vo.option_letter
        ");
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $questions = [];
        foreach ($results as $row) {
            $qnum = $row['question_number'];
            if (!isset($questions[$qnum])) {
                $questions[$qnum] = [
                    'id' => $row['id'],
                    'question_number' => $row['question_number'],
                    'question_text' => $row['question_text'],
                    'subscale' => $row['subscale'],
                    'options' => []
                ];
            }
            if ($row['option_letter']) {
                $questions[$qnum]['options'][] = [
                    'letter' => $row['option_letter'],
                    'text' => $row['option_text'],
                    'learning_style' => $row['learning_style']
                ];
            }
        }
        
        return array_values($questions);
    } catch (Exception $e) {
        error_log("Error getting VARK questions: " . $e->getMessage());
        return [];
    }
}

function calculateVARKScore($answers, $pdo) {
    try {
        $scores = [
            'Visual' => 0,
            'Auditory' => 0,
            'Reading' => 0,
            'Kinesthetic' => 0
        ];
        
        foreach ($answers as $questionNum => $selectedOption) {
            $stmt = $pdo->prepare("
                SELECT vo.learning_style
                FROM questionnaire_questions qq
                JOIN vark_answer_options vo ON qq.id = vo.question_id
                WHERE qq.questionnaire_id = 3 
                AND qq.question_number = ? 
                AND vo.option_letter = ?
            ");
            $stmt->execute([$questionNum, $selectedOption]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                $scores[$result['learning_style']]++;
            }
        }
        
        // Determine dominant style
        $maxScore = max($scores);
        $dominantStyles = array_keys($scores, $maxScore);
        
        $dominantStyle = '';
        if (count($dominantStyles) == 1) {
            $dominantStyle = $dominantStyles[0];
        } else {
            $dominantStyle = implode('/', $dominantStyles);
        }
        
        // Determine learning preference
        $learningPreference = '';
        if ($maxScore >= 8) {
            $learningPreference = 'Strong ' . $dominantStyle;
        } elseif ($maxScore >= 5) {
            $learningPreference = 'Mild ' . $dominantStyle;
        } else {
            $learningPreference = 'Multimodal';
        }
        
        return [
            'scores' => $scores,
            'dominant_style' => $dominantStyle,
            'learning_preference' => $learningPreference
        ];
    } catch (Exception $e) {
        error_log("Error calculating VARK score: " . $e->getMessage());
        return null;
    }
}

function saveVARKResult($studentId, $scores, $dominantStyle, $learningPreference, $answers, $pdo) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO vark_results 
            (student_id, visual_score, auditory_score, reading_score, kinesthetic_score, 
             dominant_style, learning_preference, answers) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $studentId,
            $scores['Visual'],
            $scores['Auditory'],
            $scores['Reading'],
            $scores['Kinesthetic'],
            $dominantStyle,
            $learningPreference,
            json_encode($answers)
        ]);
        
        return $pdo->lastInsertId();
    } catch (Exception $e) {
        error_log("Error saving VARK result: " . $e->getMessage());
        return false;
    }
}

function getStudentVARKResult($studentId, $pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM vark_results 
            WHERE student_id = ? 
            ORDER BY completed_at DESC 
            LIMIT 1
        ");
        $stmt->execute([$studentId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error getting student VARK result: " . $e->getMessage());
        return null;
    }
}

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
        ]
    ];
    
    return $tips[$dominantStyle] ?? $tips['Visual'];
}

function getAllQuestionnaireScores($studentId, $pdo) {
    try {
        // Get MSLQ and AMS scores
        $questionnaire_scores = getQuestionnaireScores($studentId, $pdo);
        
        // Get VARK result
        $vark_result = getStudentVARKResult($studentId, $pdo);
        
        return [
            'mslq' => $questionnaire_scores['mslq'],
            'ams' => $questionnaire_scores['ams'],
            'vark' => $vark_result
        ];
    } catch (Exception $e) {
        error_log("Error getting all questionnaire scores: " . $e->getMessage());
        return ['mslq' => null, 'ams' => null, 'vark' => null];
    }
}

function getStudentDetailedStats($studentId, $pdo) {
    try {
        $stats = [];
        
        // Assignment statistics
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_assignments,
                AVG(score) as avg_score,
                MAX(score) as best_score,
                MIN(score) as lowest_score,
                COUNT(CASE WHEN score >= 80 THEN 1 END) as high_scores,
                COUNT(CASE WHEN submitted_at > due_date THEN 1 END) as late_submissions
            FROM assignment_submissions 
            WHERE student_id = ? AND score IS NOT NULL
        ");
        $stmt->execute([$studentId]);
        $stats['assignments'] = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Weekly evaluation statistics
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_evaluations,
                AVG(mslq_score) as avg_mslq,
                AVG(ams_score) as avg_ams,
                MAX(mslq_score) as best_mslq,
                MAX(ams_score) as best_ams
            FROM weekly_evaluation_results 
            WHERE student_id = ?
        ");
        $stmt->execute([$studentId]);
        $stats['evaluations'] = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Activity count by type
        $stmt = $pdo->prepare("
            SELECT 
                action_type, 
                COUNT(*) as count 
            FROM activity_log 
            WHERE user_id = ? 
            GROUP BY action_type 
            ORDER BY count DESC 
            LIMIT 5
        ");
        $stmt->execute([$studentId]);
        $stats['activities'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $stats;
    } catch (Exception $e) {
        error_log("Error getting student detailed stats: " . $e->getMessage());
        return [
            'assignments' => ['total_assignments' => 0, 'avg_score' => 0, 'best_score' => 0, 'lowest_score' => 0, 'high_scores' => 0, 'late_submissions' => 0],
            'evaluations' => ['total_evaluations' => 0, 'avg_mslq' => 0, 'avg_ams' => 0, 'best_mslq' => 0, 'best_ams' => 0],
            'activities' => []
        ];
    }
}

function getStudentLearningProfile($studentId, $pdo) {
    try {
        $profile = [];
        
        // Get all assessment results
        $scores = getAllQuestionnaireScores($studentId, $pdo);
        
        // Generate learning recommendations based on assessments
        $recommendations = [];
        
        if ($scores['mslq']) {
            if ($scores['mslq'] >= 4.0) {
                $recommendations[] = "🎓 You have strong learning strategies - consider mentoring other students";
                $recommendations[] = "📚 Try advanced or challenging materials to maintain engagement";
            } elseif ($scores['mslq'] >= 3.0) {
                $recommendations[] = "📖 Focus on improving specific learning strategies";
                $recommendations[] = "⏰ Work on time management and study planning";
            } else {
                $recommendations[] = "🎯 Start with basic study skills and learning techniques";
                $recommendations[] = "👥 Consider study groups or peer learning";
            }
        }
        
        if ($scores['ams']) {
            if ($scores['ams'] >= 4.0) {
                $recommendations[] = "⭐ Your motivation is high - use it to tackle challenging projects";
            } elseif ($scores['ams'] >= 3.0) {
                $recommendations[] = "💪 Set specific goals to boost your motivation";
            } else {
                $recommendations[] = "🚀 Look for ways to connect learning to your interests";
                $recommendations[] = "🎯 Break large tasks into smaller, manageable steps";
            }
        }
        
        if ($scores['vark']) {
            $varkTips = getVARKLearningTips($scores['vark']['dominant_style']);
            $recommendations = array_merge($recommendations, array_slice($varkTips['study_tips'], 0, 2));
        }
        
        $profile['scores'] = $scores;
        $profile['recommendations'] = $recommendations;
        
        return $profile;
    } catch (Exception $e) {
        error_log("Error getting student learning profile: " . $e->getMessage());
        return ['scores' => [], 'recommendations' => []];
    }
}

function getProgressTrend($studentId, $pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                week_number,
                year,
                mslq_score,
                ams_score,
                completed_at
            FROM weekly_evaluation_results 
            WHERE student_id = ? 
            ORDER BY year DESC, week_number DESC 
            LIMIT 8
        ");
        $stmt->execute([$studentId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calculate trends
        $trends = [];
        if (count($results) >= 2) {
            $latest = $results[0];
            $previous = $results[1];
            
            $mslqTrend = $latest['mslq_score'] - $previous['mslq_score'];
            $amsTrend = $latest['ams_score'] - $previous['ams_score'];
            
            $trends['mslq_trend'] = $mslqTrend;
            $trends['ams_trend'] = $amsTrend;
            $trends['mslq_direction'] = $mslqTrend > 0 ? 'up' : ($mslqTrend < 0 ? 'down' : 'stable');
            $trends['ams_direction'] = $amsTrend > 0 ? 'up' : ($amsTrend < 0 ? 'down' : 'stable');
        }
        
        return ['data' => array_reverse($results), 'trends' => $trends];
    } catch (Exception $e) {
        error_log("Error getting progress trend: " . $e->getMessage());
        return ['data' => [], 'trends' => []];
    }
}
?>
