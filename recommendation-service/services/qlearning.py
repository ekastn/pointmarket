import math
import random

from .db import execute_query, get_connection 

from config import Config


class QLearningRecommendationSystem:
    def __init__(self):
        self.q_records = {}
        self.alpha = Config.LEARNING_RATE
        self.gamma = Config.DISCOUNT_FACTOR
        self.action_space = [101, 102, 103, 105, 106]
        self.action_labels = {
            101: "Reward",
            102: "Produk",
            103: "Hukuman",
            105: "Misi",
            106: "Coaching",
        }

    # --- helpers ---
    def is_null_or_nan(self, value):
        if value is None:
            return True
        if isinstance(value, str) and (value.lower() in ["nan", "null", "none", ""]):
            return True
        try:
            return math.isnan(float(value))
        except (ValueError, TypeError):
            return False

    def get_vark_letter(self, vark_score):
        vark_score = float(vark_score)
        if vark_score >= 8.5:
            return "V"
        elif vark_score >= 7.0:
            return "A"
        elif vark_score >= 5.5:
            return "R"
        else:
            return "K"

    def get_ams_type(self, ams_score):
        ams_score = float(ams_score)
        if ams_score >= 8.5:
            return "intrinsic"
        elif ams_score >= 7.0:
            return "achievement"
        elif ams_score >= 5.0:
            return "extrinsic"
        else:
            return "amotivation"

    def generate_state(self, vark, mslq, ams, engagement):
        vark = float(vark)
        mslq = float(mslq)
        ams = float(ams)
        engagement = float(engagement)

        vark_letter = self.get_vark_letter(vark)
        vark_level = "high"
        mslq_cat = "high" if mslq >= 7 else "medium" if mslq >= 5 else "low"
        ams_type = self.get_ams_type(ams)
        eng_cat = "high" if engagement >= 7 else "medium" if engagement >= 5 else "low"
        return (
            f"{vark_letter}_{vark_level}_mslq_{mslq_cat}_ams_{ams_type}_eng_{eng_cat}"
        )

    def find_closest_state(self, target_state, available_states):
        try:
            if target_state in available_states:
                return target_state
            parts = target_state.split("_")
            if len(parts) >= 7:
                vark_part = parts[0]
                vark_level = parts[1]
                mslq_part = parts[3]
                ams_part = parts[5]
                eng_part = parts[7]

                for state in available_states:
                    sp = state.split("_")
                    if (
                        len(sp) >= 8
                        and sp[0] == vark_part
                        and sp[1] == vark_level
                        and sp[3] == mslq_part
                        and sp[5] == ams_part
                        and sp[7] == eng_part
                    ):
                        return state
                for state in available_states:
                    sp = state.split("_")
                    if (
                        len(sp) >= 8
                        and sp[0] == vark_part
                        and sp[3] == mslq_part
                        and sp[5] == ams_part
                    ):
                        return state
                for state in available_states:
                    if state.startswith(vark_part + "_"):
                        return state
            return (
                available_states[0]
                if available_states
                else "V_high_mslq_medium_ams_extrinsic_eng_medium"
            )
        except Exception:
            return (
                available_states[0]
                if available_states
                else "V_high_mslq_medium_ams_extrinsic_eng_medium"
            )

    def get_engagement_level(self, interaction_frequency, time_spent, completion_rate):
        # Coerce possible Decimal inputs to float for safe arithmetic
        try:
            interaction_frequency = float(interaction_frequency or 0)
        except Exception:
            interaction_frequency = 0.0
        try:
            time_spent = float(time_spent or 0)
        except Exception:
            time_spent = 0.0
        try:
            completion_rate = float(completion_rate or 0)
        except Exception:
            completion_rate = 0.0

        freq_score = min(interaction_frequency / 5.0 * 10.0, 10.0)
        time_score = min(time_spent / 60.0 * 10.0, 10.0)
        completion_score = completion_rate * 10.0
        engagement = freq_score * 0.3 + time_score * 0.3 + completion_score * 0.4
        return round(engagement, 2)

    # --- DB loaders ---
    def load_data_from_db(self):
        states_rows = execute_query("SELECT state FROM unique_states ORDER BY state")
        available_states = [r["state"] for r in states_rows]

        logs_query = """
        SELECT l.*, s.vark, s.mslq, s.ams,
               l.interaction_frequency, l.time_spent, l.completion_rate
        FROM log_interaksi l
        LEFT JOIN scores_siswa s ON l.siswa_id = s.siswa_id
        """
        log_data = execute_query(logs_query)

        validated = []
        for row in log_data:
            existing_state = row.get("state")
            if existing_state and existing_state in available_states:
                row["generated_state"] = existing_state
            else:
                vark_val = row.get("vark")
                mslq_val = row.get("mslq")
                ams_val = row.get("ams")
                if any(self.is_null_or_nan(v) for v in [vark_val, mslq_val, ams_val]):
                    row["generated_state"] = (
                        available_states[0]
                        if available_states
                        else "V_high_mslq_medium_ams_extrinsic_eng_medium"
                    )
                else:
                    try:
                        vark_f = float(vark_val)
                        mslq_f = float(mslq_val)
                        ams_f = float(ams_val)
                    except Exception:
                        continue

                    freq = float(row.get("interaction_frequency") or 3.0)
                    t_spent = float(row.get("time_spent") or 30.0)
                    comp = float(row.get("completion_rate") or 0.7)
                    engagement = self.get_engagement_level(freq, t_spent, comp)
                    gen_state = self.generate_state(vark_f, mslq_f, ams_f, engagement)
                    mapped_state = self.find_closest_state(gen_state, available_states)
                    row["generated_state"] = mapped_state

            if row.get("generated_state") in available_states:
                validated.append(row)

        states_data = execute_query(
            "SELECT state, description FROM unique_states ORDER BY state"
        )
        misi_data = execute_query("SELECT * FROM data_misi_fullstate")
        reward_data = execute_query("SELECT * FROM data_reward_fullstate")
        produk_data = execute_query("SELECT * FROM data_produk_fullstate")
        hukuman_data = execute_query("SELECT * FROM data_hukuman_fullstate")
        coaching_data = execute_query("SELECT * FROM data_coaching_fullstate")

        return {
            "log_data": validated,
            "states_data": states_data,
            "available_states": available_states,
            "misi_data": misi_data,
            "reward_data": reward_data,
            "produk_data": produk_data,
            "hukuman_data": hukuman_data,
            "coaching_data": coaching_data,
        }

    def train_q_learning(self, log_data, episodes=300):
        for _ in range(episodes):
            for row in log_data:
                siswa_id = row["siswa_id"]
                state = row["generated_state"]
                action = int(row["action"])
                reward = float(row["reward"])
                key = (siswa_id, state)
                if key not in self.q_records:
                    self.q_records[key] = {a: 0.0 for a in self.action_space}
                old_q = float(self.q_records[key][action])
                max_future_q = max(float(v) for v in self.q_records[key].values())
                new_q = old_q + self.alpha * (
                    reward + self.gamma * max_future_q - old_q
                )
                self.q_records[key][action] = float(new_q)

    def get_q_table_records(self):
        rows = []
        for (siswa_id, state), q_vals in self.q_records.items():
            best_action = max(q_vals, key=q_vals.get)
            max_q = q_vals[best_action]
            row = {
                "siswa_id": siswa_id,
                "state": state,
                101: q_vals[101],
                102: q_vals[102],
                103: q_vals[103],
                105: q_vals[105],
                106: q_vals[106],
                "best_action": best_action,
                "max_q_value": max_q,
            }
            rows.append(row)
        return rows

    def save_q_table(self, q_rows, table_name="q_table_results"):
        conn = get_connection()
        if not conn:
            return False
        try:
            cur = conn.cursor()
            cur.execute(f"DELETE FROM {table_name}")
            for row in q_rows:
                query = f"""
                INSERT INTO {table_name}
                (siswa_id, state, action_101, action_102, action_103, action_105, action_106,
                 best_action, max_q_value, rekomendasi)
                VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
                """
                values = (
                    row["siswa_id"],
                    row["state"],
                    row[101],
                    row[102],
                    row[103],
                    row[105],
                    row[106],
                    row["best_action"],
                    row["max_q_value"],
                    row.get("rekomendasi", ""),
                )
                cur.execute(query, values)
            conn.commit()
            cur.close()
            conn.close()
            return True
        except Exception as e:
            print(f"Save q_table failed: {e}")
            conn.close()
            return False

    def get_multiple_recommendations(
        self, action_code, intervention_data, num_items=3, student_state=None
    ):
        recommendations = []
        try:
            if action_code == 101 and intervention_data["reward_data"]:
                data_list = intervention_data["reward_data"]
            elif action_code == 102 and intervention_data["produk_data"]:
                data_list = intervention_data["produk_data"]
            elif action_code == 103 and intervention_data["hukuman_data"]:
                data_list = intervention_data["hukuman_data"]
            elif action_code == 105 and intervention_data["misi_data"]:
                data_list = intervention_data["misi_data"]
            elif action_code == 106 and intervention_data["coaching_data"]:
                data_list = intervention_data["coaching_data"]
            else:
                data_list = []

            sample_size = min(num_items, len(data_list))
            sampled = random.sample(data_list, sample_size) if sample_size > 0 else []
            for item in sampled:
                rec = {
                    "judul": item.get("judul", ""),
                    "deskripsi": item.get("deskripsi", ""),
                    "kategori": item.get("kategori", ""),
                    "target_audience": item.get("target_audience", ""),
                    "difficulty_level": item.get("difficulty_level", ""),
                    "estimated_duration": item.get("estimated_duration", ""),
                    "action_type": self.action_labels.get(action_code, "Unknown"),
                }
                recommendations.append(rec)
        except Exception as e:
            print(f"Error get_multiple_recommendations: {e}")
        return recommendations

    def get_default_recommendations(self, state):
        # Simple subset of default logic focusing on engagement and MSLQ
        parts = state.split("_")
        eng_level = parts[7] if len(parts) > 7 else "medium"
        mslq_level = parts[3] if len(parts) > 3 else "medium"
        recs = []
        if eng_level == "low":
            recs.append(
                {
                    "state": state,
                    "action": "Reward",
                    "action_code": 101,
                    "confidence": 0.8,
                    "recommendation": "Sistem reward untuk meningkatkan motivasi",
                }
            )
            recs.append(
                {
                    "state": state,
                    "action": "Misi",
                    "action_code": 105,
                    "confidence": 0.7,
                    "recommendation": "Misi menarik untuk meningkatkan keterlibatan",
                }
            )
        elif eng_level == "medium":
            recs.append(
                {
                    "state": state,
                    "action": "Misi",
                    "action_code": 105,
                    "confidence": 0.6,
                    "recommendation": "Tantangan untuk mempertahankan engagement",
                }
            )
        else:
            recs.append(
                {
                    "state": state,
                    "action": "Reward",
                    "action_code": 101,
                    "confidence": 0.7,
                    "recommendation": "Penghargaan untuk engagement tinggi",
                }
            )
        if mslq_level == "low":
            recs.append(
                {
                    "state": state,
                    "action": "Coaching",
                    "action_code": 106,
                    "confidence": 0.9,
                    "recommendation": "Bimbingan strategi belajar",
                }
            )
        return recs[:5]

    def get_student_current_state(self, siswa_id):
        existing_state_query = """
        SELECT state FROM log_interaksi
        WHERE siswa_id = %s
        ORDER BY id DESC LIMIT 1
        """
        result = execute_query(existing_state_query, (siswa_id,))
        available_states = [
            r["state"]
            for r in execute_query("SELECT state FROM unique_states ORDER BY state")
        ]
        if result and result[0].get("state") in available_states:
            return result[0]["state"]

        query = """
        SELECT s.vark, s.mslq, s.ams,
               AVG(l.interaction_frequency) as avg_freq,
               AVG(l.time_spent) as avg_time,
               AVG(l.completion_rate) as avg_completion
        FROM scores_siswa s
        LEFT JOIN log_interaksi l ON s.siswa_id = l.siswa_id
        WHERE s.siswa_id = %s
        GROUP BY s.siswa_id, s.vark, s.mslq, s.ams
        """
        rows = execute_query(query, (siswa_id,))
        if not rows:
            return available_states[0] if available_states else None
        r = rows[0]
        engagement = self.get_engagement_level(
            r.get("avg_freq") or 3.0,
            r.get("avg_time") or 30.0,
            r.get("avg_completion") or 0.7,
        )
        gen = self.generate_state(r["vark"], r["mslq"], r["ams"], engagement)
        return self.find_closest_state(gen, available_states)

    # --- Persistence fallbacks ---
    def get_db_q_values(self, siswa_id, state=None):
        """Fetch Q-values for a student from q_table_results as a fallback.
        If state is provided, try exact state first; otherwise pick the row with the highest max_q_value.
        Returns a dict: { 'state': str, 'q_values': {action_code: float}, 'best_action': int, 'max_q_value': float }
        or None if not found.
        """
        row = None
        if state:
            rows = execute_query(
                """
                SELECT * FROM q_table_results
                WHERE siswa_id = %s AND state = %s
                ORDER BY max_q_value DESC
                LIMIT 1
                """,
                (siswa_id, state),
            )
            if rows:
                row = rows[0]
        if row is None:
            rows = execute_query(
                """
                SELECT * FROM q_table_results
                WHERE siswa_id = %s
                ORDER BY max_q_value DESC
                LIMIT 1
                """,
                (siswa_id,),
            )
            if rows:
                row = rows[0]
        if not row:
            return None
        try:
            q_vals = {
                101: float(row.get("action_101") or 0.0),
                102: float(row.get("action_102") or 0.0),
                103: float(row.get("action_103") or 0.0),
                105: float(row.get("action_105") or 0.0),
                106: float(row.get("action_106") or 0.0),
            }
            best_action = int(row.get("best_action") or max(q_vals, key=q_vals.get))
            max_q_value = float(row.get("max_q_value") or q_vals.get(best_action, 0.0))
            return {
                "state": row.get("state"),
                "q_values": q_vals,
                "best_action": best_action,
                "max_q_value": max_q_value,
            }
        except Exception:
            return None

    def get_positive_actions(self, q_values):
        """Return list of (action_code, q_value) sorted by q_value desc for positive q-values."""
        if not q_values:
            return []
        positives = [(ac, float(q)) for ac, q in q_values.items() if float(q) > 0.0]
        positives.sort(key=lambda x: x[1], reverse=True)
        return positives


ql_system = QLearningRecommendationSystem()
