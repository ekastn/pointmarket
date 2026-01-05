
    <!-- HEADER -->
    <header id="main-header" class="fixed w-full top-0 z-50 transition-all duration-300 border-b border-transparent py-5 bg-white">
        <div class="max-w-7xl mx-auto px-6 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <img src="/public/landingpage/image/logoPM.png" alt="Pointmarket Logo" class="h-10 w-auto object-contain" onerror="this.style.display='none'">
                <!-- Updated: Removed gradient classes, added text-black -->
                <span class="text-2xl font-bold text-black">
                    POINTMARKET
                </span>
            </div>
            <nav class="hidden md:flex gap-8 text-sm font-medium text-slate-600">
                <a href="/" class="hover:text-blue-600 transition-colors">Home</a>
                <a href="#problem" class="hover:text-blue-600 transition-colors">Masalah</a>
                <a href="#solution" class="hover:text-blue-600 transition-colors">Solusi AI</a>
                <a href="#tech" class="hover:text-blue-600 transition-colors">Teknologi</a>
                <a href="#roadmap" class="hover:text-blue-600 transition-colors">Roadmap</a>
            </nav>
            <button class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-full text-sm font-medium transition-all shadow-lg shadow-blue-600/20">
                Hubungi Kami
            </button>
        </div>
    </header>

    <!-- HERO SECTION -->
    <div id="home" class="relative min-h-screen flex items-center justify-center pt-20 overflow-hidden bg-white">
        <!-- Abstract Background Animation -->
        <div class="absolute inset-0 z-0 opacity-30">
            <div class="absolute top-[-10%] left-[-10%] w-[40rem] h-[40rem] bg-purple-200 rounded-full mix-blend-multiply filter blur-3xl animate-blob"></div>
            <div class="absolute top-[-10%] right-[-10%] w-[40rem] h-[40rem] bg-blue-200 rounded-full mix-blend-multiply filter blur-3xl animate-blob animation-delay-2000"></div>
            <div class="absolute bottom-[-20%] left-[20%] w-[40rem] h-[40rem] bg-indigo-200 rounded-full mix-blend-multiply filter blur-3xl animate-blob animation-delay-4000"></div>
        </div>

        <div class="relative z-10 max-w-5xl mx-auto px-6 text-center fade-in-section">
            <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-bold uppercase tracking-wider mb-4 inline-block">Motivational Engine Generasi Baru</span>
            <h1 class="text-5xl md:text-7xl font-extrabold tracking-tight text-slate-900 mb-6 leading-tight">
                Lebih Dari Sekadar <br />
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600">Gamifikasi Statis</span>
            </h1>
            <p class="text-lg md:text-xl text-slate-600 mb-10 max-w-3xl mx-auto leading-relaxed">
                Menjawab krisis motivasi dalam pembelajaran digital dengan <strong>AI Adaptif</strong> dan <strong>Psikometrik Data-Driven</strong>. Kami tidak hanya memberi poin, kami memahami <em>mengapa</em> siswa belajar.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <button class="flex items-center justify-center gap-2 bg-slate-900 text-white px-8 py-4 rounded-full font-semibold hover:bg-slate-800 transition-all shadow-xl">
                    Pelajari Teknologinya <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
                <button class="flex items-center justify-center gap-2 bg-white text-slate-700 border border-slate-200 px-8 py-4 rounded-full font-semibold hover:bg-slate-50 transition-all">
                    Unduh Whitepaper
                </button>
            </div>
        </div>
    </div>

    <!-- PROBLEM SECTION -->
    <section id="problem" class="py-20 px-6 md:px-12 lg:px-24 relative overflow-hidden bg-slate-50">
        <div class="max-w-6xl mx-auto">
            <div class="grid md:grid-cols-2 gap-16 items-center">
                <div class="fade-in-section">
                    <div class="relative">
                        <div class="absolute -inset-4 bg-gradient-to-r from-red-100 to-orange-100 rounded-2xl transform -rotate-2"></div>
                        <div class="relative bg-white p-8 rounded-xl shadow-sm border border-slate-100">
                            <h3 class="text-2xl font-bold mb-6 flex items-center gap-2 text-red-600">
                                <i data-lucide="x-circle" class="w-7 h-7"></i>
                                Gamifikasi Lama (Statis)
                            </h3>
                            <ul class="space-y-4">
                                <li class="flex items-start gap-3 text-slate-600">
                                    <div class="mt-1 bg-slate-100 p-1 rounded text-slate-400"><i data-lucide="x-circle" class="w-3 h-3"></i></div>
                                    <span><strong>Satu Ukuran untuk Semua:</strong> Semua siswa mendapat reward yang sama, mengabaikan kepribadian unik mereka.</span>
                                </li>
                                <li class="flex items-start gap-3 text-slate-600">
                                    <div class="mt-1 bg-slate-100 p-1 rounded text-slate-400"><i data-lucide="x-circle" class="w-3 h-3"></i></div>
                                    <span><strong>Insentif Dangkal:</strong> Hanya fokus pada poin dan lencana. Motivasi hilang saat hadiah hilang.</span>
                                </li>
                                <li class="flex items-start gap-3 text-slate-600">
                                    <div class="mt-1 bg-slate-100 p-1 rounded text-slate-400"><i data-lucide="x-circle" class="w-3 h-3"></i></div>
                                    <span><strong>Amotivation:</strong> Tidak efektif bagi siswa yang sudah kehilangan minat belajar sejak awal.</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="fade-in-section" style="transition-delay: 200ms;">
                    <div>
                        <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-bold uppercase tracking-wider mb-4 inline-block">Realitas Lapangan</span>
                        <h2 class="text-3xl font-bold text-slate-900 mb-6">Kesenjangan Antara Janji dan Realitas</h2>
                        <p class="text-slate-600 mb-6 leading-relaxed">
                            Studi menunjukkan 78% guru kesulitan mengakomodasi kebutuhan individual siswa. Sistem konvensional gagal karena mereka <strong>buta terhadap kondisi psikologis</strong> siswa.
                        </p>
                        <p class="text-slate-600 font-medium">
                            POINTMARKET hadir bukan sebagai perbaikan kecil, melainkan lompatan paradigma dari "Memeberikan Hadiah" menjadi "Mengelola Psikologi".
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SOLUTION SECTION (THE CYCLE) -->
    <section id="solution" class="py-20 px-6 md:px-12 lg:px-24 relative overflow-hidden bg-white">
        <div class="max-w-7xl mx-auto text-center mb-16 fade-in-section">
            <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-bold uppercase tracking-wider mb-4 inline-block">Core Engine</span>
            <h2 class="text-4xl font-bold text-slate-900 mb-4">Siklus Cerdas Pointmarket</h2>
            <p class="text-slate-600 max-w-2xl mx-auto">
                Sistem kami bekerja seperti mentor pribadi yang tak kenal lelah, terus berputar dalam tiga fase kritis untuk menjaga api semangat siswa.
            </p>
        </div>

        <div class="max-w-6xl mx-auto bg-slate-50 rounded-3xl p-8 md:p-12 border border-slate-100 shadow-sm fade-in-section">
            <!-- Intelligent Cycle Logic Container -->
            <div id="cycle-container" class="grid lg:grid-cols-2 gap-12 items-center">
                
                <!-- Visual Graphic Side -->
                <div 
                    class="relative h-[450px] w-full flex items-center justify-center select-none rounded-3xl overflow-hidden bg-slate-900 shadow-2xl"
                >
                    <!-- Background Effects -->
                    <div class="absolute inset-0 bg-gradient-to-br from-slate-900 via-blue-950 to-slate-900"></div>
                    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-blue-500/20 rounded-full blur-[80px] animate-pulse"></div>
                    <div class="absolute top-1/4 left-1/4 w-64 h-64 bg-indigo-500/20 rounded-full blur-[60px] animate-blob"></div>

                    <!-- Orbit Track & Satellite -->
                    <div class="absolute inset-0 flex items-center justify-center">
                        <!-- Orbit Ring (Faint) -->
                        <div class="absolute w-[320px] h-[320px] rounded-full border border-slate-700/50"></div>
                        
                        <!-- Main Dashed Orbit (Spinning) -->
                        <div class="w-72 h-72 rounded-full border-[6px] border-dashed border-slate-600/60 animate-spin-slow relative z-10 flex items-center justify-center">
                            <!-- Satellite/Orbiter Dot (Attached to the spinning ring) -->
                            <div class="absolute top-0 left-1/2 -translate-x-1/2 -mt-[5px] w-3 h-3 bg-white rounded-full shadow-[0_0_10px_rgba(255,255,255,0.8)]"></div>
                        </div>
                    </div>

                    <!-- Core -->
                    <div class="absolute z-20 bg-white p-5 rounded-full shadow-[0_0_40px_rgba(59,130,246,0.3)] border-4 border-slate-100 text-center flex flex-col items-center justify-center w-36 h-36">
                        <i data-lucide="brain" class="text-blue-600 mb-1 drop-shadow-md w-10 h-10"></i>
                        <span class="text-[10px] font-bold uppercase tracking-widest text-slate-500 mt-1">AI Core</span>
                    </div>

                    <!-- Node 1: Detect (Top) -->
                    <!-- Added animate-float for idle state -->
                    <button id="node-0" onclick="setCycleStep(0)" class="cycle-node animate-float absolute top-8 left-1/2 transform -translate-x-1/2 w-20 h-20 rounded-full flex items-center justify-center shadow-lg transition-all duration-500 z-30 border-4 bg-slate-800 border-slate-700 text-slate-400 hover:bg-slate-700 cursor-pointer">
                        <i data-lucide="search" class="w-7 h-7"></i>
                    </button>

                    <!-- Node 2: Adjust (Bottom Right) -->
                    <!-- Added animate-float-delayed for variation -->
                    <button id="node-1" onclick="setCycleStep(1)" class="cycle-node animate-float-delayed absolute bottom-16 right-8 md:right-16 w-20 h-20 rounded-full flex items-center justify-center shadow-lg transition-all duration-500 z-30 border-4 bg-slate-800 border-slate-700 text-slate-400 hover:bg-slate-700 cursor-pointer">
                        <i data-lucide="cpu" class="w-7 h-7"></i>
                    </button>

                    <!-- Node 3: Recommend (Bottom Left) -->
                    <!-- Added animate-float for idle state -->
                    <button id="node-2" onclick="setCycleStep(2)" class="cycle-node animate-float absolute bottom-16 left-8 md:left-16 w-20 h-20 rounded-full flex items-center justify-center shadow-lg transition-all duration-500 z-30 border-4 bg-slate-800 border-slate-700 text-slate-400 hover:bg-slate-700 cursor-pointer">
                        <i data-lucide="lightbulb" class="w-7 h-7"></i>
                    </button>

                     <!-- Arrows SVG -->
                     <svg class="absolute inset-0 w-full h-full pointer-events-none text-slate-600/50">
                        <defs>
                          <marker id="arrowhead" markerWidth="10" markerHeight="7" refX="0" refY="3.5" orient="auto">
                            <polygon points="0 0, 10 3.5, 0 7" fill="#475569" />
                          </marker>
                        </defs>
                     </svg>
                </div>

                <!-- Narrative Side -->
                <div class="relative min-h-[300px]">
                    <!-- Step 0 Content -->
                    <div id="content-0" class="cycle-content absolute inset-0 transition-all duration-500 ease-in-out flex flex-col justify-center opacity-0 translate-x-8 pointer-events-none">
                        <div class="w-fit p-3 rounded-xl mb-6 bg-blue-100 text-blue-600">
                            <i data-lucide="search" class="w-6 h-6"></i>
                        </div>
                        <h3 class="text-4xl font-bold text-slate-900 mb-6">1. Mendeteksi</h3>
                        <p class="text-lg text-slate-600 leading-relaxed mb-8 border-l-4 border-slate-200 pl-4">
                            Sistem menggunakan instrumen psikometrik (AMS & MSLQ) untuk membaca "detak jantung" motivasi siswa. Kami mengidentifikasi apakah siswa termotivasi oleh rasa ingin tahu (intrinsik) atau sekadar nilai (ekstrinsik).
                        </p>
                        <div class="flex gap-3 progress-bars">
                            <!-- JS fills this -->
                        </div>
                    </div>

                    <!-- Step 1 Content -->
                    <div id="content-1" class="cycle-content absolute inset-0 transition-all duration-500 ease-in-out flex flex-col justify-center opacity-0 translate-x-8 pointer-events-none">
                        <div class="w-fit p-3 rounded-xl mb-6 bg-indigo-100 text-indigo-600">
                            <i data-lucide="cpu" class="w-6 h-6"></i>
                        </div>
                        <h3 class="text-4xl font-bold text-slate-900 mb-6">2. Menyesuaikan</h3>
                        <p class="text-lg text-slate-600 leading-relaxed mb-8 border-l-4 border-slate-200 pl-4">
                            Mesin AI (Reinforcement Learning) menganalisis data profil 2D. Sistem memutuskan strategi terbaik secara real-time: Apakah siswa ini butuh tantangan lebih sulit? Atau butuh dukungan emosional?
                        </p>
                        <div class="flex gap-3 progress-bars"></div>
                    </div>

                    <!-- Step 2 Content -->
                    <div id="content-2" class="cycle-content absolute inset-0 transition-all duration-500 ease-in-out flex flex-col justify-center opacity-0 translate-x-8 pointer-events-none">
                        <div class="w-fit p-3 rounded-xl mb-6 bg-purple-100 text-purple-600">
                            <i data-lucide="lightbulb" class="w-6 h-6"></i>
                        </div>
                        <h3 class="text-4xl font-bold text-slate-900 mb-6">3. Merekomendasikan</h3>
                        <p class="text-lg text-slate-600 leading-relaxed mb-8 border-l-4 border-slate-200 pl-4">
                            Content-Based Filtering mengirimkan intervensi spesifik. Bukan reward generik, tapi misi yang relevan dengan minat mereka, atau feedback coaching yang personal.
                        </p>
                        <div class="flex gap-3 progress-bars"></div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- TECH STACK SECTION -->
    <section id="tech" class="py-20 px-6 md:px-12 lg:px-24 relative overflow-hidden bg-slate-900 text-white">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col md:flex-row justify-between items-end mb-16 border-b border-slate-700 pb-8 fade-in-section">
                <div>
                    <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-bold uppercase tracking-wider mb-4 inline-block">Architectural Foundation</span>
                    <h2 class="text-3xl md:text-5xl font-bold">Sinergi Psikometrik & AI</h2>
                </div>
                <p class="text-slate-400 max-w-md mt-4 md:mt-0">
                    Kombinasi unik yang membuat sistem dapat "memahami" siswa pada level yang lebih dalam, bukan sekadar melacak aktivitas.
                </p>
            </div>

            <div class="grid lg:grid-cols-12 gap-8">
                <!-- Column 1 -->
                <div class="lg:col-span-4 space-y-4 fade-in-section" style="transition-delay: 100ms;">
                    <h4 class="text-slate-400 uppercase text-xs font-bold tracking-widest mb-4">Input Data (Sensing)</h4>
                    <div class="bg-slate-800/50 p-6 rounded-xl border border-slate-700 hover:border-blue-500 transition-colors group">
                        <div class="flex justify-between items-center mb-2">
                            <h5 class="font-bold text-lg group-hover:text-blue-400 transition-colors">Profil Motivasi 2D</h5>
                            <i data-lucide="bar-chart-3" class="text-slate-500 group-hover:text-blue-400 w-5 h-5"></i>
                        </div>
                        <p class="text-sm text-slate-400">Matriks AMS (Jenis Motivasi) x MSLQ (Level Motivasi). Menghasilkan pemetaan psikologis yang presisi.</p>
                    </div>
                    <div class="bg-slate-800/50 p-6 rounded-xl border border-slate-700 hover:border-purple-500 transition-colors group">
                        <div class="flex justify-between items-center mb-2">
                            <h5 class="font-bold text-lg group-hover:text-purple-400 transition-colors">VARK + NLP</h5>
                            <i data-lucide="database" class="text-slate-500 group-hover:text-purple-400 w-5 h-5"></i>
                        </div>
                        <p class="text-sm text-slate-400">Analisis gaya belajar visual/auditori diperkaya dengan analisis teks jawaban siswa menggunakan NLP.</p>
                    </div>
                </div>

                <!-- Column 2 (Center) -->
                <div class="lg:col-span-4 flex flex-col justify-center items-center relative py-8 fade-in-section" style="transition-delay: 200ms;">
                    <div class="absolute inset-0 flex items-center justify-center -z-10 opacity-20">
                        <div class="w-full h-1 bg-gradient-to-r from-transparent via-blue-500 to-transparent"></div>
                        <div class="absolute w-1 h-full bg-gradient-to-b from-transparent via-blue-500 to-transparent"></div>
                    </div>
                    <div class="bg-gradient-to-br from-blue-600 to-indigo-700 p-1 rounded-2xl shadow-2xl shadow-blue-500/20 w-full">
                        <div class="bg-slate-900 p-8 rounded-xl text-center">
                            <div class="mx-auto bg-blue-500/20 w-16 h-16 rounded-full flex items-center justify-center mb-4 animate-pulse">
                                <i data-lucide="brain" class="text-blue-400 w-8 h-8"></i>
                            </div>
                            <h3 class="text-2xl font-bold mb-2">AI Decision Engine</h3>
                            <div class="space-y-3 mt-6 text-left">
                                <div class="flex items-center gap-3 bg-slate-800 p-3 rounded-lg border border-slate-700">
                                    <i data-lucide="zap" class="text-yellow-400 w-5 h-5"></i>
                                    <div>
                                        <span class="block text-xs text-slate-400">Strategy Selector</span>
                                        <span class="font-bold text-sm">Reinforcement Learning</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 bg-slate-800 p-3 rounded-lg border border-slate-700">
                                    <i data-lucide="layers" class="text-green-400 w-5 h-5"></i>
                                    <div>
                                        <span class="block text-xs text-slate-400">Content Personalizer</span>
                                        <span class="font-bold text-sm">Content-Based Filtering</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Column 3 -->
                <div class="lg:col-span-4 space-y-4 fade-in-section" style="transition-delay: 300ms;">
                    <h4 class="text-slate-400 uppercase text-xs font-bold tracking-widest mb-4">Output (Intervention)</h4>
                    
                    <div class="bg-slate-800/50 p-6 rounded-xl border border-slate-700 flex gap-4 items-center">
                        <div class="bg-green-500/10 p-3 rounded-full text-green-400"><i data-lucide="target" class="w-6 h-6"></i></div>
                        <div><h5 class="font-bold">Misi Terpersonalisasi</h5><p class="text-xs text-slate-400">Tantangan sesuai minat intrinsik</p></div>
                    </div>
                    
                    <div class="bg-slate-800/50 p-6 rounded-xl border border-slate-700 flex gap-4 items-center">
                        <div class="bg-yellow-500/10 p-3 rounded-full text-yellow-400"><i data-lucide="activity" class="w-6 h-6"></i></div>
                        <div><h5 class="font-bold">Coaching Adaptif</h5><p class="text-xs text-slate-400">Feedback kontekstual via NLP</p></div>
                    </div>

                    <div class="bg-slate-800/50 p-6 rounded-xl border border-slate-700 flex gap-4 items-center">
                        <div class="bg-pink-500/10 p-3 rounded-full text-pink-400"><i data-lucide="check-circle-2" class="w-6 h-6"></i></div>
                        <div><h5 class="font-bold">Dynamic Reward</h5><p class="text-xs text-slate-400">Hadiah yang bermakna, bukan generik</p></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ROADMAP SECTION (UPDATED GRAPHICAL VERSION) -->
    <section id="roadmap" class="py-20 px-6 md:px-12 lg:px-24 relative overflow-hidden bg-slate-50">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-20 fade-in-section">
                <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-bold uppercase tracking-wider mb-4 inline-block">Masa Depan</span>
                <h2 class="text-4xl font-bold text-slate-900">Peta Jalan Implementasi</h2>
                <p class="text-slate-600 mt-4">Visi jangka panjang berbasis TKT (Tingkat Kesiapan Teknologi)</p>
            </div>

            <!-- Modern Timeline Container -->
            <div class="relative fade-in-section">
                
                <!-- Desktop Connector Line (Hidden on Mobile) -->
                <!-- First segment: solid (past/present), rest dashed (future) -->
                <div class="hidden md:block absolute top-[22px] left-0 w-full h-0.5 bg-slate-200 z-0"></div>
                <div class="hidden md:block absolute top-[22px] left-0 w-[12.5%] h-0.5 bg-blue-500 z-0 shadow-[0_0_10px_rgba(59,130,246,0.5)]"></div>
                <div class="hidden md:block absolute top-[22px] left-[12.5%] w-full h-0.5 border-t-2 border-dashed border-slate-300 z-0"></div>

                <!-- Mobile Connector Line (Hidden on Desktop) -->
                <div class="md:hidden absolute top-0 left-6 h-full w-0.5 border-l-2 border-dashed border-slate-300 z-0"></div>
                <div class="md:hidden absolute top-0 left-6 h-24 w-0.5 bg-blue-500 z-0"></div>

                <!-- Grid Items -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-12 md:gap-4 relative z-10">
                    
                    <!-- 2025: Current -->
                    <div class="relative pl-16 md:pl-0 md:pt-12 group">
                        <!-- Dot Indicator -->
                        <div class="absolute left-3 md:left-1/2 md:top-0 -translate-x-1/2 -translate-y-1/2 md:translate-y-0 md:-translate-x-1/2 w-6 h-6 rounded-full bg-blue-500 border-4 border-white shadow-lg z-20 animate-pulse"></div>
                        <!-- Halo Effect for Active -->
                        <div class="absolute left-3 md:left-1/2 md:top-0 -translate-x-1/2 -translate-y-1/2 md:translate-y-0 md:-translate-x-1/2 w-10 h-10 rounded-full bg-blue-500/30 animate-ping-slow z-10"></div>
                        
                        <!-- Card Content -->
                        <div class="bg-white p-6 rounded-2xl border-2 border-blue-100 shadow-xl shadow-blue-500/10 transition-transform duration-300 hover:-translate-y-2 relative top-[-10px] md:top-0">
                            <span class="inline-block px-2 py-1 bg-blue-100 text-blue-700 text-[10px] font-bold uppercase rounded mb-3">TKT 1-2</span>
                            <h3 class="text-3xl font-black text-slate-800 mb-2">2025</h3>
                            <h4 class="font-bold text-lg text-blue-600 mb-2">Desain & Konsep</h4>
                            <p class="text-sm text-slate-600 leading-relaxed">
                                Desain konseptual arsitektur sistem dan survei motivasi awal.
                            </p>
                        </div>
                    </div>

                    <!-- 2026: Future -->
                    <div class="relative pl-16 md:pl-0 md:pt-12 group">
                        <!-- Dot Indicator -->
                        <div class="absolute left-3 md:left-1/2 md:top-0 -translate-x-1/2 -translate-y-1/2 md:translate-y-0 md:-translate-x-1/2 w-4 h-4 rounded-full bg-white border-4 border-slate-300 group-hover:border-blue-400 transition-colors z-20"></div>
                        
                        <!-- Card Content -->
                        <div class="bg-slate-50 p-6 rounded-2xl border border-slate-200 hover:bg-white hover:shadow-lg hover:border-blue-200 transition-all duration-300 hover:-translate-y-1 relative top-[-8px] md:top-0">
                            <span class="inline-block px-2 py-1 bg-slate-200 text-slate-600 text-[10px] font-bold uppercase rounded mb-3">TKT 3</span>
                            <h3 class="text-3xl font-bold text-slate-300 group-hover:text-blue-300 transition-colors mb-2">2026</h3>
                            <h4 class="font-bold text-lg text-slate-700 mb-2">Prototipe & Validasi</h4>
                            <p class="text-sm text-slate-500 leading-relaxed">
                                Pengembangan prototipe alpha dan pilot project di sekolah mitra.
                            </p>
                        </div>
                    </div>

                    <!-- 2027: Future -->
                    <div class="relative pl-16 md:pl-0 md:pt-12 group">
                        <!-- Dot Indicator -->
                        <div class="absolute left-3 md:left-1/2 md:top-0 -translate-x-1/2 -translate-y-1/2 md:translate-y-0 md:-translate-x-1/2 w-4 h-4 rounded-full bg-white border-4 border-slate-300 group-hover:border-blue-400 transition-colors z-20"></div>
                        
                        <!-- Card Content -->
                        <div class="bg-slate-50 p-6 rounded-2xl border border-slate-200 hover:bg-white hover:shadow-lg hover:border-blue-200 transition-all duration-300 hover:-translate-y-1 relative top-[-8px] md:top-0">
                            <span class="inline-block px-2 py-1 bg-slate-200 text-slate-600 text-[10px] font-bold uppercase rounded mb-3">TKT 4-5</span>
                            <h3 class="text-3xl font-bold text-slate-300 group-hover:text-blue-300 transition-colors mb-2">2027</h3>
                            <h4 class="font-bold text-lg text-slate-700 mb-2">Uji Coba Multi-Situs</h4>
                            <p class="text-sm text-slate-500 leading-relaxed">
                                Penyempurnaan sistem berdasarkan data lapangan dan uji skalabilitas.
                            </p>
                        </div>
                    </div>

                    <!-- 2028: Future -->
                    <div class="relative pl-16 md:pl-0 md:pt-12 group">
                        <!-- Dot Indicator -->
                        <div class="absolute left-3 md:left-1/2 md:top-0 -translate-x-1/2 -translate-y-1/2 md:translate-y-0 md:-translate-x-1/2 w-4 h-4 rounded-full bg-white border-4 border-slate-300 group-hover:border-blue-400 transition-colors z-20"></div>
                        
                        <!-- Card Content -->
                        <div class="bg-slate-50 p-6 rounded-2xl border border-slate-200 hover:bg-white hover:shadow-lg hover:border-blue-200 transition-all duration-300 hover:-translate-y-1 relative top-[-8px] md:top-0">
                            <span class="inline-block px-2 py-1 bg-slate-200 text-slate-600 text-[10px] font-bold uppercase rounded mb-3">TKT 6-7</span>
                            <h3 class="text-3xl font-bold text-slate-300 group-hover:text-blue-300 transition-colors mb-2">2028</h3>
                            <h4 class="font-bold text-lg text-slate-700 mb-2">Implementasi Penuh</h4>
                            <p class="text-sm text-slate-500 leading-relaxed">
                                Operasional penuh, diseminasi, dan rekomendasi kebijakan nasional.
                            </p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="bg-slate-900 text-slate-400 py-12 border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-6 grid md:grid-cols-4 gap-8">
            <div class="col-span-2">
                <div class="flex items-center gap-2 mb-4 text-white">
                    <img src="/public/landingpage/image/logoPM.png" alt="Logo" class="h-8 w-auto object-contain" onerror="this.style.display='none'">
                    <span class="text-xl font-bold">POINTMARKET</span>
                </div>
                <p class="max-w-xs text-sm">
                    Membangun ekosistem pembelajaran yang lebih manusiawi dengan kecerdasan buatan.
                </p>
            </div>
            <div>
                <h5 class="text-white font-bold mb-4">Platform</h5>
                <ul class="space-y-2 text-sm">
                    <li><a href="#" class="hover:text-blue-400">Teknologi</a></li>
                    <li><a href="#" class="hover:text-blue-400">Metodologi Riset</a></li>
                    <li><a href="#" class="hover:text-blue-400">Ekosistem Lenteramu</a></li>
                </ul>
            </div>
            <div>
                <h5 class="text-white font-bold mb-4">Legal</h5>
                <ul class="space-y-2 text-sm">
                    <li><a href="#" class="hover:text-blue-400">Privacy Policy</a></li>
                    <li><a href="#" class="hover:text-blue-400">Terms of Service</a></li>
                </ul>
            </div>
        </div>
        <div class="max-w-7xl mx-auto px-6 mt-12 pt-8 border-t border-slate-800 text-center text-xs">
            &copy; 2025 PointMarket by Lenteramu. All rights reserved.
        </div>
    </footer>

    <!-- JAVASCRIPT LOGIC -->
    <script>
        // 1. Initialize Icons
        lucide.createIcons();

        // 2. Sticky Header Logic
        const header = document.getElementById('main-header');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                header.classList.add('bg-white/90', 'backdrop-blur-md', 'shadow-sm', 'border-slate-200', 'py-3');
                header.classList.remove('bg-white', 'border-transparent', 'py-5');
            } else {
                header.classList.remove('bg-white/90', 'backdrop-blur-md', 'shadow-sm', 'border-slate-200', 'py-3');
                header.classList.add('bg-white', 'border-transparent', 'py-5');
            }
        });

        // 3. Fade In Animation Logic
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.fade-in-section').forEach((section) => {
            observer.observe(section);
        });

        // 4. Intelligent Cycle Logic
        let currentStep = 0;
        let isPaused = false;
        const totalSteps = 3;
        
        // Data for styling updates (matching React logic)
        const stepColors = ['blue', 'indigo', 'purple'];
        const nodes = [document.getElementById('node-0'), document.getElementById('node-1'), document.getElementById('node-2')];
        const contents = [document.getElementById('content-0'), document.getElementById('content-1'), document.getElementById('content-2')];
        const container = document.getElementById('cycle-container');

        // Function to update the UI
        function updateCycleUI(index) {
            currentStep = index;
            const color = stepColors[index];

            // Reset all nodes
            nodes.forEach((node, i) => {
                node.className = `cycle-node absolute w-20 h-20 rounded-full flex items-center justify-center shadow-lg transition-all duration-500 z-30 border-4 cursor-pointer bg-slate-800 border-slate-700 text-slate-400 hover:bg-slate-700`;
                
                // Add floating animations back when idle
                if (i === 0) node.classList.add('animate-float', 'top-8', 'left-1/2', '-translate-x-1/2');
                if (i === 1) node.classList.add('animate-float-delayed', 'bottom-16', 'right-8', 'md:right-16');
                if (i === 2) node.classList.add('animate-float', 'bottom-16', 'left-8', 'md:left-16');
                
                // Active State Logic
                if (i === index) {
                    // Remove hover/idle/float styles
                    node.classList.remove('bg-slate-800', 'border-slate-700', 'text-slate-400', 'hover:bg-slate-700', 'animate-float', 'animate-float-delayed');
                    
                    // Add Active Styles
                    node.classList.add('scale-110', 'text-white');
                    
                    // Specific Color & Ripple Effect
                    if(i === 0) { node.classList.add('bg-blue-600', 'border-blue-400', 'shadow-[0_0_30px_rgba(37,99,235,0.5)]', 'animate-ripple-blue'); }
                    if(i === 1) { node.classList.add('bg-indigo-600', 'border-indigo-400', 'shadow-[0_0_30px_rgba(79,70,229,0.5)]', 'animate-ripple-indigo'); }
                    if(i === 2) { node.classList.add('bg-purple-600', 'border-purple-400', 'shadow-[0_0_30px_rgba(147,51,234,0.5)]', 'animate-ripple-purple'); }
                } 
            });

            // Toggle Content Visibility
            contents.forEach((content, i) => {
                const progressContainer = content.querySelector('.progress-bars');
                progressContainer.innerHTML = ''; // Clear existing bars

                if (i === index) {
                    content.classList.remove('opacity-0', 'translate-x-8', 'pointer-events-none');
                    content.classList.add('opacity-100', 'translate-x-0', 'pointer-events-auto');
                    
                    // Re-render progress bars for active content
                    for (let j = 0; j < 3; j++) {
                        const bar = document.createElement('div');
                        bar.className = `h-2 rounded-full transition-all duration-300 ${j === index ? `w-12 bg-${stepColors[index]}-600` : 'w-3 bg-slate-200'}`;
                        progressContainer.appendChild(bar);
                    }

                } else {
                    content.classList.add('opacity-0', 'translate-x-8', 'pointer-events-none');
                    content.classList.remove('opacity-100', 'translate-x-0', 'pointer-events-auto');
                }
            });
        }

        // Global function for onclick
        window.setCycleStep = function(index) {
            updateCycleUI(index);
        }

        // Auto Cycle Interval
        setInterval(() => {
            if (!isPaused) {
                updateCycleUI((currentStep + 1) % totalSteps);
            }
        }, 4000);

        // Pause on Hover
        const graphicContainer = document.querySelector('.relative.h-\\[450px\\]'); // Targeting the graphic wrapper
        if(graphicContainer) {
            graphicContainer.addEventListener('mouseenter', () => isPaused = true);
            graphicContainer.addEventListener('mouseleave', () => isPaused = false);
        }

        // Initial render
        updateCycleUI(0);

    </script>
