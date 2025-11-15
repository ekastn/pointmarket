from flask import Blueprint, jsonify, request

from services.db import execute_query, get_connection
import re

bp = Blueprint("admin_states", __name__)


STATE_REGEX = re.compile(r"^[VARK]_high_mslq_(low|medium|high)_ams_(amotivation|extrinsic|achievement|intrinsic)_eng_(low|medium|high)$")


def _validate_state_token(s: str) -> bool:
    if not s or not isinstance(s, str):
        return False
    return bool(STATE_REGEX.match(s))


@bp.get("/unique-states")
def list_states():
    try:
        q = (request.args.get("q") or "").strip()
        limit = int(request.args.get("limit", 20))
        offset = int(request.args.get("offset", 0))
        sort = (request.args.get("sort") or "state asc").lower()
        order_sql = "state ASC"
        if sort == "state desc":
            order_sql = "state DESC"

        params = []
        where = ""
        if q:
            where = " WHERE state LIKE %s OR description LIKE %s"
            params.extend([f"%{q}%", f"%{q}%"]) 

        total_rows = execute_query(f"SELECT COUNT(*) AS cnt FROM unique_states{where}", tuple(params) if params else None)
        total = int(total_rows[0]["cnt"]) if total_rows else 0

        rows = execute_query(
            f"SELECT id, state, description FROM unique_states{where} ORDER BY {order_sql} LIMIT %s OFFSET %s",
            tuple(params + [limit, offset]) if params else (limit, offset),
        )
        states = []
        for r in rows:
            states.append({
                "id": int(r.get("id")),
                "state": r.get("state") or "",
                "description": r.get("description") or "",
            })
        return jsonify({"states": states, "meta": {"total": total, "limit": limit, "offset": offset}}), 200
    except Exception as e:
        return jsonify({"error": f"list_states failed: {str(e)}"}), 500


@bp.post("/unique-states")
def create_states():
    try:
        data = request.get_json()
        if not data:
            return jsonify({"error": "body required"}), 400
        bulk = isinstance(data, list)
        items = data if bulk else [data]
        results = []
        conn = get_connection()
        if not conn:
            return jsonify({"error": "db connection failed"}), 500
        cur = conn.cursor()
        try:
            for it in items:
                state = (it.get("state") or "").strip()
                desc = (it.get("description") or "").strip()
                errs = []
                if not _validate_state_token(state):
                    errs.append({"field": "state", "reason": "invalid_format"})
                # uniqueness
                cur.execute("SELECT id FROM unique_states WHERE state = %s LIMIT 1", (state,))
                if cur.fetchone():
                    errs.append({"field": "state", "reason": "duplicate"})
                if errs:
                    results.append({"ok": False, "errors": errs})
                    continue
                cur.execute("INSERT INTO unique_states (state, description) VALUES (%s, %s)", (state, desc))
                conn.commit()
                cur.execute("SELECT LAST_INSERT_ID() AS id")
                new_id = int(cur.fetchone()[0])
                results.append({"ok": True, "id": new_id})
        finally:
            cur.close()
            conn.close()
        if bulk:
            return jsonify({"results": results}), 200
        first = results[0]
        if first.get("ok"):
            return jsonify({"id": first.get("id"), "message": "created"}), 201
        return jsonify({"error": first.get("errors")}), 400
    except Exception as e:
        return jsonify({"error": f"create_states failed: {str(e)}"}), 500


@bp.put("/unique-states/<int:state_id>")
def update_state(state_id: int):
    try:
        body = request.get_json() or {}
        new_state = body.get("state")
        description = body.get("description")
        update_items = bool(body.get("update_items", False))

        # fetch existing
        rows = execute_query("SELECT id, state, description FROM unique_states WHERE id = %s", (state_id,))
        if not rows:
            return jsonify({"error": "not found"}), 404
        old_state = rows[0]["state"]

        # If state change requested, validate + uniqueness
        if new_state is not None:
            new_state = new_state.strip()
            if not _validate_state_token(new_state):
                return jsonify({"error": [{"field": "state", "reason": "invalid_format"}]}), 400
            rows2 = execute_query("SELECT id FROM unique_states WHERE state = %s AND id <> %s LIMIT 1", (new_state, state_id))
            if rows2:
                return jsonify({"error": [{"field": "state", "reason": "duplicate"}]}), 409

        # If state changes and items reference old state, guard unless update_items==true
        if new_state and new_state != old_state:
            cnt_rows = execute_query("SELECT COUNT(*) AS cnt FROM items WHERE state = %s", (old_state,))
            ref_cnt = int(cnt_rows[0]["cnt"]) if cnt_rows else 0
            if ref_cnt > 0 and not update_items:
                return jsonify({"error": f"state is referenced by {ref_cnt} items; pass update_items=true to propagate"}), 409

        # perform update
        conn = get_connection()
        if not conn:
            return jsonify({"error": "db connection failed"}), 500
        cur = conn.cursor()
        try:
            # propagate items if needed
            if new_state and new_state != old_state and update_items:
                cur.execute("UPDATE items SET state = %s WHERE state = %s", (new_state, old_state))
            # update row
            if new_state is not None and description is not None:
                cur.execute("UPDATE unique_states SET state = %s, description = %s WHERE id = %s", (new_state, description, state_id))
            elif new_state is not None:
                cur.execute("UPDATE unique_states SET state = %s WHERE id = %s", (new_state, state_id))
            elif description is not None:
                cur.execute("UPDATE unique_states SET description = %s WHERE id = %s", (description, state_id))
            conn.commit()
        finally:
            cur.close()
            conn.close()
        return jsonify({"message": "updated"}), 200
    except Exception as e:
        return jsonify({"error": f"update_state failed: {str(e)}"}), 500


@bp.delete("/unique-states/<int:state_id>")
def delete_state(state_id: int):
    try:
        force = (request.args.get("force") == "1")
        rows = execute_query("SELECT id, state FROM unique_states WHERE id = %s", (state_id,))
        if not rows:
            return jsonify({"error": "not found"}), 404
        state_val = rows[0]["state"]
        cnt_rows = execute_query("SELECT COUNT(*) AS cnt FROM items WHERE state = %s", (state_val,))
        ref_cnt = int(cnt_rows[0]["cnt"]) if cnt_rows else 0
        if ref_cnt > 0 and not force:
            return jsonify({"error": f"state is referenced by {ref_cnt} items; cannot delete without force=1"}), 409
        conn = get_connection()
        if not conn:
            return jsonify({"error": "db connection failed"}), 500
        cur = conn.cursor()
        try:
            cur.execute("DELETE FROM unique_states WHERE id = %s", (state_id,))
            conn.commit()
        finally:
            cur.close()
            conn.close()
        return jsonify({"message": "deleted"}), 200
    except Exception as e:
        return jsonify({"error": f"delete_state failed: {str(e)}"}), 500
