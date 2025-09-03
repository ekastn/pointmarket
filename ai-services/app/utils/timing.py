from contextlib import contextmanager
import time


def _log_debug(msg: str) -> None:
    try:
        # Prefer Flask logger when available (respects DEBUG level)
        from flask import current_app  # type: ignore
        logger = getattr(current_app, "logger", None)
        if logger is not None:
            logger.debug(msg)
            return
    except Exception:
        pass
    # Fallback to stdout if no Flask context/logger
    print(msg)


@contextmanager
def phase(name: str):
    """
    Context manager to time code blocks and emit a debug log line.
    Usage:
        with phase("parse"):
            ...
    """
    start = time.perf_counter()
    try:
        yield
    finally:
        elapsed_ms = (time.perf_counter() - start) * 1000.0
        _log_debug(f"[perf] {name} {elapsed_ms:.2f}ms")

