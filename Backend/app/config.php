<?php
// Konfigurasi aplikasi CVerity AI
// File ini memuat environment variables dari .env.php

// Disable error display for production (log only)
ini_set('display_errors', '0');
error_reporting(E_ALL);

// Load Composer autoloader
$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
} else {
    die('Error: Composer dependencies not installed. Run: composer install');
}

// Path ke file .env.php (di luar webroot)
$envPath = __DIR__ . '/../.env.php';

if (!file_exists($envPath)) {
    // Jika dipanggil dari API endpoint, return JSON error
    if (php_sapi_name() !== 'cli' && isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
        header('Content-Type: application/json');
        echo json_encode(['error' => true, 'message' => 'File .env.php tidak ditemukan. Salin .env.php.example menjadi .env.php dan isi API keys Anda.']);
        exit;
    }
    die('Error: File .env.php tidak ditemukan. Salin .env.php.example menjadi .env.php dan isi API keys Anda.');
}

// Load environment config
$config = require $envPath;

// Validasi required keys
if (empty($config['GEMINI_API_KEYS']) || !is_array($config['GEMINI_API_KEYS']) || count($config['GEMINI_API_KEYS']) === 0) {
    die("Error: GEMINI_API_KEYS belum diset di .env.php. Silakan isi dengan minimal 1 API key.");
}



// Define constants
define('GEMINI_API_KEYS', $config['GEMINI_API_KEYS']); // Array of API keys
define('AUTO_DELETE_DAYS', $config['AUTO_DELETE_DAYS'] ?? 30);
define('MAX_UPLOAD_SIZE', $config['MAX_UPLOAD_SIZE'] ?? 5242880);
// Use endpoint from .env.php or default
define('GEMINI_ENDPOINT', $config['GEMINI_ENDPOINT'] ?? 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-exp:generateContent');

// Paths
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('BASE_PATH', __DIR__ . '/../');
define('PUBLIC_PATH', __DIR__ . '/../');

// Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

return $config;
