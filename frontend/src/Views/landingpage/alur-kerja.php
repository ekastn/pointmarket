    <div id="root"></div>

    <script type="text/babel">
        const { useState, useEffect } = React;

        // --- Ikon SVG Components ---
        const Icon = ({ path, className, size = 24 }) => (
            <svg xmlns="http://www.w3.org/2000/svg" width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className={className}>{path}</svg>
        );

        const Icons = {
            Brain: (props) => <Icon {...props} path={<><path d="M12 5a3 3 0 1 0-5.997.125 4 4 0 0 0-2.526 5.77 4 4 0 0 0 .556 6.588A4 4 0 1 0 12 18Z"/><path d="M12 5a3 3 0 1 1 5.997.125 4 4 0 0 1 2.526 5.77 4 4 0 0 1-.556 6.588A4 4 0 1 1 12 18Z"/><path d="M15 13a4.5 4.5 0 0 1-3-1.4c-.168.07-.337.13-.506.18"/></>} />,
            Compass: (props) => <Icon {...props} path={<><circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/></>} />,
            Users: (props) => <Icon {...props} path={<><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></>} />,
            Zap: (props) => <Icon {...props} path={<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>} />,
            Target: (props) => <Icon {...props} path={<><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></>} />,
            Award: (props) => <Icon {...props} path={<><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></>} />,
            Smile: (props) => <Icon {...props} path={<><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></>} />,
            ArrowRight: (props) => <Icon {...props} path={<><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></>} />,
            Cpu: (props) => <Icon {...props} path={<><rect x="4" y="4" width="16" height="16" rx="2"/><rect x="9" y="9" width="6" height="6"/><path d="M15 2v2"/><path d="M15 20v2"/><path d="M2 15h2"/><path d="M2 9h2"/><path d="M20 15h2"/><path d="M20 9h2"/><path d="M9 2v2"/><path d="M9 20v2"/></>} />,
            Layers: (props) => <Icon {...props} path={<><path d="m12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83Z"/><path d="m22 17.65-9.17 4.16a2 2 0 0 1-1.66 0L2 17.65"/><path d="m22 12.65-9.17 4.16a2 2 0 0 1-1.66 0L2 12.65"/></>} />,
            Activity: (props) => <Icon {...props} path={<path d="M22 12h-4l-3 9L9 3l-3 9H2"/>} />,
            CheckCircle2: (props) => <Icon {...props} path={<><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/><path d="m9 12 2 2 4-4"/></>} />,
            BarChart3: (props) => <Icon {...props} path={<><path d="M3 3v18h18"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/></>} />,
            ClipboardList: (props) => <Icon {...props} path={<><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M9 12h6"/><path d="M9 16h6"/><path d="M9 8h6"/></>} />,
            Microscope: (props) => <Icon {...props} path={<><path d="M6 18h8"/><path d="M3 22h18"/><path d="M14 22a7 7 0 1 0 0-14h-1"/><path d="M9 14h2"/><path d="M9 12a2 2 0 0 1-2-2V6h6v4a2 2 0 0 1-2 2Z"/><path d="M12 6V3a1 1 0 0 0-1-1H9a1 1 0 0 0-1 1v3"/></>} />,
            X: (props) => <Icon {...props} path={<><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></>} />,
            Database: (props) => <Icon {...props} path={<><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></>} />,
        };

        // --- VISUAL COMPONENTS ---

        // 1. Holographic Core (Hero Graphic)
        const HolographicCore = () => (
            <div className="relative w-64 h-64 md:w-96 md:h-96 mx-auto perspective-1000">
                <div className="absolute inset-0 flex items-center justify-center">
                    {/* Outer Ring Base */}
                    <div className="w-full h-full border-2 border-indigo-500/30 rounded-full animate-spin-slow shadow-[0_0_30px_rgba(99,102,241,0.2)]"></div>
                    
                    {/* Middle Ring */}
                    <div className="absolute w-3/4 h-3/4 border border-cyan-500/40 rounded-full animate-spin-reverse-slow border-dashed"></div>
                    
                    {/* Inner Core Glow */}
                    <div className="absolute w-1/2 h-1/2 bg-gradient-to-br from-indigo-600 to-cyan-600 rounded-full opacity-20 blur-xl animate-pulse-glow"></div>
                    
                    {/* Center Icon Container */}
                    <div className="absolute w-32 h-32 bg-slate-900 rounded-full border border-indigo-400/50 flex items-center justify-center z-10 backdrop-blur-md">
                        <Icons.Cpu size={48} className="text-white drop-shadow-[0_0_10px_rgba(255,255,255,0.8)]" />
                    </div>

                    {/* NEW: Orbiting Bright Blue Particle */}
                    <div className="absolute w-full h-full animate-spin-slow" style={{ animationDuration: '6s' }}>
                        {/* Positioned at the very top edge of the rotation container */}
                        <div className="absolute -top-2 left-1/2 w-4 h-4 -ml-2 rounded-full bg-cyan-400 shadow-[0_0_20px_rgba(34,211,238,0.8)] blur-[0.5px]"></div>
                        {/* Optional: Trail effect (fainter particle following behind) */}
                        <div className="absolute top-0 left-[48%] w-2 h-2 rounded-full bg-cyan-500/50 blur-sm"></div>
                    </div>
                </div>
            </div>
        );

        // 2. Circular Gauge
        const CircularGauge = ({ percentage, color, icon, label, sublabel }) => {
            const radius = 35;
            const circumference = 2 * Math.PI * radius;
            const strokeDashoffset = circumference - (percentage / 100) * circumference;
            const colorMap = { cyan: '#22d3ee', emerald: '#34d399', rose: '#fb7185' };
            const strokeColor = colorMap[color] || '#cbd5e1';

            return (
                <div className="flex flex-col items-center text-center p-4">
                    <div className="relative w-32 h-32 mb-4">
                        <svg className="w-full h-full transform -rotate-90" viewBox="0 0 100 100">
                            <circle cx="50" cy="50" r={radius} stroke="#1e293b" strokeWidth="8" fill="none" />
                            <circle cx="50" cy="50" r={radius} stroke={strokeColor} strokeWidth="8" fill="none" 
                                strokeDasharray={circumference} strokeDashoffset={strokeDashoffset}
                                strokeLinecap="round" className="transition-all duration-1000 ease-out"
                            />
                        </svg>
                        <div className="absolute inset-0 flex flex-col items-center justify-center">
                            <div className={`text-${color}-400 mb-1`}>{icon}</div>
                            <span className="text-xl font-bold text-white">{percentage}%</span>
                        </div>
                    </div>
                    <h4 className="font-bold text-white text-lg">{label}</h4>
                    <p className="text-xs text-slate-400 uppercase tracking-widest mt-1">{sublabel}</p>
                </div>
            );
        };

        // 3. Motivation Graph
        const MotivationGraph = () => (
            <div id="visualisasi" className="w-full p-6 glass-panel rounded-2xl border border-slate-800 relative overflow-hidden group scroll-mt-24">
                <div className="flex justify-between items-end mb-6">
                    <div>
                        <h3 className="text-lg font-bold text-white flex items-center gap-2">
                            <Icons.BarChart3 className="text-emerald-400" />
                            Dampak pada Retensi Belajar
                        </h3>
                        <p className="text-sm text-slate-400">Model Tradisional vs POINTMARKET Engine</p>
                    </div>
                    <div className="flex gap-4 text-xs">
                        <div className="flex items-center gap-2">
                            <div className="w-3 h-3 rounded-full bg-slate-600"></div>
                            <span className="text-slate-400">Tradisional (Extrinsic Only)</span>
                        </div>
                        <div className="flex items-center gap-2">
                            <div className="w-3 h-3 rounded-full bg-indigo-500"></div>
                            <span className="text-white font-medium">POINTMARKET (Intrinsic)</span>
                        </div>
                    </div>
                </div>
                
                {/* SVG Graph - Tampilan Awal (Tanpa Badge Live & Grid Kompleks) */}
                <div className="relative h-64 w-full">
                    <svg className="w-full h-full" viewBox="0 0 800 300" preserveAspectRatio="none">
                        {/* Grid Lines Sederhana */}
                        <line x1="0" y1="250" x2="800" y2="250" stroke="#334155" strokeWidth="1" />
                        <line x1="0" y1="150" x2="800" y2="150" stroke="#334155" strokeWidth="1" strokeDasharray="4 4" />
                        <line x1="0" y1="50" x2="800" y2="50" stroke="#334155" strokeWidth="1" strokeDasharray="4 4" />
                        
                        {/* Traditional Line (Decaying) */}
                        <path 
                            d="M0,200 Q100,50 150,200 Q200,50 250,200 Q300,50 350,250 L800,280" 
                            fill="none" 
                            stroke="#475569" 
                            strokeWidth="2" 
                            strokeDasharray="5 5"
                        />
                        
                        {/* Pointmarket Line (Growth) - Clean Gradient Stroke */}
                        <path 
                            d="M0,220 C200,200 300,100 800,50" 
                            fill="none" 
                            stroke="url(#gradientLine)" 
                            strokeWidth="4" 
                            className="drop-shadow-[0_0_10px_rgba(99,102,241,0.5)] animate-draw"
                            strokeDasharray="1000"
                            strokeDashoffset="0"
                        />
                        
                        {/* Gradients Definition */}
                        <defs>
                            <linearGradient id="gradientLine" x1="0%" y1="0%" x2="100%" y2="0%">
                                <stop offset="0%" stopColor="#818cf8" />
                                <stop offset="100%" stopColor="#22d3ee" />
                            </linearGradient>
                        </defs>
                        
                        {/* Points */}
                        <circle cx="0" cy="220" r="4" fill="#818cf8" />
                        <circle cx="800" cy="50" r="6" fill="#22d3ee" className="animate-pulse" />
                    </svg>
                    
                    {/* Labels */}
                    <div className="absolute bottom-2 left-0 text-xs text-slate-500">Awal Semester</div>
                    <div className="absolute bottom-2 right-0 text-xs text-slate-500">Akhir Semester</div>
                </div>
            </div>
        );

        // 4. Data Insights Modal
        const DataInsightsModal = ({ isOpen, onClose }) => {
            if (!isOpen) return null;

            // Generate 144 dots for RL state visualization
            const rlStates = Array.from({ length: 144 }, (_, i) => i);

            return (
                <div className="fixed inset-0 z-[100] flex items-center justify-center p-4">
                    {/* Backdrop */}
                    <div 
                        className="absolute inset-0 bg-slate-950/90 backdrop-blur-sm transition-opacity duration-300"
                        onClick={onClose}
                    ></div>
                    
                    {/* Modal Content */}
                    <div className="relative w-full max-w-5xl bg-slate-900 border border-indigo-500/30 rounded-3xl overflow-hidden shadow-2xl animate-zoom-in flex flex-col max-h-[90vh]">
                        
                        {/* Header */}
                        <div className="px-8 py-6 border-b border-slate-800 flex justify-between items-center bg-slate-900/50 backdrop-blur-md sticky top-0 z-10">
                            <div>
                                <div className="text-xs font-bold text-indigo-400 uppercase tracking-widest mb-1">Data Driven Engine</div>
                                <h2 className="text-2xl font-bold text-white flex items-center gap-2">
                                    <Icons.Database className="text-cyan-400" />
                                    Analisis Data & RL States
                                </h2>
                            </div>
                            <button onClick={onClose} className="p-2 rounded-full hover:bg-slate-800 text-slate-400 hover:text-white transition-colors">
                                <Icons.X size={24} />
                            </button>
                        </div>

                        {/* Scrollable Body */}
                        <div className="p-8 overflow-y-auto">
                            
                            {/* Top Stats Row */}
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
                                {/* Respondents Metric */}
                                <div className="p-6 rounded-2xl bg-gradient-to-br from-slate-800 to-slate-900 border border-slate-700 relative overflow-hidden group">
                                    <div className="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                                        <Icons.Users size={64} />
                                    </div>
                                    <h4 className="text-slate-400 font-medium mb-1">Total Responden Survey</h4>
                                    <div className="text-5xl font-bold text-white mb-2">92</div>
                                    <div className="w-full bg-slate-700 h-1.5 rounded-full overflow-hidden">
                                        <div className="bg-indigo-500 h-full w-[92%]"></div>
                                    </div>
                                    <p className="text-xs text-slate-500 mt-2">Siswa & Guru (Mix Demographic)</p>
                                </div>

                                {/* RL States Metric */}
                                <div className="p-6 rounded-2xl bg-gradient-to-br from-indigo-900/30 to-slate-900 border border-indigo-500/30 relative overflow-hidden">
                                    <div className="absolute top-0 right-0 p-4 opacity-10">
                                        <Icons.Cpu size={64} />
                                    </div>
                                    <h4 className="text-indigo-300 font-medium mb-1">Total RL States Generated</h4>
                                    <div className="text-5xl font-bold text-white mb-2 flex items-baseline gap-2">
                                        144 <span className="text-sm font-normal text-slate-400">Unique States</span>
                                    </div>
                                    <div className="flex gap-1 mt-3">
                                        {[...Array(12)].map((_,i) => (
                                            <div key={i} className="h-1.5 w-full rounded-full bg-cyan-500/30 animate-pulse" style={{animationDelay: `${i * 0.1}s`}}></div>
                                        ))}
                                    </div>
                                </div>
                            </div>

                            {/* Visualization Split */}
                            <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                                
                                {/* Left: Feedback Breakdown */}
                                <div className="lg:col-span-1 space-y-6">
                                    <h3 className="text-lg font-bold text-white border-l-4 border-indigo-500 pl-3">Distribusi Feedback</h3>
                                    
                                    {/* Feedback Bars */}
                                    <div className="space-y-4">
                                        <div>
                                            <div className="flex justify-between text-sm mb-1">
                                                <span className="text-slate-300">Butuh Tantangan (Challenge)</span>
                                                <span className="text-white font-bold">45%</span>
                                            </div>
                                            <div className="w-full bg-slate-800 h-2 rounded-full overflow-hidden">
                                                <div className="bg-gradient-to-r from-indigo-500 to-cyan-500 h-full w-[45%]"></div>
                                            </div>
                                        </div>
                                        <div>
                                            <div className="flex justify-between text-sm mb-1">
                                                <span className="text-slate-300">Interaksi Sosial (Relatedness)</span>
                                                <span className="text-white font-bold">30%</span>
                                            </div>
                                            <div className="w-full bg-slate-800 h-2 rounded-full overflow-hidden">
                                                <div className="bg-gradient-to-r from-rose-500 to-orange-500 h-full w-[30%]"></div>
                                            </div>
                                        </div>
                                        <div>
                                            <div className="flex justify-between text-sm mb-1">
                                                <span className="text-slate-300">Kejelasan Materi (Competence)</span>
                                                <span className="text-white font-bold">25%</span>
                                            </div>
                                            <div className="w-full bg-slate-800 h-2 rounded-full overflow-hidden">
                                                <div className="bg-gradient-to-r from-emerald-500 to-green-500 h-full w-[25%]"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div className="p-4 bg-slate-800/50 rounded-xl border border-slate-700 text-sm text-slate-400 leading-relaxed mt-6">
                                        <strong className="text-white">Insight:</strong> Data menunjukkan mayoritas responden menginginkan variasi tingkat kesulitan (Challenge) untuk mempertahankan <em>Attention</em> (ARCS).
                                    </div>
                                </div>

                                {/* Right: RL State Grid Visualization */}
                                <div className="lg:col-span-2">
                                    <div className="flex justify-between items-center mb-4">
                                        <h3 className="text-lg font-bold text-white border-l-4 border-cyan-500 pl-3">
                                            Reinforcement Learning State Matrix
                                        </h3>
                                        <span className="text-xs px-2 py-1 rounded bg-cyan-900/50 text-cyan-400 border border-cyan-700/50">
                                            12x12 Matrix Config
                                        </span>
                                    </div>

                                    {/* The Grid */}
                                    <div className="bg-slate-950 p-6 rounded-2xl border border-slate-800 relative overflow-hidden">
                                        <div className="absolute inset-0 bg-gradient-to-b from-indigo-900/10 to-transparent pointer-events-none"></div>
                                        
                                        <div className="grid grid-cols-12 gap-1.5 relative z-10">
                                            {rlStates.map((item) => {
                                                // Randomly highlight some cells to look like active processing
                                                const isActive = Math.random() > 0.85;
                                                const delay = Math.random() * 2;
                                                return (
                                                    <div 
                                                        key={item} 
                                                        className={`aspect-square rounded-sm transition-all duration-500 ${isActive ? 'bg-cyan-500 animate-pulse-grid' : 'bg-slate-800 hover:bg-slate-700'}`}
                                                        style={isActive ? {animationDelay: `${delay}s`} : {}}
                                                        title={`State ID: ${item}`}
                                                    ></div>
                                                )
                                            })}
                                        </div>
                                        

                                        {/* Overlay Label */}
                                        <div className="absolute bottom-4 right-4 max-sm:left-1/2 max-sm:-translate-x-1/2 z-50 bg-slate-900/90 backdrop-blur border border-slate-700 px-4 py-2 rounded-lg text-xs font-mono text-cyan-400 shadow-xl">
                                            <div className="flex items-center gap-2 whitespace-nowrap">
                                            <span className="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                                                Mapping 92 Inputs → 144 States
                                             </div>
                                        </div>




                                        
                                    </div>
                                </div>

                            </div>
                        </div>

                        {/* Footer Actions */}
                        <div className="p-6 border-t border-slate-800 bg-slate-900/80 backdrop-blur flex justify-end gap-3">
                            <button onClick={onClose} className="px-6 py-2.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors font-medium">
                                Tutup
                            </button>
                            <button className="px-6 py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-900/20 font-semibold transition-all flex items-center gap-2">
                                <Icons.ClipboardList size={18} />
                                Unduh Laporan Teknis
                            </button>
                        </div>
                    </div>
                </div>
            );
        };

        // --- Main Component ---
        const PointmarketEngine = () => {
            const [activeTab, setActiveTab] = useState('sdt');
            const [activeIntegration, setActiveIntegration] = useState(0);
            const [isDataModalOpen, setIsDataModalOpen] = useState(false);

            // Data Definitions
            const sdtData = [
                { id: 'autonomy', title: 'Otonomi', color: 'cyan', icon: <Icons.Compass className="w-6 h-6" />, percentage: 85, desc: 'Kontrol Penuh', detail: 'Siswa bukan penumpang, tapi pengemudi dalam perjalanan belajarnya.' },
                { id: 'competence', title: 'Kompetensi', color: 'emerald', icon: <Icons.Target className="w-6 h-6" />, percentage: 92, desc: 'Penguasaan Skill', detail: 'Feedback instan memberikan rasa pencapaian yang nyata.' },
                { id: 'relatedness', title: 'Keterhubungan', color: 'rose', icon: <Icons.Users className="w-6 h-6" />, percentage: 78, desc: 'Koneksi Sosial', detail: 'Belajar menjadi aktivitas sosial, bukan isolasi.' }
            ];

            const arcsFlow = [
                { step: '01', title: 'Attention', icon: <Icons.Zap />, color: 'bg-yellow-500' },
                { step: '02', title: 'Relevance', icon: <Icons.Activity />, color: 'bg-blue-500' },
                { step: '03', title: 'Confidence', icon: <Icons.Award />, color: 'bg-green-500' },
                { step: '04', title: 'Satisfaction', icon: <Icons.Smile />, color: 'bg-pink-500' },
            ];

            const integrationData = [
                { sdt: 'Otonomi', arcs: 'Relevansi', goal: 'Memberi kebebasan memilih jalur belajar sesuai minat.', feature: 'Exploration Engine & Marketplace', color: 'from-cyan-500 to-blue-500' },
                { sdt: 'Kompetensi', arcs: 'Kepercayaan Diri', goal: 'Tantangan bertahap & feedback positif untuk keyakinan diri.', feature: 'Reward Engine (Badges) & Adaptive Missions', color: 'from-emerald-500 to-green-500' },
                { sdt: 'Keterhubungan', arcs: 'Perhatian & Kepuasan', goal: 'Menumbuhkan rasa keterikatan dan dukungan sosial.', feature: 'Coaching Engine & Leaderboard', color: 'from-rose-500 to-pink-500' }
            ];

            const metricsData = [
                {
                    title: "AMS (Academic Motivation Scale)",
                    icon: <Icons.ClipboardList size={32} className="text-amber-400" />,
                    role: "Sensor Motivasi",
                    desc: "Alat ukur tervalidasi untuk mendeteksi 'Mengapa' siswa belajar.",
                    points: ["Intrinsic Motivation (To Know, To Accomplish)", "Extrinsic Motivation (Regulation)", "Amotivation (Absence of drive)"],
                    connection: "Jika SDT adalah mesinnya, AMS adalah panel diagnostik yang memberitahu kita apakah mesin berjalan karena bahan bakar murni (intrinsik) atau dorongan luar (ekstrinsik)."
                },
                {
                    title: "MSLQ (Motivated Strategies for Learning)",
                    icon: <Icons.Microscope size={32} className="text-purple-400" />,
                    role: "Sensor Strategi",
                    desc: "Menilai strategi kognitif dan manajemen sumber daya siswa.",
                    points: ["Cognitive Strategies (Rehearsal, Elaboration)", "Metacognitive Control", "Resource Management (Time, Study Environment)"],
                    connection: "Mengukur apakah stimulus ARCS benar-benar mengubah perilaku belajar. MSLQ memastikan siswa tidak hanya 'terhibur' (Attention), tapi juga efektif belajar (Competence)."
                }
            ];

            return (
                <div className="min-h-screen bg-slate-950 text-slate-200 font-sans selection:bg-indigo-500 selection:text-white pb-20">
                    
                    {/* Navigation Bar */}
                    <nav className="fixed top-0 w-full z-50 bg-white shadow-sm border-b border-slate-200 transition-all duration-300">
                        <div className="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between">
                            <div className="flex items-center gap-3 font-bold text-xl tracking-tight text-black">
                                <img src="/public/landingpage/image/logoPM.png" alt="POINTMARKET Logo" className="h-10 w-auto object-contain" />
                                <span>POINTMARKET</span>
                            </div>
                            <div className="hidden md:flex gap-6 text-sm font-medium text-black">
                                <a href="/" className="hover:text-indigo-600 transition-colors py-2 border-b-2 border-transparent hover:border-indigo-600">Home</a>
                                <a href="#teori" className="hover:text-indigo-600 transition-colors py-2 border-b-2 border-transparent hover:border-indigo-600">Teori</a>
                                <a href="#engine" className="hover:text-indigo-600 transition-colors py-2 border-b-2 border-transparent hover:border-indigo-600">Engine</a>
                                <a href="#visualisasi" className="hover:text-indigo-600 transition-colors py-2 border-b-2 border-transparent hover:border-indigo-600">Visualisasi</a>
                            </div>
                        </div>
                    </nav>

                    {/* Hero Section */}
                    <header className="relative pt-32 pb-20 px-6 overflow-hidden">
                        {/* Background Elements */}
                        <div className="absolute top-0 inset-x-0 h-px bg-gradient-to-r from-transparent via-indigo-500 to-transparent opacity-50"></div>
                        <div className="absolute top-0 right-0 w-[500px] h-[500px] bg-indigo-600/10 rounded-full blur-[100px] -z-10"></div>
                        <div className="absolute bottom-0 left-0 w-[500px] h-[500px] bg-cyan-600/10 rounded-full blur-[100px] -z-10"></div>

                        <div className="max-w-6xl mx-auto grid md:grid-cols-2 gap-12 items-center">
                            <div className="text-left z-10">
                                <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-900 border border-slate-700 text-indigo-400 text-xs font-bold uppercase tracking-wider mb-6 animate-fade-in">
                                    <span className="relative flex h-2 w-2">
                                      <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                                      <span className="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
                                    </span>
                                    Landasan Teoritis
                                </div>
                                <h1 className="text-5xl md:text-7xl font-bold tracking-tight text-white mb-6 leading-tight">
                                    The <span className="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-cyan-400">Motivational</span><br/> Engine
                                </h1>
                                <p className="text-lg text-slate-400 leading-relaxed max-w-lg mb-8">
                                    Sebuah sistem cerdas yang mengubah psikologi manusia (SDT) dan strategi desain (ARCS) menjadi algoritma pembelajaran. Didukung oleh sensor diagnostik presisi (AMS & MSLQ).
                                </p>
                                <div className="flex flex-wrap gap-4">
                                    <a href="#engine" className="px-6 py-3 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg font-semibold transition-all shadow-[0_0_20px_rgba(79,70,229,0.3)] hover:shadow-[0_0_30px_rgba(79,70,229,0.5)] flex items-center gap-2">
                                        Explorasi Engine <Icons.ArrowRight size={18} />
                                    </a>
                                    <button 
                                        onClick={() => setIsDataModalOpen(true)}
                                        className="px-6 py-3 bg-slate-900 border border-slate-700 hover:border-slate-500 text-slate-300 rounded-lg font-medium transition-all"
                                    >
                                        Pelajari Data
                                    </button>
                                </div>
                            </div>

                            {/* Hero Graphic */}
                            <div className="relative flex justify-center items-center">
                                <HolographicCore />
                                {/* Floating Cards */}
                                <div className="absolute top-10 -right-4 p-3 bg-slate-800/80 backdrop-blur border border-slate-600 rounded-xl shadow-xl animate-bounce" style={{animationDuration: '3s'}}>
                                    <div className="flex items-center gap-3">
                                        <div className="p-2 bg-green-500/20 rounded-lg text-green-400"><Icons.Zap size={16} /></div>
                                        <div>
                                            <div className="text-xs text-slate-400">Engagement</div>
                                            <div className="text-sm font-bold text-white">+145%</div>
                                        </div>
                                    </div>
                                </div>
                                <div className="absolute bottom-10 -left-4 p-3 bg-slate-800/80 backdrop-blur border border-slate-600 rounded-xl shadow-xl animate-bounce" style={{animationDuration: '4s', animationDelay: '1s'}}>
                                    <div className="flex items-center gap-3">
                                        <div className="p-2 bg-indigo-500/20 rounded-lg text-indigo-400"><Icons.Target size={16} /></div>
                                        <div>
                                            <div className="text-xs text-slate-400">Goal Completion</div>
                                            <div className="text-sm font-bold text-white">98.2%</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </header>

                    {/* Stats / Visual Graph Section */}
                    <section className="max-w-6xl mx-auto px-6 py-12">
                        <MotivationGraph />
                    </section>

                    {/* Interactive Theory Section */}
                    <section id="teori" className="max-w-6xl mx-auto px-6 py-12 scroll-mt-24">
                        <div className="flex flex-col lg:flex-row gap-8 mb-12">
                            {/* Tabs Navigation */}
                            <div className="w-full lg:w-1/4 flex flex-col gap-4">
                                <div className="p-1 rounded-xl bg-slate-900 border border-slate-800 sticky top-24">
                                    <button onClick={() => setActiveTab('sdt')} className={`w-full p-4 rounded-lg text-left transition-all duration-300 mb-1 ${activeTab === 'sdt' ? 'bg-indigo-600 text-white shadow-lg' : 'text-slate-400 hover:text-white hover:bg-slate-800'}`}>
                                        <div className="font-bold flex items-center justify-between">SDT Framework <Icons.Brain size={16}/></div>
                                        <div className="text-xs opacity-70">The Engine (Why)</div>
                                    </button>
                                    <button onClick={() => setActiveTab('arcs')} className={`w-full p-4 rounded-lg text-left transition-all duration-300 mb-1 ${activeTab === 'arcs' ? 'bg-cyan-600 text-white shadow-lg' : 'text-slate-400 hover:text-white hover:bg-slate-800'}`}>
                                        <div className="font-bold flex items-center justify-between">ARCS Model <Icons.Activity size={16}/></div>
                                        <div className="text-xs opacity-70">The Fuel (How)</div>
                                    </button>
                                    <button onClick={() => setActiveTab('metrics')} className={`w-full p-4 rounded-lg text-left transition-all duration-300 ${activeTab === 'metrics' ? 'bg-amber-600 text-white shadow-lg' : 'text-slate-400 hover:text-white hover:bg-slate-800'}`}>
                                        <div className="font-bold flex items-center justify-between">Metrics (AMS/MSLQ) <Icons.ClipboardList size={16}/></div>
                                        <div className="text-xs opacity-70">The Sensors (Measure)</div>
                                    </button>
                                </div>
                            </div>

                            {/* Content Display */}
                            <div className="w-full lg:w-3/4">
                                {activeTab === 'sdt' && (
                                    <div className="animate-fade-in">
                                        <div className="mb-8 border-b border-slate-800 pb-4">
                                            <h2 className="text-3xl font-bold text-white mb-2">Self-Determination Theory (SDT)</h2>
                                            <p className="text-slate-400">Mesin psikologis yang menggerakkan motivasi intrinsik.</p>
                                        </div>
                                        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                                            {sdtData.map((item) => (
                                                <div key={item.id} className="glass-panel rounded-2xl p-6 border border-slate-700/50 hover:border-slate-500 transition-colors">
                                                    <CircularGauge percentage={item.percentage} color={item.color} icon={item.icon} label={item.title} sublabel={item.desc} />
                                                    <div className="mt-4 pt-4 border-t border-slate-800 text-center">
                                                        <p className="text-sm text-slate-300 leading-snug">{item.detail}</p>
                                                    </div>
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                )}

                                {activeTab === 'arcs' && (
                                    <div className="animate-fade-in">
                                        <div className="mb-8 border-b border-slate-800 pb-4">
                                            <h2 className="text-3xl font-bold text-white mb-2">ARCS Motivation Model</h2>
                                            <p className="text-slate-400">Strategi desain sistematis untuk mempertahankan keterlibatan.</p>
                                        </div>
                                        <div className="grid grid-cols-1 md:grid-cols-4 gap-4 relative z-10">
                                            {arcsFlow.map((item, idx) => (
                                                <div key={idx} className="group relative bg-slate-900 border border-slate-700 p-6 rounded-2xl hover:-translate-y-2 transition-transform duration-300 text-center">
                                                    <div className={`w-12 h-12 mx-auto mb-4 rounded-xl ${item.color} text-white flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform`}>
                                                        {item.icon}
                                                    </div>
                                                    <div className="text-xs font-bold text-slate-500 mb-1">STEP {item.step}</div>
                                                    <h4 className="text-lg font-bold text-white mb-2">{item.title}</h4>
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                )}

                                {activeTab === 'metrics' && (
                                    <div className="animate-fade-in">
                                        <div className="mb-8 border-b border-slate-800 pb-4">
                                            <h2 className="text-3xl font-bold text-white mb-2">Diagnostic Sensors (AMS & MSLQ)</h2>
                                            <p className="text-slate-400">Alat ukur ilmiah untuk memastikan validitas dampak engine.</p>
                                        </div>
                                        <div className="space-y-6">
                                            {metricsData.map((item, idx) => (
                                                <div key={idx} className="bg-slate-900/50 border border-slate-700 rounded-2xl p-6 hover:bg-slate-900 transition-colors group">
                                                    <div className="flex flex-col md:flex-row gap-6">
                                                        <div className="shrink-0 flex flex-col items-center justify-center w-24 border-r border-slate-800 pr-6">
                                                            <div className="mb-3 group-hover:scale-110 transition-transform duration-500">{item.icon}</div>
                                                            <span className="text-xs text-center font-mono text-slate-500 uppercase">{item.role}</span>
                                                        </div>
                                                        <div className="flex-1">
                                                            <h3 className="text-xl font-bold text-white mb-2">{item.title}</h3>
                                                            <p className="text-slate-400 mb-4">{item.desc}</p>
                                                            
                                                            <div className="bg-slate-950 rounded-lg p-4 mb-4 border border-slate-800">
                                                                <h4 className="text-xs text-indigo-400 uppercase font-bold mb-2">Dimensi Pengukuran:</h4>
                                                                <div className="flex flex-wrap gap-2">
                                                                    {item.points.map((point, i) => (
                                                                        <span key={i} className="px-2 py-1 rounded bg-slate-800 text-slate-300 text-xs border border-slate-700">{point}</span>
                                                                    ))}
                                                                </div>
                                                            </div>

                                                            <div className="flex items-start gap-3">
                                                                <Icons.Cpu className="text-slate-600 mt-1 shrink-0" size={16} />
                                                                <p className="text-sm text-slate-300 italic">
                                                                    <span className="font-bold text-indigo-400">Keterhubungan Sistem:</span> {item.connection}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                )}
                            </div>
                        </div>
                    </section>

                    {/* The Engine Core: Integration Section */}
                    <section id="engine" className="bg-slate-900/50 py-20 border-y border-slate-800 relative overflow-hidden scroll-mt-16">
                        <div className="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-20"></div>
                        <div className="max-w-6xl mx-auto px-6 relative z-10">
                            <div className="text-center mb-16">
                                <h2 className="text-3xl md:text-4xl font-bold text-white mb-4">Integrasi Cerdas: <span className="text-indigo-400">The Engine Core</span></h2>
                                <p className="text-slate-400 max-w-2xl mx-auto">
                                    Simulasi bagaimana sistem menerjemahkan kebutuhan psikologis menjadi fitur nyata.
                                </p>
                            </div>

                            <div className="grid grid-cols-1 lg:grid-cols-12 gap-6">
                                <div className="lg:col-span-4 flex flex-col gap-3">
                                    {integrationData.map((item, idx) => (
                                        <button key={idx} onClick={() => setActiveIntegration(idx)} className={`group relative p-6 text-left rounded-2xl border transition-all duration-300 overflow-hidden ${activeIntegration === idx ? 'bg-slate-800 border-slate-500 shadow-xl ring-1 ring-slate-500' : 'bg-slate-900/50 border-slate-800 hover:bg-slate-800'}`}>
                                            <div className={`absolute left-0 top-0 bottom-0 w-1.5 bg-gradient-to-b ${item.color} ${activeIntegration === idx ? 'opacity-100' : 'opacity-0'} transition-opacity`}></div>
                                            <h3 className={`text-lg font-bold mb-1 ${activeIntegration === idx ? 'text-white' : 'text-slate-400 group-hover:text-slate-200'}`}>{item.sdt}</h3>
                                            <div className="flex items-center gap-2 text-xs text-slate-500 uppercase tracking-wider font-semibold"><span>INPUT</span><Icons.ArrowRight size={12} /><span className={activeIntegration === idx ? 'text-indigo-400' : ''}>PROCESS</span></div>
                                        </button>
                                    ))}
                                </div>
                                <div className="lg:col-span-8">
                                    <div className="h-full bg-slate-950 rounded-3xl border border-slate-800 p-8 relative overflow-hidden flex flex-col justify-center min-h-[450px] shadow-2xl">
                                        <div className="absolute inset-0 opacity-20" style={{backgroundImage: 'radial-gradient(circle at 2px 2px, #4f46e5 1px, transparent 0)', backgroundSize: '40px 40px'}}></div>
                                        {integrationData.map((item, idx) => (
                                            activeIntegration === idx && (
                                                <div key={idx} className="relative z-10 animate-zoom-in">
                                                    <div className="flex flex-col md:flex-row items-center justify-between gap-6 mb-12">
                                                        <div className="text-center w-full md:w-1/3 group">
                                                            <div className={`mx-auto w-20 h-20 rounded-full bg-gradient-to-br ${item.color} flex items-center justify-center shadow-[0_0_20px_rgba(0,0,0,0.5)] mb-4 border-4 border-slate-900 group-hover:scale-110 transition-transform`}><Icons.Brain className="text-white w-10 h-10" /></div>
                                                            <div className="text-xs text-slate-500 uppercase font-bold tracking-widest mb-1">Human Input</div>
                                                            <div className="text-xl font-bold text-white">{item.sdt}</div>
                                                        </div>
                                                        <div className="hidden md:flex flex-1 items-center justify-center relative px-4">
                                                            <div className="h-0.5 w-full bg-slate-800 overflow-hidden rounded-full"><div className="h-full w-1/2 bg-gradient-to-r from-transparent via-indigo-500 to-transparent animate-dash" style={{backgroundSize: '200% 100%'}}></div></div>
                                                            <div className="absolute bg-slate-900 border border-slate-700 px-3 py-1 rounded-full text-xs text-indigo-400 font-mono shadow-lg">PROCESSING...</div>
                                                        </div>
                                                        <Icons.ArrowRight className="md:hidden text-slate-600 rotate-90 my-4" />
                                                        <div className="text-center w-full md:w-1/3 group">
                                                            <div className="mx-auto w-20 h-20 rounded-full bg-slate-800 border-2 border-slate-600 flex items-center justify-center shadow-lg mb-4 group-hover:scale-110 transition-transform"><Icons.Activity className={`w-10 h-10 bg-gradient-to-br ${item.color} bg-clip-text text-transparent`} /></div>
                                                            <div className="text-xs text-slate-500 uppercase font-bold tracking-widest mb-1">System Strategy</div>
                                                            <div className="text-xl font-bold text-white">{item.arcs}</div>
                                                        </div>
                                                    </div>
                                                    <div className="bg-slate-900/90 backdrop-blur border border-indigo-500/30 rounded-xl p-6 relative overflow-hidden shadow-[0_0_30px_rgba(79,70,229,0.1)]">
                                                        <div className={`absolute left-0 top-0 bottom-0 w-2 bg-gradient-to-b ${item.color}`}></div>
                                                        <div className="flex items-start gap-5">
                                                            <div className={`p-3 rounded-xl bg-gradient-to-br ${item.color} shadow-lg shrink-0`}><Icons.CheckCircle2 className="text-white w-8 h-8" /></div>
                                                            <div>
                                                                <h4 className="text-xs text-indigo-400 uppercase tracking-widest font-bold mb-2">OUTPUT FEATURE</h4>
                                                                <h3 className="text-2xl font-bold text-white mb-2">{item.feature}</h3>
                                                                <p className="text-slate-300 leading-relaxed text-sm">{item.goal}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            )
                                        ))}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <footer className="py-12 px-6 text-center border-t border-slate-900 bg-slate-950 mt-12">
                        <div className="max-w-3xl mx-auto">
                            <Icons.Cpu className="mx-auto text-slate-600 mb-6" size={32} />
                            <p className="text-xl font-serif italic text-slate-400 mb-8">"POINTMARKET mengintegrasikan kebutuhan manusia dengan efisiensi mesin."</p>
                            <div className="text-xs text-slate-600 font-mono">SYSTEM STATUS: ONLINE • V.2.4.1 • © 2025 PointMarket by Lenteramu. All rights reserved.</div>
                        </div>
                    </footer>
                    
                    {/* Render Modal */}
                    <DataInsightsModal 
                        isOpen={isDataModalOpen} 
                        onClose={() => setIsDataModalOpen(false)} 
                    />
                </div>
            );
        };

        const root = ReactDOM.createRoot(document.getElementById('root'));
        root.render(<PointmarketEngine />);
    </script>
