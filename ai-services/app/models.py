from .database import db
from sqlalchemy import String, DateTime, func, UniqueConstraint, Enum
from sqlalchemy.orm import Mapped, mapped_column
from datetime import datetime

class NlpLexicon(db.Model):
    __tablename__ = 'nlp_lexicon'
    
    id: Mapped[int] = mapped_column(primary_key=True)
    keyword: Mapped[str] = mapped_column(String(255), nullable=False)
    style: Mapped[str] = mapped_column(Enum('Visual', 'Aural', 'Read/Write', 'Kinesthetic', name='vark_style_enum'), nullable=False)
    weight: Mapped[int] = mapped_column(default=1)
    created_at: Mapped[datetime] = mapped_column(DateTime, nullable=False, default=func.current_timestamp())
    updated_at: Mapped[datetime] = mapped_column(DateTime, nullable=False, default=func.current_timestamp(), onupdate=func.current_timestamp())
    
    __table_args__ = (UniqueConstraint('keyword', 'style', name='unique_keyword_style'),)