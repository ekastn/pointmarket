export function renderHome() {
  return `
    <section id="home" class="section active p-4 pb-20">
        <!-- Level Card -->
        <div
            class="bg-gradient-to-br from-indigo-600 to-violet-700 rounded-[2rem] p-6 text-white mb-6 shadow-xl shadow-indigo-100"
        >
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p
                        class="opacity-80 text-xs font-bold uppercase tracking-widest mb-1"
                    >
                        Level Saat Ini
                    </p>
                    <h2 class="text-2xl font-bold">Gold Learner</h2>
                </div>
                <div class="bg-white/20 p-2 rounded-xl backdrop-blur-md">
                    <i class="fas fa-medal text-xl"></i>
                </div>
            </div>
            <div class="space-y-2">
                <div class="flex justify-between text-xs font-bold">
                    <span>Progres Diamond</span>
                    <span>750/1000 Poin</span>
                </div>
                <div class="w-full bg-black/10 rounded-full h-3 overflow-hidden">
                    <div
                        class="bg-white h-full rounded-full w-[75%] shadow-sm transition-all duration-1000"
                    ></div>
                </div>
            </div>
        </div>

        <!-- Rekomendasi Tugas & Misi -->
        <div class="mb-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-gray-800">Rekomendasi Untukmu</h3>
                <span class="text-xs text-indigo-600 font-bold">Lihat Semua</span>
            </div>

            <div class="space-y-3">
                <!-- Smart Task Recommendation -->
                <div
                    class="p-4 bg-indigo-50 border border-indigo-100 rounded-3xl flex items-center gap-4"
                >
                    <div
                        class="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center text-white shrink-0"
                    >
                        <i class="fas fa-rocket"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-sm font-bold text-indigo-900 leading-tight">
                            Misi: Master Strategi MSLQ
                        </h4>
                        <p class="text-[11px] text-indigo-600 font-medium">
                            Selesaikan modul pengaturan waktu
                        </p>
                        <div class="mt-2 flex items-center gap-2">
                            <span
                                class="bg-indigo-200 text-indigo-700 text-[10px] px-2 py-0.5 rounded-md font-bold"
                                >+200 XP</span
                            >
                            <span
                                class="bg-amber-200 text-amber-700 text-[10px] px-2 py-0.5 rounded-md font-bold"
                                >+50 Poin</span
                            >
                        </div>
                    </div>
                    <button
                        class="bg-white text-indigo-600 w-8 h-8 rounded-full flex items-center justify-center shadow-sm btn-bounce"
                    >
                        <i class="fas fa-play text-xs"></i>
                    </button>
                </div>

                <!-- Recommended Task -->
                <div
                    class="p-4 bg-white border border-gray-100 rounded-3xl flex items-center gap-4 shadow-sm"
                >
                    <div
                        class="w-12 h-12 bg-emerald-100 rounded-2xl flex items-center justify-center text-emerald-600 shrink-0"
                    >
                        <i class="fas fa-flask"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-sm font-bold text-gray-800 leading-tight">
                            Latihan Kimia: Redoks
                        </h4>
                        <p class="text-[11px] text-gray-400">
                            Direkomendasikan berdasarkan minatmu
                        </p>
                    </div>
                    <button
                        class="bg-gray-100 text-gray-600 px-4 py-2 rounded-xl text-xs font-bold btn-bounce"
                    >
                        Mulai
                    </button>
                </div>
            </div>
        </div>

        <!-- Product Recommendations -->
        <div class="mb-6">
            <h3 class="font-bold text-gray-800 mb-4">Mungkin Kamu Butuh</h3>
            <div class="flex gap-4 overflow-x-auto no-scrollbar pb-2">
                <div
                    class="min-w-[140px] bg-white border border-gray-100 p-3 rounded-2xl shadow-sm"
                >
                    <div
                        class="h-24 bg-gray-50 rounded-xl mb-3 flex items-center justify-center"
                    >
                        <i class="fas fa-wifi text-2xl text-blue-400"></i>
                    </div>
                    <p class="text-xs font-bold text-gray-700 mb-1">Paket Data 5GB</p>
                    <span class="text-[10px] text-amber-600 font-bold"
                        ><i class="fas fa-coins mr-1"></i>300 Poin</span
                    >
                </div>
                <div
                    class="min-w-[140px] bg-white border border-gray-100 p-3 rounded-2xl shadow-sm"
                >
                    <div
                        class="h-24 bg-gray-50 rounded-xl mb-3 flex items-center justify-center"
                    >
                        <i class="fas fa-coffee text-2xl text-orange-400"></i>
                    </div>
                    <p class="text-xs font-bold text-gray-700 mb-1">Voucher Kopi Sos</p>
                    <span class="text-[10px] text-amber-600 font-bold"
                        ><i class="fas fa-coins mr-1"></i>150 Poin</span
                    >
                </div>
            </div>
        </div>
    </section>
  `;
}
