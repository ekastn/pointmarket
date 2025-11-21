from flask import Blueprint, jsonify, request
from sqlalchemy import select, and_, func

from services import items_repo
from services.db import execute_query
from models import Item, RefType
from services.qlearning import ql_system

bp = Blueprint("admin_items", __name__)


def _val_action_code(v):
    try:
        iv = int(v)
    except Exception:
        return None
    return iv if iv in ql_system.action_space else None


def _state_exists(state: str) -> bool:
    if not state or not isinstance(state, str):
        return False
    rows = execute_query("SELECT 1 FROM unique_states WHERE state = %s LIMIT 1", (state,))
    return bool(rows)


def _validate_item_payload(data: dict) -> list:
    errs = []
    state = data.get("state")
    if not state or not isinstance(state, str):
        errs.append({"field": "state", "reason": "required"})
    elif not _state_exists(state):
        errs.append({"field": "state", "reason": "not found"})

    ac = _val_action_code(data.get("action_code"))
    if ac is None:
        errs.append({"field": "action_code", "reason": "unsupported"})

    rtype = data.get("ref_type")
    try:
        RefType(rtype)
    except Exception:
        errs.append({"field": "ref_type", "reason": "unsupported"})

    try:
        rid = int(data.get("ref_id"))
        if rid <= 0:
            raise ValueError
    except Exception:
        errs.append({"field": "ref_id", "reason": "must be positive"})

    return errs


def _item_to_dict(it: Item) -> dict:
    return {
        "id": int(it.id),
        "state": it.state,
        "action_code": int(it.action_code),
        "ref_type": it.ref_type.value if hasattr(it.ref_type, "value") else str(it.ref_type),
        "ref_id": int(it.ref_id),
        "is_active": bool(it.is_active),
    }


@bp.get("/items/stats")
def get_item_stats():
    try:
        rows = execute_query("SELECT ref_type, COUNT(*) AS count FROM items GROUP BY ref_type")
        stats = {row['ref_type']: int(row['count']) for row in rows}
        return jsonify(stats), 200
    except Exception as e:
        return jsonify({"error": f"get_item_stats failed: {str(e)}"}), 500


@bp.get("/items")
def list_items():
    try:
        session = items_repo.get_session()
        try:
            stmt = select(Item)
            filters = []
            state = request.args.get("state")
            if state:
                filters.append(Item.state == state)
            state_like = request.args.get("state_like")
            if state_like:
                filters.append(Item.state.like(f"%{state_like}%"))
            ac = request.args.get("action_code")
            if ac is not None and ac != "":
                try:
                    filters.append(Item.action_code == int(ac))
                except Exception:
                    return jsonify({"error": "action_code must be integer"}), 400
            rtype = request.args.get("ref_type")
            if rtype:
                try:
                    filters.append(Item.ref_type == RefType(rtype))
                except Exception:
                    return jsonify({"error": "unsupported ref_type"}), 400
            rid = request.args.get("ref_id")
            if rid:
                try:
                    filters.append(Item.ref_id == int(rid))
                except Exception:
                    return jsonify({"error": "ref_id must be integer"}), 400
            active = request.args.get("active")
            if active in ("0", "1"):
                filters.append(Item.is_active == (active == "1"))

            if filters:
                stmt = stmt.where(and_(*filters))

            # Count total using SQL COUNT(*)
            if filters:
                total = session.execute(
                    select(func.count()).select_from(Item).where(and_(*filters))
                ).scalar_one()
            else:
                total = session.execute(
                    select(func.count()).select_from(Item)
                ).scalar_one()

            limit = int(request.args.get("limit", 20))
            offset = int(request.args.get("offset", 0))
            sort = (request.args.get("sort") or "id desc").strip().lower()
            if sort == "id asc":
                stmt = stmt.order_by(Item.id.asc())
            else:
                stmt = stmt.order_by(Item.id.desc())
            stmt = stmt.offset(offset).limit(limit)
            rows = session.execute(stmt).scalars().all()
            items = [_item_to_dict(r) for r in rows]

            # Enrich ref titles from local rec DB reference tables (if present)
            try:
                # Collect IDs per type
                mission_ids, reward_ids, punish_ids, coach_ids, product_ids = set(), set(), set(), set(), set()
                for it in items:
                    rtype = it.get("ref_type")
                    rid = int(it.get("ref_id") or 0)
                    if rid <= 0:
                        continue
                    if rtype == "mission":
                        mission_ids.add(rid)
                    elif rtype == "reward":
                        reward_ids.add(rid)
                    elif rtype == "punishment":
                        punish_ids.add(rid)
                    elif rtype == "coaching":
                        coach_ids.add(rid)
                    elif rtype == "product":
                        product_ids.add(rid)

                def fetch_titles(table: str, ids: set) -> dict:
                    if not ids:
                        return {}
                    placeholders = ",".join(["%s"] * len(ids))
                    sql = f"SELECT id, judul FROM {table} WHERE id IN ({placeholders})"
                    rows = execute_query(sql, tuple(ids))
                    out = {}
                    for r in rows:
                        try:
                            out[int(r.get("id"))] = r.get("judul")
                        except Exception:
                            pass
                    return out

                m_titles = fetch_titles("data_misi_fullstate", mission_ids)
                r_titles = fetch_titles("data_reward_fullstate", reward_ids)
                p_titles = fetch_titles("data_hukuman_fullstate", punish_ids)
                c_titles = fetch_titles("data_coaching_fullstate", coach_ids)
                pr_titles = fetch_titles("data_produk_fullstate", product_ids)

                for it in items:
                    rtype = it.get("ref_type")
                    rid = int(it.get("ref_id") or 0)
                    if rtype == "mission" and rid in m_titles:
                        it["ref_title"] = m_titles[rid]
                    elif rtype == "reward" and rid in r_titles:
                        it["ref_title"] = r_titles[rid]
                    elif rtype == "punishment" and rid in p_titles:
                        it["ref_title"] = p_titles[rid]
                    elif rtype == "coaching" and rid in c_titles:
                        it["ref_title"] = c_titles[rid]
                    elif rtype == "product" and rid in pr_titles:
                        it["ref_title"] = pr_titles[rid]
            except Exception:
                # best-effort enrichment; ignore failures
                pass

            return jsonify({"items": items, "meta": {"total": total, "limit": limit, "offset": offset}}), 200
        finally:
            session.close()
    except Exception as e:
        return jsonify({"error": f"list_items failed: {str(e)}"}), 500


