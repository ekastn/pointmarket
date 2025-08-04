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
        
    def extract_keywords(self, text: str, context_type: str = None, max_keywords: int = 10) -> List[str]:
        """
        Extract keywords using a combination of frequency analysis and contextual relevance.
        """
        if not text.strip():
            return []
            
        # Method 1: Extract context-specific keywords from database
        context_keywords = self._extract_context_keywords(text, context_type)
        
        # Method 2: Extract keywords using NLP techniques
        nlp_keywords = self._extract_nlp_keywords(text, max_keywords - len(context_keywords))
        
        # Combine and deduplicate
        all_keywords = list(dict.fromkeys(context_keywords + nlp_keywords))
        
        return all_keywords[:max_keywords]
    
    def _extract_context_keywords(self, text: str, context_type: str) -> List[str]:
        """Extract keywords that are relevant to the specific context."""
        if not context_type:
            return []
            
        try:
            # Get predefined keywords for this context
            lexicon_data = self.lexicon_store.get_by_context(context_type)
            if not lexicon_data:
                return []
                
            found_keywords = []
            text_lower = text.lower()
            
            for item in lexicon_data:
                keyword = item.get('keyword', '').lower()
                if keyword and keyword in text_lower:
                    found_keywords.append(item.get('keyword', ''))
                    
            return found_keywords
            
        except Exception as e:
            print(f"Error extracting context keywords: {e}")
            return []
    
    def _extract_nlp_keywords(self, text: str, max_keywords: int) -> List[str]:
        """Extract keywords using NLP techniques with Stanza."""
        if not self.stanza_pipeline or max_keywords <= 0:
            return self._extract_frequency_keywords(text, max_keywords)
            
        try:
            # Process text with Stanza
            doc = self.stanza_pipeline(text)
            
            # Extract meaningful words (nouns, adjectives, verbs)
            meaningful_words = []
            for sentence in doc.sentences:
                for word in sentence.words:
                    # Filter by POS tags and exclude stop words
                    if (word.upos in ['NOUN', 'ADJ', 'VERB'] and 
                        word.lemma.lower() not in self.stop_words and 
                        len(word.lemma) > 3):
                        meaningful_words.append(word.lemma.lower())
            
            # Count frequency and get top words
            word_freq = Counter(meaningful_words)
            keywords = [word for word, freq in word_freq.most_common(max_keywords)]
            
            return keywords
            
        except Exception as e:
            print(f"Error in NLP keyword extraction: {e}")
            return self._extract_frequency_keywords(text, max_keywords)
    
    def _extract_frequency_keywords(self, text: str, max_keywords: int) -> List[str]:
        """Fallback: Extract keywords using simple frequency analysis."""
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
        """Split text into sentences."""
        # Use regex to split by sentence endings
        sentences = re.split(r'[.!?]+', text)
        
        # Clean and filter sentences
        cleaned_sentences = []
        for sentence in sentences:
            cleaned = sentence.strip()
            if len(cleaned) > 10:  # Only meaningful sentences
                cleaned_sentences.append(cleaned)
                
        return cleaned_sentences
    
    def _score_sentence(self, sentence: str, position: int, total_sentences: int, full_text: str) -> float:
        """Score a sentence based on various factors."""
        score = 0.0
        
        # Position score (first and last sentences are often important)
        if position == 0:
            score += 0.3  # First sentence bonus
        elif position == total_sentences - 1:
            score += 0.2  # Last sentence bonus
            
        # Length score (moderate length sentences are often key)
        words = len(sentence.split())
        if 10 <= words <= 25:
            score += 0.2
        elif words > 25:
            score += 0.1
            
        # Keyword density score
        keywords = self._extract_frequency_keywords(full_text, 10)
        sentence_lower = sentence.lower()
        keyword_count = sum(1 for keyword in keywords if keyword in sentence_lower)
        score += min(keyword_count * 0.1, 0.3)  # Cap at 0.3
        
        # Question or statement with important words
        if '?' in sentence:
            score += 0.1
        if any(word in sentence_lower for word in ['penting', 'utama', 'kesimpulan', 'hasil']):
            score += 0.15
            
        return score
    
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
    
    def analyze(self, text: str, context_type: str = None) -> dict:
        """
        Perform comprehensive text analysis returning all metrics.
        """
        return {
            'keywords': self.extract_keywords(text, context_type),
            'key_sentences': self.extract_key_sentences(text),
            'text_stats': self.calculate_text_stats(text)
        }
