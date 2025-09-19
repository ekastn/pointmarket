from typing import Dict, Tuple, Union

from services.db import execute_query


def clamp(x: float, lo: float, hi: float) -> float:
    return max(lo, min(hi, x))


def map_engagement_label_to_score(label: Union[str, float]) -> float:
    try:
        # allow numeric already
        return float(label)
    except Exception:
        pass
    mapping = {"basic": 3.0, "medium": 6.0, "high": 9.0}
    return mapping.get(str(label or "medium").lower(), 6.0)


def tri_membership(x: float, k1: float = 5.0, k2: float = 7.0) -> Tuple[float, float, float]:
    # 0..10 triangular membership for (low, medium, high)
    low = clamp((k1 - x) / (k1 - 0.0), 0.0, 1.0)
    med = clamp(min(x / (k1 - 0.0), (k2 - x) / (k2 - k1)), 0.0, 1.0)
    high = clamp((x - k2) / (10.0 - k2), 0.0, 1.0)
    s = low + med + high
    if s > 0:
        return (low / s, med / s, high / s)
    return (0.0, 1.0, 0.0)  # center default


def ams_membership(x: float) -> Dict[str, float]:
    # Soft 4-bin membership based on thresholds 5.0, 7.0, 8.5
    # Define simple triangular centers
    bins = [
        ("amotivation", 0.0, 5.0, 2.5),
        ("extrinsic", 5.0, 7.0, 6.0),
        ("achievement", 7.0, 8.5, 7.75),
        ("intrinsic", 8.5, 10.0, 9.25),
    ]
    weights: Dict[str, float] = {k: 0.0 for k, *_ in bins}
    for name, a, b, c in bins:
        if x < a or x > b:
            w = 0.0
        else:
            # linear peak at c, down to 0 at a/b
            half = (b - a) / 2.0 if (b - a) > 0 else 1.0
            w = max(0.0, 1.0 - abs(x - c) / max(half, 1e-9))
        weights[name] = w
    s = sum(weights.values())
    if s > 0:
        for k in list(weights.keys()):
            weights[k] /= s
    else:
        # fallback hard mapping
        if x >= 8.5:
            weights["intrinsic"] = 1.0
        elif x >= 7.0:
            weights["achievement"] = 1.0
        elif x >= 5.0:
            weights["extrinsic"] = 1.0
        else:
            weights["amotivation"] = 1.0
    return weights


def build_user_vector(
    vark: float,
    ams: float,
    mslq: float,
    engagement: Union[str, float],
    weights: Dict[str, float],
) -> Dict[str, Dict[str, float]]:
    # VARK: one-hot letter from thresholds used in RL
    v = float(vark)
    if v >= 8.5:
        v_onehot = {"V": 1.0, "A": 0.0, "R": 0.0, "K": 0.0}
    elif v >= 7.0:
        v_onehot = {"V": 0.0, "A": 1.0, "R": 0.0, "K": 0.0}
    elif v >= 5.5:
        v_onehot = {"V": 0.0, "A": 0.0, "R": 1.0, "K": 0.0}
    else:
        v_onehot = {"V": 0.0, "A": 0.0, "R": 0.0, "K": 1.0}

    # AMS: soft 4-bin
    ams_soft = ams_membership(float(ams))

    # MSLQ: triangular
    m_low, m_med, m_high = tri_membership(float(mslq))
    mslq_soft = {"low": m_low, "medium": m_med, "high": m_high}

    # Engagement: label -> numeric proxy -> triangular
    eng_score = map_engagement_label_to_score(engagement)
    e_low, e_med, e_high = tri_membership(float(eng_score))
    eng_soft = {"low": e_low, "medium": e_med, "high": e_high}

    return {
        "vark": v_onehot,
        "ams": ams_soft,
        "mslq": mslq_soft,
        "eng": eng_soft,
        "_weights": {
            "vark": float(weights.get("vark", 0.35)),
            "ams": float(weights.get("ams", 0.25)),
            "mslq": float(weights.get("mslq", 0.20)),
            "eng": float(weights.get("eng", 0.20)),
        },
    }


def parse_item_state(state_str: str) -> Dict[str, str]:
    # Expect tokens like: V_high_mslq_medium_ams_extrinsic_eng_high
    out = {"vark": None, "mslq": None, "ams": None, "eng": None}
    if not state_str or not isinstance(state_str, str):
        return out
    parts = state_str.split("_")
    try:
        if len(parts) >= 1:
            out["vark"] = parts[0]
        # find keywords
        if "mslq" in parts:
            i = parts.index("mslq")
            if i + 1 < len(parts):
                out["mslq"] = parts[i + 1]
        if "ams" in parts:
            i = parts.index("ams")
            if i + 1 < len(parts):
                out["ams"] = parts[i + 1]
        if "eng" in parts:
            i = parts.index("eng")
            if i + 1 < len(parts):
                out["eng"] = parts[i + 1]
    except Exception:
        pass
    return out


def score(user_vec: Dict[str, Dict[str, float]], item_state: Dict[str, str], weights: Dict[str, float]) -> float:
    # Sum per-block contribution at the item's bucket
    total_w = 0.0
    s = 0.0
    # VARK
    wv = float(weights.get("vark", 0.35))
    v_bucket = (item_state.get("vark") or "").upper()
    if v_bucket in user_vec.get("vark", {}):
        s += wv * float(user_vec["vark"][v_bucket])
        total_w += wv
    # AMS
    wa = float(weights.get("ams", 0.25))
    a_bucket = (item_state.get("ams") or "").lower()
    if a_bucket in user_vec.get("ams", {}):
        s += wa * float(user_vec["ams"][a_bucket])
        total_w += wa
    # MSLQ
    wm = float(weights.get("mslq", 0.20))
    m_bucket = (item_state.get("mslq") or "").lower()
    if m_bucket in user_vec.get("mslq", {}):
        s += wm * float(user_vec["mslq"][m_bucket])
        total_w += wm
    # Engagement
    we = float(weights.get("eng", 0.20))
    e_bucket = (item_state.get("eng") or "").lower()
    if e_bucket in user_vec.get("eng", {}):
        s += we * float(user_vec["eng"][e_bucket])
        total_w += we
    if total_w > 0:
        return s / total_w
    return 0.0


def normalize_q(values):
    if not values:
        return []
    vmin = min(values)
    vmax = max(values)
    if vmax == vmin:
        return [0.5 for _ in values]
    return [(v - vmin) / (vmax - vmin) for v in values]


def fetch_user_scores(siswa_id: str):
    rows = execute_query(
        "SELECT vark, mslq, ams, engagement FROM scores_siswa WHERE siswa_id = %s",
        (siswa_id,),
    )
    if not rows:
        return None
    r = rows[0]
    return {
        "vark": float(r.get("vark") or 0.0),
        "mslq": float(r.get("mslq") or 0.0),
        "ams": float(r.get("ams") or 0.0),
        "engagement": r.get("engagement") or "medium",
    }

