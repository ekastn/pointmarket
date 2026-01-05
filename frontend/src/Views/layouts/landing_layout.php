<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'PointMarket - Mesin Motivasi Berbasis AI'; ?></title>
    <meta name="description" content="Platform gamifikasi cerdas yang memahami DNA motivasi belajar Anda menggunakan Artificial Intelligence.">
    <link rel="icon" type="image/png" href="/public/landingpage/image/logoPM.png">
    
    <!-- Tailwind CSS for modern, rapid styling without separate files -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Chart.js for Data Visualization -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Google Fonts: Inter for clean SaaS look -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Additional fonts for specific pages -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif:ital,wght@0,400;1,400&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Phosphor Icons for modern vector iconography -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <!-- Lucide Icons (for sahabat-belajar and other pages) -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- React & ReactDOM (for alur-kerja page) -->
    <script crossorigin src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
    <script crossorigin src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
    
    <!-- Babel for JSX (for alur-kerja page) -->
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
    
    <!-- Font Awesome for Icons (for riset page) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        serif: ['Noto Serif', 'serif'],
                        jakarta: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            500: '#0ea5e9', // Sky blue
                            600: '#0284c7',
                            900: '#0c4a6e',
                        },
                        accent: {
                            500: '#8b5cf6', // Violet/AI color
                            600: '#7c3aed',
                        },
                        studio: {
                            dark: '#1e1b4b', // Deep indigo for "Director Mode"
                            light: '#312e81'
                        },
                        slate: {
                            850: '#151e2e',
                            950: '#020617',
                        },
                        primary: '#4F46E5', // Indigo 600
                        secondary: '#10B981', // Emerald 500
                        dark: '#1F2937',
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'flow': 'flow 2s linear infinite',
                        'spin-slow': 'spin 12s linear infinite',
                        'spin-reverse-slow': 'spin-reverse 15s linear infinite',
                        'pulse-glow': 'pulse-glow 3s ease-in-out infinite',
                        'dash': 'dash 3s linear forwards',
                        'blob': 'blob 7s infinite',
                        'float-delayed': 'float 3s ease-in-out 1.5s infinite',
                        'ping-slow': 'ping 2s cubic-bezier(0, 0, 0.2, 1) infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-10px)' },
                        },
                        flow: {
                            '0%': { backgroundPosition: '0 0' },
                            '100%': { backgroundPosition: '20px 0' }
                        },
                        'spin-reverse': {
                            from: { transform: 'rotate(360deg)' },
                            to: { transform: 'rotate(0deg)' },
                        },
                        'pulse-glow': {
                            '0%, 100%': { opacity: 0.6, transform: 'scale(1)' },
                            '50%': { opacity: 1, transform: 'scale(1.05)' },
                        },
                        'dash': {
                            to: { strokeDashoffset: '1000' },
                        },
                        blob: {
                            "0%": { transform: "translate(0px, 0px) scale(1)" },
                            "33%": { transform: "translate(30px, -50px) scale(1.1)" },
                            "66%": { transform: "translate(-20px, 20px) scale(0.9)" },
                            "100%": { transform: "translate(0px, 0px) scale(1)" },
                        },
                    }
                }
            }
        }
    </script>
    
    <style>
        /* Custom Utilities */
        .glass-nav {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        .text-gradient {
            background: linear-gradient(135deg, #0284c7 0%, #7c3aed 100%);
            background-clip: text;  /* Standard (future-proof) */
            -webkit-background-clip: text;  /* Chrome, Safari */
            -webkit-text-fill-color: transparent;
        }
        .hero-pattern {
            background-image: radial-gradient(#e0f2fe 1px, transparent 1px);
            background-size: 32px 32px;
        }
        
        /* Flow Animation for Pipes */
        .flow-pipe {
            background: linear-gradient(90deg, #334155 50%, #8b5cf6 50%);
            background-size: 20px 100%;
            animation: flow 1s linear infinite;
        }
        
        .ai-pulse-ring {
            box-shadow: 0 0 0 0 rgba(124, 58, 237, 0.7);
            animation: pulse-ring 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        
        @keyframes pulse-ring {
            0% {
                box-shadow: 0 0 0 0 rgba(124, 58, 237, 0.7);
            }
            70% {
                box-shadow: 0 0 0 15px rgba(124, 58, 237, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(124, 58, 237, 0);
            }
        }

        /* 3D TILT CARD STYLES */
        .tilt-card-container {
            perspective: 1000px;
        }
        
        .tilt-card {
            transition: transform 0.1s ease;
            transform-style: preserve-3d;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
        }

        .tilt-card-content {
            transform: translateZ(20px); /* Pops content out */
        }
        
        .tilt-glare {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 50% 50%, rgba(255,255,255,0.8), transparent 70%);
            opacity: 0;
            pointer-events: none;
            mix-blend-mode: overlay;
            transition: opacity 0.1s;
        }
        
        /* Custom Animation Classes for sahabat-belajar page */
        @keyframes fade-in-up {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in-up {
            animation: fade-in-up 0.8s ease-out forwards;
        }

        @keyframes fade-in {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .animate-fade-in {
            animation: fade-in 0.5s ease-out forwards;
        }
        
        /* Utility for hiding/showing simulation steps */
        .step-content {
            display: none;
        }
        .step-content.active {
            display: block;
        }
        
        /* Additional styles for alur-kerja (React page) */
        .glass-panel {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        @keyframes drawGraph {
            from { stroke-dashoffset: 1000; }
            to { stroke-dashoffset: 0; }
        }
        
        .animate-draw {
            animation: drawGraph 3s ease-out forwards;
        }
        
        @keyframes pulse-grid {
            0%, 100% { opacity: 0.2; transform: scale(1); }
            50% { opacity: 0.8; transform: scale(1.2); box-shadow: 0 0 5px cyan; }
        }
        .animate-pulse-grid {
            animation: pulse-grid 2s infinite ease-in-out;
        }
        
        /* Additional styles for studi-kasus page */
        .fade-in-section {
            opacity: 0;
            transform: translateY(40px);
            transition: opacity 1s ease-out, transform 1s ease-out;
        }
        .fade-in-section.is-visible {
            opacity: 1;
            transform: translateY(0);
        }
        
        .animation-delay-2000 { animation-delay: 2s; }
        .animation-delay-4000 { animation-delay: 4s; }

        @keyframes ripple-blue {
            0% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.7); }
            70% { box-shadow: 0 0 0 20px rgba(37, 99, 235, 0); }
            100% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0); }
        }
        @keyframes ripple-indigo {
            0% { box-shadow: 0 0 0 0 rgba(79, 70, 229, 0.7); }
            70% { box-shadow: 0 0 0 20px rgba(79, 70, 229, 0); }
            100% { box-shadow: 0 0 0 0 rgba(79, 70, 229, 0); }
        }
        @keyframes ripple-purple {
            0% { box-shadow: 0 0 0 0 rgba(147, 51, 234, 0.7); }
            70% { box-shadow: 0 0 0 20px rgba(147, 51, 234, 0); }
            100% { box-shadow: 0 0 0 0 rgba(147, 51, 234, 0); }
        }

        .animate-ripple-blue { animation: ripple-blue 1.5s infinite; }
        .animate-ripple-indigo { animation: ripple-indigo 1.5s infinite; }
        .animate-ripple-purple { animation: ripple-purple 1.5s infinite; }
        
        /* Additional styles for riset page */
        audio {
            width: 100%;
            height: 40px;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
        }

        .zoom-cursor {
            cursor: zoom-in;
        }
        
        #image-modal {
            display: none;
            position: fixed;
            z-index: 100;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.95);
            backdrop-filter: blur(8px);
            overflow: hidden;
            touch-action: none;
        }

        .modal-wrapper {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: grab;
        }

        .modal-wrapper:active {
            cursor: grabbing;
        }
        
        /* Scrollbar customizations */
        html { scroll-behavior: smooth; }
        
        ::-webkit-scrollbar { 
            width: 8px; 
        }
        ::-webkit-scrollbar-track { 
            background: #f1f5f9; 
        }
        ::-webkit-scrollbar-thumb { 
            background: #cbd5e1; 
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover { 
            background: #94a3b8; 
        }
    </style>
</head>
<body class="<?php echo $bodyClass ?? 'font-sans text-slate-800 antialiased selection:bg-brand-100 selection:text-brand-900'; ?>">
    <?php echo $content; ?>
</body>
</html>
