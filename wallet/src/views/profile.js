import { fetchProfileData } from "../lib/profile.js";
import { getVARKLearningTips } from "../lib/varkHelpers.js";

export function renderProfile() {
  return `
    <section id="profile" class="section active p-4 pb-20">
        <!-- Profile Header -->
        <div class="bg-white rounded-[2rem] border border-gray-100 p-6 mb-6 shadow-sm text-center">
            <div class="relative w-24 h-24 mx-auto mb-4">
                <img
                    id="profile-avatar"
                    src="https://api.dicebear.com/7.x/avataaars/svg?seed=placeholder"
                    class="rounded-3xl border-4 border-indigo-50 p-1 bg-white shadow-md transition-opacity duration-500 opacity-0"
                    onload="this.style.opacity='1'"
                />
                <div
                    class="absolute -bottom-2 -right-2 bg-indigo-600 text-white w-8 h-8 rounded-xl flex items-center justify-center border-4 border-white"
                >
                    <i class="fas fa-check text-[10px]"></i>
                </div>
            </div>
            <h2 id="profile-name" class="text-xl font-bold text-gray-900">Memuat...</h2>
            <p id="profile-info" class="text-gray-400 text-xs font-medium uppercase tracking-widest mt-1">
                Sedang mengambil data...
            </p>

            <!-- Gaya Belajar Utama -->
            <div id="profile-vark-pills" class="mt-4 flex flex-wrap justify-center gap-2 min-h-[32px]">
                <!-- Skeletons -->
                <div class="w-20 h-6 bg-gray-50 animate-pulse rounded-full"></div>
                <div class="w-20 h-6 bg-gray-50 animate-pulse rounded-full"></div>
            </div>
        </div>

        <!-- Strategi Direkomendasikan -->
        <div class="mb-6">
            <h3 class="font-bold text-gray-800 mb-4 px-2 flex items-center gap-2">
                <i class="fas fa-lightbulb text-amber-500"></i>
                Strategi Belajar Untukmu
            </h3>
            <div id="strategy-list" class="space-y-3 px-2">
                <!-- Skeletons -->
                <div class="h-24 bg-gray-50 animate-pulse rounded-2xl"></div>
                <div class="h-24 bg-gray-50 animate-pulse rounded-2xl"></div>
            </div>
        </div>

        <!-- Badges Section -->
        <div class="mb-6">
            <h3 class="font-bold text-gray-800 mb-4 px-2">Koleksi Badge</h3>
            <div id="badge-grid" class="grid grid-cols-4 gap-3 px-2">
                <!-- Skeletons -->
                <div class="h-14 bg-gray-50 animate-pulse rounded-2xl"></div>
                <div class="h-14 bg-gray-50 animate-pulse rounded-2xl"></div>
                <div class="h-14 bg-gray-50 animate-pulse rounded-2xl"></div>
                <div class="h-14 bg-gray-50 animate-pulse rounded-2xl"></div>
            </div>
        </div>

        <!-- Skor AMS & MSLQ -->
        <div class="bg-white rounded-[2rem] border border-gray-100 p-6 mb-6 shadow-sm">
            <h3 class="font-bold text-gray-800 mb-4">Statistik Akademik</h3>

            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="p-4 bg-gray-50 rounded-2xl text-center">
                    <p class="text-[10px] text-gray-400 font-bold uppercase mb-1 tracking-tight">
                        Skor AMS
                    </p>
                    <div class="flex items-baseline justify-center gap-1">
                        <span id="ams-score" class="text-xl font-bold text-indigo-600">--</span>
                    </div>
                </div>
                <div class="p-4 bg-gray-50 rounded-2xl text-center">
                    <p class="text-[10px] text-gray-400 font-bold uppercase mb-1 tracking-tight">
                        Skor MSLQ
                    </p>
                    <div class="flex items-baseline justify-center gap-1">
                        <span id="mslq-score" class="text-xl font-bold text-emerald-600">--</span>
                    </div>
                </div>
            </div>

            <!-- Grafik Evaluasi Mingguan -->
            <div class="mt-4">
                <p class="text-xs font-bold text-gray-700 mb-3">
                    Evaluasi Mingguan (Keterlibatan)
                </p>
                <div class="relative h-[200px]">
                    <canvas id="weeklyChart" class="w-full"></canvas>
                </div>
            </div>
        </div>

        <!-- Account Actions -->
        <div class="space-y-2">
            <button
                id="logout-btn"
                class="w-full flex items-center justify-between p-4 bg-red-50 text-red-600 rounded-2xl font-bold text-sm btn-bounce"
            >
                <div class="flex items-center gap-3">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Keluar dari Akun</span>
                </div>
            </button>
        </div>
    </section>
  `;
}

