from flask import current_app
import stanza
from app.strategies.keyword_strategy import KeywordStrategy
from app.strategies.linguistic_strategy import LinguisticStrategy
from app.strategies.hybrid_strategy import HybridStrategy
from app.stores.lexicon_store import LexiconStore

class NlpService:
    def __init__(self):
        self._strategies = {}
        self.default_strategy = "hybrid" 
        self.is_ready = False

    def init_app(self, app):
        with app.app_context():
            lexicon_store = LexiconStore()
            keyword_lexicon_cache = lexicon_store.get_all()

            if keyword_lexicon_cache:
                current_app.logger.info("Initializing Stanza pipeline...")
                stanza_pipeline = stanza.Pipeline(lang='id', processors='tokenize,lemma,pos')
                current_app.logger.info("Stanza pipeline initialized.")

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
                print("NlpService is ready with all strategies.")
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

        return analyzer.analyze(data)

nlp_service = NlpService()
