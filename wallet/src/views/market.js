import { fetchProducts, fetchCategories, purchaseProduct } from "../lib/market.js";
import { getCurrentUser } from "../lib/auth.js";

let currentCategory = null;
let currentSearch = "";

export function renderMarket() {
  return `
    <section id="market" class="section active p-4 pb-20">
        <!-- Search -->
        <div class="mb-6 sticky top-0 z-10 pt-2 pb-2">
            <div class="relative">
                <input
                    id="market-search"
                    type="text"
                    placeholder="Cari item penukar poin..."
                    class="w-full pl-12 pr-4 py-3 bg-white border border-gray-200 rounded-2xl focus:ring-2 focus:ring-indigo-500 outline-none shadow-sm"
                />
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
            </div>
        </div>

        <!-- Categories -->
        <div class="mb-6">
            <div id="category-list" class="flex gap-2 overflow-x-auto no-scrollbar pb-2">
                <!-- Skeletons -->
                <div class="w-20 h-8 bg-gray-200 rounded-full animate-pulse shrink-0"></div>
                <div class="w-24 h-8 bg-gray-200 rounded-full animate-pulse shrink-0"></div>
                <div class="w-20 h-8 bg-gray-200 rounded-full animate-pulse shrink-0"></div>
            </div>
        </div>

        <!-- Product Grid -->
        <div id="product-grid" class="grid grid-cols-2 gap-4">
            <!-- Skeletons -->
            <div class="bg-white p-4 rounded-3xl h-48 animate-pulse"></div>
            <div class="bg-white p-4 rounded-3xl h-48 animate-pulse"></div>
            <div class="bg-white p-4 rounded-3xl h-48 animate-pulse"></div>
            <div class="bg-white p-4 rounded-3xl h-48 animate-pulse"></div>
        </div>
    </section>
  `;
}

export async function initMarketView() {
    const categoryList = document.getElementById("category-list");
    const productGrid = document.getElementById("product-grid");
    const searchInput = document.getElementById("market-search");

    if (!productGrid) return;

    // 1. Fetch & Render Categories
    try {
        const categories = await fetchCategories();
        renderCategories(categories, categoryList);
    } catch (e) {
        console.error("Failed to load categories", e);
    }

    // 2. Initial Product Load
    loadProducts();

    // 3. Search Listener
    let debounceTimer;
    searchInput.addEventListener("input", (e) => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            currentSearch = e.target.value.trim();
            loadProducts();
        }, 500);
    });
}

function renderCategories(categories, container) {
    container.innerHTML = "";
    
    // "All" button
    const allBtn = createCategoryBtn(null, "Semua", currentCategory === null);
    container.appendChild(allBtn);

    categories.forEach(cat => {
        const btn = createCategoryBtn(cat.id, cat.name, currentCategory === cat.id);
        container.appendChild(btn);
    });
}

function createCategoryBtn(id, name, isActive) {
    const btn = document.createElement("button");
    btn.className = `px-4 py-1.5 rounded-full text-xs font-bold whitespace-nowrap transition-colors ${
        isActive 
        ? "bg-indigo-600 text-white shadow-md shadow-indigo-200" 
        : "bg-white text-gray-600 border border-gray-200 hover:bg-gray-50"
    }`;
    btn.innerText = name;
    
    btn.addEventListener("click", () => {
        currentCategory = id;
        loadProducts();
        
        // Update active state visuals
        const siblings = btn.parentElement.children;
        for (let sib of siblings) {
            sib.className = sib.className.replace("bg-indigo-600 text-white shadow-md shadow-indigo-200", "bg-white text-gray-600 border border-gray-200");
        }
        btn.className = btn.className.replace("bg-white text-gray-600 border border-gray-200", "bg-indigo-600 text-white shadow-md shadow-indigo-200");
    });

    return btn;
}

async function loadProducts() {
    const grid = document.getElementById("product-grid");
    grid.innerHTML = `
        <div class="col-span-2 py-10 text-center text-gray-400">
            <i class="fas fa-spinner fa-spin text-2xl mb-2"></i><br>Memuat produk...
        </div>
    `;

    try {
        const params = {
            search: currentSearch,
            limit: 20
        };
        if (currentCategory) params.category_id = currentCategory;

        const { data } = await fetchProducts(params);
        renderProductGrid(data, grid);
    } catch (e) {
        grid.innerHTML = `<div class="col-span-2 text-center text-red-500 py-10">Gagal memuat produk.</div>`;
    }
}

