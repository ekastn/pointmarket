import stanza
from .base_strategy import BaseVarkStrategy

class KeywordStrategy(BaseVarkStrategy):
    """
    VARK analysis strategy based on keyword matching.
    """
    def __init__(self, lexicon: dict, stanza_pipeline):
        self.lexicon = lexicon
        self.nlp = stanza_pipeline

    def _preprocess(self, text: str) -> list:
        """
        Processes the text using Stanza to get a clean list of lemmas.
        """
        doc = self.nlp(text)
        lemmas = []
        
        # Iterate through sentences and words to extract lemmas
        for sentence in doc.sentences:
            for word in sentence.words:
                if getattr(word, 'lemma', None):
                    lemmas.append(word.lemma.lower())
                else:
                    lemmas.append(word.text.lower())
        
        return lemmas

    def _calculate_scores(self, lemmas: list) -> dict:
        """
        Calculates the VARK scores based on the lexicon and lemmas.
        Returns a dictionary with VARK styles as keys and normalized scores as values.
        """
        raw_scores = {s: 0 for s in self.lexicon.keys()}
        lemma_set = set(lemmas)
        lemmas_text = " ".join(lemmas)
        padded = f" {lemmas_text} " if lemmas_text else ""
        
        for style, keywords in self.lexicon.items():
            for keyword, weight in keywords.items():
                kw = keyword.lower()
                if " " in kw:
                    # Phrase match on token boundaries
                    if padded and f" {kw} " in padded:
                        raw_scores[style] += weight
                else:
                    if kw in lemma_set:
                        raw_scores[style] += weight
        
        total_score = sum(raw_scores.values())

        # If total score is 0, return a uniform distribution
        if total_score == 0: return {s: 0.25 for s in self.lexicon.keys()}
        
        return {s: score / total_score for s, score in raw_scores.items()}

    def analyze(self, data: dict, ctx=None) -> dict:
        text = data.get('text', '')
        if ctx is not None and getattr(ctx, 'lemmas', None):
            lemmas = ctx.lemmas
        else:
            lemmas = self._preprocess(text)
        scores = self._calculate_scores(lemmas)
        return scores
