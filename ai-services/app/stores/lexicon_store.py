from app.models import NlpLexicon
from typing import Dict, DefaultDict, List
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
    
    def get_by_context(self, context: str) -> List[Dict]:
        """
        Fetches lexicon data filtered by context.
        Returns list of dictionaries with keyword and weight information.
        """
        try:
            records = NlpLexicon.query.filter_by(context=context).all()
            
            result = []
            for record in records:
                result.append({
                    'keyword': record.keyword,
                    'weight': record.weight,
                    'style': record.style,
                    'context': record.context
                })
                
            return result
        except Exception as e:
            print(f"Error fetching context lexicon: {e}")
            return []
