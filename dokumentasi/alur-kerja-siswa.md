# 🎯 ALUR KERJA FITUR SISWA - POINTMARKET

## 📋 OVERVIEW ALUR KERJA

```
🚪 LOGIN → 🏠 DASHBOARD → 📚 PILIH AKTIVITAS → 🤖 AI ANALYSIS → 📈 TRACK PROGRESS
```

---

## 🔄 ALUR KERJA LENGKAP

### 1️⃣ **TAHAP PERSIAPAN**
```
Start: Siswa membuka browser
  ↓
Akses: http://localhost/pointmarket
  ↓
Login Form:
├─ Username: [andi/budi/citra]
├─ Password: [password]
├─ Role: [Siswa]
└─ Submit
  ↓
Validasi Sistem:
├─ ✅ Credentials benar → Dashboard
└─ ❌ Credentials salah → Error message
```

### 2️⃣ **DASHBOARD & ORIENTASI**
```
Dashboard Loading:
  ↓
Tampil Informasi:
├─ 👋 Welcome Card (nama siswa)
├─ 📊 Statistics Cards
│  ├─ Total Points: [850]
│  ├─ Completed: [12/15]
│  ├─ MSLQ Score: [3.8/5.0]
│  └─ AMS Score: [4.2/5.0]
├─ ⚡ Quick Actions
└─ 🤖 AI Performance Metrics
  ↓
Siswa memilih aktivitas dari sidebar
```

### 3️⃣ **ALUR MENGERJAKAN ASSIGNMENT**
```
Klik "Assignments" di sidebar
  ↓
Tampil Daftar Tugas:
├─ 🟢 Selesai (hijau)
├─ 🟡 Belum dikerjakan (kuning)  
└─ 🔴 Terlambat (merah)
  ↓
Pilih assignment → Klik "Mulai Mengerjakan"
  ↓
Form Pengerjaan:
├─ 📝 Judul tugas
├─ 📅 Deadline
├─ 💎 Points yang bisa diperoleh
├─ 📖 Instruksi detail
└─ ✍️ Text area untuk jawaban
  ↓
Siswa mengetik jawaban
  ↓
Real-time NLP Preview (opsional):
├─ Grammar check: [8/10]
├─ Keyword relevance: [7/10]
├─ Structure: [6/10]
└─ Length: [9/10]
  ↓
Klik "Submit" → Konfirmasi
  ↓
NLP Analysis Running:
  ↓
Hasil Analisis:
├─ 📊 Total Score: [78/100]
├─ 📝 Detail breakdown
├─ 💡 Suggestions for improvement
└─ 🎯 Comparison dengan assignment sebelumnya
  ↓
Update Statistics di Dashboard
```

### 4️⃣ **ALUR MENGIKUTI QUIZ**
```
Klik "Quiz" di sidebar
  ↓
Tampil Daftar Quiz:
├─ ⏰ Duration: [30 menit]
├─ 📊 Questions: [10 soal]
├─ 💎 Points: [100]
└─ 📅 Available until: [deadline]
  ↓
Klik "Mulai Quiz" → Warning popup
  ↓
Konfirmasi "Ya, mulai sekarang"
  ↓
Quiz Interface:
├─ ⏰ Timer countdown: [29:58]
├─ 📊 Progress: [Soal 1 dari 10]
├─ ❓ Pertanyaan
├─ 🔘 Multiple choice options
└─ 🔄 Navigation: [Prev] [Next] [Submit]
  ↓
Siswa menjawab semua soal
  ↓
Klik "Submit Quiz" → Final confirmation
  ↓
Auto-grading & RL Analysis:
  ↓
Hasil Quiz:
├─ 🎯 Score: [85/100]
├─ ✅ Correct: [8/10]
├─ ⏱️ Time used: [25 menit]
├─ 📈 Performance analysis
└─ 🤖 RL Recommendations untuk improvement
  ↓
Update Statistics & Generate next learning path
```

