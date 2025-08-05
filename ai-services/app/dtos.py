from typing import TypedDict, Optional, List

class AnalysisRequest(TypedDict):
    text: str
    strategy: Optional[str]
    context_type: Optional[str]

class TextStats(TypedDict):
    wordCount: int
    sentenceCount: int
    avgWordLength: float
    readingTime: int

class AnalysisResponse(TypedDict):
    strategy_used: str
    word_count: int
    scores: dict
    keywords: List[str]
    key_sentences: List[str]
    text_stats: TextStats
    grammar_score: float
    readability_score: float
    sentiment_score: float
    structure_score: float
    complexity_score: float