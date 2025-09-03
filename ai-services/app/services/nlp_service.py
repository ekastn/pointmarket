import stanza
from app.strategies.keyword_strategy import KeywordStrategy
from app.strategies.linguistic_strategy import LinguisticStrategy
from app.strategies.hybrid_strategy import HybridStrategy
from app.stores.lexicon_store import LexiconStore
from .text_analysis_service import TextAnalysisService
from app.utils.timing import phase
from .context import RequestContext
import re
from flask import current_app

class NlpService:
    def __init__(self):
        self._strategies = {}
        self.default_strategy = "hybrid" 
        self.is_ready = False
        self.text_analyzer = None
        self.stanza_pipeline = None

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
                self.stanza_pipeline = stanza_pipeline

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
                current_app.logger.info("NlpService is ready with all strategies and text analyzer.")
            else:
                current_app.logger.warning("NlpService FAILED to load lexicon.")

    def _build_context(self, text: str) -> RequestContext:
        """Build per-request context, using Stanza when available, else regex fallback."""
        if self.stanza_pipeline:
            doc = self.stanza_pipeline(text)
            sentences = [s.text for s in doc.sentences]
            tokens = [w.text for s in doc.sentences for w in s.words]
            lemmas = [w.lemma.lower() if getattr(w, 'lemma', None) else w.text.lower() for s in doc.sentences for w in s.words]
            upos = [getattr(w, 'upos', '') for s in doc.sentences for w in s.words]
            return RequestContext(text=text, doc=doc, sentences=sentences, lemmas=lemmas, upos=upos, tokens=tokens)
        # Fallback simple parsing
        sentences = [seg.strip() for seg in re.split(r'[.!?]+', text) if seg.strip()]
        tokens = re.findall(r'\b\w+\b', text)
        lemmas = [t.lower() for t in tokens]
        upos: list[str] = []
        return RequestContext(text=text, doc=None, sentences=sentences, lemmas=lemmas, upos=upos, tokens=tokens)

    def analyze(self, data: dict, strategy_name: str | None  = None):
        if not self.is_ready:
            raise RuntimeError("NlpService has not been initialized.")

        if not strategy_name:
            strategy_name = self.default_strategy
        
        analyzer = self._strategies.get(strategy_name)

        if not analyzer:
            return {"error": f"Strategy '{strategy_name}' not found."}

        # Prepare text (apply optional truncation) and build context
        original_text = data.get('text', '')
        cfg = current_app.config if current_app else {}
        max_chars = int(cfg.get('MAX_INPUT_CHARS', 0)) if cfg else 0
        truncate_enabled = bool(cfg.get('PERF_TRUNCATE_ENABLED', False)) if cfg else False
        doc_reuse_enabled = bool(cfg.get('PERF_DOC_REUSE_ENABLED', True)) if cfg else True

        effective_text = original_text
        truncated = False
        if max_chars > 0 and isinstance(original_text, str) and len(original_text) > max_chars:
            if truncate_enabled:
                effective_text = original_text[:max_chars]
                truncated = True
            else:
                # Leave text as-is when truncation disabled
                pass

        ctx = self._build_context(effective_text) if doc_reuse_enabled else None

        # Get VARK scores from strategy
        with phase(f"strategy.{strategy_name}"):
            data_for_analysis = dict(data)
            data_for_analysis['text'] = effective_text
            vark_scores = analyzer.analyze(data_for_analysis, ctx)
        
        # Get comprehensive text analysis
        text = effective_text
        
        text_analysis = {}
        if self.text_analyzer:
            with phase("text_analysis"):
                text_analysis = self.text_analyzer.analyze(text, ctx)
        
        # Combine results
        with phase("assembly"):
            result = {
                **vark_scores,
                **text_analysis
            }
            if truncated:
                result['truncated'] = True
        
        return result

nlp_service = NlpService()
