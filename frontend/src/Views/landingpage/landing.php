
    <!-- NAVIGATION -->
    <nav class="fixed w-full z-50 glass-nav transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <div class="flex items-center gap-3 cursor-pointer">
                    <!-- REPLACED: Updated to logo.png -->
                    <img src="/public/landingpage/image/logoPM.png" 
                         alt="PointMarket Logo" 
                         class="h-10 w-10 object-contain rounded-lg shadow-sm"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                    
                    <!-- Fallback Logo (Hidden by default unless image fails) -->
                    <div class="hidden w-10 h-10 bg-gradient-to-br from-brand-500 to-accent-600 rounded-lg items-center justify-center text-white font-bold">P</div>
                    
                    <span class="text-xl font-bold tracking-tight text-slate-900">POINTMARKET</span>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:flex space-x-8">
                    <a href="#ai-studio" class="text-slate-600 hover:text-brand-600 font-medium transition">Teknologi Studio AI</a>
                    <a href="#analytics" class="text-slate-600 hover:text-brand-600 font-medium transition">Analitik</a>
                    <a href="#features" class="text-slate-600 hover:text-brand-600 font-medium transition">Fitur Utama</a>
                
    <!-- Menu Item 4: Memahami PointMarket (Exclusive Tree Menu) -->
                <div class="relative group">
                        <button class="text-slate-600 hover:text-brand-600 font-medium transition">
                            <span>Pahami Mendalam</span>
                            <i class="ph-bold ph-caret-down group-hover:rotate-180 transition-transform duration-300"></i>
                        </button>
                        
                        <!-- Dropdown Panel -->
                        <div class="absolute top-[80%] -left-12 mt-0 w-80 bg-white rounded-2xl shadow-xl border border-slate-100 p-6 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 translate-y-2 group-hover:translate-y-0 z-50">
                            <!-- Decorative header in dropdown -->
                            <div class="mb-4 pb-2 border-b border-slate-100">
                                <h3 class="text-xs font-bold text-brand-600 uppercase tracking-widest flex items-center gap-2">
                                    <i class="ph-bold ph-tree-structure"></i>
                                    PointMarket
                                </h3>
                            </div>

                            <!-- Tree Structure Container -->
                            <div class="relative ml-2 pl-4 py-1">
                                <!-- Main Vertical Line for Tree -->
                                <div class="absolute left-0 top-2 bottom-6 w-0.5 bg-slate-200 rounded-full"></div>

                                <!-- Tree Item 1 -->
                                <div class="relative group/item mb-4">
                                    <!-- Horizontal Connector -->
                                    <span class="absolute -left-4 top-3.5 w-4 h-0.5 bg-slate-200 group-hover/item:bg-brand-300 transition-colors"></span>
                                    <!-- Dot Indicator -->
                                    <span class="absolute -left-[19px] top-2.5 w-2.5 h-2.5 rounded-full border-2 border-white bg-slate-300 group-hover/item:bg-brand-500 group-hover/item:scale-125 transition-all z-10 box-content shadow-sm"></span>
                                    
                                    <a href="/landing/sahabat-belajar" class="block pl-2 group-hover/item:translate-x-1 transition-transform duration-200">
                                        <div class="text-sm font-bold text-slate-800 group-hover/item:text-brand-600 transition-colors">Sahabat Belajar Cerdas</div>
                                        <div class="text-xs text-slate-500 leading-tight mt-0.5">Partner AI personal siswa</div>
                                    </a>
                                </div>

                                <!-- Tree Item 2 -->
                                <div class="relative group/item mb-4">
                                    <span class="absolute -left-4 top-3.5 w-4 h-0.5 bg-slate-200 group-hover/item:bg-brand-300 transition-colors"></span>
                                    <span class="absolute -left-[19px] top-2.5 w-2.5 h-2.5 rounded-full border-2 border-white bg-slate-300 group-hover/item:bg-brand-500 group-hover/item:scale-125 transition-all z-10 box-content shadow-sm"></span>
                                    
                                    <a href="/landing/alur-kerja" class="block pl-2 group-hover/item:translate-x-1 transition-transform duration-200">
                                        <div class="text-sm font-bold text-slate-800 group-hover/item:text-brand-600 transition-colors">Landasan Teori</div>
                                        <div class="text-xs text-slate-500 leading-tight mt-0.5">Basis psikologi & gamifikasi</div>
                                    </a>
                                </div>

                                <!-- Tree Item 3 -->
                                <div class="relative group/item">
                                    <span class="absolute -left-4 top-3.5 w-4 h-0.5 bg-slate-200 group-hover/item:bg-brand-300 transition-colors"></span>
                                    <!-- Connection fixer for last item to hide bottom line overhang if needed, though simple line works best -->
                                    <span class="absolute -left-[19px] top-2.5 w-2.5 h-2.5 rounded-full border-2 border-white bg-slate-300 group-hover/item:bg-brand-500 group-hover/item:scale-125 transition-all z-10 box-content shadow-sm"></span>
                                    
                                    <a href="/landing/studi-kasus" class="block pl-2 group-hover/item:translate-x-1 transition-transform duration-200">
                                        <div class="text-sm font-bold text-slate-800 group-hover/item:text-brand-600 transition-colors">Keunggulan</div>
                                        <div class="text-xs text-slate-500 leading-tight mt-0.5">Kebaruan & Roadmap Pengembangan</div>
                                    </a>
                                </div>
                            </div>           
                            
                            <!-- Bottom Action Button (Explore DNA) -->
                            <button onclick="openDnaModal()" class="inline-flex items-center gap-2 mt-6 px-6 py-3 bg-gradient-to-r from-brand-600 to-accent-600 text-white rounded-full font-semibold shadow-lg hover:shadow-xl transition transform hover:scale-105">
                            <i class="ph ph-dna text-lg"></i>
                                Lihat DNA PointMarket
                            </button>

                        </div>
                    </div>
                </div>    
 

                <!-- CTA Button -->
                <div class="hidden md:flex">
                    <a href="/landing/riset" class="bg-slate-900 hover:bg-slate-800 text-white px-6 py-2.5 rounded-full font-medium transition shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                        Tour & Diskusi
                    </a>
                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden">
                    <i class="ph ph-list text-2xl text-slate-700"></i>
                </div>
            </div>
        </div>
    </nav>

    <!-- HERO SECTION -->
    <section class="pt-32 pb-20 lg:pt-48 lg:pb-32 overflow-hidden hero-pattern">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="text-center max-w-4xl mx-auto">
                <div class="inline-flex items-center px-3 py-1 rounded-full bg-brand-50 text-brand-600 text-sm font-semibold mb-6 border border-brand-100 animate-fade-in-up">
                    <span class="mr-2">✨</span> Revolusi Pendidikan Berbasis Data
                </div>
                <h1 class="text-5xl lg:text-7xl font-extrabold tracking-tight mb-8 leading-tight">
                    Bukan Sekadar Poin.<br>
                    Ini <span class="text-gradient">Kecerdasan Emosional</span> Untuk Belajar.
                </h1>
                <p class="text-xl text-slate-600 mb-10 max-w-2xl mx-auto leading-relaxed">
                    PointMarket adalah "mesin motivasi" berbasis AI yang memetakan DNA motivasi siswa, menyesuaikan gaya belajar, dan menciptakan pengalaman gamifikasi yang personal.
                </p>
                <div class="flex flex-col sm:flex-row justify-center gap-4">
                    <a href="#ai-studio" class="px-8 py-4 bg-brand-600 text-white rounded-full font-bold text-lg shadow-brand-500/30 shadow-xl hover:bg-brand-700 transition transform hover:scale-105">
                        Lihat Cara Kerja AI
                    </a>
                    <!-- Demo Aplikasi Link -->
                    <a href="/login" class="px-8 py-4 bg-white text-slate-700 border border-slate-200 rounded-full font-bold text-lg hover:bg-slate-50 transition flex items-center justify-center gap-2">
                        <i class="ph ph-play-circle text-xl"></i> Demo Aplikasi
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- SOCIAL PROOF / PROBLEM STATEMENT (Background Narrative) -->
    <section class="py-12 bg-slate-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center divide-y md:divide-y-0 md:divide-x divide-slate-700">
                <div class="p-4">
                    <div class="text-4xl font-bold text-brand-400 mb-2">85%</div>
                    <p class="text-slate-300">Siswa lebih termotivasi jika materi sesuai gaya belajar mereka (Studi Kasus).</p>
                </div>
                <div class="p-4">
                    <div class="text-4xl font-bold text-red-400 mb-2">78%</div>
                    <p class="text-slate-300">Pengajar kesulitan mengakomodasi kebutuhan personal setiap siswa secara manual.</p>
                </div>
                <div class="p-4">
                    <div class="text-4xl font-bold text-green-400 mb-2">144+</div>
                    <p class="text-slate-300">Kombinasi kondisi psikologis siswa yang dapat dianalisis oleh PointMarket secara real-time.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- AI STUDIO WORKFLOW (VISUALIZATION KEPT INTACT) -->
    <section id="ai-studio" class="py-24 bg-studio-dark text-white relative overflow-hidden">
        <!-- Background Elements -->
        <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-10"></div>
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-brand-600 rounded-full blur-3xl opacity-20"></div>
        <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-accent-600 rounded-full blur-3xl opacity-20"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center mb-16">
                <span class="text-accent-400 font-bold tracking-widest uppercase text-xs mb-2 block">Arsitektur Sistem Cerdas</span>
                <h2 class="text-3xl md:text-5xl font-bold mb-6">Studio Produksi Motivasi Pribadi Siswa</h2>
                <p class="text-slate-300 max-w-3xl mx-auto text-lg">
                    Sistem kami tidak bekerja secara linear. Ia bekerja seperti kru film profesional yang merancang skenario belajar khusus untuk Anda, berdasarkan <strong>144 kombinasi kondisi psikologis</strong>.
                </p>
            </div>

            <!-- THE FLOWCHART VISUALIZATION -->
            <div class="relative">
                <!-- Desktop Connection Lines (SVG) -->
                <svg class="hidden lg:block absolute inset-0 w-full h-full pointer-events-none" style="z-index: 0;">
                    <!-- Line from Input to Director -->
                    <path d="M250 160 L420 160" stroke="#475569" stroke-width="2" stroke-dasharray="5,5" />
                    <!-- Line from Director to Art Director -->
                    <path d="M720 160 L850 160" stroke="#8b5cf6" stroke-width="4" />
                </svg>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                    
                    <!-- STEP 1: INPUT (The Actor/Student) -->
                    <div class="lg:col-span-3">
                        <div class="bg-slate-800/50 backdrop-blur-sm border border-slate-700 p-6 rounded-2xl relative group hover:border-brand-500 transition duration-300">
                            <div class="absolute -top-4 left-6 bg-slate-700 text-xs font-bold px-3 py-1 rounded-full border border-slate-600">INPUT DATA</div>
                            <div class="flex items-center gap-4 mb-4">
                                <div class="p-3 bg-brand-900/50 rounded-lg text-brand-400">
                                    <i class="ph ph-user-focus text-3xl"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-lg">Kondisi Siswa</h3>
                                    <p class="text-xs text-slate-400">Real-time State</p>
                                </div>
                            </div>
                            <ul class="space-y-2 text-sm text-slate-300">
                                <li class="flex items-center gap-2"><i class="ph ph-check text-green-400"></i> Profil Motivasi (AMS)</li>
                                <li class="flex items-center gap-2"><i class="ph ph-check text-green-400"></i> Intensitas (MSLQ)</li>
                                <li class="flex items-center gap-2"><i class="ph ph-check text-green-400"></i> Gaya Belajar (VARK)</li>
                            </ul>
                        </div>
                    </div>

                    <!-- ARROW MOBILE -->
                    <div class="lg:hidden flex justify-center text-slate-500"><i class="ph ph-arrow-down text-3xl"></i></div>

                    <!-- STEP 2: THE CORE (Director & Art Director) -->
                    <div class="lg:col-span-6 bg-slate-900/80 border border-accent-500/30 p-1 rounded-3xl shadow-2xl shadow-accent-900/20">
                        <div class="bg-gradient-to-br from-studio-light to-studio-dark rounded-[20px] p-8 relative overflow-hidden">
                            <!-- Animated Pulse Background -->
                            <div class="absolute top-0 right-0 w-64 h-64 bg-accent-500/10 rounded-full blur-3xl animate-pulse"></div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 relative z-10">
                                <!-- The Director (RL) -->
                                <div class="text-center md:text-left border-b md:border-b-0 md:border-r border-white/10 pb-6 md:pb-0 md:pr-6">
                                    <div class="flex items-center justify-center md:justify-start gap-3 mb-4">
                                        <i class="ph ph-film-strip text-3xl text-yellow-400"></i>
                                        <h3 class="font-bold text-xl text-white">Sang Sutradara</h3>
                                    </div>
                                    <p class="text-xs font-mono text-accent-300 mb-2">Algorithm: Reinforcement Learning</p>
                                    <p class="text-sm text-slate-300 leading-relaxed mb-4">
                                        Memutuskan <strong>"Jenis Adegan"</strong> apa yang paling efektif saat ini untuk menjaga alur cerita motivasi Anda.
                                    </p>
                                    <div class="flex flex-wrap gap-2 justify-center md:justify-start">
                                        <span class="px-2 py-1 bg-white/10 rounded text-xs">🎬 Action: Misi</span>
                                        <span class="px-2 py-1 bg-white/10 rounded text-xs">🏆 Action: Reward</span>
                                        <span class="px-2 py-1 bg-white/10 rounded text-xs">🤝 Action: Coaching</span>
                                    </div>
                                </div>

                                <!-- The Art Director (CBF) -->
                                <div class="text-center md:text-left pt-6 md:pt-0 md:pl-2">
                                    <div class="flex items-center justify-center md:justify-start gap-3 mb-4">
                                        <i class="ph ph-paint-brush-broad text-3xl text-pink-400"></i>
                                        <h3 class="font-bold text-xl text-white">Penata Artistik</h3>
                                    </div>
                                    <p class="text-xs font-mono text-pink-300 mb-2">Algorithm: Content-Based Filtering</p>
                                    <p class="text-sm text-slate-300 leading-relaxed mb-4">
                                        Menentukan <strong>"Properti & Kostum"</strong> (konten) agar relevan dengan minat dan gaya belajar Anda.
                                    </p>
                                    <div class="bg-slate-800/50 p-3 rounded-lg border border-slate-700">
                                        <div class="flex items-center gap-2 mb-2">
                                            <div class="w-2 h-2 rounded-full bg-green-400"></div>
                                            <span class="text-xs text-slate-300">Decision Output:</span>
                                        </div>
                                        <p class="text-xs text-white font-mono">"Tampilkan Leaderboard (Ekstrinsik) + Misi Visual (VARK)"</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ARROW MOBILE -->
                    <div class="lg:hidden flex justify-center text-slate-500"><i class="ph ph-arrow-down text-3xl"></i></div>

                    <!-- STEP 3: OUTPUT (Personalized Experience) -->
                    <div class="lg:col-span-3">
                        <div class="bg-gradient-to-b from-brand-600 to-brand-800 p-6 rounded-2xl shadow-xl transform transition hover:scale-105 duration-300 border border-brand-400">
                            <div class="absolute -top-4 left-6 bg-yellow-400 text-brand-900 text-xs font-bold px-3 py-1 rounded-full shadow-lg">OUTPUT</div>
                            <div class="text-center">
                                <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center text-3xl mx-auto mb-4 backdrop-blur-sm">
                                    <i class="ph ph-sparkle text-white"></i>
                                </div>
                                <h3 class="font-bold text-xl text-white mb-2">Gamifikasi Adaptif</h3>
                                <p class="text-sm text-brand-100 mb-4">Pengalaman belajar yang terasa "dibuat khusus" untuk Anda.</p>
                                <div class="space-y-2">
                                    <div class="h-1.5 bg-brand-900/30 rounded-full overflow-hidden">
                                        <div class="h-full bg-white w-[85%] animate-[width_2s_ease-out]"></div>
                                    </div>
                                    <div class="flex justify-between text-xs text-brand-200">
                                        <span>Engagement</span>
                                        <span>85% Boost</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Metric Stats Footer -->
            <div class="mt-16 grid grid-cols-2 md:grid-cols-4 gap-4 text-center border-t border-white/10 pt-8">
                <div>
                    <div class="text-3xl font-bold text-white">144</div>
                    <div class="text-xs text-slate-400 uppercase tracking-wide mt-1">Kombinasi Kondisi</div>
                </div>
                <div>
                    <div class="text-3xl font-bold text-white">5</div>
                    <div class="text-xs text-slate-400 uppercase tracking-wide mt-1">Aksi Motivasional</div>
                </div>
                <div>
                    <div class="text-3xl font-bold text-white">2</div>
                    <div class="text-xs text-slate-400 uppercase tracking-wide mt-1">AI Engine Core</div>
                </div>
                <div>
                    <div class="text-3xl font-bold text-white">100%</div>
                    <div class="text-xs text-slate-400 uppercase tracking-wide mt-1">Personalisasi</div>
                </div>
            </div>
        </div>
    </section>

    <!-- DATA & ANALYTICS VISUALIZATION -->
    <section id="analytics" class="py-24 bg-slate-900 overflow-hidden border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row gap-12 items-center">
                
                <!-- Left: Content Explanation -->
                <div class="w-full md:w-1/3 text-white">
                    <div class="inline-flex items-center px-3 py-1 rounded-full bg-slate-800 text-brand-400 text-xs font-semibold mb-4 border border-slate-700">
                        <span class="mr-2">📊</span> Data-Driven Insights
                    </div>
                    <h2 class="text-3xl font-bold mb-4">Bukti Kinerja AI dalam Menjaga Konsistensi</h2>
                    <p class="text-slate-400 mb-6 leading-relaxed">
                        Lihat bagaimana algoritma <em>Amotivation Rescue</em> kami mendeteksi penurunan (dip) motivasi di minggu ke-4 dan melakukan intervensi otomatis untuk mengembalikan performa siswa.
                    </p>
                    
                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="w-3 h-3 rounded-full bg-brand-500"></div>
                            <span class="text-sm text-slate-300">PointMarket Adaptive AI</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-3 h-3 rounded-full bg-slate-600"></div>
                            <span class="text-sm text-slate-300">Metode Pembelajaran Tradisional</span>
                        </div>
                    </div>
                </div>

                <!-- Right: Chart Container -->
                <div class="w-full md:w-2/3 bg-slate-800 p-6 rounded-2xl border border-slate-700 shadow-2xl relative">
                    <!-- Chart 1: Motivation Graph -->
                    <canvas id="motivationChart" class="w-full h-[300px]"></canvas>
                    
                    <!-- Annotations Overlay (Simulated) -->
                    <div class="absolute top-1/2 left-1/3 transform -translate-y-12 bg-red-500/10 border border-red-500/50 text-red-400 px-2 py-1 rounded text-xs backdrop-blur-sm animate-pulse">
                        ⚠️ Amotivation Detected
                    </div>
                    <div class="absolute top-1/3 right-1/4 transform translate-y-4 bg-green-500/10 border border-green-500/50 text-green-400 px-2 py-1 rounded text-xs backdrop-blur-sm">
                        ✅ AI Recovery Action
                    </div>
                </div>
            </div>

            <!-- Second Row: DNA Profiling Visualization -->
             <div class="mt-20 grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                
                <!-- Chart Container -->
                <div class="order-2 md:order-1 bg-white p-6 rounded-2xl border border-slate-200 shadow-lg relative">
                    <canvas id="radarChart" class="w-full h-[350px]"></canvas>
                </div>

                <!-- Content Explanation -->
                <div class="order-1 md:order-2">
                    <h2 class="text-3xl font-bold text-white mb-4">Pemetaan 5 Dimensi Belajar</h2>
                    <p class="text-slate-400 mb-6 leading-relaxed">
                        Sistem kami tidak hanya menilai "pintar" atau "kurang". Kami memvisualisasikan siswa dalam spektrum <strong>VARK + Social</strong>. Radar chart ini memungkinkan guru melihat potensi tersembunyi siswa kinestetik yang seringkali terabaikan di kelas konvensional.
                    </p>
                    <ul class="space-y-3">
                        <li class="flex items-center gap-3 text-slate-300">
                            <i class="ph ph-check-circle text-brand-500 text-xl"></i>
                            <span>Visual & Auditory Processing</span>
                        </li>
                        <li class="flex items-center gap-3 text-slate-300">
                            <i class="ph ph-check-circle text-brand-500 text-xl"></i>
                            <span>Kinesthetic & Tactile Engagement</span>
                        </li>
                        <li class="flex items-center gap-3 text-slate-300">
                            <i class="ph ph-check-circle text-brand-500 text-xl"></i>
                            <span>Social vs Solitary Preference</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- KEY FEATURES GRID (UPDATED: 3D TILT EFFECT) -->
    <section id="features" class="py-24 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-12">
                <span class="text-brand-600 font-bold tracking-wider uppercase text-sm">Fitur Utama</span>
                <h2 class="text-3xl md:text-4xl font-bold text-slate-900 mt-2">5 Pilar Kecerdasan PointMarket</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="tilt-card-container group">
                    <div class="tilt-card relative bg-white p-8 rounded-2xl border border-slate-100 shadow-sm hover:shadow-2xl transition-all duration-300 overflow-hidden h-full">
                        <div class="tilt-glare"></div>
                        <div class="tilt-card-content relative z-10">
                            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center text-2xl mb-6 group-hover:bg-blue-600 group-hover:text-white transition">
                                <i class="ph ph-dna"></i>
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 mb-3">Profil Motivasi 2D</h3>
                            <p class="text-slate-600 text-sm">
                                Kami tidak melihat siswa sebagai satu entitas seragam. Kami memetakan tipe motivasi (mengapa Anda belajar) dan level intensitasnya menggunakan AMS & MSLQ.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Feature 2 -->
                <div class="tilt-card-container group">
                    <div class="tilt-card relative bg-white p-8 rounded-2xl border border-slate-100 shadow-sm hover:shadow-2xl transition-all duration-300 overflow-hidden h-full">
                        <div class="tilt-glare"></div>
                        <div class="tilt-card-content relative z-10">
                            <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-lg flex items-center justify-center text-2xl mb-6 group-hover:bg-purple-600 group-hover:text-white transition">
                                <i class="ph ph-chat-text"></i>
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 mb-3">Analisis Gaya Belajar (NLP)</h3>
                            <p class="text-slate-600 text-sm">
                                AI "mendengarkan" tulisan Anda untuk mendeteksi gaya belajar VARK dengan Decision Thresholds yang akurat, menghindari bias kuesioner statis.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Feature 3 -->
                <div class="tilt-card-container group">
                    <div class="tilt-card relative bg-white p-8 rounded-2xl border border-slate-100 shadow-sm hover:shadow-2xl transition-all duration-300 overflow-hidden h-full">
                        <div class="tilt-glare"></div>
                        <div class="tilt-card-content relative z-10">
                            <div class="w-12 h-12 bg-green-50 text-green-600 rounded-lg flex items-center justify-center text-2xl mb-6 group-hover:bg-green-600 group-hover:text-white transition">
                                <i class="ph ph-shield-check"></i>
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 mb-3">Jaring Pengaman "Amotivation"</h3>
                            <p class="text-slate-600 text-sm">
                                Saat motivasi hilang, AI menjadi mitra yang berempati—memberikan 'Quick Wins' dan dukungan psikologis, bukan hukuman yang menjatuhkan.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Feature 4 -->
                <div class="tilt-card-container group">
                    <div class="tilt-card relative bg-white p-8 rounded-2xl border border-slate-100 shadow-sm hover:shadow-2xl transition-all duration-300 overflow-hidden h-full">
                        <div class="tilt-glare"></div>
                        <div class="tilt-card-content relative z-10">
                            <div class="w-12 h-12 bg-orange-50 text-orange-600 rounded-lg flex items-center justify-center text-2xl mb-6 group-hover:bg-orange-600 group-hover:text-white transition">
                                <i class="ph ph-eye-slash"></i>
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 mb-3">Gamifikasi Adaptif</h3>
                            <p class="text-slate-600 text-sm">
                                Data-driven gamification yang tahu kapan harus menampilkan Leaderboard (untuk tipe Achievement) dan kapan harus menyembunyikannya.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Feature 5 -->
                <div class="tilt-card-container group">
                    <div class="tilt-card relative bg-white p-8 rounded-2xl border border-slate-100 shadow-sm hover:shadow-2xl transition-all duration-300 overflow-hidden h-full">
                        <div class="tilt-glare"></div>
                        <div class="tilt-card-content relative z-10">
                            <div class="w-12 h-12 bg-red-50 text-red-600 rounded-lg flex items-center justify-center text-2xl mb-6 group-hover:bg-red-600 group-hover:text-white transition">
                                <i class="ph ph-users-three"></i>
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 mb-3">Integrasi Ekosistem</h3>
                            <p class="text-slate-600 text-sm">
                                Bagian dari ekosistem pembelajaran <strong>LENTERAMU</strong>. Kolaborasi holistik antara konten, kognisi, dan motivasi.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SCENARIOS / USE CASES -->
    <section id="benefits" class="py-24 bg-white overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-center mb-16">Pengalaman yang Berbeda untuk Setiap Siswa</h2>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                <!-- Case A -->
                <div class="flex flex-col md:flex-row gap-6 items-center bg-slate-50 p-6 rounded-2xl border border-slate-100">
                    <div class="w-24 h-24 flex-shrink-0 bg-white rounded-full flex items-center justify-center text-4xl shadow-md">
                        🚀
                    </div>
                    <div>
                        <h4 class="text-lg font-bold text-slate-900">Siswa "High Achiever"</h4>
                        <p class="text-slate-500 text-sm mb-3">Tipe Ekstrinsik & Kompetitif</p>
                        <p class="text-slate-700 italic">"PointMarket menampilkan Leaderboard global untuk saya. Misi yang diberikan penuh tantangan dengan badge eksklusif yang memacu adrenalin."</p>
                    </div>
                </div>

                <!-- Case B -->
                <div class="flex flex-col md:flex-row gap-6 items-center bg-brand-50 p-6 rounded-2xl border border-brand-100">
                    <div class="w-24 h-24 flex-shrink-0 bg-white rounded-full flex items-center justify-center text-4xl shadow-md">
                        🌱
                    </div>
                    <div>
                        <h4 class="text-lg font-bold text-slate-900">Siswa "Burnout"</h4>
                        <p class="text-slate-500 text-sm mb-3">Fase Amotivation</p>
                        <p class="text-slate-700 italic">"Sistem menyembunyikan skor kompetisi. AI memberikan saya tugas kecil yang mudah diselesaikan (Quick Wins) dan pesan penyemangat pribadi."</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- NEW SECTION: LENTERAMU ECOSYSTEM -->
    <section class="py-24 bg-slate-50 border-t border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col lg:flex-row items-center gap-16">
            <div class="lg:w-1/2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-brand-100 text-brand-700 text-xs font-bold mb-6">
                    <i class="ph ph-circles-three-plus"></i> Ekosistem Terintegrasi
                </div>
                <h2 class="text-3xl md:text-4xl font-bold text-slate-900 mb-6">Jantung dari Ekosistem <span class="text-brand-600">LENTERAMU</span></h2>
                <p class="text-lg text-slate-600 leading-relaxed mb-6">
                    Inovasi PointMarket tidak berdiri sendiri. Ia adalah bagian vital dari <strong>LENTERAMU</strong> (Learning AI-Navigated Tera-Personalized Education Resource Application – Monitoring & Understanding)—sebuah ekosistem pembelajaran holistik yang menyatukan tiga elemen fundamental pendidikan masa depan.
                </p>
                <p class="text-slate-600 mb-8">
                    Jika konten adalah "bahan bakar" dan kognisi adalah "mesin", maka PointMarket adalah "sistem pengapian" yang menjaga api motivasi siswa tetap menyala.
                </p>
                
                <!-- Pillars -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="p-4 bg-white rounded-xl shadow-sm border border-slate-100 text-center transform hover:-translate-y-1 transition duration-300">
                        <div class="w-10 h-10 mx-auto bg-slate-100 rounded-full flex items-center justify-center text-slate-600 mb-3"><i class="ph ph-book text-xl"></i></div>
                        <h4 class="font-bold text-slate-800 text-sm">Konten</h4>
                    </div>
                    <div class="p-4 bg-white rounded-xl shadow-sm border border-slate-100 text-center transform hover:-translate-y-1 transition duration-300">
                        <div class="w-10 h-10 mx-auto bg-slate-100 rounded-full flex items-center justify-center text-slate-600 mb-3"><i class="ph ph-brain text-xl"></i></div>
                        <h4 class="font-bold text-slate-800 text-sm">Kognisi</h4>
                    </div>
                    <div class="p-4 bg-brand-50 rounded-xl shadow-sm border border-brand-100 text-center relative overflow-hidden transform hover:-translate-y-1 transition duration-300">
                        <div class="absolute inset-0 border-2 border-brand-500/20 rounded-xl animate-pulse"></div>
                        <div class="w-10 h-10 mx-auto bg-brand-100 rounded-full flex items-center justify-center text-brand-600 mb-3"><i class="ph ph-lightning text-xl"></i></div>
                        <h4 class="font-bold text-brand-700 text-sm">Motivasi</h4>
                    </div>
                </div>
            </div>
            
            <div class="lg:w-1/2 relative">
               <!-- Visual representation of Holistik Integration -->
               <div class="absolute -top-10 -right-10 w-32 h-32 bg-brand-200 rounded-full blur-3xl opacity-50"></div>
               <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-accent-200 rounded-full blur-3xl opacity-50"></div>
               
               <img src="/public/landingpage/image/gambar2.png" alt="Kolaborasi Ekosistem" class="relative z-10 rounded-2xl shadow-2xl grayscale hover:grayscale-0 transition duration-700 border-4 border-white">
               
               <div class="mt-6 text-center">
                   <p class="text-sm text-slate-500 italic">"Masa depan pendidikan adalah integrasi holistik antara konten, kognisi, dan motivasi."</p>
               </div>
            </div>
        </div>
    </section>

    <!-- TRUST & PARTNERSHIP -->
    <section class="py-20 bg-slate-900 text-slate-400 border-t border-slate-800">
        <div class="max-w-4xl mx-auto text-center px-4">
            <h2 class="text-2xl font-semibold text-white mb-6">Mitra Pendidik & Sekolah</h2>
            <p class="mb-8">
                Jika AI dapat menjadi mitra psikologis siswa, peran pengajar berevolusi menjadi arsitek pengalaman belajar. 
                PointMarket memberdayakan pengajar dengan data untuk memahami "sidik jari" motivasi setiap siswa.
            </p>
            <div class="flex flex-wrap justify-center gap-6">
                <!-- Placeholders for Partner Logos -->
                <div class="h-12 w-32 bg-slate-800 rounded opacity-50 hover:opacity-100 transition flex items-center justify-center font-bold text-slate-600">LENTERAMU</div>
                <div class="h-12 w-32 bg-slate-800 rounded opacity-50 hover:opacity-100 transition flex items-center justify-center font-bold text-slate-600">SEKOLAH</div>
                <div class="h-12 w-32 bg-slate-800 rounded opacity-50 hover:opacity-100 transition flex items-center justify-center font-bold text-slate-600">KAMPUS</div>
            </div>
        </div>
    </section>

    <!-- TEAM SECTION (NEW) -->
    <section class="py-20 bg-white border-t border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
             <div class="mb-12">
                <span class="text-brand-600 font-bold tracking-wider uppercase text-sm">Pengembang</span>
                <h2 class="text-3xl font-bold text-slate-900 mt-2">POINTMARKET TEAM</h2>
            </div>


            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-4xl mx-auto">

    <!-- Member 1 -->
    <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 hover:shadow-lg transition group text-center">
        <div class="w-40 h-40 rounded-full mx-auto mb-6 overflow-hidden bg-brand-100
                    ring-4 ring-brand-500/20 group-hover:ring-brand-500 transition">
            <img src="/public/landingpage/image/MY.jpg"
                 alt="M. Yusril Helmi Setyawan"
                 class="w-full h-full object-cover"
                 onerror="this.style.display='none'; this.parentElement.classList.add('flex','items-center','justify-center','text-brand-600','font-bold','text-4xl'); this.parentElement.innerText='MY';">
        </div>
        <h3 class="font-bold text-lg text-slate-900">M. Yusril Helmi Setyawan</h3>
        <p class="text-sm text-slate-500">Team Leader</p>
    </div>

    <!-- Member 2 -->
    <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 hover:shadow-lg transition group text-center">
        <div class="w-40 h-40 rounded-full mx-auto mb-6 overflow-hidden bg-accent-100
                    ring-4 ring-accent-500/20 group-hover:ring-accent-500 transition">
            <img src="/public/landingpage/image/VP.jpg"
                 alt="Virdiandry Putratama"
                 class="w-full h-full object-cover"
                 onerror="this.style.display='none'; this.parentElement.classList.add('flex','items-center','justify-center','text-accent-600','font-bold','text-4xl'); this.parentElement.innerText='VP';">
        </div>
        <h3 class="font-bold text-lg text-slate-900">Virdiandry Putratama</h3>
        <p class="text-sm text-slate-500">Core Team</p>
    </div>

    <!-- Member 3 -->
    <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 hover:shadow-lg transition group text-center">
        <div class="w-40 h-40 rounded-full mx-auto mb-6 overflow-hidden bg-blue-100
                    ring-4 ring-blue-500/20 group-hover:ring-blue-500 transition">
            <img src="/public/landingpage/image/AM.jpg"
                 alt="Ali Mohammad Reza"
                 class="w-full h-full object-cover"
                 onerror="this.style.display='none'; this.parentElement.classList.add('flex','items-center','justify-center','text-blue-600','font-bold','text-4xl'); this.parentElement.innerText='AM';">
        </div>
        <h3 class="font-bold text-lg text-slate-900">Ali Mohammad Reza</h3>
        <p class="text-sm text-slate-500">Core Team</p>
    </div>

