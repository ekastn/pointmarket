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

        // 1. Fetch Dashboard Stats
        const dashboardResp = await get("/dashboard");
        if (!dashboardResp || !dashboardResp.success) {
            throw new Error(dashboardResp?.message || "Failed to fetch dashboard data");
        }

        const stats = dashboardResp.data;

        // 2. Fetch Recommendations (using the authenticated user's ID)
        // Note: The recommendation endpoint uses the internal student_id or user_id. 
        // Based on our analysis, the backend handler expects user_id in the path.
        let recommendations = null;
        try {
            const recResp = await get(`/students/${user.id}/recommendations`);
            if (recResp && recResp.success) {
                recommendations = recResp.data;
            }
        } catch (recErr) {
            console.error("Non-critical: Failed to fetch recommendations:", recErr);
        }

        return {
            user: stats.user,
            student_stats: stats.student_stats,
            recommendations: recommendations
        };
    } catch (error) {
        console.error("Profile Data Fetch Error:", error);
        throw error;
    }
}
