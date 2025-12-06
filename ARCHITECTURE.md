# 🏗️ Architecture - CVerity AI

Dokumentasi arsitektur sistem CVerity AI.

## 📊 System Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                         CVerity AI System                        │
└─────────────────────────────────────────────────────────────────┘

┌──────────────────┐         ┌──────────────────┐         ┌──────────────────┐
│                  │         │                  │         │                  │
│    Frontend      │────────▶│     Backend      │────────▶│   Gemini AI      │
│    (Vercel)      │  HTTPS  │   (PHP Host)     │   API   │   (Google)       │
│                  │◀────────│                  │◀────────│                  │
└──────────────────┘         └──────────────────┘         └──────────────────┘
       │                            │
       │                            │
       ▼                            ▼
┌──────────────────┐         ┌──────────────────┐
│   Static Assets  │         │   File Storage   │
│   (CDN)          │         │   (uploads/)     │
└──────────────────┘         └──────────────────┘
```

## 🎯 Architecture Principles

1. **Separation of Concerns** - Frontend dan Backend terpisah
2. **Stateless API** - Backend API stateless (menggunakan session untuk upload flow)
3. **File-based Storage** - No database, menggunakan JSON files
4. **Serverless Frontend** - Deploy ke Vercel (edge network)
5. **Traditional Backend** - PHP hosting untuk compatibility

## 🌐 Frontend Architecture

### Technology Stack

```
Frontend (Vercel)
├── HTML5 (Semantic markup)
├── CSS3 (Tailwind CSS via CDN)
├── JavaScript (Vanilla JS, ES6+)
└── Assets (Images, Icons)
```

### Component Structure

```
Frontend/
├── Pages
│   ├── index.html          # Landing page + Upload
│   ├── about.html          # About page
│   └── results.html        # Results page (optional)
│
├── Assets
│   ├── css/                # Custom CSS (if any)
│   └── js/
│       ├── config.js       # API configuration
│       └── app-simple.js   # Main application logic
│
└── Static
    └── logo.png            # Logo & images
```

### Data Flow (Frontend)

```
User Action → JavaScript → Fetch API → Backend API → Response → UI Update

Example:
1. User selects CV file
2. JavaScript validates file (size, type)
3. FormData created with file
4. POST to /app/upload.php
5. Response received (success/error)
6. UI updated with message
7. If success, POST to /app/analyze.php
8. Redirect to results page
```

## 🖥️ Backend Architecture

### Technology Stack

```
Backend (PHP Hosting)
├── PHP 7.4+ (Core language)
├── Composer (Dependency management)
├── Apache/Nginx (Web server)
└── File System (Storage)
```

### Directory Structure

```
Backend/
├── app/                    # API Endpoints
│   ├── upload.php         # File upload handler
│   ├── analyze.php        # CV analysis (Gemini AI)
│   ├── get-cv.php         # Retrieve CV data
│   ├── delete-cv.php      # Delete CV
│   ├── download-docx.php  # Download improved CV
│   ├── generate-improved-cv.php  # Generate improved CV
│   └── refresh-jobs.php   # Refresh job recommendations
│
├── lib/                    # Core Libraries
│   ├── gemini_client.php  # Gemini AI integration
│   ├── cv_storage.php     # CV data storage
│   ├── cv_template.php    # CV template generator
│   ├── docx_generator.php # DOCX file generator
│   ├── job_generator.php  # Job matching engine
│   ├── api_monitor.php    # API usage monitoring
│   └── helpers.php        # Utility functions
│
├── uploads/                # Uploaded CV files
├── cv_data/                # Stored CV analysis (JSON)
├── logs/                   # Application logs
└── data-linkedin/          # LinkedIn job data
```

### API Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                      API Layer                               │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐     │
│  │   upload.php │  │ analyze.php  │  │  get-cv.php  │     │
│  └──────┬───────┘  └──────┬───────┘  └──────┬───────┘     │
│         │                  │                  │              │
│         ▼                  ▼                  ▼              │
│  ┌─────────────────────────────────────────────────┐       │
│  │            Core Libraries Layer                  │       │
│  ├─────────────────────────────────────────────────┤       │
│  │  • gemini_client.php  (AI Integration)          │       │
│  │  • cv_storage.php     (Data Persistence)        │       │
│  │  • job_generator.php  (Job Matching)            │       │
│  │  • helpers.php        (Utilities)               │       │
│  └─────────────────────────────────────────────────┘       │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

### Data Flow (Backend)

```
1. Upload Flow:
   Client → upload.php → Validate → Save to uploads/ → Session → Response