@bp.post("/items")
def create_items():
    try:
        data = request.get_json()
        if not data:
            return jsonify({"error": "body required"}), 400
        bulk = isinstance(data, list)
        items_in = data if bulk else [data]
        results = []
        session = items_repo.get_session()
        try:
            for entry in items_in:
                errs = _validate_item_payload(entry)
                if errs:
                    results.append({"ok": False, "errors": errs})
                    continue
                # uniqueness
                q = (
                    select(Item)
                    .where(
                        Item.state == entry["state"],
                        Item.action_code == int(entry["action_code"]),
                        Item.ref_type == RefType(entry["ref_type"]),
                        Item.ref_id == int(entry["ref_id"]),
                    )
                )
                exists = session.execute(q).scalars().first()
                if exists:
                    results.append({"ok": False, "errors": [{"field": "unique", "reason": "duplicate"}]})
                    continue
                new_item = Item(
                    state=entry["state"],
                    action_code=int(entry["action_code"]),
                    ref_type=RefType(entry["ref_type"]),
                    ref_id=int(entry["ref_id"]),
                    is_active=bool(entry.get("is_active", True)),
                )
                session.add(new_item)
                session.flush()
                results.append({"ok": True, "id": int(new_item.id)})
            session.commit()
        except Exception:
            session.rollback()
            raise
        finally:
            session.close()
        if bulk:
            return jsonify({"results": results}), 200
        # single
        ok = results and results[0].get("ok")
        if ok:
            return jsonify({"id": results[0]["id"], "message": "created"}), 201
        return jsonify({"error": results[0].get("errors", [])}), 400
    except Exception as e:
        return jsonify({"error": f"create_items failed: {str(e)}"}), 500


