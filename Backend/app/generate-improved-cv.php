<?php
// Generate Improved CV menggunakan AI

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
    require_once __DIR__ . '/../lib/cv_storage.php';
} catch (Exception $e) {
    ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => true, 'message' => 'Config error: ' . $e->getMessage()]);
    exit;
}

ob_end_clean();
header('Content-Type: application/json; charset=utf-8');

try {
    // Debug: Log request method
    $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN';
    
    if ($requestMethod !== 'POST') {
        errorResponse('Method not allowed. Received: ' . $requestMethod . '. Expected: POST', 405);
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $cvId = $input['cv_id'] ?? null;

    if (!$cvId) {
        errorResponse('CV ID tidak ditemukan. Silakan refresh halaman.');
    }

    $storage = new CVStorage();
    $data = $storage->get($cvId);

    if (!$data) {
        // Debug: Check if file exists
        $cvFile = __DIR__ . '/../cv_data/' . $cvId . '.json';
        $fileExists = file_exists($cvFile);
        $errorMsg = 'Data CV tidak ditemukan. ';
        $errorMsg .= 'CV ID: ' . $cvId . '. ';
        $errorMsg .= 'File exists: ' . ($fileExists ? 'YES' : 'NO') . '. ';
        if ($fileExists) {
            $errorMsg .= 'File path: ' . $cvFile;
        }
        errorResponse($errorMsg);
    }

    $cvData = $data['cv_data'];
    $evaluation = $data['evaluation'];

    // Generate improved CV menggunakan template + AI enhancement
    require_once __DIR__ . '/../lib/cv_template.php';

    $cvTemplate = new CVTemplate();

    // Use Gemini to improve bullet points and content
    $gemini = new GeminiClient();
    $improvedCV = $cvTemplate->generateWithAI($cvData, $evaluation, $gemini);

    if (!$improvedCV) {
        errorResponse('Gagal membuat CV yang ditingkatkan. Silakan coba lagi.');
    }

    // Simpan improved CV
    $storage->saveImprovedCV($cvId, $improvedCV);

    successResponse(['improved_cv' => $improvedCV], 'CV yang ditingkatkan berhasil dibuat!');
    
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
