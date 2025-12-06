# CVerity AI - Backend

Backend PHP untuk platform CVerity AI yang di-deploy ke **cPanel/Shared Hosting**.

## 📁 Struktur Folder

```
Backend/
├── app/                    # API Endpoints
│   ├── config.php          # Konfigurasi aplikasi
│   ├── upload.php          # API: Upload CV
│   ├── analyze.php         # API: Analisis CV dengan Gemini AI
│   ├── get-cv.php          # API: Get CV data
│   ├── delete-cv.php       # API: Delete CV
│   ├── download-docx.php   # API: Download CV improved (DOCX)
│   ├── generate-improved-cv.php  # API: Generate improved CV
│   ├── refresh-jobs.php    # API: Refresh job recommendations
│   └── .htaccess           # API folder protection
│
├── lib/                    # Core Libraries
│   ├── gemini_client.php   # Gemini AI client
│   ├── cv_storage.php      # CV storage handler
│   ├── cv_template.php     # CV template generator
│   ├── docx_generator.php  # DOCX generator
│   ├── docx_parser.php     # DOCX parser
│   ├── pdf_parser.php      # PDF parser
│   ├── job_generator.php   # Job matching engine
│   ├── api_monitor.php     # API monitoring
│   └── helpers.php         # Helper functions
│
├── vendor/                 # Composer dependencies
├── uploads/                # Uploaded CV files
├── cv_data/                # Stored CV analysis data (JSON)
├── logs/                   # Application logs
├── data-linkedin/          # LinkedIn job data
├── templates/              # CV templates
│
├── .htaccess               # Apache configuration
├── .env.php.example        # Environment config example
├── .env.php                # Environment config (JANGAN COMMIT!)
├── composer.json           # Composer dependencies
├── hosting-check.php       # Hosting compatibility check
└── README.md               # File ini
```

## 🚀 Deployment ke Hosting

### Prerequisites

- PHP 7.4 atau lebih tinggi
- Composer (atau akses SSH untuk install)
- PHP Extensions: `zip`, `xml`, `mbstring`, `curl`
- Memory limit minimal 128MB
- Google Gemini API Key

### Metode 1: Via cPanel File Manager (Recommended)

#### 1. Compress Backend Folder

```bash
cd Backend
zip -r backend.zip .
```

#### 2. Upload via cPanel

- Login ke cPanel
- Buka "File Manager"
- Navigate ke `public_html` atau folder domain Anda
- Upload `backend.zip`
- Extract file (klik kanan → Extract)

#### 3. Install Composer Dependencies

**Jika hosting support SSH:**
```bash
ssh user@your-hosting.com
cd public_html
composer install --no-dev --optimize-autoloader
```

**Jika tidak ada SSH:**
- Install Composer di local: `composer install --no-dev`
- Upload folder `vendor/` via FTP/cPanel File Manager

#### 4. Setup Environment

```bash
cp .env.php.example .env.php
nano .env.php  # Edit dan isi API keys
```

#### 5. Set Permissions

```bash
chmod 755 uploads/
chmod 755 cv_data/
chmod 755 logs/
chmod 644 .env.php
```

### Metode 2: Via FTP (FileZilla)

#### 1. Connect via FTP

- Host: `ftp.your-domain.com`
- Username: `your-username`
- Password: `your-password`
- Port: 21

#### 2. Upload Files

- Upload semua file di folder `Backend/` ke `public_html/`
- Pastikan struktur folder tetap sama

#### 3. Install Dependencies

Via SSH atau upload manual folder `vendor/`

#### 4. Setup Environment

Sama seperti metode 1 (step 4-5)

## ⚙️ Konfigurasi

### 1. Environment Variables

Edit file `.env.php`:

```php
<?php
return [
    // Google Gemini AI API Keys (array untuk load balancing)
    'GEMINI_API_KEYS' => [
        'AIzaSyXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX',  // Key 1 (required)
        'AIzaSyYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYY',  // Key 2 (optional)
        'AIzaSyZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZ',  // Key 3 (optional)
    ],
    
    // Gemini API Endpoint
    'GEMINI_ENDPOINT' => 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-exp:generateContent',
    
    // Auto-delete CV after X days (0 = disable)
    'AUTO_DELETE_DAYS' => 30,
    
    // Max upload size (bytes) - 5MB
    'MAX_UPLOAD_SIZE' => 5242880,
];
```

### 2. Apache Configuration

File `.htaccess` sudah dikonfigurasi untuk:
- CORS headers
- Security headers
- File upload limits
- Folder protection

**Update CORS untuk production:**

```apache
# Edit .htaccess
Header set Access-Control-Allow-Origin "https://your-frontend.vercel.app"
```

### 3. PHP Configuration (Optional)

Jika perlu adjust PHP settings, buat file `php.ini` atau `.user.ini`:

```ini
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
memory_limit = 256M
```

## 🔑 Mendapatkan API Key

### Google Gemini AI