</div>





        </div>
    </section>

    <!-- CALL TO ACTION -->
    <section class="py-24 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-brand-600 to-accent-600"></div>
        <div class="absolute inset-0 opacity-20" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
        
        <div class="max-w-4xl mx-auto px-4 text-center relative z-10">
            <h2 class="text-4xl font-bold text-white mb-6">Siap Mengubah Cara Belajar?</h2>
            <p class="text-brand-100 text-lg mb-10">
                Bergabunglah dengan ekosistem LENTERAMU dan biarkan PointMarket menemukan potensi terbaik Anda melalui gamifikasi yang berempati.
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="/login" class="px-8 py-4 bg-white text-brand-600 rounded-full font-bold text-lg shadow-xl hover:bg-slate-100 transition transform hover:scale-105">
                    Daftar Sekarang
                </a>
                <a href="#" class="px-8 py-4 bg-brand-700 text-white border border-brand-500 rounded-full font-bold text-lg hover:bg-brand-800 transition">
                    Hubungi Kami
                </a>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="bg-slate-950 text-slate-500 py-12 border-t border-slate-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-4 gap-8">
            <div class="col-span-1 md:col-span-2">
                <div class="flex items-center gap-3 mb-4 text-white">
                    <!-- Footer Logo (Updated to logo.png) -->
                    <img src="/public/landingpage/image/logoPM.png" 
                         alt="PointMarket Logo" 
                         class="h-8 w-8 object-contain rounded-md"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                    
                    <!-- Fallback -->
                    <div class="hidden w-8 h-8 bg-gradient-to-br from-brand-500 to-accent-600 rounded-lg items-center justify-center text-white font-bold text-xs">P</div>

                    <span class="font-bold text-xl">PointMarket</span>
                </div>
                <p class="text-sm max-w-xs mb-4">
                    Bagian dari ekosistem LENTERAMU (Learning AI-Navigated Tera-Personalized Education Resource Application – Monitoring & Understanding). Menggabungkan psikologi pendidikan dan kecerdasan buatan untuk masa depan pembelajaran yang lebih manusiawi.
                </p>
                <div class="flex space-x-4">
                    <a href="#" class="hover:text-white transition"><i class="ph ph-twitter-logo text-xl"></i></a>
                    <a href="#" class="hover:text-white transition"><i class="ph ph-linkedin-logo text-xl"></i></a>
                    <a href="#" class="hover:text-white transition"><i class="ph ph-instagram-logo text-xl"></i></a>
                </div>
            </div>
            
            <div>
                <h4 class="text-white font-bold mb-4">Platform</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="#" class="hover:text-brand-400 transition">Fitur Utama</a></li>
                    <li><a href="#" class="hover:text-brand-400 transition">Untuk Pengajar</a></li>
                    <li><a href="#" class="hover:text-brand-400 transition">Untuk Siswa</a></li>
                    <li><a href="#" class="hover:text-brand-400 transition">Studi Kasus</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-white font-bold mb-4">Legal</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="#" class="hover:text-brand-400 transition">Kebijakan Privasi</a></li>
                    <li><a href="#" class="hover:text-brand-400 transition">Syarat & Ketentuan</a></li>
                    <li><a href="#" class="hover:text-brand-400 transition">Keamanan Data</a></li>
                </ul>
            </div>
        </div>

        <div class="mt-12 pt-8 border-t border-slate-900">
            <!-- Grant Badge (NEW) -->
            <div class="max-w-4xl mx-auto mb-8 px-4">
                <div class="relative group">
                    <!-- Subtle Glow Effect -->
                    <div class="absolute -inset-0.5 bg-gradient-to-r from-brand-600 to-accent-600 rounded-xl opacity-30 group-hover:opacity-70 transition duration-500 blur"></div>
                    
                    <!-- Content Card -->
