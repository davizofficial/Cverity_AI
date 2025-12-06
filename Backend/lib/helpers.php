<?php
// Helper functions untuk CVerity AI

/**
 * Sanitasi input string
 */
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Hitung total bulan pengalaman dari array positions
 */
function calculateTotalMonths($positions) {
    $totalMonths = 0;
    foreach ($positions as $pos) {
        $totalMonths += $pos['months'] ?? 0;
    }
    return $totalMonths;
}

/**
 * Hitung tahun pengalaman dari bulan
 */
function monthsToYears($months) {
    return round($months / 12, 1);
}

/**
 * Parse tanggal YYYY-MM menjadi timestamp
 */
function parseDate($dateStr) {
    if (strtolower($dateStr) === 'present' || strtolower($dateStr) === 'sekarang') {
        return time();
    }
    return strtotime($dateStr . '-01');
}

/**
 * Hitung selisih bulan antara dua tanggal
 */
function monthsDiff($startDate, $endDate) {
    $start = parseDate($startDate);
    $end = parseDate($endDate);
    
    $diff = $end - $start;
    return max(1, round($diff / (30 * 24 * 60 * 60)));
}

/**
 * Generate unique filename
 */
function generateUniqueFilename($originalName) {
    $ext = pathinfo($originalName, PATHINFO_EXTENSION);
    return uniqid('cv_', true) . '.' . $ext;
}

/**
 * Validasi tipe file
 */
function isValidFileType($filename) {
    $allowedTypes = ['pdf', 'docx'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($ext, $allowedTypes);
}

/**
 * Format skor dengan warna
 */
function getScoreColor($score) {
    if ($score >= 80) return '#10B981'; // hijau
    if ($score >= 60) return '#F59E0B'; // kuning
    return '#EF4444'; // merah
}

/**
 * Format skor dengan label
 */
function getScoreLabel($score) {
    if ($score >= 80) return 'Sangat Baik';
    if ($score >= 60) return 'Baik';
    if ($score >= 40) return 'Cukup';
    return 'Perlu Perbaikan';
}

/**
 * Clean old uploads (panggil via cron atau manual)
 */
function cleanOldUploads($days = AUTO_DELETE_DAYS) {
    $uploadDir = UPLOAD_DIR;
    $files = glob($uploadDir . '*');
    $now = time();
    $deleted = 0;
    
    foreach ($files as $file) {
        if (is_file($file)) {
            if ($now - filemtime($file) >= (60 * 60 * 24 * $days)) {
                unlink($file);
                $deleted++;
            }
        }
    }
    
    return $deleted;
}

/**
 * Response JSON helper
 */
function jsonResponse($data, $statusCode = 200) {
    // Bersihkan semua output buffer
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    
    // Encode JSON tanpa pretty print untuk menghindari whitespace issues
    $json = json_encode($data, JSON_UNESCAPED_UNICODE);
    
    // Pastikan JSON valid
    if ($json === false) {
        $json = json_encode(['error' => true, 'message' => 'JSON encoding error']);
    }
    
    echo $json;
    exit;
}

/**
 * Error response helper
 */
function errorResponse($message, $statusCode = 400) {
    jsonResponse(['error' => true, 'message' => $message], $statusCode);
}

/**
 * Success response helper
 */
function successResponse($data, $message = 'Success') {
    jsonResponse(['error' => false, 'message' => $message, 'data' => $data]);
}
