export function renderModal() {
  return `
    <div
        id="modal"
        class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-8 hidden backdrop-blur-sm"
    >
        <div
            class="bg-white rounded-[2.5rem] p-8 w-full max-w-xs text-center shadow-2xl scale-in"
        >
            <div
                id="modal-icon"
                class="w-20 h-20 rounded-3xl flex items-center justify-center mx-auto mb-6 text-3xl"
            ></div>
            <h3 id="modal-title" class="text-xl font-extrabold mb-2 text-gray-900">
                Sukses!
            </h3>
            <p id="modal-msg" class="text-gray-500 text-sm mb-8 leading-relaxed"></p>
            <button
                id="modal-close-btn"
                class="w-full bg-indigo-600 text-white py-4 rounded-2xl font-bold btn-bounce shadow-lg shadow-indigo-100"
            >
                Sip, Lanjutkan!
            </button>
        </div>
    </div>
  `;
}
