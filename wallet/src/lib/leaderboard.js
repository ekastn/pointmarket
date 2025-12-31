import { get } from "./api.js";

/**
 * Fetch leaderboard data.
 * @param {number} limit - Number of top users to fetch.
 * @returns {Promise<Array>} List of leaderboard entries.
 */
export async function fetchLeaderboard(limit = 20) {
    try {
        const response = await get(`/leaderboard?limit=${limit}`);
        if (response && response.success) {
            return response.data || [];
        }
        return [];
    } catch (error) {
        return [];
    }
}
