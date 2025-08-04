from .nlp_service import nlp_service
from ..dtos import AnalysisResponse

class MotivationalEngineService:
    def __init__(self):
        self.nlp_service = nlp_service

    def init_app(self, app):
        with app.app_context():
            self.nlp_service.init_app(app)

    def get_nlp_profile(self, data: dict) -> AnalysisResponse:
        strategy_name = data.get('strategy')
        text = data.get('text', '')
        context_type = data.get('context_type', '')
        
        # Get comprehensive analysis from NLP service
        analysis_result = self.nlp_service.analyze(data, strategy_name)
        
        # Extract VARK scores (maintaining backward compatibility)
        vark_scores = {
            key: value for key, value in analysis_result.items() 
            if key in ['Visual', 'Aural', 'Read/Write', 'Kinesthetic']
        }
        
        # Build enhanced response
        response: AnalysisResponse = {
            "strategy_used": strategy_name or self.nlp_service.default_strategy,
            "word_count": len(text.split()),
            "scores": vark_scores,
            "keywords": analysis_result.get('keywords', []),
            "key_sentences": analysis_result.get('key_sentences', []),
            "text_stats": analysis_result.get('text_stats', {
                'wordCount': len(text.split()),
                'sentenceCount': 1,
                'avgWordLength': 0.0,
                'readingTime': 1
            })
        }
        
        return response


# Create a single instance to be used across the application
motivational_engine = MotivationalEngineService()
