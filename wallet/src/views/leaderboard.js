import { fetchLeaderboard } from "../lib/leaderboard.js";
import { getCurrentUser } from "../lib/auth.js";

export function renderLeaderboard() {
  return `
    <section id="leaderboard" class="section active p-4 pb-20">
        <!-- Top 3 Podium -->
        <div id="leaderboard-podium" class="flex justify-center items-end gap-4 mb-8 pt-4">
            <!-- Skeletons -->
            <div class="w-20 h-32 bg-gray-200 rounded-t-2xl animate-pulse"></div>
            <div class="w-24 h-40 bg-gray-200 rounded-t-2xl animate-pulse"></div>
            <div class="w-20 h-28 bg-gray-200 rounded-t-2xl animate-pulse"></div>
        </div>

        <!-- Ranking List -->
        <div class="bg-white rounded-[2.5rem] border border-gray-100 shadow-lg p-6 min-h-[300px]">
            <h3 class="font-bold text-gray-800 mb-4 px-2">Peringkat Global</h3>
            <div id="ranking-list" class="space-y-4">
                <!-- Skeletons -->
                <div class="h-16 bg-gray-50 rounded-2xl animate-pulse"></div>
                <div class="h-16 bg-gray-50 rounded-2xl animate-pulse"></div>
                <div class="h-16 bg-gray-50 rounded-2xl animate-pulse"></div>
            </div>
        </div>
    </section>
  `;
}

export async function initLeaderboardView() {
    try {
        const data = await fetchLeaderboard(50);
        const currentUser = getCurrentUser();
        
        hydratePodium(data);
        hydrateRankList(data, currentUser?.id);

    } catch (err) {
        // Clear skeletons on error
        const podium = document.getElementById("leaderboard-podium");
        if (podium) podium.innerHTML = '<div class="text-xs text-red-500">Gagal memuat data.</div>';
        
        const list = document.getElementById("ranking-list");
        if (list) list.innerHTML = '<div class="text-xs text-red-500 text-center py-4">Gagal memuat peringkat.</div>';
    }
}

function hydratePodium(data) {
    const container = document.getElementById("leaderboard-podium");
    if (!container) return;

    if (data.length < 3) {
        // Handle low data case gracefully or just show what we have
    }

    const top3 = [data[1], data[0], data[2]]; // Order: 2nd, 1st, 3rd for visuals
    
    const podiumHtml = top3.map((user, idx) => {
        if (!user) return `<div class="w-20"></div>`; // Empty slot filler

        // Visual mapping based on index in this specific array [2nd, 1st, 3rd]
        const isFirst = idx === 1;
        const isSecond = idx === 0;
        const isThird = idx === 2;

        let heightClass = isFirst ? "h-40" : (isSecond ? "h-32" : "h-28");
        let colorClass = isFirst ? "bg-amber-100 text-amber-600" : (isSecond ? "bg-gray-100 text-gray-600" : "bg-orange-100 text-orange-600");
        let ringColor = isFirst ? "ring-amber-400" : (isSecond ? "ring-gray-300" : "ring-orange-300");
        let rank = isFirst ? 1 : (isSecond ? 2 : 3);
        
        // Handle avatar_url (string or sql.NullString object)
        let avatarUrl = user.avatar_url;
        if (avatarUrl && typeof avatarUrl === 'object') {
            avatarUrl = (avatarUrl.Valid && avatarUrl.String) ? avatarUrl.String : null;
        }
        
        let avatar = (typeof avatarUrl === 'string' && avatarUrl.trim() !== "") 
            ? avatarUrl 
            : `https://api.dicebear.com/7.x/avataaars/svg?seed=${user.username}`;

        return `
            <div class="flex flex-col items-center">
                <div class="relative mb-2">
                    <img src="${avatar}" class="w-12 h-12 rounded-full border-2 border-white shadow-md z-10 relative ${isFirst ? 'scale-125' : ''}">
                    ${isFirst ? '<div class="absolute -top-4 left-1/2 -translate-x-1/2 text-2xl text-amber-400 drop-shadow-sm"><i class="fas fa-crown"></i></div>' : ''}
                    <div class="absolute -bottom-2 left-1/2 -translate-x-1/2 w-5 h-5 rounded-full ${colorClass} flex items-center justify-center text-[10px] font-bold border-2 border-white z-20">
                        ${rank}
                    </div>
                </div>
                <div class="w-full ${heightClass} ${colorClass.split(' ')[0]} rounded-t-2xl flex flex-col items-center justify-end pb-3 px-2 text-center min-w-[80px]">
                    <span class="text-xs font-bold truncate w-full mb-1 text-gray-800">${user.display_name}</span>
                    <span class="text-[10px] font-bold opacity-80">${user.total_points} pts</span>
                </div>
            </div>
        `;
    }).join("");

    container.innerHTML = podiumHtml;
}

function hydrateRankList(data, currentUserId) {
    const container = document.getElementById("ranking-list");
    if (!container) return;

    // Remove top 3 from list
    const rest = data.slice(3);

    if (rest.length === 0) {
        container.innerHTML = `<div class="text-center text-gray-400 text-xs py-4">Belum ada peringkat lainnya.</div>`;
        return;
    }

    container.innerHTML = rest.map((user, idx) => {
        const rank = idx + 4;
        const isMe = user.id === currentUserId; 
        
        // Handle avatar_url (string or sql.NullString object)
        let avatarUrl = user.avatar_url;
        if (avatarUrl && typeof avatarUrl === 'object') {
            avatarUrl = (avatarUrl.Valid && avatarUrl.String) ? avatarUrl.String : null;
        }
        
        let avatar = (typeof avatarUrl === 'string' && avatarUrl.trim() !== "") 
            ? avatarUrl 
            : `https://api.dicebear.com/7.x/avataaars/svg?seed=${user.username}`;

        return `
            <div class="flex items-center gap-4 p-3 rounded-2xl ${isMe ? 'bg-indigo-50 border border-indigo-100' : 'hover:bg-gray-50'} transition-colors">
                <span class="font-bold text-gray-400 w-6 text-center">${rank}</span>
                <img src="${avatar}" class="w-10 h-10 rounded-full bg-white border border-gray-100">
                <div class="flex-1">
                    <h4 class="text-sm font-bold text-gray-800 truncate ${isMe ? 'text-indigo-900' : ''}">
                        ${user.display_name} ${isMe ? '(Anda)' : ''}
                    </h4>
                    <p class="text-[10px] text-gray-400">${user.username}</p>
                </div>
                <span class="font-bold text-indigo-600 text-xs">${user.total_points} pts</span>
            </div>
        `;
    }).join("");
}
