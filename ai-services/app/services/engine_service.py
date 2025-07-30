from .nlp_service import nlp_service

class MotivationalEngineService:
    def __init__(self):
        self.nlp_service = nlp_service

    def init_app(self, app):
        with app.app_context():
            self.nlp_service.init_app(app)

    def get_nlp_profile(self, data: dict):
        strategy_name = data.get('strategy')
        text = data.get('text', '')
        analysis_result = self.nlp_service.analyze(data, strategy_name)
        
        response = {
            "strategy_used": strategy_name or self.nlp_service.default_strategy,
            "word_count": len(text.split()),
            "scores": analysis_result
        }
        return response


# Create a single instance to be used across the application
motivational_engine = MotivationalEngineService()
