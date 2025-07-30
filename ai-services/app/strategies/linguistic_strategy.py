import stanza
from .base_strategy import BaseVarkStrategy

class LinguisticStrategy(BaseVarkStrategy):
    """
    VARK analysis strategy based on linguistic features (POS ratios, etc.).
    """
    def __init__(self, stanza_pipeline):
        self.nlp = stanza_pipeline

    def _extract_metrics(self, doc) -> dict:
        num_tokens = 0
        num_content_words = 0
        num_verbs = 0
        num_adjectives = 0
        
        if len(doc.sentences) == 0:
            return {"avg_sent_length": 0, "lexical_density": 0, "verb_ratio": 0, "adj_ratio": 0}

        for sentence in doc.sentences:
            for word in sentence.words:
                if not word.pos == 'PUNCT':
                    num_tokens += 1
                if word.pos in ['NOUN', 'VERB', 'ADJ', 'ADV']:
                    num_content_words += 1
                if word.pos == 'VERB':
                    num_verbs += 1
                if word.pos == 'ADJ':
                    num_adjectives += 1
        
        num_sentences = len(doc.sentences)
        
        metrics = {
            "avg_sent_length": num_tokens / num_sentences if num_sentences > 0 else 0,
            "lexical_density": num_content_words / num_tokens if num_tokens > 0 else 0,
            "verb_ratio": num_verbs / num_tokens if num_tokens > 0 else 0,
            "adj_ratio": num_adjectives / num_tokens if num_tokens > 0 else 0
        }
        return metrics

    def _score_from_metrics(self, metrics: dict) -> dict:
        # Heuristic scoring based on linguistic metrics. Weights can be tuned.
        raw_scores = {'Visual': 0, 'Aural': 0.1, 'Read/Write': 0, 'Kinesthetic': 0}
        raw_scores["Read/Write"] = (metrics.get("lexical_density", 0) * 5) + (metrics.get("avg_sent_length", 0) * 0.1)
        raw_scores["Kinesthetic"] = metrics.get("verb_ratio", 0) * 10
        raw_scores["Visual"] = metrics.get("adj_ratio", 0) * 10
        return raw_scores

    def analyze(self, data: dict) -> dict:
        text = data.get('text', '')
        doc = self.nlp(text)
        metrics = self._extract_metrics(doc)
        raw_scores = self._score_from_metrics(metrics)
        
        total_score = sum(raw_scores.values())
        if total_score == 0: return {s: 0.25 for s in raw_scores.keys()}
        
        return {s: score / total_score for s, score in raw_scores.items()}
