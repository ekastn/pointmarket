export function renderProfile() {
  return `
    <section id="profile" class="section active p-4 pb-20">
        <div
            class="bg-white rounded-[2rem] border border-gray-100 p-6 mb-6 shadow-sm text-center"
        >
            <div class="relative w-24 h-24 mx-auto mb-4">
                <img
                    src="https://api.dicebear.com/7.x/avataaars/svg?seed=Alice"
                    class="rounded-3xl border-4 border-indigo-50 p-1 bg-white shadow-md"
                />
                <div
                    class="absolute -bottom-2 -right-2 bg-indigo-600 text-white w-8 h-8 rounded-xl flex items-center justify-center border-4 border-white"
                >
                    <i class="fas fa-check text-[10px]"></i>
                </div>
            </div>
            <h2 class="text-xl font-bold text-gray-900">Andi Wijaya</h2>
            <p class="text-gray-400 text-xs font-medium uppercase tracking-widest mt-1">
                Siswa Kelas 12 • IPA 1
            </p>

            <!-- Gaya Belajar Utama -->
            <div class="mt-4 flex flex-wrap justify-center gap-2">
                <div
                    class="px-4 py-1.5 bg-indigo-50 text-indigo-700 rounded-full text-[10px] font-bold border border-indigo-100"
                >
                    VISUAL: 75%
                </div>
                <div
                    class="px-4 py-1.5 bg-violet-50 text-violet-700 rounded-full text-[10px] font-bold border border-violet-100"
                >
                    KINESTETIK: 60%
                </div>
            </div>
        </div>

        <!-- Strategi Direkomendasikan (Baru) -->
        <div class="mb-6">
            <h3 class="font-bold text-gray-800 mb-4 px-2 flex items-center gap-2">
                <i class="fas fa-lightbulb text-amber-500"></i>
                Strategi Belajar Untukmu
            </h3>
            <div class="space-y-3 px-2">
                <div
                    class="strategy-card p-4 bg-gradient-to-r from-indigo-50 to-transparent border-l-4 border-indigo-600 rounded-r-2xl shadow-sm"
                >
                    <h4 class="text-sm font-bold text-indigo-900 mb-1">
                        Gunakan Mind Map Visual
                    </h4>
                    <p class="text-[11px] text-gray-600 leading-relaxed">
                        Skor Visual tinggimu menyarankan penggunaan peta konsep berwarna
                        untuk materi berat seperti Sejarah atau Biologi.
                    </p>
                </div>
                <div
                    class="strategy-card p-4 bg-gradient-to-r from-emerald-50 to-transparent border-l-4 border-emerald-600 rounded-r-2xl shadow-sm"
                >
                    <h4 class="text-sm font-bold text-emerald-900 mb-1">
                        Optimalkan Peer-Learning
                    </h4>
                    <p class="text-[11px] text-gray-600 leading-relaxed">
                        Berdasarkan skor AMS (Motivasi Ekstrinsik), belajar bersama
                        teman akan meningkatkan semangat kompetisimu.
                    </p>
                </div>
                <div
                    class="strategy-card p-4 bg-gradient-to-r from-amber-50 to-transparent border-l-4 border-amber-600 rounded-r-2xl shadow-sm"
                >
                    <h4 class="text-sm font-bold text-amber-900 mb-1">
                        Teknik Pomodoro 25-5
                    </h4>
                    <p class="text-[11px] text-gray-600 leading-relaxed">
                        Skor MSLQ (Regulasi Diri) menunjukkan perlunya struktur waktu
                        yang ketat untuk menjaga fokus jangka panjang.
                    </p>
                </div>
            </div>
        </div>

        <!-- Bages Section -->
        <div class="mb-6">
            <h3 class="font-bold text-gray-800 mb-4 px-2">Koleksi Badge</h3>
            <div class="grid grid-cols-4 gap-3 px-2">
                <div class="flex flex-col items-center gap-1">
                    <div
                        class="w-14 h-14 bg-amber-50 rounded-2xl flex items-center justify-center text-amber-500 border border-amber-100 badge-glow"
                    >
                        <i class="fas fa-fire text-2xl"></i>
                    </div>
                    <span class="text-[9px] font-bold text-gray-500">7 Hari Rutin</span>
                </div>
                <div class="flex flex-col items-center gap-1">
                    <div
                        class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-500 border border-blue-100"
                    >
                        <i class="fas fa-brain text-2xl"></i>
                    </div>
                    <span class="text-[9px] font-bold text-gray-500"
                        >Kuis Sempurna</span
                    >
                </div>
                <div class="flex flex-col items-center gap-1 opacity-40 grayscale">
                    <div
                        class="w-14 h-14 bg-gray-50 rounded-2xl flex items-center justify-center text-gray-400 border border-gray-100"
                    >
                        <i class="fas fa-trophy text-2xl"></i>
                    </div>
                    <span class="text-[9px] font-bold text-gray-500">Juara 1</span>
                </div>
                <div class="flex flex-col items-center gap-1">
                    <div
                        class="w-14 h-14 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-500 border border-emerald-100"
                    >
                        <i class="fas fa-users text-2xl"></i>
                    </div>
                    <span class="text-[9px] font-bold text-gray-500">Kolaborator</span>
                </div>
            </div>
        </div>

        <!-- Skor AMS & MSLQ -->
        <div class="bg-white rounded-[2rem] border border-gray-100 p-6 mb-6 shadow-sm">
            <h3 class="font-bold text-gray-800 mb-4">Statistik Akademik</h3>

            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="p-4 bg-gray-50 rounded-2xl">
                    <p
                        class="text-[10px] text-gray-400 font-bold uppercase mb-1 tracking-tight"
                    >
                        Skor AMS (Motivasi)
                    </p>
                    <div class="flex items-baseline gap-1">
                        <span class="text-xl font-bold text-indigo-600">4.2</span>
                        <span class="text-[10px] text-green-500 font-bold"
                            ><i class="fas fa-arrow-up"></i> 0.3</span
                        >
                    </div>
                    <p class="text-[9px] text-gray-400">Periode: Des 2025</p>
                </div>
                <div class="p-4 bg-gray-50 rounded-2xl">
                    <p
                        class="text-[10px] text-gray-400 font-bold uppercase mb-1 tracking-tight"
                    >
                        Skor MSLQ (Strategi)
                    </p>
                    <div class="flex items-baseline gap-1">
                        <span class="text-xl font-bold text-emerald-600">3.8</span>
                        <span class="text-[10px] text-emerald-500 font-bold"
                            >Stabil</span
                        >
                    </div>
                    <p class="text-[9px] text-gray-400">Periode: Des 2025</p>
                </div>
            </div>

            <!-- Grafik Evaluasi Mingguan -->
            <div class="mt-4">
                <p class="text-xs font-bold text-gray-700 mb-3">
                    Evaluasi Mingguan (Keterlibatan)
                </p>
                <canvas id="weeklyChart" class="w-full"></canvas>
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
