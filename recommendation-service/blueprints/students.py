import random
from datetime import datetime, timedelta

from flask import Blueprint, jsonify, request

from services.db import execute_query, get_connection
from services.qlearning import ql_system

bp = Blueprint("students", __name__)


@bp.get("")
def list_students():
    query = """
    SELECT s.siswa_id, s.vark, s.mslq, s.ams, s.engagement,
           COALESCE(qa.total_states, 0) AS total_states,
           COALESCE(qa.avg_confidence, 0) AS avg_confidence,
           COALESCE(qa.max_confidence, 0) AS max_confidence,
           qb.best_action AS best_action
    FROM scores_siswa s
    LEFT JOIN (
        SELECT siswa_id,
               COUNT(*) AS total_states,
               AVG(max_q_value) AS avg_confidence,
               MAX(max_q_value) AS max_confidence
        FROM q_table_results
        GROUP BY siswa_id
    ) qa ON s.siswa_id = qa.siswa_id
    LEFT JOIN (
        SELECT q2.siswa_id, MIN(q2.best_action) AS best_action
        FROM q_table_results q2
        JOIN (
            SELECT siswa_id, MAX(max_q_value) AS max_q_value
            FROM q_table_results
            GROUP BY siswa_id
        ) mx ON q2.siswa_id = mx.siswa_id AND q2.max_q_value = mx.max_q_value
        GROUP BY q2.siswa_id
    ) qb ON s.siswa_id = qb.siswa_id
    ORDER BY s.siswa_id
    """

    try:
        rows = execute_query(query)
        students = []
        for row in rows:
            best_action_name = "Not Trained"
            if row.get("best_action") is not None:
                try:
                    best_action_name = ql_system.action_labels.get(
                        int(row["best_action"]), f"Action {row['best_action']}"
                    )
                except Exception:
                    best_action_name = "Unknown"

            students.append(
                {
                    "siswa_id": row.get("siswa_id"),
                    "vark": float(row["vark"]) if row.get("vark") is not None else 0,
                    "mslq": float(row["mslq"]) if row.get("mslq") is not None else 0,
                    "ams": float(row["ams"]) if row.get("ams") is not None else 0,
                    "engagement": row.get("engagement") or "medium",
                    "total_states": (
                        int(row["total_states"])
                        if row.get("total_states") is not None
                        else 0
                    ),
                    "avg_confidence": (
                        float(row["avg_confidence"])
                        if row.get("avg_confidence") is not None
                        else 0
                    ),
                    "max_confidence": (
                        float(row["max_confidence"])
                        if row.get("max_confidence") is not None
                        else 0
                    ),
                    "best_action": (
                        int(row["best_action"])
                        if row.get("best_action") is not None
                        else None
                    ),
                    "best_action_name": best_action_name,
                }
            )
        return jsonify({"students": students})
    except Exception as e:
        return jsonify({"error": f"Failed to get students: {str(e)}"})


