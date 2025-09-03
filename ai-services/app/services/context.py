from dataclasses import dataclass
from typing import List, Optional, Any


@dataclass
class RequestContext:
    """
    Per-request analysis context to hold the parsed document and
    commonly used derived features to avoid repeated NLP work.
    """
    text: str
    doc: Optional[Any]
    sentences: List[str]
    lemmas: List[str]
    upos: List[str]
    tokens: List[str]

