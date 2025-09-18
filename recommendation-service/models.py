from enum import Enum

from sqlalchemy import (
    BigInteger,
    Boolean,
    Column,
    Enum as SAEnum,
    Index,
    Integer,
    String,
    text,
)
from sqlalchemy.orm import declarative_base


Base = declarative_base()


class RefType(str, Enum):
    mission = "mission"
    product = "product"
    reward = "reward"
    coaching = "coaching"
    punishment = "punishment"
    badge = "badge"


class Item(Base):
    __tablename__ = "items"

    id = Column(BigInteger, primary_key=True)
    state = Column(String(200), nullable=False)
    action_code = Column(Integer, nullable=False)
    ref_type = Column(SAEnum(RefType, name="ref_type"), nullable=False)
    ref_id = Column(BigInteger, nullable=False)
    is_active = Column(Boolean, nullable=False, server_default=text("1"))

    __table_args__ = (
        Index("idx_items_state_action", "state", "action_code"),
        Index("idx_items_ref", "ref_type", "ref_id"),
        Index(
            "uq_items_state_action_ref",
            "state",
            "action_code",
            "ref_type",
            "ref_id",
            unique=True,
        ),
    )

