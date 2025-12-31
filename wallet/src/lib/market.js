import { get, post } from "./api.js";

/**
 * Fetch products with optional filtering.
 * @param {object} params - { page, limit, search, category_id }
 * @returns {Promise<object>} { data: [], meta: {} }
 */
export async function fetchProducts(params = {}) {
    const query = new URLSearchParams(params).toString();
    try {
        const response = await get(`/products?${query}`);
        if (response && response.success) {
            return {
                data: response.data || [],
                meta: {
                    page: response.page || 1,
                    limit: response.limit || 10,
                    total: response.total_records || 0,
                    total_pages: response.total_pages || 1
                }
            };
        }
        return { data: [], meta: {} };
    } catch (error) {
        console.error("Fetch Products Error:", error);
        throw error;
    }
}

/**
 * Fetch all product categories.
 * @returns {Promise<Array>} List of categories
 */
export async function fetchCategories() {
    try {
        const response = await get("/product-categories");
        if (response && response.success) {
            return response.data || [];
        }
        return [];
    } catch (error) {
        console.error("Fetch Categories Error:", error);
        return [];
    }
}

/**
 * Purchase a product.
 * @param {number} productId
 * @returns {Promise<boolean>} Success status
 */
export async function purchaseProduct(productId) {
    try {
        const response = await post(`/products/${productId}/purchase`);
        return response && response.success;
    } catch (error) {
        console.error("Purchase Error:", error);
        throw error;
    }
}
