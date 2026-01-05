
    <!-- Navbar -->
    <nav class="fixed w-full z-50 glass-nav transition-all duration-300" id="navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20 items-center">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center gap-2 cursor-pointer" onclick="window.scrollTo(0,0)">
                    <img src="/public/landingpage/image/logoPM.png" alt="PointMarket Logo" class="h-10 w-auto object-contain">
                    <span class="font-bold text-2xl tracking-tight text-dark">POINTMARKET</span>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:flex space-x-8 items-center">
                    <a href="/" class="text-gray-600 hover:text-primary font-medium transition">Home</a>
                    <a href="#demo-video" class="text-gray-600 hover:text-primary font-medium transition">Video Tour</a>
                    <a href="#podcast" class="text-gray-600 hover:text-primary font-medium transition">Diskusi</a>
                    <a href="/login" class="bg-primary hover:bg-indigo-700 text-white px-6 py-2.5 rounded-full font-semibold transition shadow-lg shadow-primary/30 hover:shadow-primary/50 transform hover:-translate-y-0.5 inline-block">
                        Daftar Sekarang
                    </a>
                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden flex items-center">
                    <button id="mobile-menu-btn" class="text-gray-600 hover:text-primary focus:outline-none">
                        <i class="fas fa-bars text-2xl"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Mobile Menu Panel -->
        <div id="mobile-menu" class="hidden md:hidden bg-white border-t border-gray-100">
            <div class="px-4 pt-2 pb-6 space-y-2 shadow-lg">
                <a href="/" class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-primary hover:bg-gray-50 rounded-md">Home</a>
                <a href="#demo-video" class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-primary hover:bg-gray-50 rounded-md">Video Tour</a>
                <a href="#podcast" class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-primary hover:bg-gray-50 rounded-md">Diskusi</a>
                <a href="/login" class="block w-full mt-4 bg-primary text-white px-4 py-3 rounded-lg font-semibold shadow-md text-center">
                    Daftar Sekarang
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 overflow-hidden">
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 bg-purple-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob"></div>
        <div class="absolute top-0 left-0 -ml-20 -mt-20 w-96 h-96 bg-indigo-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="text-center max-w-3xl mx-auto">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-indigo-50 border border-indigo-100 text-primary font-medium text-sm mb-6 animate-fade-in-up">
                    <span class="relative flex h-2 w-2">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
                    </span>
                    The Future of Reward Ecosystem
                </div>
                <h1 class="text-5xl md:text-6xl font-bold tracking-tight text-gray-900 mb-8 leading-tight">
                    Ekosistem Loyalitas Cerdas: <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary to-purple-600">Penghargaan yang Terpersonalisasi</span>
                </h1>
                <p class="text-lg md:text-xl text-gray-600 mb-10 leading-relaxed">
                    Menghubungkan capaian belajar di <strong>LENTERAMU</strong> dengan kebebasan bertransaksi di PointMarket. Didukung teknologi AI untuk pengalaman penukaran poin yang paling relevan untuk siswa.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="#fitur" class="inline-flex justify-center items-center px-8 py-4 text-base font-semibold text-white bg-primary rounded-full hover:bg-indigo-700 transition shadow-lg shadow-primary/30 transform hover:-translate-y-1">
                        <i class="fas fa-network-wired mr-2"></i> Jelajahi Integrasi
                    </a>
                    <a href="#demo-video" class="inline-flex justify-center items-center px-8 py-4 text-base font-semibold text-gray-700 bg-white border border-gray-200 rounded-full hover:bg-gray-50 transition transform hover:-translate-y-1">
                        Lihat Video Tour
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="border-y border-gray-100 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                <div>
                    <div class="text-3xl font-bold text-gray-900 mb-1">5+</div>
                    <div class="text-sm text-gray-500">Target Merchant Partner</div>
                </div>
                <div>
                    <div class="text-3xl font-bold text-gray-900 mb-1">2M+</div>
                    <div class="text-sm text-gray-500">Target Pengguna Aktif</div>
                </div>
                <div>
                    <div class="text-3xl font-bold text-gray-900 mb-1">AI-Powered</div>
                    <div class="text-sm text-gray-500">RL Engine</div>
                </div>
                <div>
                    <div class="text-3xl font-bold text-gray-900 mb-1">Secure</div>
                    <div class="text-sm text-gray-500">Audit Transparan</div>
                </div>
            </div>
        </div>
    </section>

    <!-- VIDEO SECTION -->
    <section id="demo-video" class="py-24 bg-gray-50 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-base font-semibold text-primary uppercase tracking-wide">Video Tour</h2>
                <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                    Jelajahi Ekosistem PointMarket
                </p>
                <p class="mt-4 max-w-2xl text-xl text-gray-500 mx-auto">
                    Lihat betapa mudahnya mengelola semua poin reward siswa dalam satu dashboard terintegrasi.
                </p>
            </div>

            <div class="relative max-w-4xl mx-auto">
                <div class="absolute -top-4 -left-4 w-full h-full bg-gradient-to-r from-purple-400 to-indigo-500 rounded-2xl transform rotate-1 opacity-40 blur-sm"></div>
                <div class="absolute -bottom-4 -right-4 w-full h-full bg-gradient-to-r from-emerald-400 to-cyan-500 rounded-2xl transform -rotate-1 opacity-40 blur-sm"></div>

                <div class="relative bg-black rounded-2xl shadow-2xl overflow-hidden border-4 border-white aspect-w-16 aspect-h-9">
                    <video class="w-full h-auto object-cover rounded-lg" controls poster="/public/landingpage/image/cover_video.jpg">
                        <source src="/public/landingpage/video/PM_tour.mp4" type="video/mp4">
                        Maaf, browser Anda tidak mendukung video embedded.
                    </video>
                </div>
                <div class="mt-6 text-center text-sm text-gray-500">
                    <i class="fas fa-info-circle"></i> Format Video: MP4 High Definition
                </div>
            </div>
        </div>
    </section>

    <!-- AUDIO SECTION -->
    <section id="podcast" class="py-24 bg-white overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="lg:grid lg:grid-cols-2 lg:gap-16 items-center">
                <div class="mb-12 lg:mb-0">
                    <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl mb-6">
                        Diskusi & Debat Eksklusif
                    </h2>
                    <p class="text-lg text-gray-600 mb-8">
                        Dengarkan perdebatan mendalam tim kami mengenai masa depan Loyalty Program. Episode ini membahas topik panas: Gamifikasi vs Personalisasi Pembelajaran.
                    </p>
                    <ul class="space-y-4 mb-8">
                        <li class="flex items-start">
                            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-green-100 flex items-center justify-center text-green-600 mt-1">
                                <i class="fas fa-check text-xs"></i>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-medium text-gray-900">Analisis Mendalam</h4>
                                <p class="text-gray-500">Membedah pro dan kontra dari kedua strategi pembelajaran user.</p>
                            </div>
                        </li>
                        <li class="flex items-start">
                            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-green-100 flex items-center justify-center text-green-600 mt-1">
                                <i class="fas fa-bolt text-xs"></i>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-medium text-gray-900">Wawasan Eksklusif</h4>
                                <p class="text-gray-500">Tips penerapan strategi yang relevan untuk dunia pendidikan di Indonesia.</p>
                            </div>
                        </li>
                    </ul>
                </div>

                <div class="relative">
                    <div class="absolute top-0 right-0 -mr-12 -mt-12 w-64 h-64 bg-yellow-200 rounded-full mix-blend-multiply filter blur-2xl opacity-40"></div>
                    <div class="absolute bottom-0 left-0 -ml-12 -mb-12 w-64 h-64 bg-pink-200 rounded-full mix-blend-multiply filter blur-2xl opacity-40"></div>
                    <div class="relative bg-white p-8 rounded-2xl shadow-xl border border-gray-100">
                        <div class="flex items-center space-x-4 mb-6">
                            <div class="relative">
                                <img src="/public/landingpage/image/logoPM.png" alt="Album Art" class="w-20 h-20 rounded-lg object-cover shadow-md">
                                <div class="absolute -bottom-2 -right-2 bg-red-500 text-white text-xs px-2 py-0.5 rounded shadow">
                                    LIVE
                                </div>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900">Gamifikasi VS Personalisasi AI</h3>
                                <p class="text-sm text-primary font-medium">Virtual Discussion</p>
                                <p class="text-xs text-gray-400 mt-1">Durasi: 12:44 • Format: M4A</p>
                            </div>
                        </div>
                        <div class="bg-gray-50 rounded-full p-2 border border-gray-200">
                            <audio controls class="w-full focus:outline-none">
                                <source src="https://www2.cs.uic.edu/~i101/SoundFiles/StarWars3.wav" type="audio/wav">
                                <source src="/public/landingpage/audio/podcast.m4a" type="audio/mp4">
                                Browser Anda tidak mendukung elemen audio.
                            </audio>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="fitur" class="py-24 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-20">
                <h2 class="text-4xl font-extrabold text-gray-900">Mengapa Memilih PointMarket?</h2>
                <div class="mt-4 w-24 h-1.5 bg-primary mx-auto rounded-full"></div>
            </div>

            <div class="space-y-24">
                <!-- Row 1: Alur Integrasi -->
                <div class="grid md:grid-cols-2 gap-12 items-center">
                    <div class="order-2 md:order-1">
                        <div class="bg-white p-4 rounded-3xl shadow-xl border border-gray-100 transform hover:scale-[1.02] transition duration-500 zoom-cursor" onclick="openModal('/public/landingpage/image/alurIntegrasi.png')">
                            <img src="/public/landingpage/image/alurIntegrasi.png" alt="Alur Integrasi Lenteramu PointMarket" class="w-full rounded-2xl">
                        </div>
                        <p class="text-center text-xs text-gray-400 mt-3 italic"><i class="fas fa-search-plus mr-1"></i> Klik untuk memperbesar & geser alur</p>
                    </div>
                    <div class="order-1 md:order-2 px-4">
                        <div class="inline-flex items-center px-4 py-1.5 rounded-full bg-indigo-100 text-indigo-700 text-sm font-bold mb-6">
                            INTEGRASI SISTEM
                        </div>
                        <h3 class="text-3xl font-bold text-gray-900 mb-6 uppercase tracking-tight leading-tight">Alur Integrasi LENTERAMU-POINTMARKET</h3>
                        <p class="text-lg text-gray-600 leading-relaxed">
                            Kami menghadirkan sinergi inovatif antara ekosistem pembelajaran Lenteramu dengan keunggulan reward di PointMarket. Alur integrasi ini dirancang secara sistematis untuk memastikan setiap capaian belajar secara otomatis terkonversi menjadi poin loyalitas yang dapat digunakan di berbagai partner Pointmarket (Marketplace).
                        </p>
                    </div>
                </div>

                <!-- Row 2: Personalisasi Marketplace -->
                <div class="grid md:grid-cols-2 gap-12 items-center">
                    <div class="px-4">
                        <div class="inline-flex items-center px-4 py-1.5 rounded-full bg-pink-100 text-pink-700 text-sm font-bold mb-6">
                            USER EXPERIENCE
                        </div>
                        <h3 class="text-3xl font-bold text-gray-900 mb-6 uppercase tracking-tight leading-tight">Personalisasi Marketplace</h3>
                        <p class="text-lg text-gray-600 leading-relaxed">
                            Bukan sekadar penukaran poin biasa. Dengan algoritma RL (Reinforcement Learning), marketplace kami menyesuaikan rekomendasi hadiah berdasarkan preferensi dan kebiasaan pengguna. Alur RL Marketplace memastikan Anda mendapatkan penawaran yang paling relevan dan menguntungkan setiap saat.
                        </p>
                    </div>
                    <div>
                        <div class="bg-white p-4 rounded-3xl shadow-xl border border-gray-100 transform hover:scale-[1.02] transition duration-500 zoom-cursor" onclick="openModal('/public/landingpage/image/alurRLmarketplace.png')">
                            <img src="/public/landingpage/image/alurRLmarketplace.png" alt="Alur RL Marketplace" class="w-full rounded-2xl">
                        </div>
                        <p class="text-center text-xs text-gray-400 mt-3 italic"><i class="fas fa-search-plus mr-1"></i> Klik untuk memperbesar & geser alur</p>
                    </div>
                </div>

                <!-- Row 3: Pustaka Dokumen -->
                <div class="grid md:grid-cols-2 gap-12 items-center">
                    <div>
                        <div class="bg-white p-6 rounded-3xl shadow-xl border border-gray-100">
                            <div class="overflow-hidden rounded-xl border border-gray-100">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">No</th>
                                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nama Dokumen</th>
                                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-100 text-sm">
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-gray-500">01</td>
                                            <td class="px-6 py-4 whitespace-nowrap font-medium text-primary hover:underline">
                                                <a href="https://drive.google.com/file/d/1zNZKmrr3BR90D3CXkzFmbHUTe_qnc2H3/view?usp=sharing" target="_blank">Whitepaper V1.0</a>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap"><span class="px-2 py-1 bg-green-100 text-green-700 rounded-md text-xs">Tersedia</span></td>
                                        </tr>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-gray-500">02</td>
                                            <td class="px-6 py-4 whitespace-nowrap font-medium text-primary hover:underline">
                                                <a href="https://api.pointmarket.irc-enter.tech/api/v1/docs/index.html" target="_blank">API Documentation</a>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap"><span class="px-2 py-1 bg-green-100 text-green-700 rounded-md text-xs">Tersedia</span></td>
                                        </tr>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-gray-500">03</td>
                                            <td class="px-6 py-4 whitespace-nowrap font-medium text-primary hover:underline">
                                                <a href="https://drive.google.com/file/d/1x0WqYHJKkzLeGidtpLg3o_0tCDMa0UEB/view?usp=sharing" target="_blank">Legal Compliance</a>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap"><span class="px-2 py-1 bg-blue-100 text-blue-700 rounded-md text-xs">Tinjauan</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="px-4">
                        <div class="inline-flex items-center px-4 py-1.5 rounded-full bg-teal-100 text-teal-700 text-sm font-bold mb-6">
                            TRANSPARANSI DATA
                        </div>
                        <h3 class="text-3xl font-bold text-gray-900 mb-6 uppercase tracking-tight leading-tight">Pustaka Dokumen</h3>
                        <p class="text-lg text-gray-600 leading-relaxed">
                            Kami memberikan transparansi melalui Pustaka Dokumen yang komprehensif. Tabel di samping merinci akses ke dokumen teknis dan operasional kami. Dengan akses yang terpusat, mitra dan pengguna dapat memahami landasan keamanan optimal yang kami terapkan di setiap transaksi.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Image Modal -->
    <div id="image-modal" class="hidden">
        <span class="absolute top-5 right-8 text-white text-5xl font-light cursor-pointer hover:text-gray-400 transition z-[120]" onclick="closeModal()">&times;</span>
        
        <div class="modal-wrapper" id="modal-wrapper">
            <img src="" id="modal-content" alt="Preview">
        </div>

        <div id="caption" class="absolute top-10 left-0 w-full text-center text-gray-400 text-sm font-medium tracking-widest uppercase pointer-events-none"></div>

        <!-- Controls UI -->
        <div class="modal-controls">
            <button class="control-btn" onclick="adjustZoom(-0.5)"><i class="fas fa-search-minus"></i></button>
            <button class="control-btn" onclick="resetZoom()">Reset</button>
            <button class="control-btn" onclick="adjustZoom(0.5)"><i class="fas fa-search-plus"></i></button>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white pt-20 pb-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-16">
                <div class="col-span-1 md:col-span-1">
                    <div class="flex items-center gap-2 mb-6">
                        <img src="/public/landingpage/image/logoPM.png" alt="PointMarket Logo" class="h-8 w-auto object-contain">
                        <span class="text-xl font-bold">PointMarket</span>
                    </div>
                    <p class="text-gray-400 text-sm leading-relaxed">
                        Platform pertukaran poin digital terdepan di Indonesia. Maksimalkan nilai loyalitas Anda hari ini.
                    </p>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-6">Organisasi</h4>
                    <ul class="space-y-4 text-gray-400 text-sm">
                        <li><a href="#" class="hover:text-primary transition">Tentang Kami</a></li>
                        <li><a href="#" class="hover:text-primary transition">Karir</a></li>
                        <li><a href="#" class="hover:text-primary transition">Blog</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-6">Bantuan</h4>
                    <ul class="space-y-4 text-gray-400 text-sm">
                        <li><a href="#" class="hover:text-primary transition">Pusat Bantuan</a></li>
                        <li><a href="#" class="hover:text-primary transition">Syarat & Ketentuan</a></li>
                        <li><a href="#" class="hover:text-primary transition">Kebijakan Privasi</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-6">Hubungi Kami</h4>
                    <div class="flex space-x-4">
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-primary transition"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-primary transition"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-primary transition"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-800 pt-8 flex flex-col md:flex-row justify-between items-center">
                <p class="text-gray-500 text-sm">© 2025 PointMarket by Lenteramu. All rights reserved.</p>
                <div class="flex gap-2 mt-4 md:mt-0">
                    <button class="bg-gray-800 px-4 py-2 rounded-lg flex items-center gap-2 hover:bg-gray-700 transition text-left">
                        <i class="fab fa-apple text-xl"></i>
                        <div>
                            <div class="text-[10px] text-gray-400">Download on the</div>
                            <div class="text-sm font-bold">App Store</div>
                        </div>
                    </button>
                    <button class="bg-gray-800 px-4 py-2 rounded-lg flex items-center gap-2 hover:bg-gray-700 transition text-left">
                        <i class="fab fa-google-play text-xl"></i>
                        <div>
                            <div class="text-[10px] text-gray-400">GET IT ON</div>
                            <div class="text-sm font-bold">Google Play</div>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script>
        // Mobile Menu Toggle
        const btn = document.getElementById('mobile-menu-btn');
        const menu = document.getElementById('mobile-menu');

        btn.addEventListener('click', () => {
            menu.classList.toggle('hidden');
        });

        // Sticky Navbar Effect
        window.addEventListener('scroll', () => {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 20) {
                navbar.classList.add('shadow-md');
            } else {
                navbar.classList.remove('shadow-md');
            }
        });

        // Advanced Image Modal with Zoom & Pan
        let scale = 1;
        let pointX = 0;
        let pointY = 0;
        let startX = 0;
        let startY = 0;
        let isDragging = false;

        const modal = document.getElementById("image-modal");
        const modalImg = document.getElementById("modal-content");
        const modalWrapper = document.getElementById("modal-wrapper");
        const captionText = document.getElementById("caption");

        function setTransform() {
            modalImg.style.transform = `translate(${pointX}px, ${pointY}px) scale(${scale})`;
        }

        function openModal(imgSrc) {
            modal.classList.remove('hidden');
            modal.style.display = 'block';
            modalImg.src = imgSrc;
            captionText.innerHTML = imgSrc.split('/').pop().split('.')[0].replace(/([A-Z])/g, ' $1').trim();
            
            resetZoom();
            document.body.style.overflow = "hidden";
        }

        function closeModal() {
            modal.classList.add('hidden');
            modal.style.display = 'none';
            document.body.style.overflow = "auto";
        }

        function resetZoom() {
            scale = 1;
            pointX = 0;
            pointY = 0;
            setTransform();
        }

        function adjustZoom(delta) {
            scale += delta;
            if (scale < 0.5) scale = 0.5;
            if (scale > 5) scale = 5;
            setTransform();
        }

        // Mouse Wheel Zoom
        modalWrapper.onwheel = function (e) {
            e.preventDefault();
            const xs = (e.clientX - pointX) / scale;
            const ys = (e.clientY - pointY) / scale;
            const delta = -e.deltaY;
            
            if (delta > 0) scale *= 1.1;
            else scale /= 1.1;
            
            if (scale < 0.5) scale = 0.5;
            if (scale > 5) scale = 5;

            pointX = e.clientX - xs * scale;
            pointY = e.clientY - ys * scale;
            setTransform();
        }

        // Drag Pan Logic
        modalWrapper.onmousedown = function (e) {
            if (e.target === modalImg || e.target === modalWrapper) {
                e.preventDefault();
                startX = e.clientX - pointX;
                startY = e.clientY - pointY;
                isDragging = true;
            }
        }

        window.onmousemove = function (e) {
            if (!isDragging) return;
            e.preventDefault();
            pointX = e.clientX - startX;
            pointY = e.clientY - startY;
            setTransform();
        }

        window.onmouseup = function () {
            isDragging = false;
        }

        // Close on background click
        modalWrapper.onclick = function(e) {
            if (e.target === modalWrapper) closeModal();
        }

        // Escape key to close
        document.onkeydown = function(e) {
            if (e.key === "Escape") closeModal();
        }
    </script>
