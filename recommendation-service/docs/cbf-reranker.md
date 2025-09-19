# Content‑Based Reranker (CBF) — Recommendation Service

This document explains the CBF reranking layer added to the recommendation service. It reorders RL candidates using user raw scores and item state strings without changing schemas or response contracts, and with negligible latency.

**Why**
- RL selects which actions to show; CBF improves the order of items within each action using user–item content similarity derived from our existing “state” semantics and raw scores.

**What stays the same**
- Candidate source (RL + items table), API shape (items_refs), DB schema.

**What changes**
- Deterministic ordering of candidates per action instead of random sampling; optional debug logs.

---

## Code Map
- Helper module: recommendation-service/services/cbf.py
- Integration point: recommendation-service/services/qlearning.py: get_multiple_recommendations
- Config flags: recommendation-service/config.py

---

## Data Sources
- User profile (rec DB):
  - Table scores_siswa: columns vark, mslq, ams (0–10), engagement ('basic'|'medium'|'high').
- Item profile (rec DB):
  - Table items: columns state (string), action_code; state format: V_high_mslq_{low|medium|high}_ams_{type}_eng_{low|medium|high}.
- State dictionary: unique_states lists allowed tokens; used elsewhere to validate/match states.

---

## Feature Representation
- Blocks and dimensions
  - VARK (4 dims: V, A, R, K)
  - AMS (4 dims: amotivation, extrinsic, achievement, intrinsic)
  - MSLQ (3 dims: low, medium, high)
  - ENG (3 dims: low, medium, high)
- User vector u
  - VARK: one‑hot from get_vark_letter(vark) thresholds (≥8.5 V, ≥7.0 A, ≥5.5 R, else K).
  - AMS: soft 4‑bin membership (thresholds 5.0/7.0/8.5) with triangular peaks; renormalized to sum=1.
  - MSLQ: triangular membership with knots 5 and 7; renormalized.
  - ENG: engagement label mapped to numeric proxy (basic→3, medium→6, high→9), then triangular membership (knots 5,7); renormalized.
- Item vector i
  - One‑hot per block from its state string: VARK letter, ams type, mslq bucket, eng bucket.
- Block weights (defaults; sum to 1)
  - w_vark=0.35, w_ams=0.25, w_mslq=0.20, w_eng=0.20 (configurable).

---

## Membership Functions (Details)
- Triangular for 3‑bucket dims (x ∈ [0,10], knots k1=5, k2=7)
  - p_low(x)  = clamp((k1−x)/(k1−0), 0, 1)
  - p_med(x)  = clamp(min(x/(k1−0), (k2−x)/(k2−k1)), 0, 1)
  - p_high(x) = clamp((x−k2)/(10−k2), 0, 1)
  - Normalize so p_low+p_med+p_high = 1 if any > 0.
- AMS soft 4‑bin (thresholds 5.0, 7.0, 8.5)
  - Bins: amotivation [0,5), extrinsic [5,7), achievement [7,8.5), intrinsic [8.5,10]
  - Each bin has a triangular peak at its center; weights renormalized to sum=1.

---

## Scoring and Blending
- CBF score per item (0..1)
  - Because items are one‑hot per block: cbf_score(i) = Σ_block w_block · u_block[item_bucket(i)].
- RL blend
  - Normalize q values across candidates to q_score ∈ [0,1] (min–max; flat set → 0.5).
  - final = α·q_score + (1−α)·cbf_score.
  - Today, per action, q_value is constant across items; final reduces to ordering by cbf_score. The blend is future‑proofed for per‑item Q.

---

## End‑to‑End Serving Flow
1. Compute current_state via existing logic (logs first, else derived from scores_siswa).
2. Fetch candidate pool for (current_state, action_code). If empty, use action‑only fallback.
3. Load user scores (scores_siswa) and build user vector once per request.
4. For each candidate, parse state → {vark, ams, mslq, eng}; compute cbf_score.
5. Sort by cbf_score desc; return top N items_refs. If user scores missing or disabled, fall back to original sampling.