<div class="relative bg-slate-950 border border-slate-800 p-4 md:p-6 rounded-xl 
            flex flex-col md:flex-row items-center justify-center gap-4 
            text-center md:text-left">

    <!-- LOGO (tanpa lingkaran, diperbesar) -->
    <div class="shrink-0">
        <img src="/public/landingpage/image/logo-kemdikti.png"
             alt="Logo Kementerian Pendidikan Tinggi, Sains dan Teknologi RI"
             class="w-20 md:w-24 h-auto object-contain mx-auto md:mx-0">
    </div>

    <!-- TEXT -->
    <div>
        <p class="text-slate-300 text-sm md:text-base font-medium">
            PointMarket merupakan hasil 
            <span class="text-white font-bold">
                Penelitian Fundamental Reguler 2025–2026
            </span>
        </p>
        <p class="text-slate-500 text-xs md:text-sm mt-1">
            yang berjudul "Motivational Engine Berbasis AI dan Data-Driven Gamification untuk Ekosistem Pembelajaran Adaptif". Riset ini didanai oleh Kementerian Pendidikan Tinggi, Sains dan Teknologi RI"
        </p>
        
    </div>

</div>



                </div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-sm text-slate-600">
                &copy; 2025 PointMarket by Lenteramu. All rights reserved.
            </div>
        </div>
    </footer>

