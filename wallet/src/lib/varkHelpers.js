/**
 * Get VARK learning tips based on dominant style.
 * Provides a list of tips with titles and descriptions.
 * @param {string} dominantStyle
 * @returns {object} { study_tips: {title: string, description: string}[], description: string, icon: string, color: string }
 */
export function getVARKLearningTips(dominantStyle) {
    const tips = {
        'Visual': {
            study_tips: [
                {
                    title: 'Visualisasi dengan Diagram & Mind Map',
                    description: 'Gunakan peta konsep dan diagram berwarna untuk memetakan hubungan antar materi agar lebih mudah diingat.'
                },
                {
                    title: 'Highlighting Berwarna',
                    description: 'Gunakan stabilo atau pena warna-warni dalam catatanmu untuk menonjolkan poin-poin penting secara visual.'
                },
                {
                    title: 'Belajar dengan Video & Gambar',
                    description: 'Cari materi dalam bentuk video pembelajaran atau infografis untuk memperkuat pemahaman konsep yang abstrak.'
                }
            ],
            description: 'Anda lebih mudah belajar melalui elemen visual seperti gambar, diagram, dan grafik.',
            icon: 'fas fa-eye',
            color: 'indigo'
        },
        'Auditory': {
            study_tips: [
                {
                    title: 'Diskusi & Tanya Jawab',
                    description: 'Diskusikan materi yang baru dipelajari dengan teman atau jelaskan kembali kepada orang lain secara lisan.'
                },
                {
                    title: 'Rekam & Dengarkan Catatan',
                    description: 'Rekam suaramu saat membaca ringkasan materi dan dengarkan kembali saat sedang santai atau di perjalanan.'
                },
                {
                    title: 'Membaca dengan Suara Keras',
                    description: 'Saat membaca buku teks, ucapkan kalimatnya dengan suara keras untuk membantu otak memproses informasi lewat pendengaran.'
                }
            ],
            description: 'Anda lebih mudah belajar melalui mendengar dan berbicara.',
            icon: 'fas fa-volume-up',
            color: 'amber'
        },
        'Reading': {
            study_tips: [
                {
                    title: 'Membuat Ringkasan Tertulis',
                    description: 'Tulis kembali materi pembelajaran dengan kata-katamu sendiri dalam bentuk poin-poin atau narasi singkat.'
                },
                {
                    title: 'Optimalkan Daftar & Bullet Points',
                    description: 'Ubah paragraf yang panjang menjadi daftar terstruktur agar lebih mudah dipahami dan dihafal.'
                },
                {
                    title: 'Gunakan Flashcards Teks',
                    description: 'Buat kartu belajar dengan pertanyaan di satu sisi dan jawaban tertulis di sisi lainnya untuk melatih daya ingat.'
                }
            ],
            description: 'Anda lebih mudah belajar melalui membaca dan menulis.',
            icon: 'fas fa-book-open',
            color: 'emerald'
        },
        'Kinesthetic': {
            study_tips: [
                {
                    title: 'Praktik & Eksperimen Langsung',
                    description: 'Cobalah untuk mensimulasikan atau mempraktikkan teori yang dipelajari agar tubuhmu "mengingat" prosesnya.'
                },
                {
                    title: 'Belajar Sambil Bergerak',
                    description: 'Cobalah menghafal atau memahami konsep sambil berjalan santai atau melakukan aktivitas fisik ringan.'
                },
                {
                    title: 'Gunakan Model Fisik',
                    description: 'Gunakan benda nyata atau buat alat peraga sederhana untuk membantu memahami cara kerja suatu sistem.'
                }
            ],
            description: 'Anda lebih mudah belajar melalui pengalaman langsung dan praktik.',
            icon: 'fas fa-hand-rock',
            color: 'red'
        }
    };

    if (tips[dominantStyle]) {
        return tips[dominantStyle];
    }

    // Fallback for Multimodal (e.g. "Visual/Auditory")
    if (dominantStyle && dominantStyle.includes('/')) {
        const parts = dominantStyle.split('/');
        const first = parts[0];
        if (tips[first]) {
            return {
                ...tips[first],
                description: `Anda memiliki gaya belajar multimodal (${dominantStyle}).`,
                study_tips: [
                    {
                        title: `Metode Kombinasi ${dominantStyle}`,
                        description: `Gunakan pendekatan ${parts.join(' dan ')} secara bersamaan untuk hasil belajar yang optimal.`
                    },
                    ...tips[first].study_tips.slice(0, 2)
                ]
            };
        }
    }

    return {
        study_tips: [
            {
                title: 'Lengkapi Assessment VARK',
                description: 'Ikuti tes VARK untuk mendapatkan tips belajar yang dipersonalisasi sesuai profil unik Anda.'
            }
        ],
        description: 'Gaya belajar belum teridentifikasi.',
        icon: 'fas fa-question-circle',
        color: 'gray'
    };
}