---

## Worked Example
- User: vark=6.0 (R), ams=7.2 (achievement), mslq=6.8, engagement=medium (→6)
  - MSLQ memberships: low≈0.0, med≈0.6, high≈0.4
  - AMS memberships: extrinsic≈0.2, achievement≈0.6, intrinsic≈0.2 (illustrative)
- Candidate A state: R_high_mslq_high_ams_achievement_eng_medium
  - Picks: VARK=R, MSLQ=high, AMS=achievement, ENG=medium
  - cbf_score ≈ 0.35·1.0 (R) + 0.25·0.6 (ach) + 0.20·0.4 (high) + 0.20·(eng=medium≈1.0) = 0.35 + 0.15 + 0.08 + 0.20 = 0.78
- Candidate B state: A_high_mslq_low_ams_extrinsic_eng_low
  - Picks: A, low, extrinsic, low
  - cbf_score ≈ 0.35·0 (A) + 0.25·0.2 + 0.20·0.0 + 0.20·0.0 = 0.05
→ A ranks far above B regardless of equal q per action.

---

## Configuration (Env Vars)
- CBF_ENABLED=true|false (default true)
- CBF_ALPHA=0.7 (used when per‑item q exists)
- CBF_WEIGHTS_VARK=0.35
- CBF_WEIGHTS_AMS=0.25
- CBF_WEIGHTS_MSLQ=0.20
- CBF_WEIGHTS_ENG=0.20
- CBF_DEBUG=true|false (default false)

See: recommendation-service/config.py:1

---

## Logging / Metrics (CBF_DEBUG)
- When CBF_DEBUG=true, qlearning logs on each rerank:
  - siswa_id, action_code, current_state, pool size
  - top‑3 cbf scores, min/max/avg over the pool
- Example log line:
  - CBF applied | siswa=123 action=105 state=V_high_mslq_medium_ams_extrinsic_eng_medium pool=24 top3=[0.81, 0.79, 0.75] min=0.2100 max=0.8100 avg=0.5600
- Code: recommendation-service/services/qlearning.py: in get_multiple_recommendations

---

## Performance & Limits
- Complexity: O(N) per action over N candidates; each score is a handful of additions/mults.
- Typical N ≤ 50; latency impact ~ microseconds to low milliseconds.
- Memory: negligible; user vector built once per request.

---

## Edge Cases & Fallbacks
- Missing user scores → skip CBF and fall back to (deterministic when possible) sampling.
- Malformed item state tokens → missing blocks contribute 0; item still considered.
- Empty state pool → action‑only fallback pool.
- Flat RL q values → already covered; ordering by CBF.

---

## Testing & Validation
- Unit tests (recommended):
  - Triangular membership near knots (5,7); AMS membership coverage and sum≈1.
  - Parser robustness on incomplete state strings; case normalization.
  - Score monotonicity: items matching more blocks score higher.
- Manual checks:
  - Toggle CBF_ENABLED to compare old/new ordering.
  - Sanity users near thresholds (MSLQ 4.9/5.1 and 6.9/7.1) to verify smooth behavior.
  - Inspect logs with CBF_DEBUG for pool stats.

---

## Security & Privacy
- Uses non‑PII, non‑sensitive aggregate learning attributes; no external calls.
- Logs avoid raw content; only scores and identifiers.

---

## Future Enhancements
- Learn block weights from outcomes per action.
- Add per‑item Q blend when available (use normalize_q and α blend already present).
- Optional diversity (MMR) over top‑M to avoid near duplicates.
- Cached user vectors with short TTL if needed (likely unnecessary at current scale).

---

## File References
- recommendation-service/services/cbf.py:1 — helpers (vector build, memberships, parse, score, fetch scores)
- recommendation-service/services/qlearning.py:1 — rerank integration inside get_multiple_recommendations
- recommendation-service/config.py:1 — configuration flags

No backend/frontend changes are required. Response still returns items_refs; only order improves.