<!-- DNA MODAL -->
    <div id="dnaModal" class="fixed inset-0 z-[100] hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity opacity-0" id="modalBackdrop"></div>

        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <!-- Modal Panel -->
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-4xl opacity-0 scale-95" id="modalPanel">
                    
                    <!-- Header -->
                    <div class="bg-gradient-to-r from-slate-900 to-slate-800 px-6 py-6 sm:px-8 border-b border-slate-700 flex justify-between items-center">
                        <div>
                            <h3 class="text-xl font-bold leading-6 text-white" id="modal-title">The DNA of PointMarket</h3>
                            <p class="mt-1 text-sm text-slate-400">3 Pilar Fundamental Teknologi Kami</p>
                        </div>
                        <button onclick="closeDnaModal()" class="text-slate-400 hover:text-white transition-colors bg-slate-800/50 hover:bg-slate-700 p-2 rounded-lg">
                            <i class="ph-bold ph-x text-xl"></i>
                        </button>
                    </div>

                    <!-- Content -->
                    <div class="px-6 py-8 sm:p-8 bg-slate-50">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            
                            <!-- Pillar 1: Psychology -->
                            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow group">
                                <div class="w-12 h-12 rounded-xl bg-pink-100 text-pink-600 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                                    <i class="ph-duotone ph-brain text-2xl"></i>
                                </div>
                                <h4 class="text-lg font-bold text-slate-900 mb-2">Behavioral Psychology</h4>
                                <p class="text-sm text-slate-600 leading-relaxed">
                                    Menggunakan prinsip <em>Self-Determination Theory</em> (Autonomy, Competence, Relatedness) untuk membangun motivasi intrinsik siswa, bukan sekadar imbalan jangka pendek.
                                </p>
                            </div>

                            <!-- Pillar 2: AI Engine -->
                            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow group">
                                <div class="w-12 h-12 rounded-xl bg-brand-100 text-brand-600 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                                    <i class="ph-duotone ph-cpu text-2xl"></i>
                                </div>
                                <h4 class="text-lg font-bold text-slate-900 mb-2">Adaptive AI Engine</h4>
                                <p class="text-sm text-slate-600 leading-relaxed">
                                    Algoritma Machine Learning yang mempelajari pola belajar unik setiap siswa, memprediksi titik jenuh, dan memberikan intervensi (tantangan/bantuan) di saat yang tepat.
                                </p>
                            </div>

                            <!-- Pillar 3: Gamification -->
                            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow group">
                                <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                                    <i class="ph-duotone ph-trophy text-2xl"></i>
                                </div>
                                <h4 class="text-lg font-bold text-slate-900 mb-2">Gamification Mechanics</h4>
                                <p class="text-sm text-slate-600 leading-relaxed">
                                    Sistem ekonomi token yang seimbang (PointMarket), Leaderboard cerdas, dan sistem Badges yang menghargai usaha (progress) sama besarnya dengan hasil (result).
                                </p>
                            </div>

                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="bg-white px-6 py-4 sm:px-8 sm:flex sm:flex-row-reverse border-t border-slate-100">
                        <button type="button" onclick="closeDnaModal()" class="inline-flex w-full justify-center rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-500 sm:ml-3 sm:w-auto transition-colors">
                            Mengerti
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>




    <!-- INTERACTIVE ANIMATION SCRIPT -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            
            // --- CHARTS INITIALIZATION ---
            
            // 1. Line Chart: Motivation Dynamics
            const ctxMotivation = document.getElementById('motivationChart');
            if(ctxMotivation) {
                new Chart(ctxMotivation, {
                    type: 'line',
                    data: {
                        labels: ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4', 'Minggu 5', 'Minggu 6', 'Minggu 7', 'Minggu 8'],
                        datasets: [{
                            label: 'PointMarket (AI Adaptive)',
                            data: [65, 70, 72, 60, 75, 82, 85, 90], // Dips at W4, Recovers at W5
                            borderColor: '#0ea5e9',
                            backgroundColor: 'rgba(14, 165, 233, 0.1)',
                            borderWidth: 3,
                            tension: 0.4,
                            fill: true,
                            pointBackgroundColor: '#0ea5e9',
                            pointRadius: 4
                        }, {
                            label: 'Metode Tradisional',
                            data: [65, 68, 65, 50, 45, 40, 42, 38], // Declines after dip
                            borderColor: '#64748b',
                            borderWidth: 2,
                            borderDash: [5, 5],
                            tension: 0.4,
                            pointRadius: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                            }
                        },
                        scales: {
                            y: {
                                grid: {
                                    color: 'rgba(255, 255, 255, 0.1)'
                                },
                                ticks: {
                                    color: '#94a3b8'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    color: '#94a3b8'
                                }
                            }
                        }
                    }
                });
            }

            // 2. Radar Chart: Student Profiling
            const ctxRadar = document.getElementById('radarChart');
            if(ctxRadar) {
                new Chart(ctxRadar, {
                    type: 'radar',
                    data: {
                        labels: [
                            'Visual (Melihat)', 
                            'Auditory (Mendengar)', 
                            'Kinesthetic (Gerak)', 
                            'Reading/Writing', 
                            'Social (Kelompok)', 
                            'Solitary (Mandiri)'
                        ],
                        datasets: [{
                            label: 'Profil Siswa A',
                            data: [85, 40, 90, 60, 80, 30],
                            fill: true,
                            backgroundColor: 'rgba(124, 58, 237, 0.2)', // Violet
                            borderColor: '#7c3aed',
                            pointBackgroundColor: '#7c3aed',
                            pointBorderColor: '#fff',
                            pointHoverBackgroundColor: '#fff',
                            pointHoverBorderColor: '#7c3aed'
                        }, {
                            label: 'Rata-rata Kelas',
                            data: [60, 60, 60, 60, 60, 60],
                            fill: true,
                            backgroundColor: 'rgba(148, 163, 184, 0.1)', // Gray
                            borderColor: '#94a3b8',
                            borderDash: [5, 5],
                            pointRadius: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    font: {
                                        family: 'Inter'
                                    }
                                }
                            }
                        },
                        scales: {
                            r: {
                                angleLines: {
                                    color: 'rgba(0, 0, 0, 0.1)'
                                },
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.1)'
                                },
                                pointLabels: {
                                    font: {
                                        family: 'Inter',
                                        size: 11,
                                        weight: '600'
                                    },
                                    color: '#1e293b'
                                },
                                ticks: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            }

            // --- 3D TILT EFFECT LOGIC ---
            const tiltCards = document.querySelectorAll('.tilt-card');
            
            tiltCards.forEach(card => {
                card.addEventListener('mousemove', (e) => {
                    const rect = card.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;
                    
                    // Calculate rotation (max 10 degrees)
                    const centerX = rect.width / 2;
                    const centerY = rect.height / 2;
                    const rotateX = ((y - centerY) / centerY) * -10;
                    const rotateY = ((x - centerX) / centerX) * 10;
                    
                    // Apply rotation
                    card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale(1.02)`;
                    
                    // Update Glare position
                    const glare = card.querySelector('.tilt-glare');
                    if(glare) {
                        glare.style.background = `radial-gradient(circle at ${x}px ${y}px, rgba(255,255,255,0.7), transparent 70%)`;
                        glare.style.opacity = '1';
                    }
                });
                
                card.addEventListener('mouseleave', () => {
                    // Reset position
                    card.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) scale(1)';
                    const glare = card.querySelector('.tilt-glare');
                    if(glare) {
                        glare.style.opacity = '0';
                    }
                });
            });
        });

            // --- Modal Logic ---
        (function () {
  const modal = document.getElementById('dnaModal');
  const backdrop = document.getElementById('modalBackdrop');
  const panel = document.getElementById('modalPanel');

  if (!modal || !backdrop || !panel) {
    console.error('DNA Modal elements not found');
    return;
  }

  window.openDnaModal = function () {
    modal.classList.remove('hidden');
    void modal.offsetHeight; // force reflow
    backdrop.classList.remove('opacity-0');
    panel.classList.remove('opacity-0', 'scale-95');
    panel.classList.add('scale-100');
  };

  window.closeDnaModal = function () {
    backdrop.classList.add('opacity-0');
    panel.classList.remove('scale-100');
    panel.classList.add('opacity-0', 'scale-95');
    setTimeout(() => modal.classList.add('hidden'), 300);
  };

  modal.addEventListener('click', (e) => {
    if (e.target === modal || e.target === backdrop) {
      window.closeDnaModal();
    }
  });
})();

 
    </script>

    