function renderProductGrid(products, container) {
    container.innerHTML = "";

    if (products.length === 0) {
        container.innerHTML = `
            <div class="col-span-2 flex flex-col items-center justify-center py-10 text-gray-400">
                <i class="fas fa-box-open text-4xl mb-3 opacity-50"></i>
                <p class="text-sm">Tidak ada produk ditemukan.</p>
            </div>
        `;
        return;
    }

    products.forEach(product => {
        const card = document.createElement("div");
        card.className = "bg-white p-4 rounded-3xl border border-gray-100 shadow-sm flex flex-col h-full";
        
        // Parse metadata for image
        let imageUrl = null;
        try {
            // Backend sends raw JSON, axios might auto-parse, but if it's string:
            const meta = typeof product.metadata === 'string' ? JSON.parse(product.metadata) : product.metadata;
            imageUrl = meta?.image_url || meta?.image;
        } catch (e) {}

        const iconClass = getPlaceholderIcon(product.type);
        
        // Image / Icon
        const imageHtml = imageUrl 
            ? `<div class="h-32 rounded-2xl mb-3 bg-gray-50 bg-cover bg-center" style="background-image: url('${imageUrl}')"></div>`
            : `<div class="h-32 rounded-2xl mb-3 bg-indigo-50 flex items-center justify-center text-indigo-300"><i class="fas ${iconClass} text-4xl"></i></div>`;

        card.innerHTML = `
            ${imageHtml}
            <h3 class="font-bold text-gray-800 text-sm leading-tight mb-1 line-clamp-2">${product.name}</h3>
            <p class="text-[10px] text-gray-500 mb-3 line-clamp-2">${product.description || ''}</p>
            
            <div class="mt-auto flex justify-between items-center">
                <span class="text-xs font-bold text-amber-600 flex items-center">
                    <i class="fas fa-coins mr-1"></i>${product.points_price}
                </span>
                <button class="bg-indigo-600 text-white w-8 h-8 rounded-xl flex items-center justify-center shadow-sm shadow-indigo-200 btn-bounce buy-btn">
                    <i class="fas fa-shopping-bag text-xs"></i>
                </button>
            </div>
        `;

        // Purchase Click
        const buyBtn = card.querySelector(".buy-btn");
        buyBtn.addEventListener("click", () => confirmPurchase(product));

        container.appendChild(card);
    });
}

function getPlaceholderIcon(type) {
    if (!type) return "fa-box";
    const t = type.toLowerCase();
    if (t.includes("digital") || t.includes("data") || t.includes("voucher")) return "fa-wifi";
    if (t.includes("physical") || t.includes("barang")) return "fa-box-open";
    if (t.includes("food") || t.includes("drink")) return "fa-coffee";
    return "fa-gift";
}

function confirmPurchase(product) {
    const user = getCurrentUser();
    // Assuming user points are tracked in state or updated via profile fetch. 
    // Ideally we check locally first, but backend checks auth.
    
    // We import showModal from main.js or duplicate logic? 
    // Better to dispatch a custom event or use a global helper if available.
    // For now, let's access the global window function if we exposed it, or re-implement simple modal logic here?
    // Accessing DOM elements directly is safer given the modular structure.
    
    const modal = document.getElementById("modal");
    const title = document.getElementById("modal-title");
    const msg = document.getElementById("modal-msg");
    const icon = document.getElementById("modal-icon");
    const closeBtn = document.getElementById("modal-close-btn"); // Usually 'Tutup' or 'OK'
    
    // Customize modal for confirmation? The default modal in components/modal.js is informational (one button).
    // We need a CONFIRMATION modal (Yes/No).
    // Let's dynamically inject a "Confirm" button into the modal or use a browser confirm for MVP simplicity,
    // OR: Update modal.js to support confirmation actions.
    
    // MVP Approach: Browser confirm -> Success/Error Modal
    if (confirm(`Beli ${product.name} seharga ${product.points_price} poin?`)) {
        handlePurchase(product);
    }
}

async function handlePurchase(product) {
    // Show loading?
    try {
        const success = await purchaseProduct(product.id);
        if (success) {
            showSuccessModal(product.name);
            // Deduct points visually
            const pointsEl = document.getElementById("user-points");
            if (pointsEl) {
                const current = parseInt(pointsEl.innerText.replace(/,/g, ''));
                if (!isNaN(current)) {
                    pointsEl.innerText = (current - product.points_price).toLocaleString();
                }
            }
        } else {
            alert("Pembelian gagal. Poin mungkin tidak cukup.");
        }
    } catch (e) {
        alert("Terjadi kesalahan saat memproses pembelian.");
    }
}

function showSuccessModal(itemName) {
    const modal = document.getElementById("modal");
    if (!modal) return;
    
    document.getElementById("modal-title").innerText = "Berhasil!";
    document.getElementById("modal-msg").innerText = `Kamu telah menukar ${itemName}.`;
    const icon = document.getElementById("modal-icon");
    icon.className = "w-20 h-20 rounded-3xl flex items-center justify-center mx-auto mb-6 text-3xl bg-emerald-50 text-emerald-600";
    icon.innerHTML = `<i class="fas fa-check"></i>`;
    
    modal.classList.remove("hidden");
}