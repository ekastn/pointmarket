# POINTMARKET - PHP Dynamic Version

## 🎯 Deskripsi

POINTMARKET versi PHP adalah implementasi dinamis dari proof-of-concept pembelajaran adaptif yang menggunakan database MySQL dan phpMyAdmin. Aplikasi ini menyediakan sistem CRUD yang realistis dengan kalkulasi real-time untuk total points dan completed assignments.

## 🚀 Fitur Utama

### **Sistem Autentikasi**
- Login multi-role (Siswa, Guru, Admin)
- Session management yang aman
- Password hashing menggunakan bcrypt
- CSRF protection

### **Role-Based Access Control**
- **Siswa**: Akses assignments, quiz, kuesioner, materials
- **Guru**: Buat dan kelola content, lihat progress siswa
- **Admin**: Kelola users, system settings, reports

### **Sistem Dinamis**
- **Total Points**: Dihitung otomatis dari skor assignments + quiz
- **Completed Assignments**: Dihitung dari jumlah tugas yang diselesaikan
- Real-time updates menggunakan database views
- Data persistence dengan MySQL

### **Kuesioner Terintegrasi**
- **Weekly MSLQ & AMS Evaluations**: Systematic weekly assessment system for continuous motivation and learning strategy monitoring
- **MSLQ (Motivated Strategies for Learning)**: 20+ questions covering goal orientation, task value, self-efficacy, and learning strategies
- **AMS (Academic Motivation Scale)**: 10+ questions measuring intrinsic motivation, extrinsic motivation, and amotivation
- **Automatic scoring** dan subscale analysis untuk personalisasi AI
- **Teacher monitoring dashboard** untuk tracking completion rates dan progress
- **History tracking** dan trend analysis untuk research dan intervention

### **AI Simulation**
- Reinforcement Learning simulation berdasarkan weekly evaluation data
- Content-Based Filtering recommendations menggunakan profil MSLQ/AMS
- **🧠 Natural Language Processing (NLP)** - COMPLETE implementation untuk text analysis dan personalized feedback
- Performance metrics real-time dengan historical comparison
- **Interactive AI explanation pages** dengan demo recommendations
- **User-friendly AI demonstrations** untuk non-technical users

### **🆕 NLP Analysis System**
- **Real-time text analysis** dengan 6 komponen scoring (grammar, keywords, structure, readability, sentiment, complexity)
- **Personalized feedback** berdasarkan profil MSLQ, AMS, dan VARK learning styles
- **Auto-analysis** saat mengetik dengan debounced processing
- **Visual feedback** dengan progress bars dan color-coded results
- **History tracking** dan statistics untuk monitoring improvement
- **Context-aware analysis** berdasarkan mata pelajaran dan topik
- **API integration** untuk seamless frontend-backend communication

### **Weekly Evaluation System** 🆕
- **Automated weekly scheduling** untuk MSLQ dan AMS questionnaires
- **Student interface** dengan progress tracking dan pending notifications
- **Teacher monitoring tools** dengan completion analytics dan student detail views
- **Overdue tracking** dan reminder system
- **Export functionality** untuk data analysis dan research
- **Integration dengan AI personalization** untuk adaptive learning recommendations

### **Comprehensive Documentation**
- **Complete user guides** for students
- **Detailed workflow documentation**
- **FAQ with troubleshooting**
- **AI explanation for non-technical users**

## 📦 Struktur Direktori

```
pointmarket-php/
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── dashboard.js
├── auth/
│   ├── process_login.php
│   └── logout.php
├── database/
│   └── pointmarket.sql
├── dokumentasi/
│   ├── README.md
│   ├── panduan-siswa.md
│   ├── alur-kerja-siswa.md
│   └── faq-siswa.md
├── includes/
│   ├── config.php
│   ├── navbar.php
│   └── sidebar.php
├── index.php
├── login.php
├── dashboard.php
├── ai-explanation.php
├── ai-recommendations.php
├── AI_EXPLANATION.md
└── README.md
```

## 🔧 Setup dan Instalasi

### **1. Persiapan Environment**
```bash
# Install XAMPP/LAMP/WAMP dengan:
- Apache 2.4+
- PHP 8.0+
- MySQL 8.0+
- phpMyAdmin
```