### 5️⃣ **ALUR MENGISI QUESTIONNAIRE**
```
Klik "Questionnaires" di sidebar
  ↓
Pilih Questionnaire Type:
├─ 📋 MSLQ (81 questions)
│  └─ Tujuan: Motivasi & strategi belajar
└─ 📋 AMS (28 questions)
   └─ Tujuan: Motivasi akademik
  ↓
Klik "Mulai" → Introduction page
  ↓
Questionnaire Interface:
├─ 📊 Progress bar: [15/81]
├─ ❓ Pertanyaan dalam Bahasa Indonesia
├─ 🎚️ Likert scale: [1-2-3-4-5]
│  ├─ 1: Sangat tidak setuju
│  ├─ 3: Netral
│  └─ 5: Sangat setuju
└─ 🔄 Navigation: [Previous] [Next]
  ↓
Siswa mengisi semua pertanyaan
  ↓
Submit → Processing & Scoring
  ↓
Hasil Questionnaire:
├─ 📊 Overall Score: [3.8/5.0]
├─ 📈 Subscale breakdown:
│  ├─ Intrinsic Motivation: [4.2]
│  ├─ Test Anxiety: [2.1]
│  └─ Self-Regulation: [3.9]
├─ 📋 Interpretation & meaning
└─ 🤖 AI recommendations berdasarkan profile
  ↓
Update AI model untuk personalisasi
```

### 6️⃣ **ALUR MENGAKSES MATERIALS**
```
Klik "Materials" di sidebar
  ↓
Tampil Katalog Materi:
├─ 🎥 Video tutorials
├─ 📄 PDF documents  
├─ 🎵 Audio lectures
└─ 📊 Presentations
  ↓
Filter/Search (opsional):
├─ 🔍 Keyword search
├─ 📚 Subject filter
├─ 👨‍🏫 Teacher filter
└─ ⭐ Rating filter
  ↓
CBF Recommendations:
├─ 💡 "Recommended for you" section
├─ 👥 "Students like you also viewed"
├─ 🔥 "Trending in your class"
└─ 📊 Confidence score: [94% match]
  ↓
Klik material → Detail page:
├─ 📖 Description
├─ ⏱️ Duration/Size
├─ ⭐ Ratings & reviews
├─ 📊 Learning objectives
└─ 🎯 Prerequisites
  ↓
Action Options:
├─ 📥 Download (untuk PDF)
├─ ▶️ Play/View (untuk multimedia)
├─ 💾 Save to favorites
└─ ⭐ Rate after viewing
  ↓
Tracking System:
├─ ✅ Mark as "Viewed"
├─ ⏱️ Time spent tracking
├─ 📊 Comprehension quiz (opsional)
└─ 💬 Feedback collection
  ↓
CBF Learning: Update recommendation algorithm
```

### 7️⃣ **ALUR MONITORING PROGRESS**
```
Klik "Progress" di sidebar
  ↓
Loading Analytics Dashboard:
  ↓
Overview Section:
├─ 📊 Total points earned
├─ 📈 Weekly growth rate
├─ 🏆 Achievements unlocked
└─ 📅 Activity calendar
  ↓
Detailed Breakdown:
├─ 📚 Per-subject analysis
├─ 📝 Assignment performance trends
├─ 🧩 Quiz accuracy over time
├─ 📋 Questionnaire results timeline
└─ 🎯 Goal vs achievement comparison
  ↓
Interactive Charts:
├─ 📈 Line chart: Points over time
├─ 📊 Bar chart: Subject comparison
├─ 🥧 Pie chart: Activity distribution
└─ 🎯 Progress toward goals
  ↓
AI Insights:
├─ 🤖 Performance pattern analysis
├─ 📊 Strength & weakness identification
├─ 💡 Improvement suggestions
└─ 🎯 Personalized goal setting
  ↓
Export Options:
├─ 📥 PDF report generation
├─ 📊 Excel data export
└─ 📧 Email summary to parents (opsional)
```

