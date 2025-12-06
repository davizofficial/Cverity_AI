# CVerity AI - Frontend

Frontend untuk platform CVerity AI yang di-deploy ke **Vercel**.

## 📁 Struktur Folder

```
Frontend/
├── index.html              # Halaman utama
├── logo.png                # Logo aplikasi
├── vercel.json             # Konfigurasi Vercel
├── README.md               # File ini
└── assets/
    ├── css/                # Custom CSS (jika ada)
    └── js/
        ├── config.js       # Konfigurasi API endpoints
        └── app-simple.js   # Main JavaScript
```

## 🚀 Deployment ke Vercel

### Metode 1: Via Vercel Dashboard (Recommended)

1. **Push ke GitHub**
   ```bash
   git add .
   git commit -m "Deploy frontend"
   git push origin main
   ```

2. **Import di Vercel**
   - Kunjungi [vercel.com](https://vercel.com)
   - Klik "New Project"
   - Import repository GitHub Anda
   - **PENTING:** Set **Root Directory** ke `Frontend`
   - Framework Preset: Other
   - Klik "Deploy"

3. **Catat URL Vercel**
   - Setelah deploy selesai: `https://your-app.vercel.app`

### Metode 2: Via Vercel CLI

```bash
# Install Vercel CLI
npm install -g vercel

# Login
vercel login

# Deploy
cd Frontend
vercel --prod
```

## ⚙️ Konfigurasi

### Update Backend URL

Edit file `assets/js/config.js`:

```javascript
window.APP_CONFIG = {
    BASE_URL: 'https://your-backend-domain.com/',  // ← Ganti dengan URL backend
    API_ENDPOINTS: {
        UPLOAD: 'app/upload.php',
        ANALYZE: 'app/analyze.php',
        DOWNLOAD_DOCX: 'app/download-docx.php',
        GENERATE_IMPROVED: 'app/generate-improved-cv.php',
        REFRESH_JOBS: 'app/refresh-jobs.php',
        GET_CV: 'app/get-cv.php'
    }
};
```

**Contoh:**
```javascript
BASE_URL: 'https://api.cverity.com/',
// atau
BASE_URL: 'https://yourdomain.com/api/',
```

### CORS Configuration

Pastikan backend mengizinkan request dari domain Vercel Anda.

Edit `Backend/.htaccess`:
```apache
Header set Access-Control-Allow-Origin "https://your-app.vercel.app"
```

## 🔧 Development Lokal

### Menggunakan Live Server (VS Code)

1. Install extension "Live Server"
2. Klik kanan `index.html` → "Open with Live Server"
3. Browser akan otomatis membuka `http://localhost:5500`

### Menggunakan Python

```bash
cd Frontend
python -m http.server 8000
# Buka http://localhost:8000
```

### Menggunakan Node.js

```bash
cd Frontend
npx serve
# Buka http://localhost:3000
```

## 🎨 Customization

### Mengubah Warna Tema

Edit di `index.html` (Tailwind Config):

```javascript
tailwind.config = {
    theme: {
        extend: {
            colors: {
                primary: '#7C3AED',        // Purple
                'primary-dark': '#6D28D9',
            }
        }
    }
}
```

### Mengubah Logo

Ganti file `logo.png` dengan logo Anda (rekomendasi: 512x512px).

## 🌐 Custom Domain

### Setup di Vercel

1. Buka Vercel Dashboard → Project Settings → Domains
2. Tambahkan domain Anda (contoh: `cverity.ai`)
3. Update DNS records sesuai instruksi Vercel:

```
Type: A
Name: @
Value: 76.76.21.21

Type: CNAME
Name: www
Value: cname.vercel-dns.com
```

4. Tunggu propagasi DNS (5-10 menit)
5. Update CORS di backend dengan domain baru

## 🐛 Troubleshooting

### CORS Error

**Problem:**
```
Access to fetch at 'https://backend.com/app/upload.php' 
from origin 'https://frontend.vercel.app' has been blocked by CORS policy
```

**Solution:**

1. Update `Backend/.htaccess`:
   ```apache
   Header set Access-Control-Allow-Origin "https://your-vercel-app.vercel.app"
   Header set Access-Control-Allow-Methods "POST, GET, OPTIONS"
   Header set Access-Control-Allow-Headers "Content-Type"
   ```

2. Atau gunakan wildcard (tidak direkomendasikan untuk production):
   ```apache
   Header set Access-Control-Allow-Origin "*"
   ```

### API Endpoint Not Found (404)

**Problem:** `404 Not Found` saat memanggil API

**Solution:**
- Periksa `assets/js/config.js` - pastikan `BASE_URL` benar
- Pastikan backend sudah di-deploy dan accessible
- Test endpoint dengan curl:
  ```bash
  curl https://your-backend.com/app/upload.php
  ```

### File Upload Gagal

**Problem:** File tidak ter-upload ke backend

**Solution:**
- Periksa ukuran file (max 5MB)
- Periksa format file (hanya PDF/DOCX)
- Buka browser console (F12) untuk error details
- Pastikan backend folder `uploads/` memiliki permission 755

### Blank Page / White Screen

**Problem:** Halaman tidak muncul setelah deploy

**Solution:**
- Cek browser console (F12) untuk JavaScript errors
- Pastikan semua file assets ter-upload
- Cek `vercel.json` configuration
- Redeploy project

## 📊 Performance Optimization

### 1. Enable Caching

Sudah dikonfigurasi di `vercel.json`:

```json
{
  "headers": [
    {
      "source": "/assets/(.*)",
      "headers": [
        {
          "key": "Cache-Control",
          "value": "public, max-age=31536000, immutable"
        }
      ]
    }
  ]
}
```

### 2. Compress Images

Gunakan tools seperti [TinyPNG](https://tinypng.com/) untuk compress `logo.png`.

### 3. Minify JavaScript (Optional)

```bash
npm install -g terser
terser assets/js/app-simple.js -o assets/js/app-simple.min.js -c -m
```

Update `index.html` untuk menggunakan versi minified.

## 📱 Mobile Responsive

Frontend sudah responsive untuk semua device:
- Desktop (1920x1080)
- Tablet (768x1024)
- Mobile (375x667)

Test responsive di browser:
- Chrome DevTools (F12) → Toggle device toolbar
- Atau test di device fisik

## 🎨 Design System

- **Font:** Plus Jakarta Sans (Google Fonts)
- **Icons:** Font Awesome 6.4.0
- **CSS Framework:** Tailwind CSS (CDN)
- **Color Palette:**
  - Primary: `#7C3AED` (Purple)
  - Success: `#10B981` (Green)
  - Warning: `#FF9500` (Orange)
  - Error: `#EF4444` (Red)

## 📈 Analytics

### Enable Vercel Analytics

1. Buka Vercel Dashboard → Project → Analytics
2. Enable Analytics
3. Monitor:
   - Page views
   - Unique visitors
   - Performance metrics
   - Top pages

### Google Analytics (Optional)

Tambahkan di `index.html` sebelum `</head>`:

```html
<!-- Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-XXXXXXXXXX"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'G-XXXXXXXXXX');
</script>
```

## 🔒 Security Best Practices

- ✅ Jangan hardcode API keys di frontend
- ✅ Gunakan HTTPS untuk semua komunikasi
- ✅ Validasi input di client-side sebelum kirim ke backend
- ✅ Implement rate limiting untuk mencegah abuse
- ✅ Sanitize user input
- ✅ Use Content Security Policy (CSP)

## 📞 Support

Jika ada masalah dengan deployment frontend:
1. Cek [Vercel Documentation](https://vercel.com/docs)
2. Cek browser console (F12) untuk errors
3. Buka issue di [GitHub Issues](https://github.com/davizofficial/Cverity_AI/issues)
4. Contact: davizofficial@gmail.com

---

**Happy Deploying! 🚀**

**Made with ❤️ by CVerity AI Team**
