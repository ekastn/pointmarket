export function renderLogin() {
  return `
    <div id="login-screen">
        <div class="text-center mb-10">
            <div
                class="w-20 h-20 bg-indigo-600 rounded-3xl flex items-center justify-center mx-auto mb-4 shadow-xl shadow-indigo-200"
            >
                <i class="fas fa-graduation-cap text-4xl text-white"></i>
            </div>
            <h1 class="text-3xl font-extrabold text-gray-900">PointMarket</h1>
            <p class="text-gray-500 mt-2">Masuk untuk melanjutkan pembelajaran</p>
        </div>

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1"
                    >Email / Username</label
                >
                <input
                    type="text"
                    value="andi.wijaya@school.id"
                    class="w-full p-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all"
                />
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Password</label>
                <input
                    type="password"
                    value="password123"
                    class="w-full p-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all"
                />
            </div>
            <button
                id="login-btn"
                class="w-full bg-indigo-600 text-white p-4 rounded-2xl font-bold text-lg shadow-lg shadow-indigo-100 btn-bounce mt-4"
            >
                Masuk Sekarang
            </button>
        </div>
        <p class="text-center text-sm text-gray-400 mt-8">
            Belum punya akun? <span class="text-indigo-600 font-bold">Daftar</span>
        </p>
    </div>
  `;
}
