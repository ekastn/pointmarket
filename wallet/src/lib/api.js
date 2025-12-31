const BASE_URL = import.meta.env.VITE_API_BASE_URL || "";

/**
 * Generic API fetch function.
 * @param {string} endpoint - The API endpoint (e.g., '/users').
 * @param {object} [options={}] - Fetch options.
 * @returns {Promise<any>} - The JSON response or throws an error.
 */
export async function apiFetch(endpoint, options = {}) {
    const url = `${BASE_URL}${endpoint}`;

    const defaultHeaders = {
        "Content-Type": "application/json",
        Accept: "application/json",
    };

    const config = {
        ...options,
        headers: {
            ...defaultHeaders,
            ...options.headers,
        },
    };

    try {
        const response = await fetch(url, config);

        // Handle 401 Unauthorized (e.g., redirect to login or clear token)
        if (response.status === 401) {
            console.warn("Unauthorized access. Redirecting to login...");
            // You might want to dispatch a custom event or callback here
            // window.location.href = '/login';
        }

        // Check for HTTP errors
        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            const error = new Error(errorData.message || `HTTP Error: ${response.status}`);
            error.status = response.status;
            error.data = errorData;
            throw error;
        }

        // Return JSON response if content exists
        const contentType = response.headers.get("content-type");
        if (contentType && contentType.includes("application/json")) {
            return await response.json();
        }

        // Return text if not JSON (e.g. 204 No Content)
        return null;
    } catch (error) {
        console.error("API Fetch Error:", error);
        throw error;
    }
}

/**
 * GET request helper.
 * @param {string} endpoint
 * @param {object} [headers={}]
 */
export function get(endpoint, headers = {}) {
    return apiFetch(endpoint, { method: "GET", headers });
}

/**
 * POST request helper.
 * @param {string} endpoint
 * @param {object} body
 * @param {object} [headers={}]
 */
export function post(endpoint, body, headers = {}) {
    return apiFetch(endpoint, {
        method: "POST",
        headers,
        body: JSON.stringify(body),
    });
}

/**
 * PUT request helper.
 * @param {string} endpoint
 * @param {object} body
 * @param {object} [headers={}]
 */
export function put(endpoint, body, headers = {}) {
    return apiFetch(endpoint, {
        method: "PUT",
        headers,
        body: JSON.stringify(body),
    });
}

/**
 * PATCH request helper.
 * @param {string} endpoint
 * @param {object} body
 * @param {object} [headers={}]
 */
export function patch(endpoint, body, headers = {}) {
    return apiFetch(endpoint, {
        method: "PATCH",
        headers,
        body: JSON.stringify(body),
    });
}

/**
 * DELETE request helper.
 * @param {string} endpoint
 * @param {object} [headers={}]
 */
export function del(endpoint, headers = {}) {
    return apiFetch(endpoint, { method: "DELETE", headers });
}
