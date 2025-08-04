from flask import current_app
import stanza
from app.strategies.keyword_strategy import KeywordStrategy
from app.strategies.linguistic_strategy import LinguisticStrategy
from app.strategies.hybrid_strategy import HybridStrategy
from app.stores.lexicon_store import LexiconStore
from .text_analysis_service import TextAnalysisService

class NlpService:
    def __init__(self):
        self._strategies = {}
        self.default_strategy = "hybrid" 
        self.is_ready = False
        self.text_analyzer = None

    def init_app(self, app):
        with app.app_context():
            lexicon_store = LexiconStore()
            keyword_lexicon_cache = lexicon_store.get_all()

            if keyword_lexicon_cache:
                current_app.logger.info("Initializing Stanza pipeline...")
                stanza_pipeline = stanza.Pipeline(lang='id', processors='tokenize,lemma,pos')
                current_app.logger.info("Stanza pipeline initialized.")

                # Initialize text analyzer with Stanza pipeline
                self.text_analyzer = TextAnalysisService(stanza_pipeline)

                keyword_strat = KeywordStrategy(lexicon=keyword_lexicon_cache, stanza_pipeline=stanza_pipeline)
                linguistic_strat = LinguisticStrategy(stanza_pipeline=stanza_pipeline)

                hybrid_strat = HybridStrategy(
                    keyword_strategy=keyword_strat, 
                    linguistic_strategy=linguistic_strat
                )

                self._strategies = {
                    "keyword": keyword_strat,
                    "linguistic": linguistic_strat,
                    "hybrid": hybrid_strat
                }
                self.is_ready = True
                print("NlpService is ready with all strategies and text analyzer.")
            else:
                print("NlpService FAILED to load lexicon.")

    def analyze(self, data: dict, strategy_name: str | None  = None):
        if not self.is_ready:
            raise RuntimeError("NlpService has not been initialized.")

        if not strategy_name:
            strategy_name = self.default_strategy
        
        analyzer = self._strategies.get(strategy_name)

        if not analyzer:
            return {"error": f"Strategy '{strategy_name}' not found."}

        # Get VARK scores from strategy
        vark_scores = analyzer.analyze(data)
        
        # Get comprehensive text analysis
        text = data.get('text', '')
        context_type = data.get('context_type', '')
        
        text_analysis = {}
        if self.text_analyzer:
            text_analysis = self.text_analyzer.analyze(text, context_type)
        
        # Combine results
        result = {
            **vark_scores,
            'keywords': text_analysis.get('keywords', []),
            'key_sentences': text_analysis.get('key_sentences', []),
            'text_stats': text_analysis.get('text_stats', {})
        }
        
        return result

nlp_service = NlpService()
