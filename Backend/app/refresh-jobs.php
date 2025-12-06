<?php
/**
 * Refresh Job Recommendations
 * Endpoint untuk generate ulang rekomendasi pekerjaan
 */

require_once __DIR__ . '/../lib/cv_storage.php';
require_once __DIR__ . '/../lib/job_generator.php';

header('Content-Type: application/json');

// Get CV ID from request
$cvId = $_POST['cv_id'] ?? $_GET['cv_id'] ?? null;

if (!$cvId) {
    echo json_encode([
        'success' => false,
        'error' => 'CV ID tidak ditemukan'
    ]);
    exit;
}

try {
    // Load CV data
    $storage = new CVStorage();
    $data = $storage->get($cvId);
    
    if (!$data) {
        echo json_encode([
            'success' => false,
            'error' => 'Data CV tidak ditemukan'
        ]);
        exit;
    }
    
    $cvData = $data['cv_data'];
    
    // Generate new job recommendations
    $jobGenerator = new JobGenerator();
    $jobsResult = $jobGenerator->searchJobs($cvData);
    
    if (!$jobsResult['success']) {
        echo json_encode([
            'success' => false,
            'error' => 'Gagal generate rekomendasi pekerjaan: ' . ($jobsResult['error'] ?? 'Unknown error')
        ]);
        exit;
    }
    
    // Update jobs in storage with timestamp
    $data['jobs'] = $jobsResult['jobs'];
    $data['jobs_updated_at'] = time();
    $storage->save($cvId, $data);
    
    // Return new jobs
    echo json_encode([
        'success' => true,
        'jobs' => $jobsResult['jobs'],
        'total_found' => count($jobsResult['jobs']),
        'search_category' => $jobsResult['search_category'] ?? null,
        'search_location' => $jobsResult['search_location'] ?? null
    ]);
    
} catch (Exception $e) {
    error_log('Refresh jobs error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Terjadi kesalahan: ' . $e->getMessage()
    ]);
}