1. Kunjungi [Google AI Studio](https://makersuite.google.com/app/apikey)
2. Login dengan akun Google
3. Klik "Create API Key"
4. Copy API key dan paste ke `.env.php`

**Tips:**
- Gunakan 2-3 API keys untuk load balancing
- Free tier: 60 requests/minute per key
- Monitor usage di [Google Cloud Console](https://console.cloud.google.com/)

## 📡 API Endpoints

### 1. Upload CV

```bash
POST /app/upload.php
Content-Type: multipart/form-data

Body:
- cv_file: File (PDF/DOCX, max 5MB)

Response:
{
  "success": true,
  "data": {
    "filename": "cv_123456.pdf",
    "original_name": "my_cv.pdf",
    "size": 245678
  }
}
```

### 2. Analyze CV

```bash
POST /app/analyze.php

Response:
{
  "success": true,
  "data": {
    "cv_id": "abc123",
    "cv_data": { ... },
    "evaluation": { ... },
    "jobs": [ ... ]
  }
}
```

### 3. Get CV Data

```bash
GET /app/get-cv.php?id=abc123

Response:
{
  "success": true,
  "data": {
    "cv_data": { ... },
    "evaluation": { ... },
    "jobs": [ ... ]
  }
}
```

### 4. Generate Improved CV

```bash
POST /app/generate-improved-cv.php
Content-Type: application/json

Body:
{
  "cv_id": "abc123"
}

Response:
{
  "success": true,
  "data": {
    "improved_cv": "<html>...</html>"
  }
}
```

### 5. Download DOCX

```bash
GET /app/download-docx.php?id=abc123

Response: File download (DOCX)
```

### 6. Refresh Jobs

```bash
POST /app/refresh-jobs.php
Content-Type: application/json

Body:
{
  "cv_id": "abc123"
}

Response:
{
  "success": true,
  "data": {
    "jobs": [ ... ]
  }
}
```

## 🗄️ Data Storage

Aplikasi ini **tidak menggunakan database**. Data disimpan dalam file JSON di folder `cv_data/`.

**Struktur file:**
```
cv_data/
├── abc123.json         # CV data + analysis
├── def456.json
└── ...
```

**Format JSON:**
```json
{
  "cv_data": { ... },
  "evaluation": { ... },
  "jobs": [ ... ],
  "improved_cv": "<html>...</html>",
  "analyzed_at": 1234567890,
  "jobs_updated_at": 1234567890
}
```

## 🔒 Security

### File Upload Security
- ✅ Validasi extension (hanya PDF/DOCX)
- ✅ Validasi MIME type
- ✅ Validasi ukuran file (max 5MB)
- ✅ Generate unique filename
- ✅ Folder protection via .htaccess

### API Security
- ✅ CORS headers untuk restrict domain
- ✅ Input validation & sanitization
- ✅ Error handling & logging
- ✅ Rate limiting via API Monitor

### Environment Security
- ✅ `.env.php` tidak di-commit ke Git
- ✅ Proper file permissions
- ✅ Disable directory listing
- ✅ Protect sensitive files

### Data Privacy
- ✅ Auto-delete CV setelah 30 hari
- ✅ No database = no data breach risk
- ✅ Unique ID untuk file storage

## 🐛 Troubleshooting

### Composer Install Gagal

**Problem:** `composer: command not found`

**Solution:**
```bash
# Install Composer
curl -sS https://getcomposer.org/installer | php
php composer.phar install
```

### Permission Denied

**Problem:** `Warning: move_uploaded_file(): Permission denied`

**Solution:**
```bash
chmod 755 uploads/ cv_data/ logs/
chown www-data:www-data uploads/ cv_data/ logs/
```

### Gemini API Error 429

**Problem:** `HTTP 429: Resource Exhausted`

**Solution:**
- Tambahkan lebih banyak API keys di `.env.php`
- Tunggu beberapa menit (quota reset)
- Upgrade ke Gemini API paid plan

### Gemini API Error 400

**Problem:** `HTTP 400: Bad Request`

**Solution:**
- Periksa format request
- Periksa API key valid
- Periksa file tidak corrupt

### CORS Error

**Problem:** Frontend tidak bisa akses API

**Solution:**
```apache
# Edit .htaccess
Header set Access-Control-Allow-Origin "https://your-frontend.vercel.app"
Header set Access-Control-Allow-Methods "POST, GET, OPTIONS"
Header set Access-Control-Allow-Headers "Content-Type"
```

### Memory Limit Exceeded

**Problem:** `Fatal error: Allowed memory size exhausted`

**Solution:**
```ini
# Tambahkan di php.ini atau .user.ini
memory_limit = 256M
```

## 📊 Monitoring & Logs

### Application Logs

```
logs/
├── api_monitor.json    # API usage & errors
├── error.log           # PHP errors
└── access.log          # Access logs
```

### View Logs

```bash
# Error logs
tail -f logs/error.log

# API monitor
cat logs/api_monitor.json | jq
```

## 🔄 Maintenance

### Auto-Delete Old CVs

Jalankan cron job untuk delete CV > 30 hari:

```bash
# Tambahkan di cPanel Cron Jobs
0 2 * * * php /path/to/public_html/app/cleanup.php
```

### Backup Data

```bash
# Manual backup
tar -czf cv_data_backup_$(date +%Y%m%d).tar.gz cv_data/

# Automated backup (cron)
0 3 * * 0 tar -czf /backup/cv_data_$(date +\%Y\%m\%d).tar.gz /path/to/cv_data/
```

### Update Dependencies

```bash
composer update
```

## 📈 Performance Optimization

### 1. Enable OPcache

```ini
# php.ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
```

### 2. Compress Response

Sudah enabled di `.htaccess`:
```apache
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE application/json
    AddOutputFilterByType DEFLATE text/html
</IfModule>
```

### 3. Cache API Responses

Implement caching untuk job recommendations (sudah ada di `cv_storage.php`).

## 📞 Support

Jika ada masalah dengan deployment backend:
1. Cek logs di `logs/error.log`
2. Test API dengan Postman/curl
3. Buka issue di [GitHub Issues](https://github.com/davizofficial/Cverity_AI/issues)
4. Contact: davizofficial@gmail.com

---

**Happy Deploying! 🚀**

**Made with ❤️ by CVerity AI Team**