2. Analysis Flow:
   Client → analyze.php → Get file from session
                        → Extract text (PDF/DOCX)
                        → Call Gemini AI (extract data)
                        → Call Gemini AI (evaluate)
                        → Generate job recommendations
                        → Save to cv_data/
                        → Response with CV ID

3. Retrieve Flow:
   Client → get-cv.php → Read from cv_data/{id}.json → Response
```

## 🤖 Gemini AI Integration

### Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    Gemini AI Client                          │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  ┌──────────────────────────────────────────────────────┐  │
│  │  API Key Rotation (Multiple keys for load balancing) │  │
│  └──────────────────────────────────────────────────────┘  │
│                                                              │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐     │
│  │   Extract    │  │   Evaluate   │  │   Rewrite    │     │
│  │   (Step 1)   │  │   (Step 2)   │  │   (Step 3)   │     │
│  └──────┬───────┘  └──────┬───────┘  └──────┬───────┘     │
│         │                  │                  │              │
│         ▼                  ▼                  ▼              │
│  ┌─────────────────────────────────────────────────┐       │
│  │         Google Gemini API                        │       │
│  │  (gemini-2.0-flash-exp)                          │       │
│  └─────────────────────────────────────────────────┘       │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

### Processing Pipeline

```
CV File (PDF/DOCX)
    │
    ▼
┌─────────────────────┐
│  Text Extraction    │  (DOCX: ZipArchive, PDF: Gemini multimodal)
└─────────┬───────────┘
          │
          ▼
┌─────────────────────┐
│  Gemini AI Extract  │  (Convert text to structured JSON)
└─────────┬───────────┘
          │
          ▼
┌─────────────────────┐
│  Gemini AI Evaluate │  (Analyze CV quality, ATS score, gaps)
└─────────┬───────────┘
          │
          ▼
┌─────────────────────┐
│  Job Matching       │  (Match with LinkedIn job data)
└─────────┬───────────┘
          │
          ▼
┌─────────────────────┐
│  Store Results      │  (Save to cv_data/{id}.json)
└─────────────────────┘
```

## 💾 Data Storage

### File-based Storage (No Database)

```
cv_data/
├── {cv_id_1}.json
├── {cv_id_2}.json
└── {cv_id_3}.json

