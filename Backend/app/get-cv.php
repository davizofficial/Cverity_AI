<?php
// Get CV data by ID - API endpoint untuk frontend terpisah

// Suppress any output before JSON
error_reporting(0);
ini_set('display_errors', '0');

// Start output buffering
ob_start();

// CORS headers untuk frontend di domain berbeda
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
header('Access-Control-Allow-Credentials: true');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    require_once __DIR__ . '/config.php';
    require_once __DIR__ . '/../lib/helpers.php';
    require_once __DIR__ . '/../lib/cv_storage.php';
} catch (Exception $e) {
    ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => true, 'message' => 'Config error: ' . $e->getMessage()]);
    exit;
}

try {
    $cvId = $_GET['id'] ?? null;

    if (!$cvId) {
        errorResponse('CV ID tidak ditemukan');
    }

    $storage = new CVStorage();
    $data = $storage->get($cvId);

    if (!$data) {
        errorResponse('Data CV tidak ditemukan atau sudah dihapus', 404);
    }

    // Return CV data
    successResponse([
        'cv_id' => $cvId,
        'cv_data' => $data['cv_data'] ?? [],
        'evaluation' => $data['evaluation'] ?? [],
        'jobs' => $data['jobs'] ?? [],
        'has_improved_cv' => isset($data['improved_cv']),
        'analyzed_at' => $data['analyzed_at'] ?? null
    ], 'Data CV berhasil dimuat');
    
} catch (Throwable $e) {
    ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);
    echo json_encode([
        'error' => true, 
        'message' => 'Server error: ' . $e->getMessage()
    ]);
    exit;
}
