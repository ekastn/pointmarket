from .base_strategy import BaseVarkStrategy
from .keyword_strategy import KeywordStrategy
from .linguistic_strategy import LinguisticStrategy
import logging

class HybridStrategy(BaseVarkStrategy):
    """
    A hybrid strategy that combines results from multiple sub-strategies.
    """
    def __init__(self, keyword_strategy: KeywordStrategy, linguistic_strategy: LinguisticStrategy):
        # Inject other strategies as dependencies
        self.keyword_strategy = keyword_strategy
        self.linguistic_strategy = linguistic_strategy
        
        # Define the weights for combining scores
        self.weights = {"keyword": 0.5, "linguistic": 0.3, "context": 0.15, "interaction": 0.05}
        logging.getLogger(__name__).info("HybridStrategy initialized.")

    def _get_contextual_bias(self, context_type: str) -> dict:
        bias: dict = {'Visual': 0, 'Aural': 0, 'Read/Write': 0, 'Kinesthetic': 0}
        if context_type == 'Matematika':
            bias['Visual'] += 0.1; bias['Read/Write'] += 0.1
        elif context_type == 'Biologi':
            bias['Visual'] += 0.15
        elif context_type == 'Praktikum':
            bias['Kinesthetic'] += 0.2
        return bias

    def _get_interaction_bias(self, text: str) -> dict:
        bias: dict = {'Visual': 0, 'Aural': 0, 'Read/Write': 0, 'Kinesthetic': 0}
        if '?' in text:
            bias['Aural'] += 0.1
        if 'ringkasan' in text or 'catat' in text:
            bias['Read/Write'] += 0.1
        return bias

    def analyze(self, data: dict, ctx=None) -> dict:
        text = data.get('text', '')
        context_type = data.get('context_type', '')

        # 1. Get scores from individual strategies
        keyword_scores = self.keyword_strategy.analyze(data, ctx)
        linguistic_scores = self.linguistic_strategy.analyze(data, ctx)

        # 2. Get biases
        context_bias = self._get_contextual_bias(context_type)
        interaction_bias = self._get_interaction_bias(text)
        
        # 3. Combine all scores with a weighted average
        combined_scores = {s: 0 for s in keyword_scores.keys()}
        for style in combined_scores:
            combined_scores[style] += keyword_scores.get(style, 0) * self.weights["keyword"]
            combined_scores[style] += linguistic_scores.get(style, 0) * self.weights["linguistic"]
            combined_scores[style] += context_bias.get(style, 0) * self.weights["context"]
            combined_scores[style] += interaction_bias.get(style, 0) * self.weights["interaction"]
            
        # 4. Normalize the final result
        total_score = sum(combined_scores.values())
        if total_score == 0: return {s: 0.25 for s in combined_scores.keys()}
        
        return {s: score / total_score for s, score in combined_scores.items()}