export async function initProfileView() {
    try {
        const data = await fetchProfileData();
        const { user, student_stats, recommendations } = data;

        // 1. Hydrate Profile Header
        document.getElementById("profile-name").innerText = user.name || user.username;
        document.getElementById("profile-info").innerText = `Mahasiswa • ${user.role.toUpperCase()}`;
        document.getElementById("profile-avatar").src = user.avatar || `https://api.dicebear.com/7.x/avataaars/svg?seed=${user.username}`;
        
        // VARK Pills
        const varkContainer = document.getElementById("profile-vark-pills");
        varkContainer.innerHTML = "";
        const scores = student_stats?.learning_style?.scores || {};
        const styleEntries = Object.entries(scores)
            .filter(([_, val]) => val > 0)
            .sort((a, b) => b[1] - a[1])
            .slice(0, 2);

        if (styleEntries.length > 0) {
            styleEntries.forEach(([key, val], idx) => {
                const color = idx === 0 ? "indigo" : "violet";
                const pill = document.createElement("div");
                pill.className = `px-4 py-1.5 bg-${color}-50 text-${color}-700 rounded-full text-[10px] font-bold border border-${color}-100 uppercase`;
                pill.innerText = `${key}: ${val}%`;
                varkContainer.appendChild(pill);
            });
        } else {
            varkContainer.innerHTML = `<span class="text-[10px] text-gray-400">Belum ada data VARK</span>`;
        }

        // 2. Hydrate Strategies (VARK Tips + AI Coaching)
        const strategyContainer = document.getElementById("strategy-list");
        strategyContainer.innerHTML = "";
        const combinedStrategies = [];

        // A. VARK Tips (Static based on dominant style)
        if (student_stats?.learning_style?.label) {
            const varkInfo = getVARKLearningTips(student_stats.learning_style.label);
            // Add all VARK tips as individual cards
            if (varkInfo.study_tips && varkInfo.study_tips.length > 0) {
                varkInfo.study_tips.forEach(tip => {
                    combinedStrategies.push({
                        title: tip.title,
                        description: tip.description,
                        color: varkInfo.color || 'indigo'
                    });
                });
            }
        }

        // B. AI Coaching (Action 106)
        if (recommendations && recommendations.actions) {
            const coachingAction = recommendations.actions.find(a => a.action_code === 106);
            if (coachingAction && coachingAction.items) {
                coachingAction.items.forEach(item => {
                    combinedStrategies.push({
                        title: item.title,
                        description: item.description,
                        color: 'emerald' // Distinguish AI coaching
                    });
                });
            }
        }

        if (combinedStrategies.length > 0) {
            combinedStrategies.forEach((item) => {
                const color = item.color || 'indigo';
                const card = document.createElement("div");
                card.className = `strategy-card p-4 bg-gradient-to-r from-${color}-50 to-transparent border-l-4 border-${color}-600 rounded-r-2xl shadow-sm`;
                card.innerHTML = `
                    <h4 class="text-sm font-bold text-${color}-900 mb-1">${item.title}</h4>
                    <p class="text-[11px] text-gray-600 leading-relaxed">${item.description}</p>
                `;
                strategyContainer.appendChild(card);
            });
        } else {
            strategyContainer.innerHTML = `<div class="p-4 bg-gray-50 rounded-2xl text-center text-[11px] text-gray-400">Lengkapi kuesioner untuk mendapatkan strategi belajar.</div>`;
        }

        // 3. Hydrate Badges
        const badgeGrid = document.getElementById("badge-grid");
        badgeGrid.innerHTML = "";
        const badges = student_stats?.badges_progress || [];
        
        if (badges.length > 0) {
            badges.slice(0, 4).forEach(badge => {
                const achieved = badge.achieved;
                const badgeEl = document.createElement("div");
                badgeEl.className = `flex flex-col items-center gap-1 ${achieved ? "" : "opacity-40 grayscale"}`;
                badgeEl.innerHTML = `
                    <div class="w-14 h-14 ${achieved ? 'bg-indigo-50 border-indigo-100 text-indigo-500 badge-glow' : 'bg-gray-50 border-gray-100 text-gray-400'} rounded-2xl flex items-center justify-center border">
                        <i class="fas ${getBadgeIcon(badge.title)} text-2xl"></i>
                    </div>
                    <span class="text-[9px] font-bold text-gray-500 text-center truncate w-full">${badge.title}</span>
                `;
                badgeGrid.appendChild(badgeEl);
            });
        } else {
            badgeGrid.innerHTML = `<div class="col-span-4 p-4 text-center text-[10px] text-gray-400">Belum ada badge yang tersedia</div>`;
        }

        // 4. Hydrate Scores
        document.getElementById("ams-score").innerText = student_stats?.ams_score ? student_stats.ams_score.toFixed(1) : "--";
        document.getElementById("mslq-score").innerText = student_stats?.mslq_score ? student_stats.mslq_score.toFixed(1) : "--";

        // 5. Initialize Chart
        initProfileChart(student_stats?.weekly_evaluations || []);

    } catch (err) {
        console.error("Hydration Error:", err);
        const nameEl = document.getElementById("profile-name");
        if (nameEl) nameEl.innerText = "Gagal Memuat";
    }
}

