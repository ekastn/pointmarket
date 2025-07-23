<?php
/**
 * NLP (Natural Language Processing) Model untuk POINTMARKET
 * 
 * Model ini menganalisis teks jawaban siswa dan memberikan feedback 
 * berdasarkan profil MSLQ, AMS, dan VARK learning styles
 * 
 * @author POINTMARKET Team
 * @version 1.0
 */

require_once 'config.php';

class NLPModel {
    private $pdo;
    private $stopWords;
    private $keywordWeights;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->initializeStopWords();
        $this->initializeKeywordWeights();
    }
    
    /**
     * Main function untuk menganalisis teks
     * 
     * @param string $text - Teks yang akan dianalisis
     * @param string $context - Konteks (assignment, quiz, etc.)
     * @param int $student_id - ID siswa untuk personalisasi
     * @return array - Hasil analisis NLP
     */
    public function analyzeText($text, $context = 'assignment', $student_id = null) {
        // Validasi input
        if (empty($text) || strlen($text) < 10) {
            return $this->createErrorResult('Teks terlalu pendek untuk dianalisis');
        }
        
        try {
            // Preprocessing
            $cleanText = $this->preprocessText($text);
            
            // Core Analysis
            $analysis = [
                'original_text' => $text,
                'clean_text' => $cleanText,
                'word_count' => $this->countWords($cleanText),
                'sentence_count' => $this->countSentences($cleanText),
                'grammar_score' => $this->analyzeGrammar($cleanText),
                'keyword_score' => $this->analyzeKeywords($cleanText, $context),
                'structure_score' => $this->analyzeStructure($cleanText),
            'readability_score' => $this->analyzeReadability($cleanText),
            'sentiment_score' => $this->analyzeSentiment($cleanText),
            'complexity_score' => $this->analyzeComplexity($cleanText)
        ];
        
        // Calculate overall score
        $analysis['total_score'] = $this->calculateTotalScore($analysis);
        
        // Generate feedback
        $analysis['feedback'] = $this->generateFeedback($analysis, $student_id);
        
        // Get personalized recommendations
        if ($student_id) {
            $analysis['personalized_feedback'] = $this->getPersonalizedFeedback($analysis, $student_id);
        }
        
        return $analysis;
        
        } catch (Exception $e) {
            error_log("NLP Model Error: " . $e->getMessage());
            return $this->createErrorResult('Terjadi kesalahan dalam analisis: ' . $e->getMessage());
        }
    }
    
    /**
     * Preprocessing teks untuk analisis
     */
    private function preprocessText($text) {
        // Remove extra whitespace
        $text = trim(preg_replace('/\s+/', ' ', $text));
        
        // Basic cleaning
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        $text = strip_tags($text);
        
        return $text;
    }
    
    /**
     * Hitung jumlah kata
     */
    private function countWords($text) {
        return str_word_count($text, 0, 'áéíóúàèìòùâêîôûäëïöüñç');
    }
    
    /**
     * Hitung jumlah kalimat
     */
    private function countSentences($text) {
        return preg_match_all('/[.!?]+/', $text);
    }
    
    /**
     * Analisis grammar sederhana
     */
    private function analyzeGrammar($text) {
        $score = 100;
        
        // Check for common grammar issues
        $issues = [
            '/\b(saya|aku)\s+(adalah|merupakan)\b/i' => -5, // Redundant "saya adalah"
            '/\b(yang|yg)\s+(yang|yg)\b/i' => -10, // Double "yang"
            '/\b(di|ke|dari)\s+(di|ke|dari)\b/i' => -10, // Double prepositions
            '/\b(pada|dalam)\s+(pada|dalam)\b/i' => -10, // Double prepositions
            '/[a-z][A-Z]/' => -3, // Inconsistent capitalization
            '/\s{2,}/' => -2, // Multiple spaces
            '/[.!?]{2,}/' => -3, // Multiple punctuation
        ];
        
        foreach ($issues as $pattern => $penalty) {
            $count = preg_match_all($pattern, $text);
            $score += ($count * $penalty);
        }
        
        // Bonus for good practices
        if (preg_match('/^[A-Z]/', $text)) $score += 5; // Starts with capital
        if (preg_match('/[.!?]$/', $text)) $score += 5; // Ends with punctuation
        
        return max(0, min(100, $score));
    }
    
    /**
     * Analisis kata kunci berdasarkan konteks
     */
    private function analyzeKeywords($text, $context) {
        $keywords = $this->getContextKeywords($context);
        $foundKeywords = 0;
        $totalKeywords = count($keywords);
        
        foreach ($keywords as $keyword => $weight) {
            if (preg_match('/\b' . preg_quote($keyword, '/') . '\b/i', $text)) {
                $foundKeywords += $weight;
            }
        }
        
        if ($totalKeywords == 0) return 50; // Default if no keywords defined
        
        return min(100, ($foundKeywords / $totalKeywords) * 100);
    }
    
    /**
     * Analisis struktur teks
     */
    private function analyzeStructure($text) {
        $score = 50; // Base score
        
        // Check for good structure indicators
        $structures = [
            '/\b(pertama|kedua|ketiga|keempat|kelima)\b/i' => 10, // Numbering
            '/\b(selanjutnya|kemudian|setelah itu|akhirnya)\b/i' => 8, // Transitions
            '/\b(karena|sebab|oleh karena itu|sehingga)\b/i' => 8, // Causality
            '/\b(namun|tetapi|akan tetapi|meskipun)\b/i' => 8, // Contrast
            '/\b(misalnya|contohnya|sebagai contoh)\b/i' => 10, // Examples
            '/\b(kesimpulan|ringkasan|dengan demikian)\b/i' => 12, // Conclusion
        ];
        
        foreach ($structures as $pattern => $bonus) {
            if (preg_match($pattern, $text)) {
                $score += $bonus;
            }
        }
        
        return min(100, $score);
    }
    
    /**
     * Analisis readability (keterbacaan)
     */
    private function analyzeReadability($text) {
        $wordCount = $this->countWords($text);
        $sentenceCount = $this->countSentences($text);
        
        if ($sentenceCount == 0) return 0;
        
        $averageWordsPerSentence = $wordCount / $sentenceCount;
        
        // Ideal range: 15-20 words per sentence
        if ($averageWordsPerSentence >= 15 && $averageWordsPerSentence <= 20) {
            return 100;
        } elseif ($averageWordsPerSentence >= 10 && $averageWordsPerSentence <= 25) {
            return 80;
        } elseif ($averageWordsPerSentence >= 5 && $averageWordsPerSentence <= 30) {
            return 60;
        } else {
            return 40;
        }
    }
    
    /**
     * Analisis sentiment sederhana
     */
    private function analyzeSentiment($text) {
        $positiveWords = ['baik', 'bagus', 'hebat', 'luar biasa', 'sangat', 'penting', 'bermanfaat', 'efektif', 'berhasil'];
        $negativeWords = ['buruk', 'jelek', 'tidak', 'bukan', 'gagal', 'sulit', 'susah', 'masalah', 'error'];
        
        $positiveCount = 0;
        $negativeCount = 0;
        
        foreach ($positiveWords as $word) {
            $positiveCount += preg_match_all('/\b' . preg_quote($word, '/') . '\b/i', $text);
        }
        
        foreach ($negativeWords as $word) {
            $negativeCount += preg_match_all('/\b' . preg_quote($word, '/') . '\b/i', $text);
        }
        
        $totalWords = $positiveCount + $negativeCount;
        if ($totalWords == 0) return 70; // Neutral
        
        return min(100, max(0, (($positiveCount / $totalWords) * 100)));
    }
    
    /**
     * Analisis kompleksitas teks
     */
    private function analyzeComplexity($text) {
        $wordCount = $this->countWords($text);
        $complexWords = 0;
        
        // Count complex words (3+ syllables, approximated by vowel patterns)
        $words = explode(' ', $text);
        foreach ($words as $word) {
            $vowelCount = preg_match_all('/[aeiouAEIOU]/', $word);
            if ($vowelCount >= 3) {
                $complexWords++;
            }
        }
        
        if ($wordCount == 0) return 0;
        
        $complexityRatio = ($complexWords / $wordCount) * 100;
        
        // Ideal complexity: 10-30% complex words
        if ($complexityRatio >= 10 && $complexityRatio <= 30) {
            return 100;
        } elseif ($complexityRatio >= 5 && $complexityRatio <= 40) {
            return 80;
        } else {
            return 60;
        }
    }
    
    /**
     * Hitung total score berdasarkan semua faktor
     */
    private function calculateTotalScore($analysis) {
        $weights = [
            'grammar_score' => 0.25,
            'keyword_score' => 0.20,
            'structure_score' => 0.20,
            'readability_score' => 0.15,
            'sentiment_score' => 0.10,
            'complexity_score' => 0.10
        ];
        
        $totalScore = 0;
        foreach ($weights as $component => $weight) {
            $totalScore += $analysis[$component] * $weight;
        }
        
        return round($totalScore, 2);
    }
    
    /**
     * Generate basic feedback
     */
    private function generateFeedback($analysis, $student_id = null) {
        $feedback = [];
        
        // Grammar feedback
        if ($analysis['grammar_score'] < 60) {
            $feedback[] = "⚠️ Terdapat beberapa kesalahan tata bahasa. Periksa kembali penggunaan kata hubung dan tanda baca.";
        } elseif ($analysis['grammar_score'] >= 80) {
            $feedback[] = "✅ Tata bahasa sudah baik!";
        }
        
        // Keyword feedback
        if ($analysis['keyword_score'] < 50) {
            $feedback[] = "📝 Coba gunakan lebih banyak kata kunci yang relevan dengan topik.";
        } elseif ($analysis['keyword_score'] >= 80) {
            $feedback[] = "🎯 Penggunaan kata kunci sudah tepat!";
        }
        
        // Structure feedback
        if ($analysis['structure_score'] < 60) {
            $feedback[] = "🔄 Struktur tulisan bisa diperbaiki dengan menggunakan kata penghubung dan numbering.";
        } elseif ($analysis['structure_score'] >= 80) {
            $feedback[] = "📊 Struktur tulisan sudah terorganisir dengan baik!";
        }
        
        // Length feedback
        if ($analysis['word_count'] < 50) {
            $feedback[] = "📏 Coba kembangkan jawaban dengan lebih detail dan contoh.";
        } elseif ($analysis['word_count'] > 300) {
            $feedback[] = "✂️ Jawaban sudah lengkap, bisa dipertimbangkan untuk lebih ringkas.";
        }
        
        return $feedback;
    }
    
    /**
     * Get personalized feedback berdasarkan profil siswa
     */
    private function getPersonalizedFeedback($analysis, $student_id) {
        try {
            // Get student's MSLQ, AMS, and VARK data
            $profile = $this->getStudentProfile($student_id);
            
            $personalizedFeedback = [];
            
            // MSLQ-based feedback
            if ($profile['mslq_critical_thinking'] > 75) {
                $personalizedFeedback[] = "🧠 Berdasarkan profil MSLQ Anda, coba analisis lebih dalam dan berikan evaluasi kritis terhadap topik ini.";
            }
            
            if ($profile['mslq_elaboration'] > 75) {
                $personalizedFeedback[] = "🔗 Anda memiliki kemampuan elaborasi yang baik. Coba hubungkan konsep ini dengan pengetahuan sebelumnya.";
            }
            
            // AMS-based feedback
            if ($profile['ams_intrinsic_to_know'] > 75) {
                $personalizedFeedback[] = "📚 Motivasi belajar Anda tinggi! Coba eksplorasi lebih dalam aspek-aspek yang menarik dari topik ini.";
            }
            
            // VARK-based feedback
            if ($profile['vark_dominant'] == 'Visual') {
                $personalizedFeedback[] = "👁️ Sebagai visual learner, coba tambahkan deskripsi visual atau diagram dalam jawaban Anda.";
            } elseif ($profile['vark_dominant'] == 'Reading/Writing') {
                $personalizedFeedback[] = "📝 Gaya belajar reading/writing Anda sudah sesuai dengan format essay ini. Pertahankan!";
            }
            
            return $personalizedFeedback;
            
        } catch (Exception $e) {
            error_log("Error getting personalized feedback: " . $e->getMessage());
            return ["💡 Terus berlatih menulis untuk meningkatkan kemampuan Anda!"];
        }
    }
    
    /**
     * Get student profile dari database
     */
    private function getStudentProfile($student_id) {
        $stmt = $this->pdo->prepare("
            SELECT 
                -- MSLQ scores (mock data untuk sekarang)
                75 as mslq_critical_thinking,
                80 as mslq_elaboration,
                70 as mslq_organization,
                -- AMS scores (mock data untuk sekarang)
                85 as ams_intrinsic_to_know,
                75 as ams_intrinsic_to_accomplish,
                -- VARK dominant style
                'Reading/Writing' as vark_dominant
        ");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get context keywords berdasarkan mata pelajaran/topik
     */
    private function getContextKeywords($context) {
        $keywords = [
            'matematik' => [
                'rumus' => 1.0, 'perhitungan' => 1.0, 'angka' => 0.8,
                'operasi' => 0.9, 'hasil' => 0.7, 'metode' => 0.8
            ],
            'fisika' => [
                'gaya' => 1.0, 'gerak' => 1.0, 'energi' => 1.0,
                'hukum' => 0.9, 'rumus' => 0.8, 'percepatan' => 0.9
            ],
            'kimia' => [
                'unsur' => 1.0, 'reaksi' => 1.0, 'molekul' => 1.0,
                'senyawa' => 0.9, 'atom' => 0.8, 'ikatan' => 0.9
            ],
            'biologi' => [
                'sel' => 1.0, 'organisme' => 1.0, 'protein' => 0.9,
                'gen' => 0.8, 'evolusi' => 0.9, 'ekosistem' => 0.8
            ],
            'assignment' => [
                'analisis' => 1.0, 'konsep' => 1.0, 'penjelasan' => 0.9,
                'contoh' => 0.8, 'kesimpulan' => 0.9, 'argumen' => 0.8
            ]
        ];
        
        return $keywords[$context] ?? $keywords['assignment'];
    }
    
    /**
     * Initialize stop words
     */
    private function initializeStopWords() {
        $this->stopWords = [
            'dan', 'atau', 'tetapi', 'namun', 'karena', 'untuk', 'dengan',
            'pada', 'dari', 'ke', 'di', 'yang', 'ini', 'itu', 'adalah',
            'akan', 'telah', 'sudah', 'sedang', 'saya', 'kami', 'kita'
        ];
    }
    
    /**
     * Initialize keyword weights
     */
    private function initializeKeywordWeights() {
        $this->keywordWeights = [
            'academic' => 1.0,
            'analysis' => 1.2,
            'critical' => 1.1,
            'explanation' => 1.0,
            'example' => 0.9,
            'conclusion' => 1.1
        ];
    }
    
    /**
     * Create error result
     */
    private function createErrorResult($message) {
        return [
            'success' => false,
            'error' => $message,
            'total_score' => 0,
            'feedback' => [$message]
        ];
    }
    
    /**
     * Save analysis result ke database
     */
    public function saveAnalysisResult($student_id, $assignment_id, $analysis) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO nlp_analysis_results 
                (student_id, assignment_id, original_text, total_score, 
                 grammar_score, keyword_score, structure_score, 
                 feedback, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $student_id,
                $assignment_id,
                $analysis['original_text'],
                $analysis['total_score'],
                $analysis['grammar_score'],
                $analysis['keyword_score'],
                $analysis['structure_score'],
                json_encode($analysis['feedback'])
            ]);
            
            return $this->pdo->lastInsertId();
            
        } catch (Exception $e) {
            error_log("Error saving NLP analysis: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get analysis history untuk student
     */
    public function getAnalysisHistory($student_id, $limit = 10) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT nar.*, a.title as assignment_title
                FROM nlp_analysis_results nar
                LEFT JOIN assignments a ON nar.assignment_id = a.id
                WHERE nar.student_id = ?
                ORDER BY nar.created_at DESC
                LIMIT ?
            ");
            $stmt->execute([$student_id, $limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error getting analysis history: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get PDO connection
     */
    public function getPDO() {
        return $this->pdo;
    }
}
?>
