<?php
// Analisis CV menggunakan Gemini AI

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
    require_once __DIR__ . '/../lib/gemini_client.php';
    require_once __DIR__ . '/../lib/job_generator.php';
} catch (Exception $e) {
    ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => true, 'message' => 'Config error: ' . $e->getMessage()]);
    exit;
}

try {
    // Log session info untuk debugging
    error_log("Analyze.php - Session ID: " . session_id());
    error_log("Analyze.php - Session data: " . json_encode($_SESSION));
    
    // Cek apakah ada file yang sudah diupload
    if (!isset($_SESSION['uploaded_file'])) {
        error_log("Analyze.php - ERROR: No uploaded_file in session");
        errorResponse('Tidak ada file yang diupload. Silakan upload file terlebih dahulu.');
    }

    $fileInfo = $_SESSION['uploaded_file'];
    $filePath = $fileInfo['path'];
    
    error_log("Analyze.php - File path: " . $filePath);

    if (!file_exists($filePath)) {
        error_log("Analyze.php - ERROR: File not found: " . $filePath);
        errorResponse('File tidak ditemukan: ' . basename($filePath));
    }
    
    error_log("Analyze.php - File found, starting extraction...");

    // Baca file sebagai base64 untuk dikirim ke Gemini
    $fileData = base64_encode(file_get_contents($filePath));
    
    // Deteksi mime type dengan fallback ke ekstensi file
    $mimeType = mime_content_type($filePath);
    
    // Fallback: jika mime type tidak terdeteksi dengan benar, gunakan ekstensi file
    if ($mimeType === 'application/octet-stream' || empty($mimeType)) {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if ($extension === 'pdf') {
            $mimeType = 'application/pdf';
        } elseif ($extension === 'docx') {
            $mimeType = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
        }
    }

    // Validasi ukuran file untuk Gemini (max 20MB untuk API)
    if (strlen($fileData) > 20 * 1024 * 1024) {
        errorResponse('File terlalu besar untuk diproses. Maksimal 20MB.');
    }

    // Panggil Gemini untuk ekstraksi (Multimodal)
    error_log("Analyze.php - Initializing GeminiClient...");
    $gemini = new GeminiClient();
    
    error_log("Analyze.php - Calling Gemini API for extraction...");
    error_log("Analyze.php - File size (base64): " . strlen($fileData) . " bytes");
    error_log("Analyze.php - MIME type: " . $mimeType);
    
    $extractedData = $gemini->callExtract($fileData, $mimeType);
    
    error_log("Analyze.php - Extraction result: " . json_encode(['success' => $extractedData['success'], 'has_error' => isset($extractedData['error'])]));

    if (!$extractedData['success']) {
        // Berikan pesan error yang lebih informatif
        $errorMsg = $extractedData['error'];
        error_log("Analyze.php - Extraction ERROR: " . $errorMsg);
        
        // Cek apakah HTTP 400 (Bad Request)
        if (strpos($errorMsg, 'HTTP 400') !== false || strpos($errorMsg, 'Bad Request') !== false) {
            error_log("Analyze.php - HTTP 400 Bad Request detected");
            errorResponse('❌ Request tidak valid. Kemungkinan: 1) File corrupt/tidak valid, 2) API key tidak valid, 3) Format request salah. Detail: ' . $errorMsg);
        }
        
        // Cek apakah HTTP 429 (Rate Limit / Resource Exhausted)
        if (strpos($errorMsg, 'HTTP 429') !== false || strpos($errorMsg, 'RESOURCE_EXHAUSTED') !== false || strpos($errorMsg, 'Rate limit') !== false) {
            errorResponse('⏱️ API sedang sibuk (terlalu banyak request). Silakan tunggu 1-3 menit dan coba lagi. Jika masalah berlanjut, tambahkan API key baru di .env.php');
        }
        
        // Cek apakah MAX_TOKENS (quota habis)
        if (strpos($errorMsg, 'MAX_TOKENS') !== false) {
            errorResponse('Semua API key Gemini sudah mencapai limit quota. Silakan tambahkan API key baru di .env.php atau coba lagi nanti (quota reset setiap hari).');
        }
        
        // Cek apakah error karena file tidak bisa dibaca
        if (strpos($errorMsg, 'Invalid') !== false || strpos($errorMsg, 'parse') !== false) {
            errorResponse('File tidak dapat dibaca oleh sistem. Pastikan file PDF/DOCX Anda valid dan tidak terproteksi password.');
        }
        
        errorResponse('Gagal mengekstrak data CV: ' . $errorMsg);
    }
    
    error_log("Analyze.php - Extraction successful, CV data extracted");

    $cvData = $extractedData['data'];

    // Panggil Gemini untuk evaluasi
    $evaluation = $gemini->callEvaluate($cvData);

    if (!$evaluation['success']) {
        $errorMsg = $evaluation['error'];
        
        // Cek apakah HTTP 429 (Rate Limit / Resource Exhausted)
        if (strpos($errorMsg, 'HTTP 429') !== false || strpos($errorMsg, 'RESOURCE_EXHAUSTED') !== false || strpos($errorMsg, 'Rate limit') !== false) {
            errorResponse('⏱️ API sedang sibuk (terlalu banyak request). Silakan tunggu 1-3 menit dan coba lagi. Jika masalah berlanjut, tambahkan API key baru di .env.php');
        }
        
        // Cek apakah MAX_TOKENS
        if (strpos($errorMsg, 'MAX_TOKENS') !== false) {
            errorResponse('Semua API key Gemini sudah mencapai limit quota. Silakan tambahkan API key baru di .env.php atau coba lagi nanti.');
        }
        
        errorResponse('Gagal mengevaluasi CV: ' . $errorMsg);
    }

    $evalData = $evaluation['data'];

    // Generate job recommendations menggunakan Gemini
    $jobGenerator = new JobGenerator();
    $jobsResult = $jobGenerator->searchJobs($cvData);

    // Jika job generator gagal, tetap lanjutkan dengan jobs kosong
    // User masih bisa lihat CV analysis dan chatbot
    $jobs = [];
    if ($jobsResult['success']) {
        $jobs = $jobsResult['jobs'];
    } else {
        // Log error tapi jangan stop proses
        // User akan lihat fallback UI di results.php
    }

    // Generate unique ID untuk CV ini
    require_once __DIR__ . '/../lib/cv_storage.php';
    $storage = new CVStorage();
    $cvId = $storage->generateId();
    
    // Simpan hasil analisis
    $analysisData = [
        'cv_data' => $cvData,
        'evaluation' => $evalData,
        'jobs' => $jobs,
        'analyzed_at' => time(),
        'original_filename' => $fileInfo['original_name'],
        'file_path' => $filePath
    ];
    
    $storage->save($cvId, $analysisData);
    
    // Simpan hasil di session
    $_SESSION['analysis_result'] = $analysisData;
    $_SESSION['current_cv_id'] = $cvId;

    successResponse([
        'cv_id' => $cvId,
        'cv_data' => $cvData,
        'evaluation' => $evalData,
        'jobs' => $jobs
    ], 'Analisis selesai');
    
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
