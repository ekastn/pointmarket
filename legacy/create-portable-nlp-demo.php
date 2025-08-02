<?php
/**
 * Script untuk membuat package portable NLP Demo
 */

echo "🚀 Creating portable NLP Demo package...\n\n";

// Direktori tujuan
$targetDir = __DIR__ . '/portable-nlp-demo';

// Buat direktori jika belum ada
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0755, true);
    echo "📁 Created directory: $targetDir\n";
} else {
    echo "📁 Directory already exists: $targetDir\n";
}

if (!is_dir($targetDir . '/api')) {
    mkdir($targetDir . '/api', 0755, true);
    echo "📁 Created API directory: $targetDir/api\n";
}

// Check if source files exist
$sourceFiles = [
    'super-simple-nlp-demo.php' => 'Main demo file',
    'api/nlp-mini-api.php' => 'API endpoint',
    'panduan-nlp-demo-simplified.html' => 'User guide documentation'
];

$allFilesExist = true;
foreach ($sourceFiles as $file => $description) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "✅ Found: $file ($description)\n";
    } else {
        echo "❌ Missing: $file ($description)\n";
        $allFilesExist = false;
    }
}

if (!$allFilesExist) {
    echo "\n❌ Some required files are missing. Cannot create package.\n";
    exit(1);
}

echo "\n📋 Copying files...\n";

// Copy file utama sebagai index.php
if (copy(__DIR__ . '/super-simple-nlp-demo.php', $targetDir . '/index.php')) {
    echo "✅ Copied: super-simple-nlp-demo.php → index.php\n";
} else {
    echo "❌ Failed to copy main demo file\n";
    exit(1);
}

// Copy API file
if (copy(__DIR__ . '/api/nlp-mini-api.php', $targetDir . '/api/nlp-mini-api.php')) {
    echo "✅ Copied: api/nlp-mini-api.php\n";
} else {
    echo "❌ Failed to copy API file\n";
    exit(1);
}

// Copy panduan file
if (copy(__DIR__ . '/panduan-nlp-demo-simplified.html', $targetDir . '/panduan-nlp-demo-simplified.html')) {
    echo "✅ Copied: panduan-nlp-demo-simplified.html\n";
} else {
    echo "❌ Failed to copy panduan file\n";
    exit(1);
}

// Buat README untuk package
$readme = <<<README
# 🎯 Portable NLP Demo Package

## 📋 Quick Start

1. **Upload this folder** to your web server
2. **Access**: `http://yourserver/your-folder/index.php`
3. **That's it!** No database or additional setup required

## 📁 What's Included

- `index.php` - Main NLP demo page (15KB)
- `api/nlp-mini-api.php` - API endpoint (8KB)
- `panduan-nlp-demo-simplified.html` - Complete user guide (25KB)
- `README.md` - This documentation
- `.htaccess` - Optional Apache configuration

## ✨ Features

✅ **Zero Configuration** - No setup required
✅ **No Database** - Uses simulated data
✅ **Responsive Design** - Works on all devices
✅ **Multiple Examples** - 11 built-in example texts
✅ **Real-time Analysis** - Instant text processing simulation
✅ **Modern UI** - Bootstrap 5 with animations

## 🔧 Technical Requirements

- **PHP**: 5.6+ (no special extensions required)
- **Web Server**: Apache/Nginx with PHP support
- **Internet**: Required for CDN resources (Bootstrap, jQuery, FontAwesome)
- **Database**: Not required
- **Special Modules**: None

## 🎭 What It Simulates

This demo provides realistic NLP analysis including:

- **Grammar Analysis** (0-100 score)
- **Keyword Extraction** (relevant terms)
- **Structure Analysis** (organization score)
- **Readability Assessment** (ease of reading)
- **Sentiment Analysis** (positive/negative/neutral)
- **Complexity Scoring** (text difficulty)

## 📊 Example Texts Included

The demo includes 11 pre-written examples covering:

1. **Assignment**: General academic writing
2. **Mathematics**: Math problem explanations
3. **Physics**: Scientific concepts
4. **Chemistry**: Chemical processes
5. **Biology**: Life science topics
6. **History**: Historical analysis
7. **Literature**: Literary analysis
8. **Economics**: Economic concepts
9. **Technology**: Tech explanations
10. **Philosophy**: Philosophical discussions
11. **Environmental**: Environmental topics

## 🌐 CDN Dependencies

All styling and functionality loaded via CDN:

- Bootstrap 5.3.0 (CSS framework)
- Font Awesome 6.4.0 (icons)
- jQuery 3.6.0 (JavaScript library)

## 🎯 Perfect For

- **Client Demonstrations** - Show NLP capabilities
- **Educational Use** - Teaching text analysis concepts
- **Portfolio Showcase** - Display technical skills
- **Prototyping** - Quick NLP interface mockups
- **Testing** - UI/UX validation

## ⚠️ Important Notes

