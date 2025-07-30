from flask import Blueprint, request, jsonify, Response
from .services.engine_service import motivational_engine
from .dtos import AnalysisRequest
from typing import Tuple, cast

api_bp = Blueprint('api', __name__, url_prefix='/api')

@api_bp.route('/analyze', methods=['POST'])
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
