import { login } from "../lib/auth.js";

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
            <div id="login-error" class="hidden p-3 bg-red-50 text-red-600 rounded-xl text-xs font-bold border border-red-100 mb-2">
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1"
                    >Username / NIM</label
                >
                <input
                    id="login-username"
                    type="text"
                    placeholder="Username / NIM"
                    class="w-full p-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all"
                />
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Password</label>
                <input
                    id="login-password"
                    type="password"
                    placeholder="Password"
                    class="w-full p-4 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all"
                />
            </div>
            <button
                id="login-btn"
                class="w-full bg-indigo-600 text-white p-4 rounded-2xl font-bold text-lg shadow-lg shadow-indigo-100 btn-bounce mt-4 flex items-center justify-center gap-2"
            >
                <span>Masuk Sekarang</span>
            </button>
        </div>
    </div>
  `;
}

export function setupLoginEvents(onSuccess) {
  const loginBtn = document.getElementById("login-btn");
  const usernameInput = document.getElementById("login-username");
  const passwordInput = document.getElementById("login-password");
  const errorEl = document.getElementById("login-error");

  if (!loginBtn) return;

  loginBtn.addEventListener("click", async () => {
    const username = usernameInput.value.trim();
    const password = passwordInput.value;

    if (!username || !password) {
      errorEl.innerText = "Username dan password wajib diisi";
      errorEl.classList.remove("hidden");
      return;
    }

    // UI Feedback
    const btnSpan = loginBtn.querySelector("span");
    const originalText = btnSpan.innerText;
    btnSpan.innerText = "Memproses...";
    loginBtn.disabled = true;
    errorEl.classList.add("hidden");

    try {
      await login(username, password);
      onSuccess();
    } catch (err) {
      errorEl.innerText = err.message || "Gagal masuk. Periksa kembali akun Anda.";
      errorEl.classList.remove("hidden");
      btnSpan.innerText = originalText;
      loginBtn.disabled = false;
    }
  });
}