### 8️⃣ **ALUR AI INTERACTION**
```
Klik "AI Features" di sidebar
  ↓
Sub-menu Options:
├─ 📚 "Cara Kerja AI" → Educational content
├─ 🤖 "AI Recommendations" → Personal suggestions
└─ 📊 "Learning Analytics" → Deep insights
  ↓
AI Recommendations Page:
├─ 🔄 Real-time processing demo
├─ 📊 AI confidence metrics
├─ 💡 Personalized suggestions:
│  ├─ 📝 NLP: Essay improvement tips
│  ├─ 🎯 RL: Optimal study schedule  
│  └─ 📚 CBF: Material recommendations
└─ ✅ Action buttons untuk implement
  ↓
Implementation Tracking:
├─ ✅ Mark recommendation as "Applied"
├─ 📊 Measure effectiveness
├─ 🔄 Feedback loop untuk AI improvement
└─ 📈 Success rate tracking
```

---

## 🔄 WORKFLOW INTEGRATION

### **Cross-Feature Data Flow:**
```
QUESTIONNAIRE RESULTS
         ↓
    AI PROFILE UPDATE
         ↓
CBF ALGORITHM TRAINING
         ↓
PERSONALIZED RECOMMENDATIONS
         ↓
MATERIAL & QUIZ SUGGESTIONS
         ↓
STUDENT ENGAGEMENT
         ↓
PERFORMANCE DATA
         ↓
NLP & RL ANALYSIS
         ↓
REFINED RECOMMENDATIONS
         ↓
IMPROVED LEARNING OUTCOMES
```

### **Real-time Updates:**
```
User Action → Database Update → AI Processing → UI Refresh → Notification
     ↑                                                            ↓
     └──────────── Feedback Loop ←──── Analytics ←────────────────┘
```

---

## ⏱️ ESTIMASI WAKTU PER AKTIVITAS

| Aktivitas | Waktu Typical | Waktu Maksimal |
|-----------|---------------|----------------|
| 🚪 Login | 30 detik | 2 menit |
| 📝 Assignment | 15-45 menit | 2 jam |
| 🧩 Quiz | 20-30 menit | 45 menit |
| 📋 MSLQ | 15-20 menit | 30 menit |
| 📋 AMS | 8-12 menit | 20 menit |
| 📚 Materials | 5-60 menit | Variable |
| 📊 Progress Check | 3-5 menit | 10 menit |
| 🤖 AI Interaction | 2-10 menit | 15 menit |

---

## 🎯 SUCCESS METRICS

### **Per Activity Completion:**
```
📝 Assignment:
├─ ✅ Submitted on time
├─ 📊 NLP Score > 70
├─ 💡 Applied improvement suggestions
└─ 📈 Score improvement trend

🧩 Quiz: 
├─ ✅ Completed within time limit
├─ 📊 Accuracy > 70%
├─ 🎯 Consistent performance
└─ 📚 Used recommended study materials

📋 Questionnaire:
├─ ✅ 100% completion rate
├─ 🤔 Thoughtful responses (not all neutral)
├─ 📊 Consistent with previous results
└─ 🔄 Willingness to retake for accuracy

📚 Materials:
├─ ⏱️ Adequate time spent
├─ ⭐ Rating provided
├─ 📝 Notes taken (tracked)
└─ 📊 Follow-up quiz performance

📊 Progress:
├─ 📅 Regular monitoring (weekly)
├─ 🎯 Goal achievement rate
├─ 📈 Positive trend maintenance
└─ 🤖 AI recommendation adoption
```

---

## 🔧 ERROR HANDLING & FALLBACKS

### **Common Issues & Solutions:**
```
❌ Session Timeout:
   └─ Auto-redirect ke login + message
   
❌ Network Error:
   └─ Local storage backup + retry mechanism
   
❌ Database Connection:
   └─ Graceful degradation + offline mode
   
❌ AI Service Down:
   └─ Basic scoring + manual recommendations
   
❌ File Upload Failure:
   └─ Multiple retry + alternative upload methods
   
❌ Browser Compatibility:
   └─ Progressive enhancement + fallback UI
```

---

**📅 Document Version:** 1.0  
**🔄 Last Updated:** 3 Juli 2025  
**👥 Target Audience:** Siswa POINTMARKET  
**📧 Questions:** admin@pointmarket.com
