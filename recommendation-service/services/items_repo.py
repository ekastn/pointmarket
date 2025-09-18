from typing import Sequence, List

from sqlalchemy import select
from sqlalchemy.orm import Session, sessionmaker

from services.db import get_engine
from models import Item
import random


_engine = get_engine()
SessionLocal = sessionmaker(bind=_engine) if _engine is not None else None


def get_session() -> Session:
    if SessionLocal is None:
        raise RuntimeError("Database engine is not initialized")
    return SessionLocal()


def get_items_for_state_action(
    session: Session, state: str, action_code: int, limit: int
) -> List[Item]:
    stmt = (
        select(Item)
        .where(Item.is_active == True, Item.state == state, Item.action_code == action_code)
        .limit(50)
    )
    rows: Sequence[Item] = session.execute(stmt).scalars().all()
    if not rows:
        return []
    if len(rows) <= limit:
        return list(rows)
    return random.sample(list(rows), limit)


def get_fallback_items_for_action(session: Session, action_code: int, limit: int) -> List[Item]:
    stmt = select(Item).where(Item.is_active == True, Item.action_code == action_code).limit(50)
    rows: Sequence[Item] = session.execute(stmt).scalars().all()
    if not rows:
        return []
    if len(rows) <= limit:
        return list(rows)
    return random.sample(list(rows), limit)

