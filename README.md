<div align="center">

![CVerity AI Logo](Frontend/logo.png)

# 🚀 CVerity AI - CV Analysis Platform

### *Tingkatkan Peluang Karir Anda dengan Kekuatan AI*

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue)](https://www.php.net/)
[![Powered by Gemini AI](https://img.shields.io/badge/Powered%20by-Gemini%20AI-orange)](https://ai.google.dev/)
[![Deploy with Vercel](https://img.shields.io/badge/Deploy-Vercel-black)](https://vercel.com)

[Demo](https://cverity.vercel.app) • [Documentation](#-dokumentasi) • [Report Bug](https://github.com/davizofficial/Cverity_AI/issues) • [Request Feature](https://github.com/davizofficial/Cverity_AI/issues)

</div>

---

## 📖 Tentang Proyek

### 🎯 Latar Belakang

Di era digital yang kompetitif ini, **CV (Curriculum Vitae)** adalah pintu gerbang pertama menuju karir impian. Namun, banyak pencari kerja menghadapi tantangan:

- 📉 **75% CV ditolak** oleh sistem ATS (Applicant Tracking System) sebelum dilihat HRD
- 🤔 **Tidak tahu** bagian mana dari CV yang perlu diperbaiki
- ⏰ **Membuang waktu** melamar ke posisi yang tidak sesuai dengan profil
- 💸 **Biaya mahal** untuk konsultasi CV profesional (Rp 500.000 - Rp 2.000.000)

### 💡 Solusi

**CVerity AI** hadir sebagai solusi **GRATIS** dan **CERDAS** untuk membantu Anda:

✅ **Analisis CV Mendalam** - Evaluasi komprehensif menggunakan Google Gemini AI  
✅ **Skor ATS Real-Time** - Prediksi peluang lolos screening otomatis  
✅ **Rekomendasi Actionable** - Saran perbaikan yang spesifik dan dapat langsung diterapkan  
✅ **Job Matching Cerdas** - Rekomendasi lowongan kerja yang sesuai dengan profil Anda  
✅ **CV Improvement** - Generate CV yang lebih baik secara otomatis  
✅ **Export DOCX** - Download CV hasil improvement dalam format profesional  

### 🎓 Tujuan Proyek

1. **Demokratisasi Akses** - Memberikan akses gratis ke tools evaluasi CV berkualitas
2. **Meningkatkan Employability** - Membantu pencari kerja meningkatkan daya saing
3. **Efisiensi Waktu** - Mempercepat proses perbaikan CV dari minggu menjadi menit
4. **Data-Driven Insights** - Memberikan feedback berbasis data dan AI, bukan opini subjektif

---

## ✨ Fitur Utama

<table>
<tr>
<td width="50%">

### 🤖 AI-Powered Analysis
Evaluasi CV menggunakan **Google Gemini AI** dengan kemampuan:
- Natural Language Processing
- Context Understanding
- Multi-language Support
- Industry-specific Analysis

</td>
<td width="50%">

### 📊 ATS Score Prediction
Simulasi screening ATS untuk prediksi peluang lolos:
- Keyword Matching
- Format Compatibility
- Section Completeness
- Industry Standards

</td>
</tr>
<tr>
<td width="50%">

### 💡 Smart Recommendations
Saran perbaikan yang actionable:
- Specific Improvements
- Priority-based Suggestions
- Before-After Examples
- Industry Best Practices

</td>
<td width="50%">

### 💼 Job Matching Engine
Rekomendasi lowongan kerja yang sesuai:
- Skills Matching
- Experience Level
- Industry Alignment
- Location Preferences

</td>
</tr>
<tr>
<td width="50%">

### 📄 CV Improvement Generator
Generate CV yang lebih baik otomatis:
- Professional Templates
- ATS-Friendly Format
- Optimized Content
- Industry-specific Layout

</td>
<td width="50%">

### 📥 DOCX Export
Download CV dalam format profesional:
- Microsoft Word Compatible
- Editable Format
- Professional Styling
- Ready to Submit

</td>
</tr>
</table>

---

## 🏗️ Arsitektur Sistem

### 📐 System Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                         USER INTERFACE                          │
│                    (Frontend - Vercel)                          │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐         │
│  │  Upload CV   │  │  View Result │  │  Download CV │         │
│  └──────────────┘  └──────────────┘  └──────────────┘         │
└─────────────────────────────────────────────────────────────────┘
                              │
                              │ HTTPS / REST API
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                      BACKEND API LAYER                          │
│                   (PHP - cPanel Hosting)                        │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐         │
│  │  Upload API  │  │  Analyze API │  │  Export API  │         │
│  └──────────────┘  └──────────────┘  └──────────────┘         │
└─────────────────────────────────────────────────────────────────┘
                              │
                    ┌─────────┴─────────┐
                    ▼                   ▼
        ┌───────────────────┐  ┌───────────────────┐
        │   File Storage    │  │   Google Gemini   │
        │   (JSON-based)    │  │      AI API       │
        │                   │  │                   │
        │  • CV Data        │  │  • CV Analysis    │
        │  • Analysis       │  │  • Evaluation     │
        │  • Job Matches    │  │  • Improvement    │
        └───────────────────┘  └───────────────────┘
```

### 🔄 Alur Sistem (User Flow)

```
┌─────────────────────────────────────────────────────────────────┐
│                    1. UPLOAD CV                                 │
│  User upload CV (PDF/DOCX) → Validasi → Simpan ke Server       │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                    2. PARSING CV                                │
│  Extract text dari PDF/DOCX → Parse struktur → Extract data    │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                    3. AI ANALYSIS                               │
│  Kirim ke Gemini AI → Analisis mendalam → Generate insights    │
│  • Personal Info    • Skills Analysis    • ATS Score           │
│  • Experience       • Education          • Recommendations     │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                    4. JOB MATCHING                              │
│  Analisis skills & experience → Match dengan job database       │
│  → Generate rekomendasi lowongan yang sesuai                    │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                    5. DISPLAY RESULTS                           │
│  Tampilkan hasil analisis → Skor ATS → Rekomendasi             │
│  → Job matches → Option untuk improve CV                        │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                    6. CV IMPROVEMENT (Optional)                 │
│  Generate improved CV → Apply recommendations → Format ATS      │
│  → Export ke DOCX → Download                                    │
└─────────────────────────────────────────────────────────────────┘
```

### 🔐 Security Flow

```
Frontend (Vercel)
    │
    ├─► HTTPS Only
    ├─► Input Validation
    ├─► File Type Check
    └─► Size Limit (5MB)
            │
            ▼
Backend (cPanel)
    │
    ├─► CORS Protection
    ├─► File Upload Validation
    │   ├─► Extension Check (PDF/DOCX only)
    │   ├─► MIME Type Validation
    │   └─► Virus Scan (optional)
    │
    ├─► API Rate Limiting
    ├─► Error Handling & Logging
    └─► Auto-delete after 30 days
            │
            ▼
Google Gemini AI
    │
    ├─► API Key Rotation
    ├─► Request Encryption
    └─► No Data Storage
```

---

## 🛠️ Tech Stack

<table>
<tr>
<td width="50%" valign="top">

### 🎨 Frontend
- **Framework:** Vanilla JavaScript
- **CSS:** Tailwind CSS (CDN)
- **Icons:** Font Awesome 6.4.0
- **Fonts:** Plus Jakarta Sans (Google Fonts)
- **Deployment:** Vercel
- **Features:**
  - ⚡ Fast & Lightweight
  - 📱 Fully Responsive
  - 🎨 Modern UI/UX
  - ♿ Accessibility Compliant

</td>
<td width="50%" valign="top">

### ⚙️ Backend
- **Language:** PHP 7.4+
- **AI Engine:** Google Gemini AI
- **Libraries:**
  - PHPWord (DOCX generation)
  - PDFParser (PDF parsing)
- **Storage:** JSON-based (No database)
- **Deployment:** cPanel/Shared Hosting
- **Features:**
  - 🚀 High Performance
  - 🔒 Secure File Handling
  - 📊 API Monitoring
  - 🔄 Auto-cleanup

</td>
</tr>
</table>

---

## 📁 Struktur Proyek

```
CVerity-AI/
│
├── 📂 Frontend/                    # Frontend Application
│   ├── 📄 index.html              # Main page
│   ├── 🖼️ logo.png                # Logo
│   ├── ⚙️ vercel.json             # Vercel config
│   ├── 📖 README.md               # Frontend docs
│   └── 📂 assets/
│       ├── 📂 css/                # Stylesheets
│       └── 📂 js/
│           ├── config.js          # API configuration
│           └── app-simple.js      # Main JavaScript
│
├── 📂 Backend/                     # Backend API
│   ├── 📂 app/                    # API Endpoints (9 files)
│   │   ├── upload.php             # Upload CV
│   │   ├── analyze.php            # Analyze CV
│   │   ├── get-cv.php             # Get CV data
│   │   ├── delete-cv.php          # Delete CV
│   │   ├── download-docx.php      # Download DOCX
│   │   ├── generate-improved-cv.php  # Generate improved CV
│   │   ├── refresh-jobs.php       # Refresh job matches
│   │   ├── config.php             # App config
│   │   └── .htaccess              # API protection
│   │
│   ├── 📂 lib/                    # Core Libraries (10 files)
│   │   ├── gemini_client.php      # Gemini AI client
│   │   ├── cv_storage.php         # CV storage handler
│   │   ├── cv_template.php        # CV template generator
│   │   ├── docx_generator.php     # DOCX generator
│   │   ├── docx_parser.php        # DOCX parser
│   │   ├── pdf_parser.php         # PDF parser
│   │   ├── job_generator.php      # Job matching engine
│   │   ├── api_monitor.php        # API monitoring
│   │   └── helpers.php            # Helper functions
│   │
│   ├── 📂 vendor/                 # Composer dependencies
│   ├── 📂 uploads/                # Uploaded CV files
│   ├── 📂 cv_data/                # CV analysis data (JSON)
│   ├── 📂 logs/                   # Application logs
│   ├── 📂 data-linkedin/          # Job data
│   ├── 📂 templates/              # CV templates
│   │
│   ├── ⚙️ .htaccess               # Apache config
│   ├── 🔑 .env.php.example        # Environment template
│   ├── 📦 composer.json           # Dependencies
│   ├── 🔍 hosting-check.php       # Hosting check
│   └── 📖 README.md               # Backend docs
│
├── 📖 README.md                    # Main documentation (this file)
├── 🏗️ ARCHITECTURE.md             # System architecture
├── 📝 CHANGELOG.md                # Version history
├── 🤝 CONTRIBUTING.md             # Contribution guide
├── ❓ FAQ.md                      # Frequently Asked Questions
├── 📄 LICENSE                     # MIT License
└── 🚫 .gitignore                  # Git ignore rules
```

---

## 🚀 Quick Start

### 📋 Prerequisites

Sebelum memulai, pastikan Anda memiliki:

- ✅ **Akun GitHub** - Untuk repository
- ✅ **Akun Vercel** - Untuk deploy frontend (gratis)
- ✅ **PHP Hosting** - cPanel/shared hosting dengan PHP 7.4+
- ✅ **Google Gemini API Key** - [Dapatkan di sini](https://makersuite.google.com/app/apikey) (gratis)

### 📥 Installation

#### 1️⃣ Clone Repository

```bash
git clone https://github.com/davizofficial/Cverity_AI.git
cd Cverity_AI
```

#### 2️⃣ Setup Backend

```bash
cd Backend

# Install dependencies
composer install --no-dev --optimize-autoloader

# Setup environment
cp .env.php.example .env.php
nano .env.php  # Edit dan isi API keys

# Set permissions
chmod 755 uploads/ cv_data/ logs/
chmod 644 .env.php
```

**Edit `.env.php`:**
```php
'GEMINI_API_KEYS' => [
    'AIzaSyXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX',  // Your API key
],
```

📖 **Dokumentasi lengkap:** [Backend/README.md](Backend/README.md)

#### 3️⃣ Setup Frontend

```bash
cd Frontend

# Edit config
nano assets/js/config.js  # Update BASE_URL dengan URL backend
```

**Edit `assets/js/config.js`:**
```javascript
BASE_URL: 'https://your-backend-domain.com/',
```

📖 **Dokumentasi lengkap:** [Frontend/README.md](Frontend/README.md)

---

## 🌐 Deployment

### 🎨 Deploy Frontend ke Vercel

1. **Push ke GitHub**
   ```bash
   git add .
   git commit -m "Initial deployment"
   git push origin main
   ```

2. **Import di Vercel**
   - Login ke [vercel.com](https://vercel.com)
   - Klik "New Project"
   - Import repository GitHub
   - **Set Root Directory:** `Frontend` ⚠️ PENTING!
   - Klik "Deploy"

3. **Catat URL Vercel**
   - URL: `https://your-app.vercel.app`

### ⚙️ Deploy Backend ke Hosting

1. **Compress Backend**
   ```bash
   cd Backend
   zip -r backend.zip .
   ```

2. **Upload via cPanel**
   - Login ke cPanel
   - File Manager → Upload `backend.zip`
   - Extract di `public_html`

3. **Install Dependencies**
   ```bash
   composer install --no-dev
   ```

4. **Setup Environment**
   ```bash
   cp .env.php.example .env.php
   nano .env.php  # Isi API keys
   ```

5. **Set Permissions**
   ```bash
   chmod 755 uploads/ cv_data/ logs/
   ```

### 🔗 Post-Deployment

1. **Update Frontend Config**
   - Edit `Frontend/assets/js/config.js`
   - Ganti `BASE_URL` dengan URL backend

2. **Update Backend CORS**
   - Edit `Backend/.htaccess`
   - Ganti CORS origin dengan URL Vercel

3. **Test Everything**
   - Upload CV
   - Analyze CV
   - Download DOCX

---

## 🔑 Mendapatkan API Key

### 🤖 Google Gemini AI

1. Kunjungi [Google AI Studio](https://makersuite.google.com/app/apikey)
2. Login dengan akun Google
3. Klik "Create API Key"
4. Copy API key
5. Paste ke `Backend/.env.php`

**💡 Tips:**
- Gunakan 2-3 API keys untuk load balancing
- Free tier: **60 requests/minute** per key
- Monitor usage di [Google Cloud Console](https://console.cloud.google.com/)

---

## 🧪 Testing

### 🔍 Test Backend API

```bash
# Test hosting compatibility
curl https://your-backend.com/hosting-check.php

# Test upload endpoint
curl -X POST https://your-backend.com/app/upload.php \
  -F "cv_file=@test.pdf"

# Test analyze endpoint
curl -X POST https://your-backend.com/app/analyze.php
```

### 🎨 Test Frontend

1. ✅ Buka aplikasi di browser
2. ✅ Upload CV (PDF/DOCX)
3. ✅ Klik "Analisis CV"
4. ✅ Verifikasi hasil analisis muncul
5. ✅ Test download DOCX
6. ✅ Test refresh jobs
7. ✅ Cek browser console (F12) - tidak ada error

---

## 📊 Monitoring & Analytics

### 📈 Backend Monitoring

```bash
# View error logs
tail -f Backend/logs/error.log

# View API monitor
cat Backend/logs/api_monitor.json | jq

# Check API usage
php Backend/app/api-stats.php
```

### 📊 Frontend Analytics

- **Vercel Analytics** - Built-in analytics di Vercel Dashboard
- **Google Analytics** - Optional, tambahkan tracking code
- **User Behavior** - Monitor upload success rate, analysis completion

---

## 🔒 Security Features

<table>
<tr>
<td width="33%">

### 🛡️ File Upload
- ✅ Extension validation
- ✅ MIME type check
- ✅ Size limit (5MB)
- ✅ Unique filename
- ✅ Folder protection

</td>
<td width="33%">

### 🔐 API Security
- ✅ CORS protection
- ✅ Input sanitization
- ✅ Rate limiting
- ✅ Error handling
- ✅ Logging

</td>
<td width="33%">

### 🗄️ Data Privacy
- ✅ No database
- ✅ Auto-delete (30 days)
- ✅ Unique IDs
- ✅ No PII storage
- ✅ HTTPS only

</td>
</tr>
</table>

---

## 📖 Dokumentasi

| Dokumen | Deskripsi |
|---------|-----------|
| 📘 [Frontend README](Frontend/README.md) | Setup & deployment frontend |
| 📗 [Backend README](Backend/README.md) | Setup & deployment backend |
| 🏗️ [Architecture](ARCHITECTURE.md) | Arsitektur sistem lengkap |
| ❓ [FAQ](FAQ.md) | Pertanyaan yang sering diajukan |
| 📝 [Changelog](CHANGELOG.md) | Riwayat perubahan versi |
| 🤝 [Contributing](CONTRIBUTING.md) | Panduan kontribusi |

---

## 🤝 Contributing

Kontribusi sangat kami apresiasi! 🎉

### 🌟 Cara Berkontribusi

1. **Fork** repository ini
2. **Create** feature branch (`git checkout -b feature/AmazingFeature`)
3. **Commit** changes (`git commit -m 'Add some AmazingFeature'`)
4. **Push** to branch (`git push origin feature/AmazingFeature`)
5. **Open** Pull Request

### 💡 Ideas untuk Kontribusi

- 🐛 Fix bugs
- ✨ Add new features
- 📝 Improve documentation
- 🌍 Add translations
- 🎨 Improve UI/UX
- ⚡ Performance optimization

Baca [CONTRIBUTING.md](CONTRIBUTING.md) untuk guidelines lengkap.

### 🐛 Found a Bug?

Silakan buka issue di [GitHub Issues](https://github.com/davizofficial/Cverity_AI/issues) dengan detail:
- Deskripsi bug
- Steps to reproduce
- Expected vs actual behavior
- Screenshots (jika ada)

---

## 📄 License

Distributed under the **MIT License**. See [LICENSE](LICENSE) for more information.

```
MIT License - Copyright (c) 2025 CVerity AI Team
```

---

## 👨‍💻 Authors & Contributors

<table>
<tr>
<td align="center">
<a href="https://github.com/davizofficial">
<img src="https://github.com/davizofficial.png" width="100px;" alt="Daviz Official"/><br />
<sub><b>Daviz Official</b></sub>
</a><br />
<sub>Creator & Lead Developer</sub><br />
💻 🎨 📖 🚀
</td>
<td align="center">
<sub><b>Contributors Welcome!</b></sub><br />
<a href="https://github.com/davizofficial/Cverity_AI/graphs/contributors">
<img src="https://contrib.rocks/image?repo=davizofficial/Cverity_AI" />
</a>
</td>
</tr>
</table>

---

## 📞 Support & Contact

<div align="center">

### 💬 Butuh Bantuan?

[![Email](https://img.shields.io/badge/Email-davizofficial%40gmail.com-blue?style=for-the-badge&logo=gmail)](mailto:davizofficial@gmail.com)
[![GitHub](https://img.shields.io/badge/GitHub-davizofficial-green?style=for-the-badge&logo=github)](https://github.com/davizofficial)
[![GitHub Issues](https://img.shields.io/badge/Issues-GitHub-red?style=for-the-badge&logo=github)](https://github.com/davizofficial/Cverity_AI/issues)

</div>

**Developer:** [Daviz Official](https://github.com/davizofficial)  
**Response Time:** Biasanya dalam 24-48 jam

---

## 🙏 Acknowledgments

Terima kasih kepada:

- 🤖 [Google Gemini AI](https://ai.google.dev/) - AI engine yang powerful
- 🎨 [Tailwind CSS](https://tailwindcss.com/) - CSS framework yang amazing
- 🎯 [Font Awesome](https://fontawesome.com/) - Icon library terlengkap
- 📄 [PHPWord](https://github.com/PHPOffice/PHPWord) - DOCX generation library
- 📑 [PDFParser](https://github.com/smalot/pdfparser) - PDF parsing library
- ☁️ [Vercel](https://vercel.com/) - Frontend hosting yang cepat
- 💼 [LinkedIn](https://www.linkedin.com/) - Job data source

---

## 📈 Project Stats

<div align="center">

![GitHub stars](https://img.shields.io/github/stars/davizofficial/Cverity_AI?style=social)
![GitHub forks](https://img.shields.io/github/forks/davizofficial/Cverity_AI?style=social)
![GitHub watchers](https://img.shields.io/github/watchers/davizofficial/Cverity_AI?style=social)

![GitHub issues](https://img.shields.io/github/issues/davizofficial/Cverity_AI)
![GitHub pull requests](https://img.shields.io/github/issues-pr/davizofficial/Cverity_AI)
![GitHub last commit](https://img.shields.io/github/last-commit/davizofficial/Cverity_AI)

</div>

---

## 🎯 Roadmap

### 🚀 Version 1.0 (Current)
- ✅ CV Upload (PDF/DOCX)
- ✅ AI Analysis dengan Gemini
- ✅ ATS Score Prediction
- ✅ Job Matching
- ✅ CV Improvement
- ✅ DOCX Export

### 🔮 Version 2.0 (Planned)
- 🔄 Multi-language support (EN, ID, etc.)
- 📊 Advanced analytics dashboard
- 💾 User accounts & history
- 🎨 Multiple CV templates
- 📧 Email notifications
- 🔗 LinkedIn integration
- 📱 Mobile app (React Native)

### 🌟 Version 3.0 (Future)
- 🤝 Interview preparation AI
- 📹 Video CV analysis
- 🎓 Skills gap analysis
- 📚 Learning recommendations
- 🏢 Company culture matching
- 💰 Salary insights

---

<div align="center">

## ⭐ Star History

[![Star History Chart](https://api.star-history.com/svg?repos=davizofficial/Cverity_AI&type=Date)](https://star-history.com/#davizofficial/Cverity_AI&Date)

---

### 💖 Jika proyek ini membantu Anda, berikan ⭐ di GitHub!

**Made with ❤️ by CVerity AI Team**

*Empowering job seekers with AI technology*

---

**© 2025 CVerity AI. All rights reserved.**

</div>
