from flask import Blueprint, jsonify, request
from datetime import datetime, timezone

from services.qlearning import ql_system
from services import items_repo
from services.cbf import (
    build_user_vector,
    parse_item_state,
    score as cbf_score_fn,
    fetch_user_scores,
)
from services.db import fetch_recent_rewards
from config import Config

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
        trace_mode = (request.args.get("trace") == "1")
        current_state = ql_system.get_student_current_state(siswa_id)
        if not current_state:
            return jsonify({"error": "Unable to determine student state or student not found"}), 404

        action_recs = {}
        key = (siswa_id, current_state)
        data = None
        q_values_src = None  # "in_memory" | "db_fallback" | None
        q_values_all = None

        # In-memory Q-values first
        q_values = ql_system.q_records.get(key)
        actions = ql_system.get_positive_actions(q_values) if q_values else []
        if q_values:
            q_values_src = "in_memory"
            q_values_all = {int(ac): float(v) for ac, v in q_values.items()}

        # Fallback to DB saved q_table_results
        if not actions:
            db_row = ql_system.get_db_q_values(siswa_id, state=current_state)
            if db_row:
                actions = ql_system.get_positive_actions(db_row["q_values"])
                # Use the state from DB if different (trained state)
                if db_row.get("state"):
                    current_state = db_row["state"]
                q_values_src = "db_fallback"
                q_values_all = {int(ac): float(v) for ac, v in db_row.get("q_values", {}).items()}

        if actions:
            if data is None:
                data = ql_system.load_data_from_db()
                if not data:
                    return jsonify({"error": "Failed to load intervention data"}), 502
            try:
                for action_code, q_value in actions:
                    items_refs = ql_system.get_multiple_recommendations(
                        siswa_id, action_code, data, num_items=3, student_state=current_state
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

        payload = {
            "siswa_id": siswa_id,
            "current_state": current_state,
            "total_recommendations": sum(v.get("item_count", 0) for v in action_recs.values()),
            "recommendations": [],
            "action_recommendations": action_recs,
            "message": message,
            "summary": {
                "actions_with_recommendations": len(action_recs),
                "total_items": sum(a.get("item_count", 0) for a in action_recs.values()),
                "has_trained_q_values": (key in ql_system.q_records if key else False),
                "all_q_values_zero_or_negative": key in ql_system.q_records and len(action_recs) == 0,
            },
        }

        if trace_mode:
            # Build trace block (best-effort; never fail the whole request)
            try:
                trace = {
                    "timestamp": datetime.now(timezone.utc).isoformat(),
                    "source": q_values_src or ("default" if len(action_recs) == 0 else ("in_memory" if key in ql_system.q_records else "default")),
                    "q_values": q_values_all or {},
                    "cbf": {
                        "config": {
                            "enabled": getattr(Config, "CBF_ENABLED", True),
                            "weights": {
                                "vark": getattr(Config, "CBF_WEIGHTS_VARK", 0.35),
                                "ams": getattr(Config, "CBF_WEIGHTS_AMS", 0.25),
                                "mslq": getattr(Config, "CBF_WEIGHTS_MSLQ", 0.20),
                                "eng": getattr(Config, "CBF_WEIGHTS_ENG", 0.20),
                            },
                            "alpha": getattr(Config, "CBF_ALPHA", 0.7),
                        },
                        "user_vector": {},
                        "actions": [],
                    },
                    "rewards": [],
                }
                try:
                    scores = fetch_user_scores(str(siswa_id))
                except Exception:
                    scores = None
                weights = {
                    "vark": getattr(Config, "CBF_WEIGHTS_VARK", 0.35),
                    "ams": getattr(Config, "CBF_WEIGHTS_AMS", 0.25),
                    "mslq": getattr(Config, "CBF_WEIGHTS_MSLQ", 0.20),
                    "eng": getattr(Config, "CBF_WEIGHTS_ENG", 0.20),
                }
                if scores:
                    uvec = build_user_vector(
                        scores.get("vark", 0.0),
                        scores.get("ams", 0.0),
                        scores.get("mslq", 0.0),
                        scores.get("engagement", "medium"),
                        weights,
                    )
                    trace["cbf"]["user_vector"] = uvec
                # Compute per-action top items with cbf scores (skip gracefully if session not available)
                try:
                    session = items_repo.get_session()
                except Exception:
                    session = None
                try:
                    for name, rec in (action_recs or {}).items():
                        ac = int(rec.get("action_code"))
                        pool_state = items_repo.get_items_for_state_action(session, current_state or "", ac, 50) if session else []
                        pool_fallback = []
                        if not pool_state and session:
                            pool_fallback = items_repo.get_fallback_items_for_action(session, ac, 50)
                        ranked = []
                        if scores and (pool_state or pool_fallback):
                            pool = pool_state or pool_fallback
                            for it in (pool or []):
                                istate = parse_item_state(getattr(it, "state", None))
                                s = cbf_score_fn(uvec, istate, weights)
                                ranked.append((s, it))
                            ranked.sort(key=lambda x: x[0], reverse=True)
                        top_items = []
                        for sc, it in (ranked[:10] if ranked else []):
                            top_items.append(
                                {
                                    "ref_type": getattr(it.ref_type, "value", str(it.ref_type)),
                                    "ref_id": int(it.ref_id),
                                    "item_state": getattr(it, "state", None),
                                    "cbf_score": round(float(sc), 4),
                                }
                            )
                        trace["cbf"]["actions"].append(
                            {
                                "action_code": ac,
                                "pool_size_state": len(pool_state) if pool_state else 0,
                                "pool_size_action_fallback": len(pool_fallback) if pool_fallback else 0,
                                "top_items": top_items,
                            }
                        )
                    # Rewards overlay for actions present
                    for name, rec in (action_recs or {}).items():
                        ac = int(rec.get("action_code"))
                        recent = fetch_recent_rewards(siswa_id, ac, current_state, limit=20) or []
                        # Coerce decimals/bytes to primitives
                        norm = []
                        for r in recent:
                            ts = r.get("timestamp")
                            ts_s = ts.isoformat() if hasattr(ts, "isoformat") else (str(ts) if ts is not None else "")
                            norm.append(
                                {
                                    "timestamp": ts_s,
                                    "state": r.get("state"),
                                    "reward": float(r.get("reward") or 0.0),
                                }
                            )
                        trace["rewards"].append({"action_code": ac, "recent": norm})
                finally:
                    if session is not None:
                        try:
                            session.close()
                        except Exception:
                            pass
                payload["trace"] = trace
            except Exception as te:
                # Best-effort attach trace error instead of failing the whole request
                payload["trace"] = {"trace_error": str(te)}


        return jsonify(payload)
    except Exception as e:
        print(f"Failed to get recommendations: {e}")
        return jsonify({"error": f"Failed to get recommendations: {str(e)}"}), 500


def get_action_recommendations(siswa_id, action_code):
    try:
        if action_code not in ql_system.action_space:
            return jsonify({"error": f"Invalid action code: {action_code}"}), 400
        current_state = ql_system.get_student_current_state(siswa_id)
        if not current_state:
            return jsonify({"error": "Unable to determine student state or student not found"}), 404

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
                siswa_id, action_code, data, num_items=5, student_state=current_state
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
