from app.models import NlpLexicon
from typing import Dict, DefaultDict
from collections import defaultdict


VARK_Lexicon = Dict[str, Dict[str, int]]

class LexiconStore:
    def get_all(self) -> VARK_Lexicon:
        """
        Fetches all lexicon data using the ORM and formats it.
        """
        records = NlpLexicon.query.all()
        
        lexicon: DefaultDict[str, Dict[str, int]] = defaultdict(dict)
        for record in records:
            lexicon[record.style][record.keyword] = record.weight
            
        return dict(lexicon)
