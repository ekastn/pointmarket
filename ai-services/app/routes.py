from flask import Blueprint, request, jsonify, Response
from .services.engine_service import motivational_engine
from .dtos import AnalysisRequest
from typing import Tuple, cast
from .database import db
from sqlalchemy import text as sa_text

api_bp = Blueprint('api', __name__)

@api_bp.route('/health', methods=['GET'])
def health_handler() -> Tuple[Response, int]:
    """Liveness/readiness probe.
    Reports DB connectivity and NLP readiness (lexicon + Stanza).
    Always returns 200 with a status payload; callers can inspect fields.
    """
    # DB check
    db_ok = False
    try:
        db.session.execute(sa_text("SELECT 1"))
        db_ok = True
    except Exception:
        db_ok = False

    nlp = motivational_engine.nlp_service
    nlp_ready = bool(getattr(nlp, 'is_ready', False))
    stanza_loaded = bool(getattr(nlp, 'stanza_pipeline', None))
    strategies = list(getattr(nlp, '_strategies', {}).keys())

    status = 'ok' if (db_ok and nlp_ready) else ('degraded' if (db_ok or nlp_ready) else 'down')

    return jsonify({
        'status': status,
        'ready': bool(db_ok and nlp_ready),
        'components': {
            'db': {'ok': db_ok},
            'nlp_service': {
                'ready': nlp_ready,
                'stanza_loaded': stanza_loaded,
                'strategies': strategies,
            }
        }
    }), 200

@api_bp.route('/nlp/predict', methods=['POST'])
def analyze_handler() -> Tuple[Response, int]:
    if not request.is_json:
        return jsonify({"error": "Request must be JSON"}), 400

    # Use cast to give the type checker a hint
    data = cast(AnalysisRequest, request.get_json())
    
    if not data or 'text' not in data:
        return jsonify({"error": "Missing 'text' key in request body"}), 400

    # Pass the typed dictionary to the service
    result = motivational_engine.get_nlp_profile(data)
    
    return jsonify(result), 200
