import { get } from "./api.js";
import { getCurrentUser } from "./auth.js";

/**
 * Fetches all necessary data for the student profile.
 * Aggregates data from the dashboard and recommendation endpoints.
 * @returns {Promise<object>} Combined profile data.
 */
export async function fetchProfileData() {
    try {
        const user = getCurrentUser();
        if (!user) throw new Error("User not authenticated");

        // 1. Fetch Dashboard Stats, Recommendations & VARK History
        const [dashboardResp, recResp, varkResp] = await Promise.all([
            get("/dashboard"),
            get(`/students/${user.id}/recommendations`),
            get("/questionnaires/history?type=VARK&limit=20")
        ]);

        if (!dashboardResp || !dashboardResp.success) {
            throw new Error(dashboardResp?.message || "Failed to fetch dashboard data");
        }

        const stats = dashboardResp.data;
        const recommendations = (recResp && recResp.success) ? recResp.data : null;
        const varkHistory = (varkResp && varkResp.success) ? varkResp.data : [];

        return {
            user: stats.user,
            student_stats: stats.student_stats,
            recommendations: recommendations,
            vark_history: varkHistory
        };
    } catch (error) {
        console.error("Profile Data Fetch Error:", error);
        throw error;
    }
}