Structure:
{
  "cv_data": {
    "name": "John Doe",
    "emails": ["john@example.com"],
    "positions": [...],
    "skills": [...],
    "education": [...]
  },
  "evaluation": {
    "job_match_score": 85,
    "reasons": [...],
    "gaps": [...],
    "suggested_actions": [...]
  },
  "jobs": [
    {
      "title": "Software Engineer",
      "company": "Tech Corp",
      "match_score": 90,
      ...
    }
  ],
  "improved_cv": "<html>...</html>",
  "analyzed_at": 1234567890,
  "jobs_updated_at": 1234567890
}
```

### Why File-based?

✅ **Pros:**
- Simple setup (no database required)
- Easy backup (just copy folder)
- No database management overhead
- Fast for small-medium scale
- Easy to migrate

❌ **Cons:**
- Not suitable for high traffic
- No complex queries
- Manual cleanup required
- Limited concurrent access

## 🔒 Security Architecture

### Layers of Security

```
┌─────────────────────────────────────────────────────────────┐
│                     Security Layers                          │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  1. Transport Layer (HTTPS)                                 │
│     └─ SSL/TLS encryption for all communications            │
│                                                              │
│  2. Application Layer                                       │
│     ├─ Input validation (file type, size)                   │
│     ├─ CORS headers (restrict origins)                      │
│     ├─ Session management                                   │
│     └─ Error handling (no sensitive info leak)              │
│                                                              │
│  3. File System Layer                                       │
│     ├─ .htaccess protection (uploads/, cv_data/)            │
│     ├─ Unique filenames (prevent overwrite)                 │
│     └─ Proper permissions (755/644)                         │
│                                                              │
│  4. API Layer                                               │
│     ├─ API key rotation (Gemini AI)                         │
│     ├─ Rate limiting (API monitor)                          │
│     └─ Request validation                                   │
│                                                              │
│  5. Data Layer                                              │
│     ├─ Auto-delete (30 days)                                │
│     ├─ No sensitive data storage                            │
│     └─ Content filtering (profanity, PII)                   │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

## 🚀 Deployment Architecture

### Production Setup

```
┌─────────────────────────────────────────────────────────────┐
│                    Production Environment                     │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  ┌──────────────────────────────────────────────────────┐  │
│  │              Frontend (Vercel)                        │  │
│  │  • Global CDN (Edge Network)                          │  │
│  │  • Auto-scaling                                       │  │
│  │  • HTTPS by default                                   │  │
│  │  • Custom domain support                              │  │
│  └──────────────────────────────────────────────────────┘  │
│                          │                                   │
│                          │ HTTPS                             │
│                          ▼                                   │
│  ┌──────────────────────────────────────────────────────┐  │
│  │              Backend (PHP Hosting)                    │  │
│  │  • cPanel/Shared Hosting                              │  │
│  │  • PHP 7.4+                                           │  │
│  │  • Apache/Nginx                                       │  │
│  │  • SSL Certificate                                    │  │
│  └──────────────────────────────────────────────────────┘  │
│                          │                                   │
│                          │ HTTPS                             │
│                          ▼                                   │
│  ┌──────────────────────────────────────────────────────┐  │
│  │           Google Gemini AI API                        │  │
│  │  • Multiple API keys                                  │  │
│  │  • Load balancing                                     │  │
│  │  • Rate limiting                                      │  │
│  └──────────────────────────────────────────────────────┘  │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

## 📊 Performance Considerations

### Frontend Performance

- **CDN:** Vercel edge network (global)
- **Caching:** Static assets cached (1 year)
- **Compression:** Gzip/Brotli enabled
- **Lazy Loading:** Images lazy loaded
- **Minification:** CSS/JS minified (production)

### Backend Performance

- **OPcache:** PHP OPcache enabled
- **Compression:** Gzip enabled (.htaccess)
- **File Storage:** Fast file I/O
- **API Caching:** Job recommendations cached
- **Session:** File-based session storage

## 🔄 Scalability

### Current Limitations

- File-based storage (not suitable for high traffic)
- Single server backend (no load balancing)
- Session-based upload (not stateless)

### Future Improvements

1. **Database Migration**
   - MySQL/PostgreSQL for CV data
   - Redis for session & caching

2. **API Improvements**
   - Stateless API (JWT tokens)
   - Rate limiting per user
   - Webhook support

3. **Infrastructure**
   - Load balancer for backend
   - CDN for static assets
   - Queue system for long-running tasks

4. **Monitoring**
   - Application monitoring (New Relic, Datadog)
   - Error tracking (Sentry)
   - Analytics (Google Analytics, Mixpanel)

## 📞 Support

Untuk pertanyaan tentang arsitektur:
- Email: architecture@cverity.ai
- GitHub Issues: [Technical Discussion](https://github.com/username/cverity-ai/issues)

---

**Last Updated:** December 4, 2024  
**Version:** 1.0
