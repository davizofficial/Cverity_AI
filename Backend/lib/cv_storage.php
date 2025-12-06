<?php
/**
 * CV Storage - Menyimpan dan mengelola CV yang diupload
 */

class CVStorage {
    private $storageDir;
    private $dataFile;
    
    public function __construct() {
        $this->storageDir = __DIR__ . '/../cv_data/';
        $this->dataFile = $this->storageDir . 'cv_index.json';
        
        // Buat folder jika belum ada
        if (!file_exists($this->storageDir)) {
            mkdir($this->storageDir, 0755, true);
        }
        
        // Buat file index jika belum ada
        if (!file_exists($this->dataFile)) {
            file_put_contents($this->dataFile, json_encode([]));
        }
    }
    
    /**
     * Generate unique ID untuk CV
     */
    public function generateId() {
        return uniqid('cv_', true);
    }
    
    /**
     * Simpan CV dan hasil analisis
     */
    public function save($cvId, $data) {
        // Simpan data CV ke file JSON
        $cvFile = $this->storageDir . $cvId . '.json';
        file_put_contents($cvFile, json_encode($data, JSON_PRETTY_PRINT));
        
        // Update index
        $index = $this->getIndex();
        $index[$cvId] = [
            'id' => $cvId,
            'name' => $data['cv_data']['name'] ?? 'Unknown',
            'role' => $data['cv_data']['positions'][0]['title'] ?? 'N/A',
            'score' => $data['evaluation']['job_match_score'] ?? 0,
            'uploaded_at' => time(),
            'original_filename' => $data['original_filename'] ?? 'cv.pdf'
        ];
        
        file_put_contents($this->dataFile, json_encode($index, JSON_PRETTY_PRINT));
        
        return true;
    }
    
    /**
     * Ambil data CV berdasarkan ID
     */
    public function get($cvId) {
        $cvFile = $this->storageDir . $cvId . '.json';
        
        if (!file_exists($cvFile)) {
            return null;
        }
        
        $data = json_decode(file_get_contents($cvFile), true);
        return $data;
    }
    
    /**
     * Ambil semua CV (index)
     */
    public function getAll() {
        $index = $this->getIndex();
        
        // Sort by uploaded_at descending
        uasort($index, function($a, $b) {
            return $b['uploaded_at'] - $a['uploaded_at'];
        });
        
        return array_values($index);
    }
    
    /**
     * Hapus CV
     */
    public function delete($cvId) {
        $cvFile = $this->storageDir . $cvId . '.json';
        
        if (file_exists($cvFile)) {
            unlink($cvFile);
        }
        
        // Update index
        $index = $this->getIndex();
        unset($index[$cvId]);
        file_put_contents($this->dataFile, json_encode($index, JSON_PRETTY_PRINT));
        
        return true;
    }
    
    /**
     * Simpan improved CV
     */
    public function saveImprovedCV($cvId, $improvedData) {
        $data = $this->get($cvId);
        
        if (!$data) {
            return false;
        }
        
        $data['improved_cv'] = $improvedData;
        $data['improved_at'] = time();
        
        return $this->save($cvId, $data);
    }
    
    /**
     * Get index
     */
    private function getIndex() {
        if (!file_exists($this->dataFile)) {
            return [];
        }
        
        $data = json_decode(file_get_contents($this->dataFile), true);
        return $data ?? [];
    }
}
