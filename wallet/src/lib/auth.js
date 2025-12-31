import { post } from "./api.js";

/**
 * Log in a user.
 * @param {string} username
 * @param {string} password
 * @returns {Promise<object>}
 */
export async function login(username, password) {
    try {
        const response = await post("/auth/login", { username, password });
        
        if (response && response.success && response.data.token) {
            localStorage.setItem("token", response.data.token);
            localStorage.setItem("user", JSON.stringify(response.data.user));
            return response.data;
        } else {
            throw new Error(response.message || "Login failed");
        }
    } catch (error) {
        throw error;
    }
}

/**
 * Log out the current user.
 */
export function logout() {
    localStorage.removeItem("token");
    localStorage.removeItem("user");
}

/**
 * Check if the user is currently authenticated.
 * @returns {boolean}
 */
export function isAuthenticated() {
    return !!localStorage.getItem("token");
}

/**
 * Get the current user data from localStorage.
 * @returns {object|null}
 */
export function getCurrentUser() {
    const user = localStorage.getItem("user");
    return user ? JSON.parse(user) : null;
}
