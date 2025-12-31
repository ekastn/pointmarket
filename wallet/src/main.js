import "./style.css";
import { renderHeader } from "./components/header.js";
import { renderBottomNav } from "./components/bottomNav.js";
import { renderModal } from "./components/modal.js";
import { renderLogin, setupLoginEvents } from "./views/login.js";
import { renderHome, initHomeView } from "./views/home.js";
import { renderMarket, initMarketView } from "./views/market.js";
import { renderLeaderboard } from "./views/leaderboard.js";
import { renderProfile, initProfileView } from "./views/profile.js";
import { isAuthenticated, logout, getCurrentUser } from "./lib/auth.js";

let state = {
  isLoggedIn: isAuthenticated(),
  activeTab: "home",
  points: 0,
  chartInstance: null,
};

// DOM Root
const app = document.querySelector("#app");

// Render Function
function render() {
  // If not logged in, show login screen only
  if (!state.isLoggedIn) {
    app.innerHTML = `
      <div class="mobile-container">
        ${renderLogin()}
      </div>
    `;
    setupLoginEvents(() => {
      state.isLoggedIn = true;
      render();
    });
    return;
  }

  // Update points from current user if available
  const user = getCurrentUser();
  if (user && user.points !== undefined) {
    state.points = user.points;
  }

  // If logged in, show Layout (Header + Content + Nav + Modal)
  let contentHtml = "";
  switch (state.activeTab) {
    case "home":
      contentHtml = renderHome();
      break;
    case "market":
      contentHtml = renderMarket();
      break;
    case "leaderboard":
      contentHtml = renderLeaderboard();
      break;
    case "profile":
      contentHtml = renderProfile();
      break;
    default:
      contentHtml = renderHome();
  }

  // Determine Page Title
  const titles = {
    home: "Beranda",
    market: "Marketplace",
    leaderboard: "Papan Skor",
    profile: "Akun",
  };
  const pageTitle = titles[state.activeTab] || "PointMarket";

  app.innerHTML = `
    <div class="mobile-container relative min-h-screen pb-20">
        ${renderHeader(state.points, pageTitle)}
        <main id="app-content">
            ${contentHtml}
        </main>
        ${renderBottomNav(state.activeTab)}
        ${renderModal()}
    </div>
  `;

  // Post-render setup
  setupAppListeners();
  
  // Show header (it has 'hidden' class by default in template, remove it)
  const header = document.getElementById("main-header");
  const nav = document.getElementById("bottom-nav");
  if (header) header.classList.remove("hidden");
  if (nav) nav.classList.remove("hidden");

  // View Initializations
  if (state.activeTab === "home") {
    setTimeout(initHomeView, 100);
  } else if (state.activeTab === "profile") {
    setTimeout(initProfileView, 100);
  } else if (state.activeTab === "market") {
    setTimeout(initMarketView, 100);
  }
}

// Event Listeners Setup
function setupAppListeners() {
  // Navigation
  document.querySelectorAll(".nav-btn").forEach((btn) => {
    btn.addEventListener("click", (e) => {
      const button = e.target.closest(".nav-btn");
      if (button) {
        state.activeTab = button.dataset.tab;
        render();
      }
    });
  });

  // Logout
  const logoutBtn = document.getElementById("logout-btn");
  if (logoutBtn) {
    logoutBtn.addEventListener("click", () => {
      logout();
      state.isLoggedIn = false;
      state.activeTab = "home";
      render();
    });
  }

  // Redeem Items
  document.querySelectorAll(".redeem-btn").forEach((btn) => {
    btn.addEventListener("click", (e) => {
      const button = e.target.closest(".redeem-btn");
      if (button) {
        const name = button.dataset.name;
        const cost = parseInt(button.dataset.cost, 10);
        redeemItem(name, cost);
      }
    });
  });

  // Modal Close
  const modalCloseBtn = document.getElementById("modal-close-btn");
  if (modalCloseBtn) {
    modalCloseBtn.addEventListener("click", closeModal);
  }
}

// Actions
function redeemItem(itemName, cost) {
  if (state.points >= cost) {
    state.points -= cost;
    // Re-render header points immediately
    const pointsEl = document.getElementById("user-points");
    if(pointsEl) pointsEl.innerText = state.points.toLocaleString();
    
    showModal(
      "Berhasil!",
      `Kamu telah menukar ${itemName}. Hadiah dikirim ke inventori.`,
      "check",
      "emerald"
    );
  } else {
    showModal(
      "Poin Kurang",
      "Kumpulkan poin lebih banyak dengan menyelesaikan tugas belajar.",
      "triangle-exclamation",
      "amber"
    );
  }
}

function showModal(title, msg, icon, colorClass) {
  const modal = document.getElementById("modal");
  const modalTitle = document.getElementById("modal-title");
  const modalMsg = document.getElementById("modal-msg");
  const modalIcon = document.getElementById("modal-icon");

  if (!modal) return;

  modalTitle.innerText = title;
  modalMsg.innerText = msg;
  modalIcon.className = `w-20 h-20 rounded-3xl flex items-center justify-center mx-auto mb-6 text-3xl bg-${colorClass}-50 text-${colorClass}-600`;
  modalIcon.innerHTML = `<i class="fas fa-${icon}"></i>`;

  modal.classList.remove("hidden");
}

function closeModal() {
  const modal = document.getElementById("modal");
  if (modal) modal.classList.add("hidden");
}

function initChart() {
  const canvas = document.getElementById("weeklyChart");
  if (!canvas) return;
  
  // Check if Chart is available
  if (typeof Chart === "undefined") return;

  const ctx = canvas.getContext("2d");

  if (state.chartInstance) {
    state.chartInstance.destroy();
  }

  state.chartInstance = new Chart(ctx, {
    type: "line",
    data: {
      labels: ["Minggu 1", "Minggu 2", "Minggu 3", "Minggu 4"],
      datasets: [
        {
          label: "Periode Terkini",
          data: [65, 78, 82, 90],
          borderColor: "#4f46e5",
          backgroundColor: "rgba(79, 70, 229, 0.1)",
          fill: true,
          tension: 0.4,
          pointRadius: 4,
          pointBackgroundColor: "#fff",
          borderWidth: 3,
        },
        {
          label: "Periode Lalu",
          data: [60, 65, 70, 72],
          borderColor: "#e2e8f0",
          borderDash: [5, 5],
          fill: false,
          tension: 0.4,
          borderWidth: 2,
        },
      ],
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false },
      },
      scales: {
        y: {
          beginAtZero: true,
          max: 100,
          grid: { display: false },
          ticks: { font: { size: 9 } },
        },
        x: {
          grid: { display: false },
          ticks: { font: { size: 9 } },
        },
      },
    },
  });
}

// Initial Render
render();
