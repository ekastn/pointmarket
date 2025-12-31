import { get } from "./api.js";
import { getCurrentUser } from "./auth.js";
import { fetchProducts } from "./market.js";

/**
 * Fetches data for the Home view.
 * @returns {Promise<object>}
 */
export async function fetchHomeData() {
    try {
        const user = getCurrentUser();
        if (!user) throw new Error("User not authenticated");

        // 1. Fetch Dashboard & Recommendations in parallel
        const [dashboardResp, recResp] = await Promise.all([
            get("/dashboard"),
            get(`/students/${user.id}/recommendations`)
        ]);

        const dashboard = dashboardResp?.success ? dashboardResp.data : null;
        const recommendations = recResp?.success ? recResp.data : null;

        // 2. Determine if we need fallback products
        // Check if recommendations contain Action 102 (Product)
        const hasRecProducts = recommendations?.actions?.some(a => a.action_code === 102 && a.items?.length > 0);
        
        let fallbackProducts = [];
        if (!hasRecProducts) {
            const prodResp = await fetchProducts({ limit: 5 });
            fallbackProducts = prodResp.data;
        }

        return {
            dashboard,
            recommendations,
            fallbackProducts
        };
    } catch (error) {
        throw error;
    }
}
