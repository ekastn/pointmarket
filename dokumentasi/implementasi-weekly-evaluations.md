# Implementasi Sistem Evaluasi Mingguan MSLQ dan AMS

## Overview
Sistem evaluasi mingguan telah berhasil diimplementasikan dalam POINTMARKET untuk memungkinkan monitoring progress siswa secara berkala menggunakan instrumen MSLQ (Motivated Strategies for Learning Questionnaire) dan AMS (Academic Motivation Scale).

## Fitur Utama

### 1. **Weekly Evaluations untuk Siswa** 📝
- **Lokasi**: `weekly-evaluations.php`
- **Akses**: Hanya siswa yang login
- **Fitur**:
  - Dashboard evaluasi mingguan dengan status pending, completed, dan overdue
  - Interface interaktif untuk mengisi kuesioner MSLQ dan AMS
  - Progress tracking untuk 8 minggu terakhir
  - Notifikasi untuk evaluasi yang tertunda
  - Scoring otomatis dan feedback langsung

### 2. **Teacher Monitoring Dashboard** 📊
- **Lokasi**: `teacher-evaluation-monitoring.php`
- **Akses**: Hanya guru yang login
- **Fitur**:
  - Overview statistik completion rate mingguan
  - Monitoring status setiap siswa (completed, pending, overdue)
  - Tracking skor MSLQ dan AMS per siswa
  - Export data untuk analisis lebih lanjut
  - Detail history evaluasi setiap siswa

### 3. **Automatic Weekly Generation** ⚙️
- Sistem otomatis menggenerate evaluasi mingguan baru
- Update status overdue secara otomatis
- Tracking berdasarkan nomor minggu dan tahun
- Due date otomatis (akhir minggu)

## Struktur Database

### Tabel Baru:
1. **`weekly_evaluations`** - Tracking jadwal dan status evaluasi mingguan
2. **`questionnaire_questions`** - Menyimpan pertanyaan MSLQ dan AMS
3. **`activity_log`** - Log aktivitas sistem

### Tabel yang Diupdate:
1. **`questionnaire_results`** - Ditambah kolom `week_number` dan `year`

## Cara Penggunaan

### Untuk Administrator:
1. **Inisialisasi Sistem**:
   ```
   Jalankan: http://localhost/pointmarket/initialize-weekly-evaluations.php
   ```
   Script ini akan:
   - Membuat tabel yang diperlukan
   - Memasukkan pertanyaan MSLQ dan AMS
   - Generate evaluasi mingguan untuk minggu aktif

### Untuk Siswa:
1. **Akses Evaluasi Mingguan**:
   - Login ke POINTMARKET
   - Klik "Weekly Evaluations" di sidebar
   - Lengkapi evaluasi yang pending

2. **Mengisi Kuesioner**:
   - Klik "Start Evaluation" pada evaluasi yang pending
   - Rating 1-7 untuk setiap pertanyaan
   - Submit untuk mendapat skor langsung

### Untuk Guru:
1. **Monitoring Progress**:
   - Login sebagai guru
   - Klik "Evaluation Monitoring" di sidebar
   - Review completion rate dan student progress

2. **Export Data**:
   - Gunakan tombol "Export" untuk download CSV
   - Data dapat dianalisis di Excel atau tools lain

## Konfigurasi MSLQ dan AMS

### MSLQ (20 pertanyaan sampel):
- **Intrinsic Goal Orientation** (4 items)
- **Extrinsic Goal Orientation** (4 items) 
- **Task Value** (4 items)
- **Control of Learning Beliefs** (4 items)
- **Self-Efficacy for Learning and Performance** (4 items)

### AMS (10 pertanyaan sampel):
- **Intrinsic Motivation - To Know** (2 items)
- **Intrinsic Motivation - To Experience Stimulation** (2 items)
- **Extrinsic Motivation - Identified** (2 items)
- **Extrinsic Motivation - Introjected** (2 items)
- **Amotivation** (2 items)

## Algoritma Scoring

### Scoring Sederhana (Current):
- Total score = Rata-rata dari semua jawaban (1-7)
- Interpretasi:
  - **5.5-7.0**: High motivation/strategy use
  - **4.0-5.4**: Medium motivation/strategy use
  - **1.0-3.9**: Low motivation/strategy use

### Scoring Lanjutan (Future Enhancement):
- Weighted scoring berdasarkan subscale
- Reverse scoring untuk item tertentu
- Normalized scores berdasarkan populasi

## Integration dengan AI System

### 1. **Data Collection**:
- Weekly scores tersimpan dengan timestamp
- Tracking progress per subscale
- Historical trend analysis

### 2. **Personalization Input**:
- Skor MSLQ → Learning strategy recommendations
- Skor AMS → Motivation enhancement suggestions
- Weekly changes → Adaptive content delivery

### 3. **Reinforcement Learning**:
- Student response patterns
- Intervention effectiveness
- Personalized feedback loops

## Maintenance dan Monitoring

### Weekly Tasks:
- ✅ Auto-generate evaluations (automated)
- ✅ Update overdue status (automated)
- 📋 Review completion rates (manual)
- 📊 Analyze trend data (manual)

### Monthly Tasks:
- 📈 Generate progress reports
- 🔍 Identify at-risk students
- ⚙️ Adjust reminder frequency
- 📚 Update questionnaire items if needed

## API Endpoints (AJAX)

### Student Interface:
- `POST /weekly-evaluations.php?action=get_questionnaire`
- `POST /weekly-evaluations.php?action=submit_questionnaire`

### Teacher Interface:
- `POST /teacher-evaluation-monitoring.php?action=get_student_detail`

## Security Features

### Access Control:
- Role-based access (siswa/guru/admin)
- Session validation
- CSRF protection

### Data Protection:
- Sanitized inputs
- Prepared statements
- Activity logging

## Performance Optimization

### Database:
- Indexed columns for fast queries
- Efficient joins for progress tracking
- Cached student statistics

### Frontend:
- Lazy loading for large datasets
- Progressive form submission
- Real-time validation

## Troubleshooting

### Common Issues:

1. **Evaluations not generating**:
   - Check if `generateWeeklyEvaluations()` is called
   - Verify active questionnaires exist
   - Ensure students are registered

2. **Scores not calculating**:
   - Verify all questions are answered
   - Check JSON encoding in answers
   - Review scoring algorithm

3. **Teacher dashboard empty**:
   - Confirm students have started evaluations
   - Check week/year filtering
   - Verify role permissions

## Roadmap Future Enhancements

### Phase 1 (Current): ✅
- Basic weekly evaluation system
- MSLQ and AMS implementation
- Teacher monitoring dashboard

### Phase 2 (Planned):
- 📧 Email reminders for pending evaluations
- 📱 Mobile-responsive questionnaire interface
- 📊 Advanced analytics and visualization

### Phase 3 (Future):
- 🤖 ML-based intervention recommendations
- 🎯 Adaptive questionnaire length
- 🔗 Integration dengan Learning Management System

## Support dan Dokumentasi

### Files Terkait:
- `/dokumentasi/peran-mslq-ams.md` - Penjelasan scientific basis
- `/dokumentasi/panduan-siswa.md` - Guide untuk siswa
- `/initialize-weekly-evaluations.php` - Setup script

### Contact:
Untuk pertanyaan atau issue terkait weekly evaluation system, silakan review dokumentasi atau periksa activity log di database.

---
*Dokumentasi ini dibuat untuk memastikan implementasi weekly evaluation system berjalan optimal dan dapat di-maintain dengan baik.*