@bp.get("/<siswa_id>")
def get_student(siswa_id):
    query = """
    SELECT s.siswa_id, s.vark, s.mslq, s.ams, s.engagement,
           COALESCE(qa.total_states, 0) AS total_states,
           COALESCE(qa.avg_confidence, 0) AS avg_confidence,
           COALESCE(qa.max_confidence, 0) AS max_confidence,
           qb.best_action AS best_action
    FROM scores_siswa s
    LEFT JOIN (
        SELECT siswa_id,
               COUNT(*) AS total_states,
               AVG(max_q_value) AS avg_confidence,
               MAX(max_q_value) AS max_confidence
        FROM q_table_results
        GROUP BY siswa_id
    ) qa ON s.siswa_id = qa.siswa_id
    LEFT JOIN (
        SELECT q2.siswa_id, MIN(q2.best_action) AS best_action
        FROM q_table_results q2
        JOIN (
            SELECT siswa_id, MAX(max_q_value) AS max_q_value
            FROM q_table_results
            GROUP BY siswa_id
        ) mx ON q2.siswa_id = mx.siswa_id AND q2.max_q_value = mx.max_q_value
        GROUP BY q2.siswa_id
    ) qb ON s.siswa_id = qb.siswa_id
    WHERE s.siswa_id = %s
    """
    try:
        rows = execute_query(query, (siswa_id,))
        if not rows:
            return jsonify({"success": False, "message": "Student not found"}), 404
        row = rows[0]
        best_action_name = "Not Trained"
        if row.get("best_action") is not None:
            try:
                best_action_name = ql_system.action_labels.get(
                    int(row["best_action"]), f"Action {row['best_action']}"
                )
            except Exception:
                best_action_name = "Unknown"
        student = {
            "siswa_id": row.get("siswa_id"),
            "vark": float(row["vark"]) if row.get("vark") is not None else 0,
            "mslq": float(row["mslq"]) if row.get("mslq") is not None else 0,
            "ams": float(row["ams"]) if row.get("ams") is not None else 0,
            "engagement": row.get("engagement") or "medium",
            "total_states": (
                int(row["total_states"]) if row.get("total_states") is not None else 0
            ),
            "avg_confidence": (
                float(row["avg_confidence"])
                if row.get("avg_confidence") is not None
                else 0
            ),
            "max_confidence": (
                float(row["max_confidence"])
                if row.get("max_confidence") is not None
                else 0
            ),
            "best_action": (
                int(row["best_action"]) if row.get("best_action") is not None else None
            ),
            "best_action_name": best_action_name,
        }
        return jsonify({"student": student})
    except Exception as e:
        return jsonify({"error": f"Failed to get student: {str(e)}"})


@bp.post("")
def add_student():
    try:
        data = request.get_json()
        siswa_id = data["siswa_id"]
        vark = float(data["vark"])
        mslq = float(data["mslq"])
        ams = float(data["ams"])
        engagement = data.get("engagement", "medium")  # basic, medium, high
        engagement_map = {"basic": 3, "medium": 6, "high": 9}
        engagement_num = engagement_map.get(engagement, 6)

        # Validate ranges
        if not (0 <= vark <= 10) or not (0 <= mslq <= 10) or not (0 <= ams <= 10):
            return jsonify(
                {"success": False, "message": "Scores must be between 0 and 10"}
            )
        if engagement not in ["basic", "medium", "high"]:
            return jsonify(
                {
                    "success": False,
                    "message": "Engagement must be basic, medium, or high",
                }
            )

        conn = get_connection()
        if not conn:
            return jsonify({"success": False, "message": "Database connection failed"})
        cursor = conn.cursor()

        # Check existing
        cursor.execute(
            "SELECT siswa_id FROM scores_siswa WHERE siswa_id = %s", (siswa_id,)
        )
        if cursor.fetchone():
            cursor.close()
            conn.close()
            return jsonify({"success": False, "message": "Student ID already exists"})

        # Insert student
        cursor.execute(
            "INSERT INTO scores_siswa (siswa_id, vark, mslq, ams, engagement) VALUES (%s, %s, %s, %s, %s)",
            (siswa_id, vark, mslq, ams, engagement),
        )
        conn.commit()

        # Seed interaction logs (5 entries) similar to main app
        try:
            base_time = datetime.now()
            state = ql_system.generate_state(vark, mslq, ams, engagement_num)
            n = 5
            actions = random.choices(list(ql_system.action_space), k=n)
            hasil_list = [None] * n
            reward_list = [round(random.uniform(0.0010, 2.0000), 4) for _ in range(n)]

            def stratified_values(n, lo, hi, decimals):
                step = (hi - lo) / n
                return [
                    round(random.uniform(lo + i * step, lo + (i + 1) * step), decimals)
                    for i in range(n)
                ]

            interaction_list = stratified_values(n, 1.0, 5.0, 2)
            time_spent_list = stratified_values(n, 10.0, 99.0, 2)
            completion_list = stratified_values(n, 0.0010, 0.9999, 4)

            for i, action in enumerate(actions):
                waktu_i = (base_time + timedelta(seconds=i)).strftime(
                    "%Y-%m-%d %H:%M:%S"
                )
                try:
                    action_val = int(action)
                except Exception:
                    action_val = action
                cursor.execute(
                    """
                    INSERT INTO log_interaksi
                        (siswa_id, state, action, reward, hasil,
                        interaction_frequency, time_spent, completion_rate, `timestamp`)
                    VALUES
                        (%s, %s, %s, %s, %s,
                        %s, %s, %s, %s)
                    """,
                    (
                        siswa_id,
                        state,
                        action_val,
                        reward_list[i],
                        hasil_list[i],
                        interaction_list[i],
                        time_spent_list[i],
                        completion_list[i],
                        waktu_i,
                    ),
                )
            conn.commit()
        except Exception as log_err:
            print("Failed to insert log_interaksi:", log_err)
        finally:
            cursor.close()
            conn.close()

        return jsonify(
            {
                "success": True,
                "message": f"Student {siswa_id} added successfully with engagement level: {engagement}",
            }
        )

    except Exception as e:
        return jsonify(
            {"success": False, "message": f"Failed to add student: {str(e)}"}
        )


