export function renderLeaderboard() {
  return `
    <section id="leaderboard" class="section active p-4 pb-20">
        <div class="bg-indigo-50 p-6 rounded-[2rem] mb-6 flex justify-around items-end">
            <!-- Rank UI -->
            <div class="text-center">
                <div
                    class="w-12 h-12 bg-gray-200 rounded-full border-2 border-white overflow-hidden mb-2 mx-auto"
                >
                    <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=Bob" />
                </div>
                <div
                    class="bg-white w-10 h-10 rounded-xl flex items-center justify-center mx-auto shadow-sm font-bold text-gray-500"
                >
                    2
                </div>
            </div>
            <div class="text-center">
                <i class="fas fa-crown text-amber-400 mb-1"></i>
                <div
                    class="w-16 h-16 bg-amber-100 rounded-full border-4 border-white overflow-hidden mb-2 mx-auto ring-4 ring-amber-400/20"
                >
                    <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=Alice" />
                </div>
                <div
                    class="bg-amber-400 w-12 h-14 rounded-xl flex items-center justify-center mx-auto shadow-lg font-bold text-white text-xl"
                >
                    1
                </div>
            </div>
            <div class="text-center">
                <div
                    class="w-12 h-12 bg-orange-100 rounded-full border-2 border-white overflow-hidden mb-2 mx-auto"
                >
                    <img
                        src="https://api.dicebear.com/7.x/avataaars/svg?seed=Charlie"
                    />
                </div>
                <div
                    class="bg-white w-10 h-8 rounded-xl flex items-center justify-center mx-auto shadow-sm font-bold text-orange-400"
                >
                    3
                </div>
            </div>
        </div>

        <div class="space-y-2">
            <div
                class="flex items-center p-4 bg-white border border-gray-100 rounded-2xl shadow-sm"
            >
                <span class="w-8 font-bold text-gray-400">4</span>
                <img
                    src="https://api.dicebear.com/7.x/avataaars/svg?seed=Deni"
                    class="w-10 h-10 rounded-full bg-gray-50 mr-3"
                />
                <h4 class="font-bold text-sm flex-1">Deni Setiawan</h4>
                <span class="font-bold text-indigo-600"
                    >1,120 <span class="text-[10px] text-gray-400">pts</span></span
                >
            </div>
        </div>
    </section>
  `;
}
