from flask import Blueprint, jsonify, request

from services.qlearning import ql_system

bp = Blueprint("training", __name__)


@bp.post("/train")
def train_model():
    try:
        episodes = int(
            (request.json or {}).get("episodes", None)
            or request.form.get("episodes", None)
            or 300
        )
        if episodes < 50:
            return jsonify(
                {"success": False, "message": "Minimum 50 episodes required."}
            )
        if episodes > 1000:
            return jsonify(
                {"success": False, "message": "Maximum 1000 episodes allowed."}
            )

        data = ql_system.load_data_from_db()
        if not data:
            return jsonify(
                {"success": False, "message": "Failed to load data from database."}
            )

        ql_system.train_q_learning(data["log_data"], episodes)
        q_rows = ql_system.get_q_table_records()
        for row in q_rows:
            # simple recommendation text based on best_action label
            row["rekomendasi"] = ql_system.action_labels.get(
                row["best_action"], "Unknown"
            )

        ok1 = ql_system.save_q_table(q_rows)
        ok2 = ql_system.save_q_table(q_rows, table_name="q_table_results_quick")
        if ok1 and ok2:
            unique_states = len(set(r["state"] for r in q_rows))
            unique_students = len(set(r["siswa_id"] for r in q_rows))
            episode_quality = (
                "optimal"
                if 200 <= episodes <= 500
                else "acceptable" if episodes < 200 else "high (may overfit)"
            )
            return jsonify(
                {
                    "success": True,
                    "message": f"Training completed successfully with {episodes} episodes",
                    "total_records": len(q_rows),
                    "episode_quality": episode_quality,
                    "training_details": {
                        "episodes": episodes,
                        "unique_states": unique_states,
                        "students_trained": unique_students,
                        "learning_parameters": {
                            "alpha": ql_system.alpha,
                            "gamma": ql_system.gamma,
                        },
                    },
                }
            )
        return jsonify(
            {
                "success": False,
                "message": "Training completed but failed to save results to database.",
            }
        )
    except ValueError:
        return jsonify(
            {
                "success": False,
                "message": "Invalid episodes value. Please enter number 50-1000.",
            }
        )
    except Exception as e:
        return jsonify({"success": False, "message": f"Training failed: {str(e)}"})
