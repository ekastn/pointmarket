from flask import Blueprint, jsonify, request

from services.qlearning import ql_system

bp = Blueprint("recommendations", __name__)


@bp.get("")
def get_recommendations_query():
    siswa_id = request.args.get("student_id")
    if not siswa_id:
        return jsonify({"error": "Missing required query parameter: student_id"}), 400
    return get_recommendations(siswa_id)


@bp.get("/items")
def get_action_items_query():
    siswa_id = request.args.get("student_id")
    action = request.args.get("action")
    if not siswa_id:
        return jsonify({"error": "Missing required query parameter: student_id"}), 400
    if action is None:
        return jsonify({"error": "Missing required query parameter: action"}), 400
    try:
        action_code = int(action)
    except ValueError:
        return jsonify({"error": "Invalid action, must be an integer"}), 400
    return get_action_recommendations(siswa_id, action_code)


def get_recommendations(siswa_id):
    try:
        current_state = ql_system.get_student_current_state(siswa_id)
        if not current_state:
            return jsonify(
                {"error": "Unable to determine student state or student not found"}
            )

        action_recs = {}
        key = (siswa_id, current_state)
        data = None

        # In-memory Q-values first
        q_values = ql_system.q_records.get(key)
        actions = ql_system.get_positive_actions(q_values) if q_values else []

        # Fallback to DB saved q_table_results
        if not actions:
            db_row = ql_system.get_db_q_values(siswa_id, state=current_state)
            if db_row:
                actions = ql_system.get_positive_actions(db_row["q_values"])
                # Use the state from DB if different (trained state)
                if db_row.get("state"):
                    current_state = db_row["state"]

        if actions:
            if data is None:
                data = ql_system.load_data_from_db()
                if not data:
                    return jsonify({"error": "Failed to load intervention data"})
            try:
                for action_code, q_value in actions:
                    items_refs = ql_system.get_multiple_recommendations(
                        action_code, data, num_items=3, student_state=current_state
                    )
                    action_name = ql_system.action_labels.get(action_code, "Unknown")
                    action_recs[action_name] = {
                        "action_code": action_code,
                        "q_value": round(q_value, 4),
                        "items_refs": items_refs,
                        "items": [],  # legacy field kept for compatibility (empty)
                        "item_count": len(items_refs),
                    }
            finally:
                # best-effort close the session if present
                try:
                    sess = data.get("session")
                    if sess is not None:
                        sess.close()
                except Exception:
                    pass

        if all(len(v.get("items_refs", [])) == 0 for v in action_recs.values()):
            defaults = ql_system.get_default_recommendations(current_state)
            message = "Showing default recommendations - no trained Q-values available"
            for rec in defaults:
                action_name = rec["action"]
                action_code = rec["action_code"]
                if action_name not in action_recs:
                    action_recs[action_name] = {
                        "action_code": action_code,
                        "q_value": rec.get("confidence", 0.0),
                        "items_refs": [],
                        "items": [],
                        "item_count": 0,
                    }
        else:
            message = f"Showing {len(action_recs)} actions with positive Q-values"

        return jsonify(
            {
                "siswa_id": siswa_id,
                "current_state": current_state,
                "total_recommendations": sum(v.get("item_count", 0) for v in action_recs.values()),
                "recommendations": [],
                "action_recommendations": action_recs,
                "message": message,
                "summary": {
                    "actions_with_recommendations": len(action_recs),
                    "total_items": sum(a.get("item_count", 0) for a in action_recs.values()),
                    "has_trained_q_values": (
                        key in ql_system.q_records if key else False
                    ),
                    "all_q_values_zero_or_negative": key in ql_system.q_records
                    and len(action_recs) == 0,
                },
            }
        )
    except Exception as e:
        return jsonify({"error": f"Failed to get recommendations: {str(e)}"})


def get_action_recommendations(siswa_id, action_code):
    try:
        if action_code not in ql_system.action_space:
            return jsonify({"error": f"Invalid action code: {action_code}"})
        current_state = ql_system.get_student_current_state(siswa_id)
        if not current_state:
            return jsonify(
                {"error": "Unable to determine student state or student not found"}
            )

        key = (siswa_id, current_state)
        q_value = None
        # In-memory
        if key in ql_system.q_records and action_code in ql_system.q_records[key]:
            q_value = float(ql_system.q_records[key][action_code])
        # Fallback to DB row
        if q_value is None or q_value <= 0.0:
            db_row = ql_system.get_db_q_values(siswa_id, state=current_state)
            if db_row and action_code in db_row["q_values"]:
                q_value = float(db_row["q_values"][action_code])
                # prefer trained state if provided
                if db_row.get("state"):
                    current_state = db_row["state"]

        if not q_value or q_value <= 0.0:
            return jsonify(
                {
                    "siswa_id": siswa_id,
                    "current_state": current_state,
                    "action": ql_system.action_labels.get(action_code, "Unknown"),
                    "action_code": action_code,
                    "q_value": round(float(q_value or 0.0), 4),
                    "total_items": 0,
                    "recommendations": [],
                    "has_trained_q_value": key in ql_system.q_records
                    and action_code in ql_system.q_records.get(key, {}),
                    "message": "No recommendations available - Q-value is not positive",
                }
            )

        data = ql_system.load_data_from_db()
        if not data:
            return jsonify({"error": "Failed to load intervention data"})
        try:
            items_refs = ql_system.get_multiple_recommendations(
                action_code, data, num_items=5, student_state=current_state
            )
        finally:
            try:
                sess = data.get("session")
                if sess is not None:
                    sess.close()
            except Exception:
                pass
        action_name = ql_system.action_labels.get(action_code, "Unknown")
        return jsonify(
            {
                "siswa_id": siswa_id,
                "current_state": current_state,
                "action": action_name,
                "action_code": action_code,
                "q_value": round(q_value, 4),
                "total_items": len(items_refs),
                "items_refs": items_refs,
                "recommendations": [],
                "has_trained_q_value": True,
            }
        )
    except Exception as e:
        return jsonify({"error": f"Failed to get action recommendations: {str(e)}"})
