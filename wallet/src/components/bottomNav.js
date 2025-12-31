export function renderBottomNav(activeTab = 'home') {
  const getNavClass = (tab) => 
    tab === activeTab 
      ? 'nav-btn flex flex-col items-center gap-1 nav-active transition-all' 
      : 'nav-btn flex flex-col items-center gap-1 text-gray-400 transition-all';

  return `
    <nav
        id="bottom-nav"
        class="hidden fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-[480px] bg-white border-t border-gray-100 flex justify-around py-4 px-2 z-30"
    >
        <button
            data-tab="home"
            id="nav-home"
            class="${getNavClass('home')}"
        >
            <i class="fas fa-house-chimney text-lg"></i>
            <span class="text-[9px] font-extrabold uppercase tracking-tighter"
                >Beranda</span
            >
        </button>
        <button
            data-tab="market"
            id="nav-market"
            class="${getNavClass('market')}"
        >
            <i class="fas fa-shop text-lg"></i>
            <span class="text-[9px] font-extrabold uppercase tracking-tighter">Pasar</span>
        </button>
        <button
            data-tab="leaderboard"
            id="nav-leaderboard"
            class="${getNavClass('leaderboard')}"
        >
            <i class="fas fa-trophy text-lg"></i>
            <span class="text-[9px] font-extrabold uppercase tracking-tighter"
                >Peringkat</span
            >
        </button>
        <button
            data-tab="profile"
            id="nav-profile"
            class="${getNavClass('profile')}"
        >
            <i class="fas fa-user-ninja text-lg"></i>
            <span class="text-[9px] font-extrabold uppercase tracking-tighter">Akun</span>
        </button>
    </nav>
  `;
}