@bp.delete("/<siswa_id>")
def delete_student(siswa_id):
    try:
        conn = get_connection()
        if not conn:
            return jsonify({"success": False, "message": "Database connection failed"})
        cursor = conn.cursor()

        cursor.execute("DELETE FROM log_interaksi WHERE siswa_id = %s", (siswa_id,))
        cursor.execute("DELETE FROM q_table_results WHERE siswa_id = %s", (siswa_id,))
        cursor.execute("DELETE FROM scores_siswa WHERE siswa_id = %s", (siswa_id,))

        if cursor.rowcount == 0:
            cursor.close()
            conn.close()
            return jsonify({"success": False, "message": "Student not found"})

        conn.commit()
        cursor.close()
        conn.close()
        return jsonify(
            {"success": True, "message": f"Student {siswa_id} deleted successfully"}
        )
    except Exception as e:
        return jsonify(
            {"success": False, "message": f"Failed to delete student: {str(e)}"}
        )


@bp.put("/<siswa_id>")
def update_student(siswa_id):
    try:
        data = request.get_json() or {}
        allowed_fields = {"vark", "mslq", "ams", "engagement"}
        updates = {k: v for k, v in data.items() if k in allowed_fields}
        if not updates:
            return (
                jsonify({"success": False, "message": "No valid fields to update"}),
                400,
            )

        # Validate
        if "engagement" in updates and updates["engagement"] not in [
            "basic",
            "medium",
            "high",
        ]:
            return (
                jsonify(
                    {
                        "success": False,
                        "message": "Engagement must be basic, medium, or high",
                    }
                ),
                400,
            )
        for k in ["vark", "mslq", "ams"]:
            if k in updates:
                try:
                    val = float(updates[k])
                except Exception:
                    return (
                        jsonify({"success": False, "message": f"{k} must be a number"}),
                        400,
                    )
                if not (0 <= val <= 10):
                    return (
                        jsonify(
                            {
                                "success": False,
                                "message": f"{k} must be between 0 and 10",
                            }
                        ),
                        400,
                    )
                updates[k] = val

        conn = get_connection()
        if not conn:
            return jsonify({"success": False, "message": "Database connection failed"})
        cursor = conn.cursor()

        # Ensure exists
        cursor.execute("SELECT 1 FROM scores_siswa WHERE siswa_id = %s", (siswa_id,))
        if not cursor.fetchone():
            cursor.close()
            conn.close()
            return jsonify({"success": False, "message": "Student not found"}), 404

        set_clauses = []
        params = []
        for k, v in updates.items():
            set_clauses.append(f"{k} = %s")
            params.append(v)
        params.append(siswa_id)
        sql = f"UPDATE scores_siswa SET {', '.join(set_clauses)} WHERE siswa_id = %s"
        cursor.execute(sql, tuple(params))
        conn.commit()
        cursor.close()
        conn.close()
        return jsonify(
            {"success": True, "message": f"Student {siswa_id} updated successfully"}
        )
    except Exception as e:
        return jsonify(
            {"success": False, "message": f"Failed to update student: {str(e)}"}
        )
