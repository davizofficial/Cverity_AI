// Configuration for CVerity AI
// This file contains all configuration for the application

// Base URL for API calls - CHANGE THIS FOR PRODUCTION
// For production, use your backend URL: 'https://your-backend-domain.com/'
window.APP_CONFIG = {
    BASE_URL: 'https://your-backend-domain.com/',  // ← GANTI DENGAN URL BACKEND ANDA
    API_ENDPOINTS: {
        UPLOAD: 'app/upload.php',
        ANALYZE: 'app/analyze.php',
        DOWNLOAD_DOCX: 'app/download-docx.php',
        GENERATE_IMPROVED: 'app/generate-improved-cv.php',
        REFRESH_JOBS: 'app/refresh-jobs.php',
        GET_CV: 'app/get-cv.php'
    },
    ASSETS: {
        LOGO: 'logo.png',
        CSS: 'assets/css/',
        JS: 'assets/js/'
    }
};

// Set BASE_URL globally for backward compatibility
window.BASE_URL = window.APP_CONFIG.BASE_URL;

// Helper function to get full API URL
window.getApiUrl = function(endpoint) {
    return window.APP_CONFIG.BASE_URL + window.APP_CONFIG.API_ENDPOINTS[endpoint];
};

// Helper function to get asset URL
window.getAssetUrl = function(path) {
    return window.APP_CONFIG.BASE_URL + path;
};

console.log('CVerity AI Config loaded:', window.APP_CONFIG);
