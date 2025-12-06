<?php
/**
 * Download CV as DOCX (ATS-Friendly)
 * Endpoint untuk generate dan download CV dalam format DOCX
 */

// Disable output buffering dan error display untuk file download
ini_set('display_errors', 0);
error_reporting(0);

// Clean any previous output
if (ob_get_level()) {
    ob_end_clean();
}

require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../lib/cv_storage.php';
require_once __DIR__ . '/../lib/docx_generator.php';

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

/**
 * Send JSON error response
 */
function sendJsonError($message, $code = 500) {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'error' => true,
        'message' => $message
    ]);
    exit;
}

try {
    // Get CV ID from request
    $cvId = null;
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        $cvId = $data['cv_id'] ?? null;
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $cvId = $_GET['cv_id'] ?? null;
    }
    
    if (empty($cvId)) {
        sendJsonError('CV ID tidak ditemukan', 400);
    }
    
    // Load CV data
    $storage = new CVStorage();
    $cvRecord = $storage->get($cvId);
    
    if (!$cvRecord) {
        sendJsonError('Data CV tidak ditemukan', 404);
    }
    
    // Gunakan improved_cv jika ada, jika tidak gunakan cv_data asli
    $cvData = $cvRecord['improved_cv'] ?? $cvRecord['cv_data'];
    $evaluation = $cvRecord['evaluation'] ?? null;
    
    // Generate DOCX
    $docxGenerator = new DocxGenerator();
    
    // Create temp file
    $tempDir = __DIR__ . '/../uploads/temp/';
    if (!file_exists($tempDir)) {
        mkdir($tempDir, 0755, true);
    }
    
    $filename = 'CV_' . ($cvData['name'] ?? 'Professional') . '_' . date('Y-m-d') . '.docx';
    $filename = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $filename); // Sanitize filename
    $tempFile = $tempDir . uniqid('cv_') . '.docx'; // Use unique temp filename
    
    // Generate DOCX file
    $docxGenerator->generate($cvData, $tempFile);
    
    if (!file_exists($tempFile)) {
        sendJsonError('Gagal generate file DOCX');
    }
    
    $fileSize = filesize($tempFile);
    
    if ($fileSize === 0) {
        unlink($tempFile);
        sendJsonError('File DOCX kosong, gagal generate');
    }
    
    // For GET request, download file directly
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Clear all previous headers
        header_remove();
        
        // Set proper headers for file download
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . $fileSize);
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Flush headers
        if (ob_get_level()) {
            ob_end_clean();
        }
        flush();
        
        // Read and output file
        readfile($tempFile);
        
        // Clean up temp file
        @unlink($tempFile);
        exit;
    }
    
    // For POST request, return download URL
    header('Content-Type: application/json; charset=utf-8');
    
    $downloadUrl = '/app/download-docx.php?cv_id=' . urlencode($cvId);
    
    echo json_encode([
        'error' => false,
        'message' => 'File DOCX berhasil dibuat',
        'data' => [
            'filename' => $filename,
            'download_url' => $downloadUrl,
            'size' => $fileSize
        ]
    ]);
    
    // Clean up temp file after response
    @unlink($tempFile);
    
} catch (Exception $e) {
    sendJsonError('Server error: ' . $e->getMessage());
}
