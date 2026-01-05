
    <!-- Navigation -->
    <!-- Header Background ubah jadi putih (bg-white), teks jadi hitam/gelap -->
    <nav id="navbar" class="fixed w-full z-50 transition-all duration-300 bg-white py-6">
        <div class="container mx-auto px-6 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <!-- Ganti Icon CSS dengan Logo Image -->
                <img src="/public/landingpage/image/logoPM.png" alt="Logo POINTMARKET" class="h-10 w-auto object-contain">
                <!-- Teks POINTMARKET warna hitam (slate-900) -->
                <span class="text-xl font-bold tracking-tight text-slate-900">POINTMARKET</span>
            </div>
            <!-- Menu Navigasi warna hitam/gelap (slate-700) -->
            <div class="hidden md:flex gap-8 text-sm font-medium text-slate-700">
                <a href="/" class="hover:text-cyan-600 transition-colors">Home</a>
                <a href="#masalah" class="hover:text-cyan-600 transition-colors">Masalah</a>
                <a href="#solusi" class="hover:text-cyan-600 transition-colors">Solusi</a>
                <a href="#teknologi" class="hover:text-cyan-600 transition-colors">Teknologi</a>
                <a href="#simulasi" class="hover:text-cyan-600 transition-colors">Cara Kerja</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative min-h-screen flex items-center justify-center pt-20 overflow-hidden">
        <!-- Abstract Background Elements -->
        <div class="absolute top-20 left-10 w-72 h-72 bg-blue-600/20 rounded-full blur-[100px] animate-pulse"></div>
        <div class="absolute bottom-20 right-10 w-96 h-96 bg-cyan-500/10 rounded-full blur-[100px]"></div>

        <div class="container mx-auto px-6 relative z-10 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-slate-800/50 border border-slate-700 mb-8 backdrop-blur-sm animate-fade-in-up">
                <span class="w-2 h-2 rounded-full bg-cyan-400 animate-ping"></span>
                <span class="text-cyan-400 text-xs font-bold uppercase tracking-wider">Motivational Engine AI</span>
            </div>
            
            <h1 class="text-5xl md:text-7xl font-extrabold mb-6 leading-tight bg-clip-text text-transparent bg-gradient-to-r from-white via-cyan-100 to-slate-400 max-w-4xl mx-auto animate-fade-in-up" style="animation-delay: 0.1s;">
                Sahabat Belajar Cerdas
            </h1>
            
            <p class="text-lg md:text-xl text-slate-400 max-w-2xl mx-auto mb-10 leading-relaxed animate-fade-in-up" style="animation-delay: 0.2s;">
                Ketika belajar terasa membosankan, POINTMARKET hadir sebagai pelatih pribadi yang memahami kapan Anda butuh dorongan, tantangan, atau apresiasi.
            </p>

            <div class="flex flex-col sm:flex-row gap-4 justify-center animate-fade-in-up" style="animation-delay: 0.3s;">
                <a href="#teknologi" class="px-8 py-4 bg-gradient-to-r from-cyan-500 to-blue-600 rounded-xl font-bold text-white shadow-lg shadow-cyan-500/25 hover:shadow-cyan-500/40 transform hover:-translate-y-1 transition-all">
                    Jelajahi Teknologi
                </a>
                <a href="#simulasi" class="px-8 py-4 bg-slate-800 border border-slate-700 rounded-xl font-bold text-slate-300 hover:bg-slate-700 transition-all">
                    Lihat Simulasi
                </a>
            </div>
        </div>
    </section>

    <!-- The Problem & Analogy Section -->
    <section id="masalah" class="py-24 bg-slate-900 relative">
        <div class="container mx-auto px-6">
            <div class="grid md:grid-cols-2 gap-16 items-center">
                <div class="space-y-6">
                    <h2 class="text-3xl md:text-4xl font-bold text-white">
                        Bukan Pendekatan <br/>
                        <span class="text-cyan-400">"Satu Untuk Semua"</span>
                    </h2>
                    <p class="text-slate-400 text-lg leading-relaxed">
                        Tantangan terbesar dalam pembelajaran digital adalah hilangnya motivasi karena materi yang generik. POINTMARKET menolak pendekatan ini.
                    </p>
                    <div class="p-6 bg-slate-800/50 rounded-2xl border border-slate-700">
                        <h3 class="text-xl font-semibold text-white mb-2 flex items-center gap-2">
                            <i data-lucide="users" class="text-yellow-400"></i>
                            Analogi Pelatih Olahraga
                        </h3>
                        <p class="text-slate-400">
                            Seperti pelatih profesional yang memberikan latihan berbeda untuk atlet lari cepat vs angkat berat, POINTMARKET merancang aktivitas belajar berdasarkan profil unik Anda.
                        </p>
                    </div>
                </div>
                
                <div class="relative">
                    <div class="absolute inset-0 bg-gradient-to-r from-cyan-500/20 to-blue-500/20 rounded-3xl blur-3xl"></div>
                    <div class="relative grid grid-cols-2 gap-4">
                        <div class="bg-slate-800 p-6 rounded-2xl border border-slate-700 hover:border-cyan-500/50 transition-colors">
                            <div class="w-12 h-12 bg-red-500/20 rounded-lg flex items-center justify-center mb-4">
                                <i data-lucide="trending-up" class="text-red-400"></i>
                            </div>
                            <h4 class="font-bold text-white">Bosan</h4>
                            <p class="text-slate-500 text-sm">Kehilangan semangat saat materi monoton.</p>
                        </div>
                        <div class="bg-slate-800 p-6 rounded-2xl border border-slate-700 hover:border-cyan-500/50 transition-colors mt-8">
                            <div class="w-12 h-12 bg-blue-500/20 rounded-lg flex items-center justify-center mb-4">
                                <i data-lucide="target" class="text-blue-400"></i>
                            </div>
                            <h4 class="font-bold text-white">Fokus</h4>
                            <p class="text-slate-500 text-sm">Kesulitan menjaga konsentrasi mandiri.</p>
                        </div>
                        <div class="bg-slate-800 p-6 rounded-2xl border border-slate-700 hover:border-cyan-500/50 transition-colors">
                            <div class="w-12 h-12 bg-green-500/20 rounded-lg flex items-center justify-center mb-4">
                                <i data-lucide="lightbulb" class="text-green-400"></i>
                            </div>
                            <h4 class="font-bold text-white">Gaya Belajar</h4>
                            <p class="text-slate-500 text-sm">Setiap otak memproses info secara berbeda.</p>
                        </div>
                        <div class="bg-slate-800 p-6 rounded-2xl border border-slate-700 hover:border-cyan-500/50 transition-colors mt-8">
                            <div class="w-12 h-12 bg-purple-500/20 rounded-lg flex items-center justify-center mb-4">
                                <i data-lucide="activity" class="text-purple-400"></i>
                            </div>
                            <h4 class="font-bold text-white">Dukungan</h4>
                            <p class="text-slate-500 text-sm">Butuh dorongan tepat waktu.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Video Section (Solusi) -->
    <section id="solusi" class="py-24 bg-slate-950 relative overflow-hidden">
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-cyan-500/5 rounded-full blur-[120px] pointer-events-none"></div>
        
        <div class="container mx-auto px-6 relative z-10 text-center">
            <div class="mb-12">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-slate-800/50 border border-slate-700 mb-6 backdrop-blur-sm">
                    <i data-lucide="play-circle" class="text-cyan-400 w-4 h-4"></i>
                    <span class="text-cyan-400 text-xs font-bold uppercase tracking-wider">Video Pengenalan</span>
                </div>
                <h2 class="text-3xl md:text-5xl font-bold mb-6 text-white">POINTMARKET <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-500">IN ACTION</span></h2>
                <p class="text-slate-400 max-w-2xl mx-auto text-lg">
                    Saksikan bagaimana teknologi kami bekerja di balik layar untuk menciptakan pengalaman belajar yang personal dan memotivasi.
                </p>
            </div>

            <div class="max-w-4xl mx-auto">
                <div class="relative group rounded-3xl p-2 bg-gradient-to-b from-slate-700 to-slate-800 border border-slate-700 shadow-2xl shadow-cyan-900/20">
                    <div class="relative rounded-2xl overflow-hidden aspect-video bg-slate-900 flex items-center justify-center group">
                        <!-- Video Player -->
                        <video controls class="w-full h-full object-cover">
                            <!-- Update src sesuai nama file Anda -->
                            <source src="/public/landingpage/video/POINTMARKET__Mesin_Motivasi.mp4" type="video/mp4">
                            Browser Anda tidak mendukung tag video.
                        </video>
                    </div>
                </div>
                <p class="mt-6 text-sm text-slate-500">
                    *Video Ilustrasi Konsep Motivational Engine
                </p>
            </div>
        </div>
    </section>

    <!-- The 3 Core Technologies Section -->
    <section id="teknologi" class="py-24 bg-slate-900">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-5xl font-bold mb-4">Tiga 'Bahan Ajaib'</h2>
                <p class="text-slate-400 max-w-2xl mx-auto">
                    POINTMARKET didukung oleh tiga teknologi kecerdasan buatan yang bekerja secara harmonis untuk mengenali dan memotivasi Anda.
                </p>
            </div>

            <div class="grid lg:grid-cols-3 gap-8">
                <!-- Tech 1: NLP -->
                <div class="bg-slate-950 rounded-3xl p-8 border border-slate-800 transition-all duration-300 group hover:border-indigo-500/50 hover:shadow-indigo-500/20">
                    <div class="w-16 h-16 rounded-2xl bg-slate-900 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                        <i data-lucide="ear" class="text-indigo-400 w-8 h-8"></i>
                    </div>
                    <div class="mb-4">
                        <h3 class="text-4xl font-extrabold text-white mb-1">NLP</h3>
                        <p class="text-sm font-bold uppercase tracking-wider text-indigo-400">Natural Language Processing</p>
                    </div>
                    <div class="mb-6 pb-6 border-b border-slate-800">
                        <h4 class="text-xl font-semibold text-slate-200 mb-2">Si Pendengar yang Baik</h4>
                        <p class="text-slate-400 text-sm leading-relaxed">
                            Menganalisis tulisan dan jawaban Anda untuk memahami gaya belajar (Visual, Auditory, Read/Write, Kinesthetic).
                        </p>
                    </div>
                    <ul class="space-y-2">
                        <li class="flex items-center gap-2 text-sm text-slate-400">
                            <i data-lucide="check-circle-2" class="text-indigo-500 w-4 h-4"></i> Analisis Teks
                        </li>
                        <li class="flex items-center gap-2 text-sm text-slate-400">
                            <i data-lucide="check-circle-2" class="text-indigo-500 w-4 h-4"></i> Deteksi Gaya Belajar
                        </li>
                        <li class="flex items-center gap-2 text-sm text-slate-400">
                            <i data-lucide="check-circle-2" class="text-indigo-500 w-4 h-4"></i> Pemahaman Konteks
                        </li>
                    </ul>
                </div>

                <!-- Tech 2: RL -->
                <div class="bg-slate-950 rounded-3xl p-8 border border-slate-800 transition-all duration-300 group hover:border-cyan-500/50 hover:shadow-cyan-500/20 lg:-mt-4 lg:mb-4 relative z-10 shadow-xl">
                    <div class="w-16 h-16 rounded-2xl bg-slate-900 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                        <i data-lucide="brain" class="text-cyan-400 w-8 h-8"></i>
                    </div>
                    <div class="mb-4">
                        <h3 class="text-4xl font-extrabold text-white mb-1">RL</h3>
                        <p class="text-sm font-bold uppercase tracking-wider text-cyan-400">Reinforcement Learning</p>
                    </div>
                    <div class="mb-6 pb-6 border-b border-slate-800">
                        <h4 class="text-xl font-semibold text-slate-200 mb-2">Sang Pelatih Cerdas</h4>
                        <p class="text-slate-400 text-sm leading-relaxed">
                            Otak pengambil keputusan. Memutuskan strategi terbaik untuk menjaga semangat Anda berdasarkan profil motivasi.
                        </p>
                    </div>
                    <ul class="space-y-2">
                        <li class="flex items-center gap-2 text-sm text-slate-400">
                            <i data-lucide="check-circle-2" class="text-cyan-500 w-4 h-4"></i> Reward/Hadiah
                        </li>
                        <li class="flex items-center gap-2 text-sm text-slate-400">
                            <i data-lucide="check-circle-2" class="text-cyan-500 w-4 h-4"></i> Tantangan Baru
                        </li>
                        <li class="flex items-center gap-2 text-sm text-slate-400">
                            <i data-lucide="check-circle-2" class="text-cyan-500 w-4 h-4"></i> Coaching & Feedback
                        </li>
                    </ul>
                </div>

                <!-- Tech 3: CBF -->
                <div class="bg-slate-950 rounded-3xl p-8 border border-slate-800 transition-all duration-300 group hover:border-emerald-500/50 hover:shadow-emerald-500/20">
                    <div class="w-16 h-16 rounded-2xl bg-slate-900 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                        <i data-lucide="library" class="text-emerald-400 w-8 h-8"></i>
                    </div>
                    <div class="mb-4">
                        <h3 class="text-4xl font-extrabold text-white mb-1">CBF</h3>
                        <p class="text-sm font-bold uppercase tracking-wider text-emerald-400">Content-Based Filtering</p>
                    </div>
                    <div class="mb-6 pb-6 border-b border-slate-800">
                        <h4 class="text-xl font-semibold text-slate-200 mb-2">Si Pustakawan Pribadi</h4>
                        <p class="text-slate-400 text-sm leading-relaxed">
                            Memilih konten atau misi spesifik yang paling relevan dari katalog setelah strategi ditentukan oleh RL.
                        </p>
                    </div>
                    <ul class="space-y-2">
                        <li class="flex items-center gap-2 text-sm text-slate-400">
                            <i data-lucide="check-circle-2" class="text-emerald-500 w-4 h-4"></i> Personalisasi Konten
                        </li>
                        <li class="flex items-center gap-2 text-sm text-slate-400">
                            <i data-lucide="check-circle-2" class="text-emerald-500 w-4 h-4"></i> Relevansi Minat
                        </li>
                        <li class="flex items-center gap-2 text-sm text-slate-400">
                            <i data-lucide="check-circle-2" class="text-emerald-500 w-4 h-4"></i> Katalog Adaptif
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Interactive Simulation / Workflow -->
    <section id="simulasi" class="py-24 bg-slate-900">
        <div class="container mx-auto px-6">
            <div class="mb-12">
                <h2 class="text-3xl md:text-4xl font-bold mb-4">Bagaimana Mereka Bekerja Bersama?</h2>
                <p class="text-slate-400">Ikuti simulasi alur kerja POINTMARKET mulai dari masalah hingga hadiah di Marketplace.</p>
            </div>

            <div class="bg-slate-800/30 rounded-3xl border border-slate-700 overflow-hidden shadow-2xl">
                <div class="grid md:grid-cols-12 min-h-[550px]">
                    
                    <!-- Left: Steps Control -->
                    <div class="md:col-span-4 bg-slate-800/80 p-8 border-r border-slate-700 flex flex-col justify-center gap-2">
                        
                        <!-- Step Button 1 -->
                        <button onclick="setStep(0)" id="btn-step-0" class="step-btn text-left p-4 rounded-xl transition-all duration-300 flex items-start gap-4 w-full bg-cyan-500/10 border border-cyan-500/50">
                            <div class="step-badge w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold shrink-0 transition-colors bg-cyan-500 text-white">1</div>
                            <div>
                                <h4 class="step-title font-bold transition-colors text-white">Deteksi & Analisis</h4>
                                <p class="text-xs text-slate-500 mt-1">NLP mengenali kondisi & gaya belajar.</p>
                            </div>
                        </button>
                        
                        <!-- Connector 1 -->
                        <div id="line-1" class="h-6 w-0.5 mx-6 transition-colors duration-500 bg-slate-700"></div>

                        <!-- Step Button 2 -->
                        <button onclick="setStep(1)" id="btn-step-1" class="step-btn text-left p-4 rounded-xl transition-all duration-300 flex items-start gap-4 w-full hover:bg-slate-700/50 border border-transparent">
                            <div class="step-badge w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold shrink-0 transition-colors bg-slate-700 text-slate-400">2</div>
                            <div>
                                <h4 class="step-title font-bold transition-colors text-slate-300">Keputusan Strategis</h4>
                                <p class="text-xs text-slate-500 mt-1">RL (Pelatih) menentukan tindakan.</p>
                            </div>
                        </button>

                        <!-- Connector 2 -->
                        <div id="line-2" class="h-6 w-0.5 mx-6 transition-colors duration-500 bg-slate-700"></div>

                        <!-- Step Button 3 -->
                        <button onclick="setStep(2)" id="btn-step-2" class="step-btn text-left p-4 rounded-xl transition-all duration-300 flex items-start gap-4 w-full hover:bg-slate-700/50 border border-transparent">
                            <div class="step-badge w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold shrink-0 transition-colors bg-slate-700 text-slate-400">3</div>
                            <div>
                                <h4 class="step-title font-bold transition-colors text-slate-300">Rekomendasi Konten</h4>
                                <p class="text-xs text-slate-500 mt-1">CBF memberikan misi personal.</p>
                            </div>
                        </button>

                         <!-- Connector 3 -->
                         <div id="line-3" class="h-6 w-0.5 mx-6 transition-colors duration-500 bg-slate-700"></div>

                        <!-- Step Button 4 -->
                        <button onclick="setStep(3)" id="btn-step-3" class="step-btn text-left p-4 rounded-xl transition-all duration-300 flex items-start gap-4 w-full hover:bg-slate-700/50 border border-transparent">
                            <div class="step-badge w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold shrink-0 transition-colors bg-slate-700 text-slate-400">4</div>
                            <div>
                                <h4 class="step-title font-bold transition-colors text-slate-300">Reward & Transaksi</h4>
                                <p class="text-xs text-slate-500 mt-1">Dapat Poin & Belanja di Marketplace.</p>
                            </div>
                        </button>
                    </div>

                    <!-- Right: Visual Output -->
                    <div class="md:col-span-8 p-8 flex items-center justify-center relative bg-gradient-to-br from-slate-900 to-slate-800">
                        <div class="w-full max-w-lg transition-all duration-500 ease-in-out">
                            
                            <!-- Content Step 0: NLP -->
                            <div id="content-step-0" class="step-content active animate-fade-in">
                                <div class="space-y-6">
                                    <div class="flex items-center gap-4 mb-8">
                                        <div class="w-16 h-16 rounded-full bg-slate-700 flex items-center justify-center border-2 border-slate-600">
                                            <i data-lucide="users" class="text-slate-300 w-8 h-8"></i>
                                        </div>
                                        <div>
                                            <h4 class="text-xl font-bold text-white">Profil Siswa: Andi</h4>
                                            <p class="text-slate-400">Sedang belajar Sejarah</p>
                                        </div>
                                    </div>
                                    
                                    <div class="bg-slate-900/50 p-4 rounded-xl border border-slate-700 space-y-3">
                                        <div class="flex justify-between items-center">
                                            <span class="text-slate-400">Status Motivasi (Data)</span>
                                            <span class="text-red-400 font-bold flex items-center gap-2">
                                                <i data-lucide="trending-up" class="w-4 h-4 rotate-180"></i> Menurun
                                            </span>
                                        </div>
                                        <div class="h-2 w-full bg-slate-700 rounded-full overflow-hidden">
                                            <div class="h-full w-[30%] bg-red-500 animate-pulse"></div>
                                        </div>
                                    </div>

                                    <div class="bg-indigo-900/20 p-4 rounded-xl border border-indigo-500/30">
                                        <div class="flex items-center gap-3 mb-2">
                                            <i data-lucide="ear" class="text-indigo-400 w-5 h-5"></i>
                                            <span class="font-bold text-indigo-300">Analisis NLP</span>
                                        </div>
                                        <p class="text-sm text-indigo-100">
                                            "Berdasarkan catatan belajar Andi, terdeteksi preferensi gaya belajar <strong>VISUAL</strong> dan tanda-tanda kebosanan pada teks panjang."
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Content Step 1: RL -->
                            <div id="content-step-1" class="step-content animate-fade-in">
                                <div class="space-y-6">
                                    <div class="flex items-center justify-center mb-6">
                                        <div class="w-20 h-20 rounded-full bg-cyan-500/20 flex items-center justify-center border-2 border-cyan-500 animate-pulse">
                                            <i data-lucide="brain" class="text-cyan-400 w-10 h-10"></i>
                                        </div>
                                    </div>
                                    
                                    <h4 class="text-center text-xl font-bold text-white mb-4">RL Sedang Berpikir...</h4>
                                    
                                    <div class="grid grid-cols-2 gap-3">
                                        <!-- Inactive -->
                                        <div class="p-3 rounded-lg flex items-center gap-3 transition-all bg-slate-800 text-slate-500 opacity-50">
                                            <i data-lucide="award" class="w-4 h-4"></i> <span class="text-xs font-bold">Beri Hadiah</span>
                                        </div>
                                        <!-- Active -->
                                        <div class="p-3 rounded-lg flex items-center gap-3 transition-all bg-cyan-500 text-white shadow-lg shadow-cyan-500/25 scale-105">
                                            <i data-lucide="target" class="w-4 h-4"></i> <span class="text-xs font-bold">Beri Misi (Dipilih)</span>
                                        </div>
                                        <!-- Inactive -->
                                        <div class="p-3 rounded-lg flex items-center gap-3 transition-all bg-slate-800 text-slate-500 opacity-50">
                                            <i data-lucide="alert-triangle" class="w-4 h-4"></i> <span class="text-xs font-bold">Beri Denda</span>
                                        </div>
                                        <!-- Inactive -->
                                        <div class="p-3 rounded-lg flex items-center gap-3 transition-all bg-slate-800 text-slate-500 opacity-50">
                                            <i data-lucide="message-circle" class="w-4 h-4"></i> <span class="text-xs font-bold">Coaching</span>
                                        </div>
                                    </div>

                                    <div class="bg-cyan-900/20 p-4 rounded-xl border border-cyan-500/30 mt-4">
                                        <p class="text-sm text-cyan-100 text-center">
                                            "Motivasi rendah butuh kemenangan cepat (Quick Wins). <br/><strong>Strategi: Berikan Misi Singkat & Interaktif.</strong>"
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Content Step 2: CBF -->
                            <div id="content-step-2" class="step-content animate-fade-in">
                                <div class="space-y-6">
                                    <div class="flex items-center gap-4 mb-6">
                                        <div class="w-16 h-16 rounded-full bg-emerald-500/20 flex items-center justify-center border-2 border-emerald-500">
                                            <i data-lucide="library" class="text-emerald-400 w-8 h-8"></i>
                                        </div>
                                        <div>
                                            <h4 class="text-xl font-bold text-white">Rekomendasi Final (CBF)</h4>
                                            <p class="text-emerald-400 text-sm">Mencocokkan Strategi + Gaya Belajar</p>
                                        </div>
                                    </div>

                                    <div class="bg-gradient-to-r from-slate-800 to-slate-900 p-6 rounded-2xl border border-emerald-500/50 shadow-lg shadow-emerald-900/20 relative overflow-hidden group">
                                        <div class="absolute top-0 right-0 p-3 bg-emerald-500/20 rounded-bl-xl text-emerald-400 text-xs font-bold uppercase">
                                            Misi Baru
                                        </div>
                                        
                                        <h3 class="text-2xl font-bold text-white mb-2">Buat Mind Map</h3>
                                        <p class="text-slate-400 mb-4">
                                            "Buatlah rangkuman visual (mind map) dari bab terakhir yang kamu pelajari untuk mendapatkan +50 Poin."
                                        </p>

                                        <div class="flex gap-2">
                                            <span class="px-3 py-1 bg-slate-700 rounded text-xs text-slate-300">Tipe: Visual</span>
                                            <span class="px-3 py-1 bg-slate-700 rounded text-xs text-slate-300">Durasi: 15 Menit</span>
                                        </div>
                                    </div>

                                    <div class="text-center mt-6">
                                        <p class="text-slate-400 text-sm italic">
                                            "Siswa mengerjakan misi ini..."
                                        </p>
                                        <button onclick="setStep(3)" class="mt-4 px-6 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-full text-sm font-bold transition-all flex items-center gap-2 mx-auto cursor-pointer">
                                            Selesaikan Misi <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Content Step 3: Transaction -->
                            <div id="content-step-3" class="step-content animate-fade-in">
                                <div class="space-y-6">
                                    <!-- Success State -->
                                    <div class="bg-gradient-to-r from-yellow-500/20 to-orange-500/20 border border-yellow-500/50 rounded-2xl p-4 flex items-center justify-between mb-6">
                                        <div class="flex items-center gap-3">
                                            <div class="bg-yellow-500 rounded-full p-2">
                                                <i data-lucide="check-circle-2" class="text-slate-900 w-5 h-5"></i>
                                            </div>
                                            <div>
                                                <h4 class="font-bold text-white">Misi Selesai!</h4>
                                                <p class="text-yellow-200 text-xs">Motivasi +100%</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2 bg-slate-900/50 px-3 py-1 rounded-lg border border-yellow-500/30">
                                            <i data-lucide="coins" class="text-yellow-400 w-4 h-4"></i>
                                            <span class="font-bold text-yellow-400">+50 Poin</span>
                                        </div>
                                    </div>

                                    <!-- Marketplace UI -->
                                    <div class="bg-slate-800 rounded-2xl border border-slate-700 overflow-hidden">
                                        <div class="bg-slate-900/80 p-3 border-b border-slate-700 flex justify-between items-center">
                                            <div class="flex items-center gap-2">
                                                <i data-lucide="shopping-bag" class="text-cyan-400 w-4 h-4"></i>
                                                <span class="font-bold text-slate-200">PointMarket</span>
                                            </div>
                                            <span class="text-xs text-slate-500">Saldo: 50 Poin</span>
                                        </div>
                                        
                                        <div class="p-4 grid grid-cols-2 gap-3">
                                            <!-- Item 1 -->
                                            <div class="bg-slate-700/50 p-3 rounded-xl border border-slate-600 opacity-50">
                                                <div class="w-full h-16 bg-slate-600 rounded-lg mb-2 flex items-center justify-center">
                                                    <i data-lucide="gift" class="text-slate-400 w-6 h-6"></i>
                                                </div>
                                                <h5 class="text-xs font-bold text-slate-300">Voucher Kantin</h5>
                                                <p class="text-xs text-slate-500">100 Poin</p>
                                            </div>

                                            <!-- Item 2 (Active) -->
                                            <div class="bg-gradient-to-b from-cyan-900/40 to-slate-800 p-3 rounded-xl border border-cyan-500/50 relative overflow-hidden group cursor-pointer hover:bg-cyan-900/60 transition-colors">
                                                <div class="absolute top-2 right-2 w-2 h-2 bg-green-500 rounded-full animate-ping"></div>
                                                <div class="w-full h-16 bg-slate-700 rounded-lg mb-2 flex items-center justify-center">
                                                    <i data-lucide="users" class="text-cyan-400 w-6 h-6"></i>
                                                </div>
                                                <h5 class="text-xs font-bold text-white">Skin Avatar Baru</h5>
                                                <div class="flex justify-between items-center mt-1">
                                                    <p class="text-xs text-cyan-400 font-bold">50 Poin</p>
                                                    <button class="px-2 py-1 bg-cyan-500 hover:bg-cyan-400 rounded text-[10px] font-bold text-white">
                                                        Tukar
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="text-center mt-2">
                                         <p class="text-slate-500 text-xs italic">
                                            "Andi menggunakan poin hasil belajarnya untuk mengkustomisasi avatar, menciptakan siklus motivasi positif."
                                        </p>
                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <!-- Footer / Conclusion -->
    <footer class="bg-slate-950 py-16 border-t border-slate-800">
        <div class="container mx-auto px-6 text-center">
            
            <h2 class="text-3xl font-bold text-white mb-6">Lebih Dari Sekadar Poin</h2>
            <p class="text-slate-400 max-w-2xl mx-auto mb-10 leading-relaxed">
                POINTMARKET bukan hanya gamifikasi. Ini adalah <span class="text-cyan-400 font-semibold">Motivational Engine</span>. 
                Sahabat belajar cerdas yang mengubah setiap tantangan menjadi peluang untuk tumbuh.
            </p>
            <div class="flex justify-center gap-4">
                <span class="text-slate-600 font-medium">© 2025 PointMarket by Lenteramu. All rights reserved.</span>
            </div>
        </div>
    </footer>

    <script>
        // Initialize Lucide Icons
        lucide.createIcons();

        // Navbar Scroll Logic
        const navbar = document.getElementById('navbar');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                // Keep white background but add shadow and reduce padding on scroll
                navbar.classList.add('bg-white/95', 'backdrop-blur-md', 'shadow-lg', 'py-4');
                navbar.classList.remove('bg-white', 'py-6');
            } else {
                // Reset to initial white background and larger padding
                navbar.classList.remove('bg-white/95', 'backdrop-blur-md', 'shadow-lg', 'py-4');
                navbar.classList.add('bg-white', 'py-6');
            }
        });

        // Simulation Steps Logic
        function setStep(stepIndex) {
            // Update Buttons State
            for (let i = 0; i < 4; i++) {
                const btn = document.getElementById(`btn-step-${i}`);
                const badge = btn.querySelector('.step-badge');
                const title = btn.querySelector('.step-title');
                
                // Reset styles
                btn.className = `step-btn text-left p-4 rounded-xl transition-all duration-300 flex items-start gap-4 w-full border border-transparent hover:bg-slate-700/50`;
                badge.className = `step-badge w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold shrink-0 transition-colors bg-slate-700 text-slate-400`;
                title.className = `step-title font-bold transition-colors text-slate-300`;

                // Set Active Style
                if (i === stepIndex) {
                    btn.classList.remove('hover:bg-slate-700/50', 'border-transparent');
                    btn.classList.add('bg-cyan-500/10', 'border-cyan-500/50');
                    
                    badge.classList.remove('bg-slate-700', 'text-slate-400');
                    badge.classList.add('bg-cyan-500', 'text-white');
                    
                    title.classList.remove('text-slate-300');
                    title.classList.add('text-white');
                }
            }

            // Update Connector Lines
            for (let i = 1; i < 4; i++) {
                const line = document.getElementById(`line-${i}`);
                if (stepIndex >= i) {
                    line.classList.remove('bg-slate-700');
                    line.classList.add('bg-cyan-500');
                } else {
                    line.classList.remove('bg-cyan-500');
                    line.classList.add('bg-slate-700');
                }
            }

            // Show/Hide Content
            const allContent = document.querySelectorAll('.step-content');
            allContent.forEach(content => {
                content.classList.remove('active');
            });
            
            const activeContent = document.getElementById(`content-step-${stepIndex}`);
            activeContent.classList.add('active');
            
            // Re-run icons for newly visible content if needed (sometimes needed for dynamic insertion, 
            // but here we just toggle display so it should stay rendered, but lucide.createIcons is safe to call again)
            lucide.createIcons();
        }
    </script>
