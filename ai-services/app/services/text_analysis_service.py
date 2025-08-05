import re
import math
from typing import List
from collections import Counter
import stanza
from app.stores.lexicon_store import LexiconStore

class TextAnalysisService:
    """
    Service for comprehensive text analysis including keywords, key sentences,
    and various text statistics using proper NLP techniques.
    """
    
    def __init__(self, stanza_pipeline=None):
        self.stanza_pipeline = stanza_pipeline
        self.lexicon_store = LexiconStore()
        
        # Indonesian stop words
        self.stop_words = {
            'dan', 'atau', 'dengan', 'untuk', 'dari', 'ke', 'di', 'pada', 'oleh', 
            'dalam', 'yang', 'ini', 'itu', 'adalah', 'akan', 'telah', 'sudah',
            'dapat', 'bisa', 'harus', 'juga', 'tidak', 'ada', 'bila', 'karena',
            'sebab', 'jika', 'maka', 'bahwa', 'hal', 'cara', 'saat', 'waktu',
            'tahun', 'hari', 'bulan', 'minggu', 'jam', 'menit', 'detik'
        }
        
    def extract_keywords(self, text: str, max_keywords: int = 10) -> List[str]:
        """
        Extract keywords using NLP techniques.
        """
        if not text.strip():
            return []
            
        # Directly use NLP techniques for keyword extraction
        nlp_keywords = self._extract_nlp_keywords(text, max_keywords)
        
        return nlp_keywords[:max_keywords]

    def calculate_text_stats(self, text: str) -> dict:
       """Calculate comprehensive text statistics."""
       if not text.strip():
           return {
               'wordCount': 0,
               'sentenceCount': 0,
               'avgWordLength': 0.0,
               'readingTime': 0
           }
           
       # Basic counts
       words = re.findall(r'\b\w+\b', text)
       word_count = len(words)
       
       sentences = self._split_sentences(text)
       sentence_count = len(sentences)
       
       # Average word length
       if words:
           total_char_count = sum(len(word) for word in words)
           avg_word_length = total_char_count / len(words)
       else:
           avg_word_length = 0.0
           
       # Reading time (average 200 words per minute for Indonesian)
       reading_time_minutes = max(1, math.ceil(word_count / 200))
       
       return {
           'wordCount': word_count,
           'sentenceCount': sentence_count,
           'avgWordLength': round(avg_word_length, 2),
           'readingTime': reading_time_minutes
       }
    
    def _extract_context_keywords(self, text: str, context_type: str) -> List[str]:
        """
        This method is no longer used as context-specific keywords are removed.
        It remains for backward compatibility if any old code still calls it.
        """
        return []
    
    def _extract_nlp_keywords(self, text: str, max_keywords: int) -> List[str]:
        """
        Extract keywords using NLP techniques with Stanza.
        """
        if not self.stanza_pipeline or max_keywords <= 0:
            return self._extract_frequency_keywords(text, max_keywords)
            
        try:
            # Process text with Stanza
            doc = self.stanza_pipeline(text)
            
            # Extract meaningful words (nouns, adjectives, verbs, proper nouns)
            meaningful_words = []
            for sentence in doc.sentences:
                for word in sentence.words:
                    # Filter by POS tags and exclude stop words
                    if (word.upos in ['NOUN', 'PROPN', 'ADJ', 'VERB'] and 
                        word.lemma.lower() not in self.stop_words and 
                        len(word.lemma) > 2): # Lower length threshold for more keywords
                        meaningful_words.append(word.lemma.lower())
            
            # Count frequency and get top words
            word_freq = Counter(meaningful_words)
            keywords = [word for word, freq in word_freq.most_common(max_keywords)]
            
            return keywords
            
        except Exception as e:
            print(f"Error in NLP keyword extraction: {e}")
            return self._extract_frequency_keywords(text, max_keywords)
    
    def _extract_frequency_keywords(self, text: str, max_keywords: int) -> List[str]:
        """
        Fallback: Extract keywords using simple frequency analysis.
        """
        if not text.strip():
            return []
            
        # Clean and tokenize
        words = re.findall(r'\b\w+\b', text.lower())
        
        # Filter out stop words and short words
        filtered_words = [
            word for word in words 
            if word not in self.stop_words and len(word) > 3
        ]
        
        # Count frequency and get top words
        word_freq = Counter(filtered_words)
        keywords = [word for word, freq in word_freq.most_common(max_keywords)]
        
        return keywords
    
    def extract_key_sentences(self, text: str, max_sentences: int = 3) -> List[str]:
        """
        Extract key sentences using a combination of position, length, and keyword density.
        """
        if not text.strip():
            return []
            
        # Split into sentences
        sentences = self._split_sentences(text)
        if len(sentences) <= max_sentences:
            return sentences
            
        # Score sentences
        sentence_scores = []
        for i, sentence in enumerate(sentences):
            score = self._score_sentence(sentence, i, len(sentences), text)
            sentence_scores.append((sentence, score))
            
        # Sort by score and return top sentences
        sentence_scores.sort(key=lambda x: x[1], reverse=True)
        key_sentences = [sent for sent, score in sentence_scores[:max_sentences]]
        
        return key_sentences
    
    def _split_sentences(self, text: str) -> List[str]:
        """
        Split text into sentences using Stanza.
        """
        if not self.stanza_pipeline:
            # Fallback to regex if stanza is not available
            sentences = re.split(r'[.!?]+', text)
            cleaned_sentences = []
            for sentence in sentences:
                cleaned = sentence.strip()
                if len(cleaned) > 10:  # Only meaningful sentences
                    cleaned_sentences.append(cleaned)
            return cleaned_sentences

        doc = self.stanza_pipeline(text)
        return [sentence.text for sentence in doc.sentences if len(sentence.text.strip()) > 10] # Filter short sentences
    
    def _split_paragraphs(self, text: str) -> List[str]:
        """
        Splits text into paragraphs based on double newlines.
        """
        paragraphs = [p.strip() for p in text.split('\n\n') if p.strip()]
        return paragraphs

    def _score_sentence(self, sentence: str, position: int, total_sentences: int, full_text: str) -> float:
        """
        Score a sentence based on various factors.
        """
        score = 0.0
        
        # Position score (first and last sentences are often important)
        if position == 0:
            score += 0.3  # First sentence bonus
        elif position == total_sentences - 1:
            score += 0.2  # Last sentence bonus
            
        # Length score (moderate length sentences are often key)
        words = sentence.split()
        word_count = len(words)
        if 10 <= word_count <= 25:
            score += 0.2
        elif word_count > 25:
            score += 0.1
            
        # Keyword density score (using NLP-extracted keywords for better relevance)
        # Re-extracting keywords for the full text here might be inefficient if called frequently.
        # Consider passing keywords from analyze() or caching them.
        nlp_keywords = self._extract_nlp_keywords(full_text, 10) # Get top 10 NLP keywords from full text
        sentence_lower = sentence.lower()
        keyword_count = sum(1 for keyword in nlp_keywords if keyword in sentence_lower)
        score += min(keyword_count * 0.1, 0.3)  # Cap at 0.3
        
        # Proper Noun Density
        if self.stanza_pipeline:
            doc = self.stanza_pipeline(sentence)
            proper_nouns = [word.text for sent in doc.sentences for word in sent.words if word.upos == 'PROPN']
            if word_count > 0:
                proper_noun_density = len(proper_nouns) / word_count
                score += min(proper_noun_density * 0.5, 0.2) # Cap at 0.2
        
        # Vocabulary Richness (Type-Token Ratio)
        if word_count > 0:
            unique_words = set(w.lower() for w in words if w.lower() not in self.stop_words)
            ttr = len(unique_words) / word_count
            score += min(ttr * 0.3, 0.15) # Cap at 0.15
            
        # Question or statement with important words
        if '?' in sentence:
            score += 0.1
        if any(word in sentence_lower for word in ['penting', 'utama', 'kesimpulan', 'hasil', 'fakta', 'bukti']):
            score += 0.15
            
        return score
    
    def _count_syllables_id(self, word: str) -> int:
        """
        Estimates the number of syllables in an Indonesian word.
        This is a rule-based approach based on common Indonesian phonology.
        It's an estimation and might not be 100% accurate for all words.
        """
        word = word.lower()
        if not word:
            return 0

        vowels = "aiueo"
        syllable_count = 0
        last_char_vowel = False

        for i, char in enumerate(word):
            if char in vowels:
                if not last_char_vowel: # Start of a new vowel group/syllable
                    syllable_count += 1
                last_char_vowel = True
            else:
                last_char_vowel = False
        
        # Consonant clusters (e.g., 'ng', 'ny', 'kh', 'sy') often act as single sounds
        # This is a simplification; a full phonological analysis is complex.
        # For simplicity, we'll just ensure a minimum of 1 syllable for any word with vowels.
        if syllable_count == 0 and any(v in word for v in vowels):
            syllable_count = 1

        return max(1, syllable_count) # Ensure at least 1 syllable for valid words

    def calculate_grammar_score(self, text: str) -> float:
        """
        Calculates a grammar score based on common grammatical errors.
        This is a simplified implementation for demonstration purposes.
        """
        score = 100.0
        errors = 0

        # Check for common issues
        if "  " in text:  # Double spaces
            errors += 1
        if ",," in text or ".." in text:  # Double punctuation
            errors += 1
        if " ." in text or " ," in text:  # Space before punctuation
            errors += 1
        
        # Basic check for sentence start capitalization (simplified)
        sentences = self._split_sentences(text)
        for sentence in sentences:
            if sentence and sentence[0].islower():
                errors += 1

        # Check for "adalah merupakan" redundancy using Stanza
        if self.stanza_pipeline:
            doc = self.stanza_pipeline(text)
            for sentence in doc.sentences:
                lemmas = [word.lemma.lower() for word in sentence.words]
                # Check for "adalah merupakan" sequence
                for i in range(len(lemmas) - 1):
                    if lemmas[i] == "adalah" and lemmas[i+1] == "merupakan":
                        errors += 1 # Deduct points for this redundancy
                        break # Only count once per sentence

        if errors > 0:
            score = max(0, 100.0 - (float(errors) * 10))  # Deduct points for errors
        return score

    def calculate_readability_score(self, text: str) -> float:
        """
        Calculates a simplified readability score tailored for Indonesian text.
        This heuristic is based on average sentence length and average word length.
        """
        words = re.findall(r'\b\w+\b', text)
        word_count = len(words)
        sentences = self._split_sentences(text)
        sentence_count = len(sentences)

        if word_count == 0 or sentence_count == 0:
            return 0.0

        avg_sentence_length = float(word_count) / float(sentence_count)
        
        total_char_count = sum(len(w) for w in words)
        avg_word_length = float(total_char_count) / float(word_count)
        # Start with a base score of 100
        score = 100.0

        # Penalty for long sentences (common in formal Indonesian, but can reduce readability)
        if avg_sentence_length > 25: # More than 25 words is quite long
            score -= (avg_sentence_length - 25) * 1.5 # Penalty factor
        elif avg_sentence_length < 8: # Very short sentences can feel disjointed
            score -= (8 - avg_sentence_length) * 1.0 # Smaller penalty

        # Penalty for long words (less common in Indonesian but indicates complexity)
        if avg_word_length > 8: # Average word length over 8 is high for Indonesian
            score -= (avg_word_length - 8) * 5.0 # Stronger penalty for long words

        # Ensure the score is within the 0-100 range
        return max(0, min(100, score))

    def calculate_sentiment_score(self, text: str) -> float:
        """
        Calculates a basic sentiment score based on positive and negative keywords.
        """
        positive_words = {
            "baik", "bagus", "positif", "hebat", "efektif",
            "penting", "menarik", "membantu", "sukses", "maju",
            "senang", "gembira", "optimis", "berhasil", "luar biasa",
            "cemerlang", "sempurna", "menyenangkan", "berkualitas", "istimewa"
        }
        negative_words = {
            "buruk", "jelek", "negatif", "sulit", "masalah",
            "gagal", "kurang", "tidak", "kesalahan", "rumit",
            "sedih", "kecewa", "pesimis", "menyesal", "buruk",
            "mengerikan", "mengecewakan", "parah", "cacat", "gila"
        }
        negation_words = {"tidak", "bukan", "belum", "jangan"}

        pos_count = 0
        neg_count = 0

        if not self.stanza_pipeline:
            # Fallback to simple regex if stanza is not available
            words = re.findall(r'\b\w+\b', text.lower())
            for word in words:
                if word in positive_words:
                    pos_count += 1
                elif word in negative_words:
                    neg_count += 1
        else:
            doc = self.stanza_pipeline(text)
            for sentence in doc.sentences:
                for i, word_obj in enumerate(sentence.words):
                    lemma = word_obj.lemma.lower()
                    is_negated = False
                    # Check for negation in the preceding word
                    if i > 0 and sentence.words[i-1].lemma.lower() in negation_words:
                        is_negated = True

                    if lemma in positive_words:
                        if is_negated:
                            neg_count += 1 # Negated positive becomes negative
                        else:
                            pos_count += 1
                    elif lemma in negative_words:
                        if is_negated:
                            pos_count += 1 # Negated negative becomes positive
                        else:
                            neg_count += 1

        total_sentiment_words = pos_count + neg_count
        if total_sentiment_words == 0:
            return 50.0  # Neutral if no sentiment words found

        sentiment_ratio = float(pos_count) / float(total_sentiment_words)
        score = sentiment_ratio * 100.0
        return score

    def calculate_structure_score(self, text: str) -> float:
        """
        Calculates a basic structure score based on sentence count and paragraphing.
        """
        sentences = self._split_sentences(text)
        sentence_count = len(sentences)
        
        paragraphs = self._split_paragraphs(text)
        paragraph_count = len(paragraphs)

        score = 100.0
        errors = 0

        # Rule 1: Penalize very short texts
        if sentence_count < 3:
            errors += 2 # Significant penalty for very few sentences
        if paragraph_count < 1:
            errors += 3 # Very significant penalty for no paragraphs

        # Rule 2: Reward reasonable paragraph count
        if 3 <= paragraph_count <= 7: # Ideal range for typical short essays/responses
            score += 5 # Bonus for good paragraphing
        elif paragraph_count > 7: # Too many paragraphs might indicate fragmentation
            errors += 1

        # Rule 3: Reward reasonable average sentences per paragraph
        if paragraph_count > 0:
            avg_sentences_per_paragraph = sentence_count / paragraph_count
            if not (3 <= avg_sentences_per_paragraph <= 7): # Ideal range
                errors += 1
        
        # Rule 4: Sentence length variation (using standard deviation)
        sentence_word_counts = [len(s.split()) for s in sentences if s.strip()]
        if len(sentence_word_counts) > 1:
            std_dev_sentence_length = math.sqrt(sum((x - (sum(sentence_word_counts) / len(sentence_word_counts))) ** 2 for x in sentence_word_counts) / len(sentence_word_counts))
            # Reward moderate variation, penalize very low or very high variation
            if 3 <= std_dev_sentence_length <= 10: # Arbitrary range for good variation
                score += 5
            else:
                errors += 1
        elif len(sentence_word_counts) == 1: # Single sentence, no variation
            errors += 0.5 # Small penalty

        # Apply penalties
        if errors > 0:
            score = max(0, score - (float(errors) * 10))
        
        # Ensure score is within 0-100 range
        score = max(0, min(100, score))
        return score

    def calculate_complexity_score(self, text: str) -> float:
        """
        Calculates a complexity score based on lexical diversity and syntactic complexity.
        """
        if not text.strip():
            return 0.0

        words = re.findall(r'\b\w+\b', text.lower())
        word_count = len(words)

        if word_count == 0:
            return 0.0

        score = 0.0

        # 1. Lexical Diversity (using lemmas for TTR)
        if self.stanza_pipeline:
            doc = self.stanza_pipeline(text)
            lemmas = [word.lemma.lower() for sentence in doc.sentences for word in sentence.words if word.lemma]
            if lemmas:
                unique_lemmas = set(lemmas)
                ttr = len(unique_lemmas) / len(lemmas)
                score += ttr * 40 # Weight for lexical diversity (e.g., up to 40 points)
        else:
            # Fallback to simple TTR if Stanza not available
            unique_words = set(words)
            ttr = len(unique_words) / word_count
            score += ttr * 30 # Lower weight for less accurate TTR

        # 2. Syntactic Complexity (simplified: average verbs per sentence)
        if self.stanza_pipeline:
            doc = self.stanza_pipeline(text)
            total_verbs = 0
            sentence_count = len(doc.sentences)
            for sentence in doc.sentences:
                for word in sentence.words:
                    if word.upos == 'VERB':
                        total_verbs += 1
            
            if sentence_count > 0:
                avg_verbs_per_sentence = total_verbs / sentence_count
                # Scale this to contribute to the score
                # Assuming 1-3 verbs per sentence is common, higher indicates more complexity
                score += min(avg_verbs_per_sentence * 10, 30) # Max 30 points for syntactic complexity
        else:
            # Fallback: use average word length as a very rough proxy for syntactic complexity
            total_char_count = sum(len(word) for word in words)
            avg_word_length = float(total_char_count) / float(word_count)
            score += min(avg_word_length * 5, 20) # Max 20 points

        # 3. Overall Length (still a factor for complexity)
        # Scale word_count to contribute to the score, e.g., up to 30 points
        score += min(word_count / 100.0 * 30, 30)

        # Ensure score is within 0-100 range
        score = max(0, min(100, score))
        return score

    def analyze(self, text: str) -> dict:
        """
        Perform comprehensive text analysis returning all metrics.
        """
        return {
            'keywords': self.extract_keywords(text),
            'key_sentences': self.extract_key_sentences(text),
            'text_stats': self.calculate_text_stats(text),
            'grammar_score': self.calculate_grammar_score(text),
            'readability_score': self.calculate_readability_score(text),
            'sentiment_score': self.calculate_sentiment_score(text),
            'structure_score': self.calculate_structure_score(text),
            'complexity_score': self.calculate_complexity_score(text)
        }
