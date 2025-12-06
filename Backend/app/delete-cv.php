<?php
// Delete CV

ob_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/../lib/cv_storage.php';
ob_end_clean();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    errorResponse('Method not allowed', 405);
}

$input = json_decode(file_get_contents('php://input'), true);
$cvId = $input['cv_id'] ?? null;

if (!$cvId) {
    errorResponse('CV ID required');
}

$storage = new CVStorage();
$result = $storage->delete($cvId);

if ($result) {
    successResponse([], 'CV berhasil dihapus');
} else {
    errorResponse('Gagal menghapus CV');
}
