<?php
/**
 * CVerity AI - Environment Configuration
 * 
 * INSTRUKSI:
 * 1. Copy file ini menjadi .env.php
 * 2. Isi semua API keys dan konfigurasi
 * 3. JANGAN commit file .env.php ke Git!
 */

return [
    /**
     * Google Gemini AI API Keys
     * 
     * Dapatkan API key di: https://makersuite.google.com/app/apikey
     * 
     * Tips:
     * - Gunakan multiple API keys untuk load balancing
     * - Setiap key memiliki quota: 60 requests/minute (free tier)
     * - Tambahkan lebih banyak keys jika traffic tinggi
     */
    'GEMINI_API_KEYS' => [
        'AIzaSyXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX',  // Key 1 (required)
        // 'AIzaSyYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYY',  // Key 2 (optional)
        // 'AIzaSyZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZ',  // Key 3 (optional)
    ],
    
    /**
     * Gemini API Endpoint
     * 
     * Default: gemini-2.0-flash-exp (experimental, fastest)
     * Alternative: gemini-1.5-pro (more stable)
     */
    'GEMINI_ENDPOINT' => 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-exp:generateContent',
    
    /**
     * Auto-delete CV after X days
     * 
     * CV yang lebih lama dari X hari akan otomatis dihapus
     * Set 0 untuk disable auto-delete
     */
    'AUTO_DELETE_DAYS' => 30,
    
    /**
     * Max upload size (bytes)
     * 
     * Default: 5MB (5242880 bytes)
     * Sesuaikan dengan limit hosting Anda
     */
    'MAX_UPLOAD_SIZE' => 5242880,
    
    /**
     * Application Settings (Optional)
     */
    'APP_NAME' => 'CVerity AI',
    'APP_ENV' => 'production', // development, staging, production
    'APP_DEBUG' => false,
    
    /**
     * CORS Settings (Optional)
     * 
     * Jika frontend di domain berbeda, set allowed origins
     */
    'CORS_ALLOWED_ORIGINS' => [
        'https://your-vercel-app.vercel.app',
        'https://cverity.ai',
        // 'http://localhost:3000', // untuk development
    ],
];
