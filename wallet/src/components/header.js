export function renderHeader(points = 1250, title = 'Beranda') {
  const dateOptions = { weekday: 'long', year: 'numeric', month: 'short', day: 'numeric' };
  const currentDate = new Date().toLocaleDateString('id-ID', dateOptions);

  return `
    <header
        id="main-header"
        class="hidden p-4 flex justify-between items-center sticky top-0 bg-white/80 backdrop-blur-md z-20 border-b border-gray-100"
    >
        <div>
            <h1 id="page-title" class="text-xl font-bold text-indigo-600">${title}</h1>
            <p class="text-[10px] text-gray-400 uppercase tracking-wider font-bold">
                ${currentDate}
            </p>
        </div>
        <div class="flex items-center gap-3">
            <div
                class="bg-amber-100 px-3 py-1.5 rounded-full flex items-center gap-2 border border-amber-200"
            >
                <i class="fas fa-coins text-amber-500"></i>
                <span id="user-points" class="font-bold text-amber-700 text-sm">${points.toLocaleString()}</span>
            </div>
            <div
                class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center relative"
            >
                <i class="fas fa-bell text-gray-600"></i>
                <span
                    class="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full border-2 border-white"
                ></span>
            </div>
        </div>
    </header>
  `;
}