<?php
// Handle upload CV

// Suppress any output before JSON
error_reporting(0);
ini_set('display_errors', '0');

// Start output buffering
ob_start();

// CORS headers - HARUS di paling atas
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    require_once __DIR__ . '/config.php';
    require_once __DIR__ . '/../lib/helpers.php';
} catch (Exception $e) {
    ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => true, 'message' => 'Config error: ' . $e->getMessage()]);
    exit;
}

try {
    // Validasi request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        errorResponse('Method not allowed', 405);
    }

    // Cek apakah ada file
    if (!isset($_FILES['cv_file']) || $_FILES['cv_file']['error'] === UPLOAD_ERR_NO_FILE) {
        errorResponse('Tidak ada file yang diupload');
    }

    $file = $_FILES['cv_file'];

    // Cek error upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        errorResponse('Error saat upload file: ' . $file['error']);
    }

    // Validasi ukuran file
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        errorResponse('Ukuran file terlalu besar. Maksimal ' . (MAX_UPLOAD_SIZE / 1024 / 1024) . 'MB');
    }

    // Validasi tipe file
    if (!isValidFileType($file['name'])) {
        errorResponse('Tipe file tidak valid. Hanya PDF dan DOCX yang diperbolehkan');
    }

    // Generate unique filename
    $newFilename = generateUniqueFilename($file['name']);
    $uploadPath = UPLOAD_DIR . $newFilename;

    // Pindahkan file
    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        errorResponse('Gagal menyimpan file');
    }

    // Simpan info di session
    $_SESSION['uploaded_file'] = [
        'filename' => $newFilename,
        'original_name' => $file['name'],
        'path' => $uploadPath,
        'size' => $file['size'],
        'uploaded_at' => time()
    ];
    
    // Log untuk debugging
    error_log("File uploaded successfully: " . $newFilename);
    error_log("Session ID: " . session_id());
    error_log("Session data: " . json_encode($_SESSION['uploaded_file']));

    successResponse([
        'filename' => $newFilename,
        'original_name' => $file['name'],
        'size' => $file['size'],
        'session_id' => session_id() // untuk debugging
    ], 'File berhasil diupload');
    
} catch (Throwable $e) {
    // Tangkap semua error termasuk fatal error
    ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);
    echo json_encode([
        'error' => true, 
        'message' => 'Server error: ' . $e->getMessage(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine()
    ]);
    exit;
}
