from typing import TypedDict, Optional

class AnalysisRequest(TypedDict):
    text: str
    strategy: Optional[str]
    context_type: Optional[str]