### **2. Database Setup**
1. Buka phpMyAdmin (http://localhost/phpmyadmin)
2. Buat database baru: `pointmarket`
3. Import file: `database/pointmarket.sql`
4. Verifikasi tabel dan data demo berhasil dibuat

### **3. Konfigurasi Aplikasi**
1. Copy folder `pointmarket-php` ke directory web server
   ```bash
   # Untuk XAMPP
   C:\xampp\htdocs\pointmarket-php\
   
   # Untuk LAMP
   /var/www/html/pointmarket-php/
   ```

2. Edit konfigurasi database di `includes/config.php`:
   ```php
   private $host = "localhost";
   private $db_name = "pointmarket";
   private $username = "root";  // Sesuaikan dengan setup MySQL
   private $password = "";      // Sesuaikan dengan password MySQL
   ```

3. Set permissions yang sesuai:
   ```bash
   chmod 755 pointmarket-php/
   chmod 644 pointmarket-php/*.php
   ```

### **4. Akses Aplikasi**
- URL: `http://localhost/pointmarket-php/`
- Aplikasi akan redirect ke halaman login

## 👥 Demo Accounts

### **Login Credentials**
```
Siswa:
- Username: andi | Password: password
- Username: budi | Password: password

Guru:
- Username: sarah | Password: password
- Username: ahmad | Password: password

Admin:
- Username: admin | Password: password
```

**Note**: Password default "password" sudah di-hash dalam database.

## 💾 Database Schema

### **Tabel Utama**
- `users` - Data pengguna dan role
- `assignments` - Tugas dari guru
- `quiz` - Kuis dan ujian
- `student_assignments` - Tracking penyelesaian tugas
- `student_quiz` - Tracking penyelesaian quiz
- `questionnaires` - Data kuesioner MSLQ/AMS
- `questionnaire_results` - Hasil kuesioner siswa
- `materials` - Materi pembelajaran

### **Database View**
- `student_stats` - View untuk kalkulasi otomatis:
  ```sql
  CREATE VIEW student_stats AS
  SELECT 
      u.id as student_id,
      u.name as student_name,
      (SELECT SUM(sa.score) FROM student_assignments sa 
       WHERE sa.student_id = u.id AND sa.status = 'completed') +
      (SELECT SUM(sq.score) FROM student_quiz sq 
       WHERE sq.student_id = u.id AND sq.status = 'completed') as total_points,
      (SELECT COUNT(*) FROM student_assignments sa 
       WHERE sa.student_id = u.id AND sa.status = 'completed') +
      (SELECT COUNT(*) FROM student_quiz sq 
       WHERE sq.student_id = u.id AND sq.status = 'completed') as completed_assignments
  FROM users u WHERE u.role = 'siswa';
  ```

## 🔍 Perbedaan dengan Versi JavaScript

### **Yang Berbeda:**
1. **Data Storage**: MySQL database vs localStorage
2. **Kalkulasi**: Real-time SQL queries vs static values
3. **Authentication**: Server-side session vs client-side
4. **Security**: CSRF protection, password hashing, SQL injection prevention
5. **Scalability**: Multi-user concurrent access

### **Yang Sama:**
1. UI/UX design dan layout
2. Role-based access control
3. Kuesioner MSLQ dan AMS
4. AI simulation features
5. Responsive design

## 🧪 Testing

### **Manual Testing**
1. **Authentication Testing**
   ```
   ✓ Login dengan semua demo accounts
   ✓ Logout functionality
   ✓ Session persistence
   ✓ Role-based access restrictions
   ```

2. **CRUD Operations**
   ```
   ✓ Create assignments/quiz (guru)
   ✓ Submit assignments (siswa)
   ✓ Grade submissions (guru)
   ✓ View real-time statistics
   ```

3. **Database Integration**
   ```
   ✓ Data persistence after browser refresh
   ✓ Real-time total points calculation
   ✓ Completed assignments counter accuracy
   ✓ Questionnaire results storage
   ```

### **Database Testing**
```sql
-- Test total points calculation
SELECT * FROM student_stats WHERE student_id = 1;

-- Test questionnaire results
SELECT * FROM questionnaire_results WHERE student_id = 1;

-- Test assignment submissions
SELECT * FROM student_assignments WHERE student_id = 1;
```

## 📊 Features Implementation

### **Dynamic Statistics Calculation**
```php
function getStudentStats($student_id, $pdo) {
    $stmt = $pdo->prepare("SELECT total_points, completed_assignments FROM student_stats WHERE student_id = ?");
    $stmt->execute([$student_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
```

### **Security Features**
- Password hashing dengan `password_hash()`
- CSRF token protection
- SQL injection prevention dengan prepared statements
- Input sanitization
- Session security

### **Performance Optimization**
- Database indexing
- Prepared statements untuk queries
- View untuk kalkulasi kompleks
- Lazy loading untuk data besar

## 🔒 Security Considerations

1. **Database Security**
   - Gunakan user MySQL dengan privileges terbatas
   - Backup database secara berkala
   - Monitor access logs

2. **Application Security**
   - Update PHP secara berkala
   - Gunakan HTTPS di production
   - Implement rate limiting untuk login

3. **File Security**
   - Set proper file permissions
   - Disable directory listing
   - Validate file uploads

## 🚀 Deployment ke Production

### **Server Requirements**
- PHP 8.0+ dengan extensions: PDO, MySQL, mbstring
- MySQL 8.0+ atau MariaDB 10.4+
- Apache/Nginx web server
- SSL certificate untuk HTTPS

### **Production Checklist**
```
□ Database credentials dalam environment variables
□ Error reporting disabled di production
□ HTTPS enabled
□ Database backup system
□ Monitoring dan logging
□ Performance optimization
□ Security headers configured
```

## 📈 Future Development

### **Planned Features**
1. **Real AI Integration**
   - TensorFlow.js untuk client-side AI
   - Python backend untuk ML models
   - API integration untuk NLP services

2. **Advanced Analytics**
   - Learning path recommendation
   - Predictive modeling untuk student success
   - Real-time collaboration features

3. **Mobile App**
   - React Native/Flutter mobile app
   - Push notifications
   - Offline learning capabilities

## 🎓 Research Value

### **Untuk Penelitian Fundamental**
1. **Data Collection**: Real user interaction data
2. **A/B Testing**: Different learning approaches
3. **Analytics**: Learning pattern analysis
4. **Scalability Testing**: Multi-user performance
5. **Security Research**: Educational platform security

### **Metrics untuk Evaluasi**
- User engagement rates
- Learning outcome improvements
- System performance metrics
- User satisfaction scores
- Feature adoption rates

## 📞 Support dan Dokumentasi

### **User Documentation**
- **Student Guide**: `dokumentasi/panduan-siswa.md` - Panduan lengkap untuk siswa
- **Workflow**: `dokumentasi/alur-kerja-siswa.md` - Alur kerja detail fitur siswa  
- **FAQ**: `dokumentasi/faq-siswa.md` - Pertanyaan yang sering diajukan
- **AI Explanation**: `AI_EXPLANATION.md` - Penjelasan teknologi AI untuk awam

### **Technical Support**
- Database issues: Check MySQL logs
- PHP errors: Enable error logging
- Performance: Use profiling tools
- Security: Regular security audits

### **Documentation**
- API documentation (jika ada)
- Database schema documentation
- Deployment guides
- User manuals per role

---

## 🎉 Kesimpulan

Versi PHP ini memberikan implementasi yang lebih realistis dari konsep POINTMARKET dengan:

✅ **Database dinamis** yang menggantikan localStorage  
✅ **Kalkulasi real-time** untuk total points dan completed assignments  
✅ **Security features** untuk production-ready application  
✅ **Scalability** untuk multiple concurrent users  
✅ **Research capabilities** untuk data collection dan analysis  

**Status**: ✅ Ready for deployment dan research implementation

**Next Steps**: Deploy ke testing server dan mulai user testing dengan target audience (siswa, guru, admin).

## 🔧 Troubleshooting NLP System

Jika mengalami error `SyntaxError: Unexpected token '<'` pada NLP demo:

1. **Quick Fix**: Buka `http://localhost/pointmarket/setup-nlp.php` untuk auto-setup
2. **Manual Fix**: Import SQL dari `database/nlp-schema.sql` ke phpMyAdmin
3. **Test**: Gunakan tombol "Test API" di `nlp-demo.php`

Lihat [NLP Setup Guide](NLP_SETUP_GUIDE.md) untuk detail lengkap.

## 📊 Analytics dan Monitoring

### **Monitoring Tools**
- **Database Monitoring**: MySQL slow query log, query performance insights
- **PHP Monitoring**: Error logs, performance profiling
- **Web Server Monitoring**: Access logs, error logs, performance metrics

### **Analytics Tools**
- **Google Analytics**: User behavior tracking, conversion tracking
- **Custom Analytics**: Database-driven analytics for in-depth insights

### **Monitoring Metrics**
- User engagement: active users, session duration, page views
- System performance: response time, query execution time, error rates
- Security: login attempts, data access logs, file integrity checks

### **Analytics Insights**
- User behavior patterns
- Feature usage statistics
- Performance bottlenecks
- Security incident trends