1. **Simulation Only**: Uses algorithmic simulation, not real AI/ML models
2. **Educational Purpose**: Designed for demonstration and learning
3. **Realistic Results**: Provides convincing but simulated analysis
4. **No Data Storage**: Analysis results are not saved

## 🚀 Deployment Examples

### Local Development
```
http://localhost/portable-nlp-demo/index.php
```

### Shared Hosting
```
http://yourdomain.com/nlp-demo/index.php
```

### Cloud Hosting
```
http://your-cloud-server.com/demo/index.php
```

## 📞 Support

This package is part of the POINTMARKET NLP System.
For questions or issues, refer to the main documentation.

---

**Package Size**: ~25KB total
**Setup Time**: <1 minute
**Dependencies**: Zero local files
**Configuration**: None required

**Created**: July 14, 2025
**Version**: Portable Demo v1.0
README;

if (file_put_contents($targetDir . '/README.md', $readme)) {
    echo "✅ Created: README.md with complete documentation\n";
} else {
    echo "❌ Failed to create README.md\n";
}

// Buat file .htaccess untuk optimasi
$htaccess = <<<HTACCESS
# Portable NLP Demo - Apache Configuration

# Set index file
DirectoryIndex index.php

# Disable directory browsing
Options -Indexes

# Enable compression for better performance
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/css text/javascript application/javascript application/json
</IfModule>

# Enable browser caching
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType image/jpg "access plus 1 month"
    ExpiresByType image/jpeg "access plus 1 month"
    ExpiresByType image/gif "access plus 1 month"
    ExpiresByType image/svg+xml "access plus 1 month"
</IfModule>

# Security headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options SAMEORIGIN
    Header always set X-XSS-Protection "1; mode=block"
</IfModule>

# PHP settings for better performance
<IfModule mod_php.c>
    php_value upload_max_filesize 2M
    php_value post_max_size 2M
    php_value max_execution_time 30
    php_value max_input_time 30
</IfModule>
HTACCESS;

if (file_put_contents($targetDir . '/.htaccess', $htaccess)) {
    echo "✅ Created: .htaccess with optimization settings\n";
} else {
    echo "❌ Failed to create .htaccess\n";
}

// Buat file info.txt dengan statistik
$info = <<<INFO
Portable NLP Demo Package Information
=====================================

Created: July 14, 2025
Package Version: 1.0
Total Files: 5

File Details:
- index.php (Main demo): ~15KB
- api/nlp-mini-api.php (API): ~8KB  
- panduan-nlp-demo-simplified.html (User guide): ~25KB
- README.md (Documentation): ~4KB
- .htaccess (Configuration): ~1KB

Total Package Size: ~53KB

Requirements:
- PHP 5.6+
- Web server with PHP support
- Internet connection (for CDN resources)

Features:
- Zero configuration required
- No database needed
- Responsive design
- 11 example texts included
- Real-time analysis simulation
- Modern Bootstrap interface

Deployment:
1. Upload entire folder to web server
2. Access index.php in browser
3. Start using immediately

Support:
- No installation required
- No dependencies to install
- Works on any PHP hosting
- Compatible with shared hosting

This package is completely self-contained and portable.
INFO;

if (file_put_contents($targetDir . '/info.txt', $info)) {
    echo "✅ Created: info.txt with package details\n";
} else {
    echo "❌ Failed to create info.txt\n";
}

// Hitung total ukuran package
$totalSize = 0;
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($targetDir));
foreach ($iterator as $file) {
    if ($file->isFile()) {
        $totalSize += $file->getSize();
    }
}

echo "\n🎉 Package created successfully!\n";
echo "📁 Location: $targetDir\n";
echo "📊 Total size: " . number_format($totalSize) . " bytes (" . number_format($totalSize/1024, 1) . " KB)\n";
echo "📋 Files included: 4 files\n";
echo "\n🌐 Access URLs:\n";
echo "   Local: http://localhost/pointmarket/portable-nlp-demo/\n";
echo "   Direct: http://localhost/pointmarket/portable-nlp-demo/index.php\n";
echo "\n✅ Ready to deploy!\n";
echo "   You can now copy the 'portable-nlp-demo' folder to any web server.\n";
echo "   No additional configuration required - just upload and access index.php\n";

echo "\n📋 Next Steps:\n";
echo "   1. Test locally: Open http://localhost/pointmarket/portable-nlp-demo/\n";
echo "   2. Upload to server: Copy entire 'portable-nlp-demo' folder\n";
echo "   3. Access on server: Browse to your-domain/folder-name/index.php\n";
echo "   4. Share the demo: Send the URL to clients or colleagues\n";

echo "\n🎯 Package Summary:\n";
echo "   ✅ Zero configuration\n";
echo "   ✅ No database required\n";
echo "   ✅ Self-contained\n";
echo "   ✅ Ready to distribute\n";
echo "   ✅ Works anywhere with PHP\n";
?>