function getBadgeIcon(title) {
    const t = title.toLowerCase();
    if (t.includes("kuis") || t.includes("quiz")) return "fa-brain";
    if (t.includes("tugas") || t.includes("assignment")) return "fa-tasks";
    if (t.includes("poin") || t.includes("point")) return "fa-coins";
    if (t.includes("kolaborasi") || t.includes("collaborate")) return "fa-users";
    if (t.includes("rajin") || t.includes("rutin")) return "fa-fire";
    return "fa-medal";
}

let profileChartInstance = null;

function initProfileChart(weeklyEvaluations) {
    const canvas = document.getElementById("weeklyChart");
    if (!canvas || typeof Chart === "undefined") return;

    const ctx = canvas.getContext("2d");
    if (profileChartInstance) profileChartInstance.destroy();

    // Prepare data
    // Sort and map evaluations to weekly labels
    const sortedEvals = [...weeklyEvaluations].sort((a, b) => new Date(a.due_date) - new Date(b.due_date));
    
    // Aggregate by week label if necessary, or just take the last 4-6
    const displayEvals = sortedEvals.slice(-6);
    const labels = displayEvals.map(e => `W${e.week_number || '?'}`);

    // If we have mixed types in one week, this logic needs to be smarter.
    // For simplicity in this mobile view, we'll show a single trend of the scores.
    
    profileChartInstance = new Chart(ctx, {
        type: "line",
        data: {
            labels: labels.length > 0 ? labels : ["W1", "W2", "W3", "W4"],
            datasets: [
                {
                    label: "Skor",
                    data: displayEvals.map(e => e.score),
                    borderColor: "#4f46e5",
                    backgroundColor: "rgba(79, 70, 229, 0.1)",
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: "#fff",
                    borderWidth: 3,
                }
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 7,
                    grid: { color: "#f1f5f9" },
                    ticks: { font: { size: 9 }, stepSize: 1 },
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 9 } },
                },
            },
        },
    });
}
