# Abstract base class for all strategies.
from abc import ABC, abstractmethod

class BaseVarkStrategy(ABC):
    @abstractmethod
    def analyze(self, data: dict) -> dict:
        # Each strategy must implement this method.
        # It accepts a dictionary to be more flexible (e.g., for context_type).
        pass
