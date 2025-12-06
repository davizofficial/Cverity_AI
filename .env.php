<?php
// Konfigurasi environment untuk hosting
// Pastikan API key Gemini sudah valid

return [
    // Multiple Gemini API keys untuk rotation (fallback otomatis kalau limit)
    'GEMINI_API_KEYS' => [
        'AIzaSyDZJ-GcPF4ybiTxHXrKmuTzs6WQOV2O5D8',
        // Tambahkan API key lain dari akun Google berbeda untuk backup
    ],

    // Jooble API Configuration
    'JOOBLE_API_KEY' => 'ed51e29a-16b2-4af8-b3ad-03f944b1e5df',
    'JOOBLE_API_URL' => 'https://jooble.org/api/',

    'AUTO_DELETE_DAYS' => 30,
    'MAX_UPLOAD_SIZE' => 2097152, // 2MB untuk hosting (sesuai limit hosting)
    
    // Gunakan gemini-2.0-flash yang stabil
    'GEMINI_ENDPOINT' => 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent'
];
