<?php
/**
 * Basic NLP Model Implementation
 * 
 * This is a simplified implementation of the NLPModel class
 * that can be used if the original is missing or has issues.
 */

// Check if NLPModel already exists to avoid conflicts
if (!class_exists('NLPModel')) {
    class NLPModel {
        private $pdo;
        
        public function __construct($pdo) {
            $this->pdo = $pdo;
        }
        
        /**
         * Analyze text using basic NLP techniques
         * 
         * @param string $text Text to analyze
         * @param string $context Context of the analysis
         * @param int $student_id Student ID
         * @return array Analysis results
         */
        public function analyzeText($text, $context = 'assignment', $student_id = null) {
            // Validate input
            if (empty($text) || strlen($text) < 10) {
                return $this->createErrorResult('Text is too short for analysis');
            }
            
            try {
                // Preprocess text
                $cleanText = $this->preprocessText($text);
                
                // Basic analysis
                $wordCount = $this->countWords($cleanText);
                $sentenceCount = $this->countSentences($cleanText);
                $charCount = strlen($cleanText);
                
                // Calculate scores (simplified algorithm)
                $grammarScore = $this->calculateGrammarScore($cleanText);
                $keywordScore = $this->calculateKeywordScore($cleanText, $context);
                $structureScore = $this->calculateStructureScore($cleanText);
                $readabilityScore = $this->calculateReadabilityScore($cleanText);
                $sentimentScore = $this->calculateSentimentScore($cleanText);
                $complexityScore = $this->calculateComplexityScore($cleanText);
                
                // Calculate total score
                $totalScore = round(($grammarScore + $keywordScore + $structureScore + 
                                    $readabilityScore + $sentimentScore + $complexityScore) / 6);
                
                // Create analysis result
                $analysis = [
                    'original_text' => $text,
                    'clean_text' => $cleanText,
                    'word_count' => $wordCount,
                    'sentence_count' => $sentenceCount,
                    'char_count' => $charCount,
                    'grammar_score' => $grammarScore,
                    'keyword_score' => $keywordScore,
                    'structure_score' => $structureScore,
                    'readability_score' => $readabilityScore,
                    'sentiment_score' => $sentimentScore,
                    'complexity_score' => $complexityScore,
                    'total_score' => $totalScore,
                    'feedback' => $this->generateFeedback($totalScore, $grammarScore, $keywordScore, $structureScore)
                ];
                
                // Save analysis to database if requested
                if ($student_id) {
                    $this->saveAnalysis($analysis, $student_id, $context);
                }
                
                return $analysis;
            } catch (Exception $e) {
                error_log("NLP Analysis Error: " . $e->getMessage());
                return $this->createErrorResult('Error analyzing text: ' . $e->getMessage());
            }
        }
        
        /**
         * Preprocess text for analysis
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
         * Count words in text
         */
        private function countWords($text) {
            return str_word_count($text);
        }
        
        /**
         * Count sentences in text
         */
        private function countSentences($text) {
            return preg_match_all('/[.!?]+/', $text, $matches);
        }
        
        /**
         * Calculate grammar score (simplified)
         */
        private function calculateGrammarScore($text) {
            // This is a simplified scoring algorithm
            // Real implementation would use NLP libraries or API
            
            // Check for basic grammar issues
            $issues = 0;
            
            // Check for capitalization at the beginning of sentences
            preg_match_all('/[.!?]\s+[a-z]/', $text, $matches);
            $issues += count($matches[0]);
            
            // Check for double spaces
            preg_match_all('/\s\s+/', $text, $matches);
            $issues += count($matches[0]);
            
            // Check for repeated punctuation
            preg_match_all('/[,.!?]{2,}/', $text, $matches);
            $issues += count($matches[0]);
            
            // Calculate score based on issues per word
            $wordCount = $this->countWords($text);
            $score = 100 - min(40, ($issues / max(1, $wordCount)) * 1000);
            
            return max(0, min(100, round($score)));
        }
        
        /**
         * Calculate keyword score based on context
         */
        private function calculateKeywordScore($text, $context) {
            // Define keywords for different contexts
            $keywords = [
                'assignment' => ['analysis', 'research', 'study', 'evidence', 'theory', 'data', 'conclusion'],
                'matematik' => ['equation', 'calculation', 'formula', 'problem', 'solution', 'theorem', 'proof'],
                'fisika' => ['force', 'energy', 'motion', 'velocity', 'acceleration', 'mass', 'gravity'],
                'kimia' => ['reaction', 'element', 'compound', 'molecule', 'acid', 'base', 'solution'],
                'biologi' => ['cell', 'organism', 'system', 'evolution', 'species', 'dna', 'ecology']
            ];
            
            // Get keywords for the current context
            $contextKeywords = isset($keywords[$context]) ? $keywords[$context] : $keywords['assignment'];
            
            // Count matching keywords
            $matches = 0;
            $text = strtolower($text);
            
            foreach ($contextKeywords as $keyword) {
                $matches += substr_count($text, $keyword);
            }
            
            // Calculate score based on keyword density
            $wordCount = $this->countWords($text);
            $score = min(100, ($matches / max(1, $wordCount)) * 500);
            
            return max(0, min(100, round($score)));
        }
        
        /**
         * Calculate structure score (simplified)
         */
        private function calculateStructureScore($text) {
            // This is a simplified scoring algorithm
            // Real implementation would analyze paragraph structure, transitions, etc.
            
            // Check for paragraph breaks
            $paragraphs = preg_split('/\n\s*\n/', $text);
            $paragraphCount = count($paragraphs);
            
            // Calculate average sentence length
            $sentences = preg_split('/[.!?]+/', $text);
            $sentenceCount = count($sentences);
            
            $totalWords = 0;
            foreach ($sentences as $sentence) {
                $totalWords += str_word_count(trim($sentence));
            }
            
            $avgSentenceLength = $sentenceCount > 0 ? $totalWords / $sentenceCount : 0;
            
            // Ideal sentence length is between 15-25 words
            $sentenceLengthScore = 100 - min(50, abs($avgSentenceLength - 20) * 5);
            
            // Structure score based on paragraph count and sentence length
            $paragraphScore = min(100, $paragraphCount * 20);
            
            $score = ($sentenceLengthScore + $paragraphScore) / 2;
            
            return max(0, min(100, round($score)));
        }
        
        /**
         * Calculate readability score (simplified)
         */
        private function calculateReadabilityScore($text) {
            // This is a simplified version of Flesch Reading Ease
            
            $wordCount = $this->countWords($text);
            $sentenceCount = max(1, $this->countSentences($text));
            
            // Count syllables (very simplified)
            $syllables = 0;
            $words = str_word_count($text, 1);
            
            foreach ($words as $word) {
                // Count vowels as a rough approximation of syllables
                $vowelCount = preg_match_all('/[aeiouy]/i', $word, $matches);
                $syllables += max(1, $vowelCount);
            }
            
            // Calculate Flesch Reading Ease (simplified)
            $wordsPerSentence = $wordCount / $sentenceCount;
            $syllablesPerWord = $syllables / max(1, $wordCount);
            
            $readability = 206.835 - (1.015 * $wordsPerSentence) - (84.6 * $syllablesPerWord);
            
            // Convert to a 0-100 scale
            $score = min(100, max(0, $readability));
            
            return round($score);
        }
        
        /**
         * Calculate sentiment score (simplified)
         */
        private function calculateSentimentScore($text) {
            // This is a very simplified sentiment analysis
            // Real implementation would use NLP libraries or API
            
            $positiveWords = ['good', 'great', 'excellent', 'positive', 'best', 'success', 'beneficial', 'advantage'];
            $negativeWords = ['bad', 'poor', 'negative', 'worst', 'failure', 'problem', 'difficult', 'disadvantage'];
            
            $text = strtolower($text);
            
            // Count positive and negative words
            $positiveCount = 0;
            $negativeCount = 0;
            
            foreach ($positiveWords as $word) {
                $positiveCount += substr_count($text, $word);
            }
            
            foreach ($negativeWords as $word) {
                $negativeCount += substr_count($text, $word);
            }
            
            // Calculate sentiment score
            $totalWords = $this->countWords($text);
            $sentimentRatio = ($positiveCount - $negativeCount) / max(1, $totalWords);
            
            // Convert to a 0-100 scale
            $score = 50 + ($sentimentRatio * 500);
            
            return max(0, min(100, round($score)));
        }
        
        /**
         * Calculate complexity score (simplified)
         */
        private function calculateComplexityScore($text) {
            // This is a simplified complexity analysis
            
            // Count long words (>7 characters)
            $words = str_word_count($text, 1);
            $longWordCount = 0;
            
            foreach ($words as $word) {
                if (strlen($word) > 7) {
                    $longWordCount++;
                }
            }
            
            // Calculate average word length
            $totalLength = 0;
            foreach ($words as $word) {
                $totalLength += strlen($word);
            }
            
            $avgWordLength = count($words) > 0 ? $totalLength / count($words) : 0;
            
            // Calculate complexity score
            $longWordRatio = $longWordCount / max(1, count($words));
            $lengthFactor = ($avgWordLength - 4) / 3; // 4 is average, 3 is scaling factor
            
            $score = 50 + ($longWordRatio * 100) + ($lengthFactor * 25);
            
            return max(0, min(100, round($score)));
        }
        
        /**
         * Generate feedback based on scores
         */
        private function generateFeedback($totalScore, $grammarScore, $keywordScore, $structureScore) {
            $feedback = "Overall Analysis:\n\n";
            
            if ($totalScore >= 80) {
                $feedback .= "Your text is well-written and demonstrates strong communication skills. ";
            } elseif ($totalScore >= 60) {
                $feedback .= "Your text is good but has some areas that could be improved. ";
            } elseif ($totalScore >= 40) {
                $feedback .= "Your text needs improvement in several areas. ";
            } else {
                $feedback .= "Your text requires significant revision to meet academic standards. ";
            }
            
            $feedback .= "\n\nSpecific Feedback:\n\n";
            
            // Grammar feedback
            if ($grammarScore >= 80) {
                $feedback .= "- Grammar: Excellent grammar and spelling. Keep up the good work.\n";
            } elseif ($grammarScore >= 60) {
                $feedback .= "- Grammar: Good grammar overall, but check for minor errors.\n";
            } else {
                $feedback .= "- Grammar: Significant grammar issues. Consider using a grammar checker.\n";
            }
            
            // Keyword feedback
            if ($keywordScore >= 80) {
                $feedback .= "- Keywords: Excellent use of relevant terminology and concepts.\n";
            } elseif ($keywordScore >= 60) {
                $feedback .= "- Keywords: Good use of terminology, but could include more relevant concepts.\n";
            } else {
                $feedback .= "- Keywords: Limited use of relevant terminology. Include more subject-specific terms.\n";
            }
            
            // Structure feedback
            if ($structureScore >= 80) {
                $feedback .= "- Structure: Well-organized with clear flow of ideas.\n";
            } elseif ($structureScore >= 60) {
                $feedback .= "- Structure: Reasonably organized, but could improve transitions between ideas.\n";
            } else {
                $feedback .= "- Structure: Poor organization. Work on paragraph structure and transitions.\n";
            }
            
            return $feedback;
        }
        
        /**
         * Save analysis to database
         */
        private function saveAnalysis($analysis, $student_id, $context) {
            try {
                // Check if table exists
                $tableExists = false;
                $stmt = $this->pdo->query("SHOW TABLES LIKE 'nlp_analysis'");
                if ($stmt->rowCount() > 0) {
                    $tableExists = true;
                }
                
                // Create table if it doesn't exist
                if (!$tableExists) {
                    $this->pdo->exec("
                        CREATE TABLE nlp_analysis (
                            id INT(11) AUTO_INCREMENT PRIMARY KEY,
                            student_id INT(11) NOT NULL,
                            context VARCHAR(50) NOT NULL,
                            text TEXT NOT NULL,
                            total_score INT(11) NOT NULL,
                            grammar_score INT(11) NOT NULL,
                            keyword_score INT(11) NOT NULL,
                            structure_score INT(11) NOT NULL,
                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                        )
                    ");
                }
                
                // Insert analysis
                $stmt = $this->pdo->prepare("
                    INSERT INTO nlp_analysis 
                    (student_id, context, text, total_score, grammar_score, keyword_score, structure_score)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $student_id,
                    $context,
                    $analysis['clean_text'],
                    $analysis['total_score'],
                    $analysis['grammar_score'],
                    $analysis['keyword_score'],
                    $analysis['structure_score']
                ]);
                
                return true;
            } catch (Exception $e) {
                error_log("Error saving analysis: " . $e->getMessage());
                return false;
            }
        }
        
        /**
         * Create error result
         */
        private function createErrorResult($message) {
            return [
                'error' => $message
            ];
        }
    }
}
?>
