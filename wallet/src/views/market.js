export function renderMarket() {
  return `
    <section id="market" class="section active p-4 pb-20">
        <div class="flex gap-2 mb-6 overflow-x-auto pb-2 no-scrollbar">
            <button
                class="bg-indigo-600 text-white px-5 py-2.5 rounded-2xl text-xs font-bold"
            >
                Semua
            </button>
            <button
                class="bg-white border text-gray-600 px-5 py-2.5 rounded-2xl text-xs font-bold"
            >
                Pendidikan
            </button>
            <button
                class="bg-white border text-gray-600 px-5 py-2.5 rounded-2xl text-xs font-bold"
            >
                Voucher
            </button>
            <button
                class="bg-white border text-gray-600 px-5 py-2.5 rounded-2xl text-xs font-bold"
            >
                Donasi
            </button>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <!-- Marketplace Items -->
            <div class="bg-white rounded-3xl border border-gray-100 p-3 shadow-sm">
                <div
                    class="h-32 bg-indigo-50 rounded-2xl mb-3 flex items-center justify-center text-indigo-300"
                >
                    <i class="fas fa-ticket-alt text-4xl"></i>
                </div>
                <h4 class="font-bold text-sm mb-1 truncate">Voucher Try Out Premium</h4>
                <div class="flex items-center gap-1 text-amber-600 mb-3">
                    <i class="fas fa-coins text-xs"></i>
                    <span class="text-xs font-bold">450 Poin</span>
                </div>
                <button
                    class="redeem-btn w-full bg-indigo-600 text-white py-2.5 rounded-xl text-xs font-bold btn-bounce"
                    data-name="Try Out Premium"
                    data-cost="450"
                >
                    Tukar
                </button>
            </div>
            <div class="bg-white rounded-3xl border border-gray-100 p-3 shadow-sm">
                <div
                    class="h-32 bg-emerald-50 rounded-2xl mb-3 flex items-center justify-center text-emerald-300"
                >
                    <i class="fas fa-leaf text-4xl"></i>
                </div>
                <h4 class="font-bold text-sm mb-1 truncate">Adopsi Pohon Belajar</h4>
                <div class="flex items-center gap-1 text-amber-600 mb-3">
                    <i class="fas fa-coins text-xs"></i>
                    <span class="text-xs font-bold">200 Poin</span>
                </div>
                <button
                    class="redeem-btn w-full bg-emerald-600 text-white py-2.5 rounded-xl text-xs font-bold btn-bounce"
                    data-name="Adopsi Pohon"
                    data-cost="200"
                >
                    Tukar
                </button>
            </div>
        </div>
    </section>
  `;
}