@bp.put("/items/<int:item_id>")
def update_item(item_id: int):
    try:
        data = request.get_json() or {}
        session = items_repo.get_session()
        try:
            it = session.get(Item, item_id)
            if not it:
                return jsonify({"error": "not found"}), 404
            merged = {
                "state": data.get("state", it.state),
                "action_code": data.get("action_code", int(it.action_code)),
                "ref_type": data.get("ref_type", (it.ref_type.value if hasattr(it.ref_type, "value") else str(it.ref_type))),
                "ref_id": data.get("ref_id", int(it.ref_id)),
                "is_active": data.get("is_active", bool(it.is_active)),
            }
            errs = _validate_item_payload(merged)
            if errs:
                return jsonify({"error": errs}), 400
            # uniqueness check (exclude self)
            q = (
                select(Item)
                .where(
                    Item.state == merged["state"],
                    Item.action_code == int(merged["action_code"]),
                    Item.ref_type == RefType(merged["ref_type"]),
                    Item.ref_id == int(merged["ref_id"]),
                    Item.id != item_id,
                )
            )
            exists = session.execute(q).scalars().first()
            if exists:
                return jsonify({"error": [{"field": "unique", "reason": "duplicate"}]}), 409
            # apply updates
            it.state = merged["state"]
            it.action_code = int(merged["action_code"])
            it.ref_type = RefType(merged["ref_type"])
            it.ref_id = int(merged["ref_id"])
            it.is_active = bool(merged["is_active"])
            session.commit()
            return jsonify({"message": "updated"}), 200
        except Exception:
            session.rollback()
            raise
        finally:
            session.close()
    except Exception as e:
        return jsonify({"error": f"update_item failed: {str(e)}"}), 500


@bp.patch("/items/<int:item_id>/toggle")
def toggle_item(item_id: int):
    try:
        data = request.get_json() or {}
        if "is_active" not in data:
            return jsonify({"error": "is_active required"}), 400
        session = items_repo.get_session()
        try:
            it = session.get(Item, item_id)
            if not it:
                return jsonify({"error": "not found"}), 404
            it.is_active = bool(data.get("is_active"))
            session.commit()
            return jsonify({"message": "toggled", "is_active": bool(it.is_active)}), 200
        except Exception:
            session.rollback()
            raise
        finally:
            session.close()
    except Exception as e:
        return jsonify({"error": f"toggle_item failed: {str(e)}"}), 500


@bp.delete("/items/<int:item_id>")
def delete_item(item_id: int):
    try:
        force = request.args.get("force") == "1"
        session = items_repo.get_session()
        try:
            it = session.get(Item, item_id)
            if not it:
                return jsonify({"error": "not found"}), 404
            if force:
                session.delete(it)
            else:
                it.is_active = False
            session.commit()
            return jsonify({"message": "deleted" if force else "deactivated"}), 200
        except Exception:
            session.rollback()
            raise
        finally:
            session.close()
    except Exception as e:
        return jsonify({"error": f"delete_item failed: {str(e)}"}), 500


@bp.get("/states")
def list_states():
    """List active states with optional search query q."""
    try:
        q = (request.args.get("q") or "").strip()
        limit = int(request.args.get("limit", 20))
        if q:
            rows = execute_query(
                "SELECT state FROM unique_states WHERE state LIKE %s LIMIT %s",
                (f"%{q}%", limit),
            )
        else:
            rows = execute_query(
                "SELECT state FROM unique_states LIMIT %s",
                (limit,),
            )
        states = [r.get("state") for r in rows]
        return jsonify({"states": states}), 200
    except Exception as e:
        return jsonify({"error": f"list_states failed: {str(e)}"}), 500


@bp.get("/refs")
def search_refs():
    """Search reference items by type and text query using local rec DB tables."""
    try:
        rtype = (request.args.get("ref_type") or "").strip()
        q = (request.args.get("q") or "").strip()
        limit = int(request.args.get("limit", 20))
        if rtype not in ("mission", "product", "reward", "coaching", "punishment"):
            return jsonify({"error": "unsupported ref_type"}), 400
        table = {
            "mission": "data_misi_fullstate",
            "product": "data_produk_fullstate",
            "reward": "data_reward_fullstate",
            "coaching": "data_coaching_fullstate",
            "punishment": "data_hukuman_fullstate",
        }[rtype]
        if q:
            rows = execute_query(
                f"SELECT id, judul FROM {table} WHERE judul LIKE %s LIMIT %s",
                (f"%{q}%", limit),
            )
        else:
            rows = execute_query(
                f"SELECT id, judul FROM {table} LIMIT %s",
                (limit,),
            )
        refs = [{"id": int(r.get("id")), "title": r.get("judul") or ""} for r in rows]
        return jsonify({"refs": refs}), 200
    except Exception as e:
        return jsonify({"error": f"search_refs failed: {str(e)}"}), 500
