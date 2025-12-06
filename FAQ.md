# ❓ FAQ - Frequently Asked Questions

Pertanyaan yang sering ditanyakan tentang CVerity AI.

## 📋 Table of Contents

- [General](#general)
- [Setup & Installation](#setup--installation)
- [Deployment](#deployment)
- [Technical](#technical)
- [Troubleshooting](#troubleshooting)
- [Features](#features)
- [Security & Privacy](#security--privacy)

---

## 🌟 General

### Q: Apa itu CVerity AI?

**A:** CVerity AI adalah platform evaluasi CV berbasis kecerdasan buatan yang menggunakan Google Gemini AI untuk memberikan analisis mendalam, skor ATS real-time, dan rekomendasi strategis untuk meningkatkan daya saing CV Anda di pasar kerja.

### Q: Apakah CVerity AI gratis?

**A:** Ya, CVerity AI adalah open-source dan gratis untuk digunakan. Anda hanya perlu menyediakan:
- Google Gemini API key (gratis dengan quota terbatas)
- Hosting untuk backend (bisa shared hosting murah)
- Vercel untuk frontend (gratis)

### Q: Siapa yang bisa menggunakan CVerity AI?

**A:** Siapa saja yang ingin meningkatkan kualitas CV mereka:
- Fresh graduates
- Job seekers
- Career changers
- Professionals
- HR professionals (untuk evaluasi CV kandidat)

### Q: Bahasa apa yang didukung?

**A:** Saat ini CVerity AI mendukung:
- **Interface:** Bahasa Indonesia
- **CV Analysis:** Bahasa Indonesia & Inggris
- **Job Data:** Fokus pada pasar kerja Indonesia

---

## 🛠️ Setup & Installation

### Q: Apa saja yang dibutuhkan untuk setup CVerity AI?

**A:** Anda membutuhkan:
1. **Akun GitHub** (gratis)
2. **Akun Vercel** (gratis)
3. **Hosting PHP 7.4+** (shared hosting ~$2-5/bulan)
4. **Google Gemini API Key** (gratis)
5. **Git** installed di komputer

### Q: Berapa lama waktu setup?

**A:** Dengan mengikuti [QUICK_START.md](QUICK_START.md), setup bisa selesai dalam **15 menit**.

### Q: Apakah saya perlu pengalaman programming?

**A:** Tidak wajib, tapi akan membantu. Panduan kami dibuat untuk:
- **Beginner:** Ikuti step-by-step guide
- **Intermediate:** Customize sesuai kebutuhan
- **Advanced:** Extend dengan fitur baru

### Q: Bagaimana cara mendapatkan Google Gemini API Key?

**A:** 
1. Kunjungi [Google AI Studio](https://makersuite.google.com/app/apikey)
2. Login dengan akun Google
3. Klik "Create API Key"
4. Copy API key
5. Paste ke `Backend/.env.php`

**Quota gratis:** 60 requests/minute per API key.

### Q: Apakah bisa menggunakan multiple API keys?

**A:** Ya! Bahkan direkomendasikan. Tambahkan di `.env.php`:
```php
'GEMINI_API_KEYS' => [
    'AIzaSyXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX',  // Key 1
    'AIzaSyYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYY',  // Key 2
    'AIzaSyZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZ',  // Key 3
],
```

Sistem akan otomatis rotate keys untuk load balancing.

---

## 🚀 Deployment

### Q: Dimana saya harus deploy Frontend?

**A:** **Vercel** (recommended) karena:
- ✅ Gratis
- ✅ Global CDN (fast loading)
- ✅ Auto-deployment dari GitHub
- ✅ HTTPS by default
- ✅ Custom domain support

Alternatif: Netlify, GitHub Pages, Cloudflare Pages.

### Q: Dimana saya harus deploy Backend?

**A:** **PHP Hosting** dengan cPanel, seperti:
- Hostinger (~$2/bulan)
- Niagahoster (~$3/bulan)
- Rumahweb (~$4/bulan)
- Atau hosting lokal lainnya

**Requirements:**
- PHP 7.4+
- Composer support (atau SSH access)
- 256MB memory limit
- SSL certificate

### Q: Apakah bisa deploy Backend ke Vercel juga?

**A:** Tidak direkomendasikan. Vercel adalah serverless platform yang tidak cocok untuk:
- File uploads
- Session management
- File-based storage

Gunakan traditional PHP hosting untuk backend.

### Q: Berapa biaya deployment per bulan?

**A:** 
- **Frontend (Vercel):** $0 (gratis)
- **Backend (Hosting):** $2-5/bulan
- **Gemini API:** $0 (gratis tier) atau ~$10/bulan (paid)
- **Domain (optional):** ~$10/tahun

**Total:** ~$2-5/bulan (tanpa domain & paid API)

### Q: Apakah bisa deploy di localhost saja?

**A:** Ya, untuk development atau personal use:
```bash
# Backend
cd Backend
php -S localhost:8080

# Frontend
cd Frontend
python -m http.server 8000
```

Tapi tidak bisa diakses dari internet.

---

## 🔧 Technical

### Q: Teknologi apa yang digunakan?

**A:**
- **Frontend:** HTML5, CSS3 (Tailwind), JavaScript (Vanilla)
- **Backend:** PHP 7.4+, Composer
- **AI:** Google Gemini 2.0 Flash
- **Storage:** File-based (JSON)
- **Deployment:** Vercel + PHP Hosting

### Q: Apakah menggunakan database?

**A:** Tidak. CVerity AI menggunakan **file-based storage** (JSON files) karena:
- ✅ Simple setup
- ✅ No database management
- ✅ Easy backup
- ✅ Cukup untuk small-medium scale

Untuk high-traffic, bisa migrate ke MySQL/PostgreSQL.

### Q: Bagaimana cara kerja analisis CV?

**A:**
1. User upload CV (PDF/DOCX)
2. Backend extract text dari file
3. Gemini AI parse text → structured JSON
4. Gemini AI evaluate CV → ATS score, gaps, suggestions
5. Job matching engine → recommend jobs
6. Results disimpan di `cv_data/{id}.json`
7. User lihat hasil di results page

### Q: Berapa lama proses analisis?

**A:** Rata-rata **30-60 detik**, tergantung:
- Ukuran file CV
- Kompleksitas CV
- Response time Gemini API
- Network latency

### Q: Apakah support PDF dan DOCX?

**A:** Ya, kedua format didukung:
- **PDF:** Extracted via Gemini multimodal API
- **DOCX:** Extracted via PHP ZipArchive

Max file size: **5MB**.

### Q: Bagaimana job matching bekerja?

**A:** 
1. CV di-parse → extract skills, experience, education
2. Compare dengan database 135+ job profiles dari LinkedIn
3. Calculate match score berdasarkan:
   - Skills match
   - Experience level match
   - Education match
   - Location match
4. Sort by match score (highest first)

**Note:** Saat ini menggunakan static data. Future: real-time API integration.

---

## 🐛 Troubleshooting

### Q: Error "Failed to fetch" di frontend

**A:** Kemungkinan penyebab:
1. **Backend URL salah** → Check `Frontend/assets/js/config.js`
2. **CORS error** → Update CORS headers di backend
3. **Backend down** → Check backend accessible

**Fix:**
```javascript
// Frontend/assets/js/config.js
BASE_URL: 'https://your-actual-backend-url.com/'
```

```apache
# Backend/.htaccess
Header set Access-Control-Allow-Origin "https://your-vercel-app.vercel.app"
```

### Q: Error "500 Internal Server Error"

**A:** Check:
1. `Backend/logs/error.log` untuk detail error
2. `.env.php` sudah di-setup dengan benar
3. Permissions: `chmod 755 uploads/ cv_data/ logs/`
4. Composer dependencies: `composer install`

### Q: Error "CORS policy blocked"

**A:** Update CORS headers di backend:

**Method 1:** Edit `.htaccess`
```apache
Header set Access-Control-Allow-Origin "https://your-frontend-url.com"
```

**Method 2:** Edit PHP files
```php
header('Access-Control-Allow-Origin: https://your-frontend-url.com');
```

### Q: File upload gagal

**A:** Check:
1. Folder `uploads/` exists dan permissions 755
2. File size < 5MB
3. File format PDF atau DOCX
4. PHP `upload_max_filesize` cukup besar

**Fix:**
```bash
chmod 755 uploads/
```

```ini
# php.ini atau .htaccess
upload_max_filesize = 10M
post_max_size = 10M
```

### Q: Gemini API error "Resource Exhausted"

**A:** API quota habis. Solutions:
1. **Tunggu** → Quota reset setiap hari
2. **Tambah API keys** → Multiple keys di `.env.php`
3. **Upgrade** → Gemini API paid plan

### Q: Session tidak persist setelah upload

**A:** Kemungkinan:
1. Session storage issue
2. CORS credentials not sent
3. Different domains (frontend vs backend)

**Fix:**
```javascript
// Frontend
fetch(url, {
    credentials: 'include'  // Send cookies
})
```

---

## ✨ Features

### Q: Apa saja fitur yang tersedia?

**A:**
- ✅ CV upload (PDF/DOCX)
- ✅ AI-powered CV analysis
- ✅ ATS score calculation
- ✅ Gap analysis & suggestions
- ✅ Job recommendations (135+ profiles)
- ✅ Improved CV generation (HTML)
- ✅ DOCX download (coming soon)

### Q: Apakah bisa generate improved CV?

**A:** Ya! Fitur "Generate Improved CV" akan:
1. Analyze CV Anda
2. Identify areas to improve
3. Generate improved version (HTML format)
4. Optimize untuk ATS
5. Add missing sections
6. Improve descriptions

### Q: Apakah job recommendations real-time?

**A:** Saat ini **tidak**. Menggunakan static data (135+ profiles dari LinkedIn).

**Future plan:** Integrasi dengan:
- LinkedIn Jobs API
- Indeed API
- JobStreet API
- Glints API

### Q: Apakah bisa save CV history?

**A:** Saat ini **tidak**. CV disimpan 30 hari lalu auto-delete.

**Future plan:** User account system dengan:
- CV history
- Version tracking
- Progress monitoring

### Q: Apakah support multiple languages?

**A:** 
- **Interface:** Bahasa Indonesia (saat ini)
- **CV Analysis:** Indonesia & English
- **Future:** Multi-language support

---

## 🔒 Security & Privacy

### Q: Apakah data CV saya aman?

**A:** Ya, kami menerapkan multiple security layers:
1. **HTTPS encryption** untuk semua komunikasi
2. **File storage** dengan unique ID (tidak bisa ditebak)
3. **Auto-delete** setelah 30 hari
4. **No database** = no data breach risk
5. **.htaccess protection** untuk uploads folder
6. **Input validation** untuk file uploads

### Q: Apakah CV saya dibagikan ke pihak ketiga?

**A:** **Tidak**. CV Anda:
- Hanya diproses oleh Gemini AI (Google)
- Tidak disimpan di server Google
- Tidak dibagikan ke pihak lain
- Auto-delete setelah 30 hari

### Q: Bagaimana dengan data pribadi di CV?

**A:** Sistem kami:
- **Filter** kata-kata tidak pantas
- **Detect** PII (Personally Identifiable Information)
- **Censor** data sensitif (nomor KTP, kartu kredit, dll)
- **Log** filtered terms untuk audit

### Q: Apakah bisa menghapus CV sebelum 30 hari?

**A:** Ya, gunakan API endpoint:
```
DELETE /app/delete-cv.php?id={cv_id}
```

Atau tambahkan delete button di UI.

### Q: Dimana CV disimpan?

**A:** Di server hosting Anda:
- **Uploaded files:** `Backend/uploads/{filename}`
- **Analysis data:** `Backend/cv_data/{id}.json`

Anda punya full control atas data.

### Q: Apakah open-source?

**A:** Ya! CVerity AI adalah **open-source** dengan MIT License. Anda bisa:
- ✅ Use untuk personal/commercial
- ✅ Modify sesuai kebutuhan
- ✅ Distribute
- ✅ Contribute

---

## 📞 Still Have Questions?

- 📖 Read: [README.md](README.md)
- 🚀 Quick Start: [QUICK_START.md](QUICK_START.md)
- 📚 Full Guide: [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md)
- 🏗️ Architecture: [ARCHITECTURE.md](ARCHITECTURE.md)
- 💬 GitHub Issues: [Ask Question](https://github.com/username/cverity-ai/issues)
- 📧 Email: support@cverity.ai

---

**Last Updated:** December 4, 2024
