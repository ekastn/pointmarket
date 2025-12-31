import { fetchHomeData } from "../lib/home.js";

export function renderHome() {
  return `
    <section id="home" class="section active p-4 pb-20">
        <!-- Level Card -->
        <div
            id="level-card"
            class="bg-gradient-to-br from-indigo-600 to-violet-700 rounded-[2rem] p-6 text-white mb-6 shadow-xl shadow-indigo-100 min-h-[160px] flex flex-col justify-center"
        >
            <div class="animate-pulse flex flex-col gap-4">
                <div class="h-4 bg-white/20 rounded w-1/3"></div>
                <div class="h-8 bg-white/20 rounded w-1/2"></div>
                <div class="h-3 bg-white/20 rounded-full w-full mt-4"></div>
            </div>
        </div>

        <!-- Rekomendasi Tugas & Misi -->
        <div class="mb-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-gray-800">Rekomendasi Untukmu</h3>
            </div>

            <div id="mission-list" class="space-y-3">
                <!-- Skeletons -->
                <div class="p-4 bg-gray-50 rounded-3xl h-20 animate-pulse"></div>
                <div class="p-4 bg-gray-50 rounded-3xl h-20 animate-pulse"></div>
            </div>
        </div>

        <!-- Product Recommendations -->
        <div class="mb-6">
            <h3 class="font-bold text-gray-800 mb-4">Mungkin Kamu Butuh</h3>
            <div id="home-product-carousel" class="flex gap-4 overflow-x-auto no-scrollbar pb-2">
                <!-- Skeletons -->
                <div class="min-w-[140px] bg-gray-50 h-32 rounded-2xl animate-pulse"></div>
                <div class="min-w-[140px] bg-gray-50 h-32 rounded-2xl animate-pulse"></div>
            </div>
        </div>
    </section>
  `;
}

export async function initHomeView() {
    try {
        const data = await fetchHomeData();
        const { dashboard, recommendations, fallbackProducts } = data;

        // Sync points
        if (dashboard?.student_stats?.total_points !== undefined) {
            window.updateHeaderPoints(dashboard.student_stats.total_points);
        }

        // 1. Hydrate Level Card
        hydrateLevelCard(dashboard?.student_stats?.total_points || 0);

        // 2. Hydrate Missions (Action 105)
        hydrateMissions(recommendations);

        // 3. Hydrate Products (Action 102 or Fallback)
        hydrateProducts(recommendations, fallbackProducts);

    } catch (err) {
        console.error("Home Hydration Error:", err);
    }
}

function hydrateLevelCard(points) {
    const container = document.getElementById("level-card");
    if (!container) return;

    let level = "Bronze Learner";
    let nextLevel = "Silver";
    let threshold = 500;
    let icon = "fa-medal";

    if (points >= 2000) {
        level = "Diamond Pro";
        nextLevel = "Max Level";
        threshold = points;
        icon = "fa-gem";
    } else if (points >= 1000) {
        level = "Gold Learner";
        nextLevel = "Diamond";
        threshold = 2000;
        icon = "fa-crown";
    } else if (points >= 500) {
        level = "Silver Learner";
        nextLevel = "Gold";
        threshold = 1000;
        icon = "fa-award";
    }

    const progress = Math.min((points / threshold) * 100, 100);

    container.innerHTML = `
        <div class="flex justify-between items-start mb-4">
            <div>
                <p class="opacity-80 text-xs font-bold uppercase tracking-widest mb-1">Level Saat Ini</p>
                <h2 class="text-2xl font-bold">${level}</h2>
            </div>
            <div class="bg-white/20 p-2 rounded-xl backdrop-blur-md">
                <i class="fas ${icon} text-xl"></i>
            </div>
        </div>
        <div class="space-y-2">
            <div class="flex justify-between text-xs font-bold">
                <span>Progres ${nextLevel}</span>
                <span>${points}/${threshold} Poin</span>
            </div>
            <div class="w-full bg-black/10 rounded-full h-3 overflow-hidden">
                <div
                    class="bg-white h-full rounded-full shadow-sm transition-all duration-1000"
                    style="width: ${progress}%"
                ></div>
            </div>
        </div>
    `;
}

function hydrateMissions(recommendations) {
    const container = document.getElementById("mission-list");
    if (!container) return;

    const missionAction = recommendations?.actions?.find(a => a.action_code === 105);
    const items = missionAction?.items || [];

    if (items.length === 0) {
        container.innerHTML = `
            <div class="p-6 bg-white border border-dashed border-gray-200 rounded-3xl text-center text-gray-400">
                <p class="text-xs">Selesaikan tugas aktif untuk mendapatkan rekomendasi baru.</p>
            </div>
        `;
        return;
    }

    container.innerHTML = items.map(item => `
        <div class="p-4 bg-indigo-50 border border-indigo-100 rounded-3xl flex items-center gap-4">
            <div class="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center text-white shrink-0">
                <i class="fas fa-rocket"></i>
            </div>
            <div class="flex-1">
                <h4 class="text-sm font-bold text-indigo-900 leading-tight">${item.title}</h4>
                <p class="text-[11px] text-indigo-600 font-medium line-clamp-1">${item.description || 'Misi spesial untukmu'}</p>
                <div class="mt-2 flex items-center gap-2">
                    <span class="bg-indigo-200 text-indigo-700 text-[10px] px-2 py-0.5 rounded-md font-bold">+ XP</span>
                    <span class="bg-amber-200 text-amber-700 text-[10px] px-2 py-0.5 rounded-md font-bold">+ Poin</span>
                </div>
            </div>
            <button class="bg-white text-indigo-600 w-8 h-8 rounded-full flex items-center justify-center shadow-sm btn-bounce">
                <i class="fas fa-play text-xs"></i>
            </button>
        </div>
    `).join('');
}

function hydrateProducts(recommendations, fallback) {
    const container = document.getElementById("home-product-carousel");
    if (!container) return;

    const recAction = recommendations?.actions?.find(a => a.action_code === 102);
    const items = (recAction && recAction.items?.length > 0) ? recAction.items : fallback;

    if (items.length === 0) {
        container.innerHTML = `<p class="text-xs text-gray-400 px-2">Cek Marketplace untuk item menarik.</p>`;
        return;
    }

    container.innerHTML = items.map(product => {
        const iconClass = getPlaceholderIcon(product.type || "");
        return `
            <div class="min-w-[140px] bg-white border border-gray-100 p-3 rounded-2xl shadow-sm">
                <div class="h-24 bg-gray-50 rounded-xl mb-3 flex items-center justify-center text-indigo-200">
                    <i class="fas ${iconClass} text-2xl"></i>
                </div>
                <p class="text-xs font-bold text-gray-700 mb-1 truncate">${product.title || product.name}</p>
                <span class="text-[10px] text-amber-600 font-bold">
                    <i class="fas fa-coins mr-1"></i>${product.points_price || '---'} Poin
                </span>
            </div>
        `;
    }).join('');
}

function getPlaceholderIcon(type) {
    const t = type.toLowerCase();
    if (t.includes("digital") || t.includes("data")) return "fa-wifi";
    if (t.includes("food") || t.includes("coffee")) return "fa-coffee";
    return "fa-gift";
}