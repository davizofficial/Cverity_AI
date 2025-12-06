<?php
/**
 * API Monitor - Simple monitoring for Gemini API usage
 * Tracks API calls, response times, and errors
 */

class APIMonitor {
    private $logFile;
    private $statsFile;
    
    public function __construct() {
        $logDir = __DIR__ . '/../logs';
        if (!file_exists($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        
        $this->logFile = $logDir . '/api_calls.log';
        $this->statsFile = $logDir . '/api_stats.json';
    }
    
    /**
     * Log API call
     */
    public function logCall($endpoint, $model, $startTime, $endTime, $success, $error = null) {
        $duration = round(($endTime - $startTime) * 1000, 2); // ms
        
        $logEntry = sprintf(
            "[%s] %s | Model: %s | Duration: %sms | Status: %s%s\n",
            date('Y-m-d H:i:s'),
            $endpoint,
            $model,
            $duration,
            $success ? 'SUCCESS' : 'FAILED',
            $error ? " | Error: $error" : ''
        );
        
        @file_put_contents($this->logFile, $logEntry, FILE_APPEND);
        
        // Update stats
        $this->updateStats($success, $duration);
    }
    
    /**
     * Update statistics
     */
    private function updateStats($success, $duration) {
        $stats = $this->getStats();
        
        $stats['total_calls']++;
        if ($success) {
            $stats['successful_calls']++;
        } else {
            $stats['failed_calls']++;
        }
        
        $stats['total_duration'] += $duration;
        $stats['avg_duration'] = round($stats['total_duration'] / $stats['total_calls'], 2);
        
        if ($duration < $stats['min_duration'] || $stats['min_duration'] === 0) {
            $stats['min_duration'] = $duration;
        }
        
        if ($duration > $stats['max_duration']) {
            $stats['max_duration'] = $duration;
        }
        
        $stats['last_updated'] = date('Y-m-d H:i:s');
        
        @file_put_contents($this->statsFile, json_encode($stats, JSON_PRETTY_PRINT));
    }
    
    /**
     * Get current statistics
     */
    public function getStats() {
        if (file_exists($this->statsFile)) {
            $data = @file_get_contents($this->statsFile);
            if ($data) {
                return json_decode($data, true);
            }
        }
        
        return [
            'total_calls' => 0,
            'successful_calls' => 0,
            'failed_calls' => 0,
            'total_duration' => 0,
            'avg_duration' => 0,
            'min_duration' => 0,
            'max_duration' => 0,
            'last_updated' => null
        ];
    }
    
    /**
     * Get recent logs
     */
    public function getRecentLogs($lines = 50) {
        if (!file_exists($this->logFile)) {
            return [];
        }
        
        $file = @file($this->logFile);
        if (!$file) {
            return [];
        }
        
        return array_slice($file, -$lines);
    }
    
    /**
     * Clear logs and stats
     */
    public function clearLogs() {
        @unlink($this->logFile);
        @unlink($this->statsFile);
    }
    
    /**
     * Get best API key based on usage statistics
     * Returns the API key with lowest failure rate or least recent usage
     */
    public function getBestApiKey($apiKeys) {
        if (empty($apiKeys)) {
            return null;
        }
        
        // If only one key, return it
        if (count($apiKeys) === 1) {
            return $apiKeys[0];
        }
        
        // Load key usage stats
        $keyStatsFile = __DIR__ . '/../logs/api_key_stats.json';
        $keyStats = [];
        
        if (file_exists($keyStatsFile)) {
            $data = @file_get_contents($keyStatsFile);
            if ($data) {
                $keyStats = json_decode($data, true) ?: [];
            }
        }
        
        // Find best key (lowest failure rate, or least recently used)
        $bestKey = null;
        $bestScore = PHP_INT_MAX;
        
        foreach ($apiKeys as $key) {
            $keyHash = substr(md5($key), 0, 8);
            
            if (!isset($keyStats[$keyHash])) {
                // New key, prioritize it
                return $key;
            }
            
            $stats = $keyStats[$keyHash];
            $totalCalls = $stats['total'] ?? 0;
            $failedCalls = $stats['failed'] ?? 0;
            $lastUsed = $stats['last_used'] ?? 0;
            
            // Calculate score (lower is better)
            $failureRate = $totalCalls > 0 ? ($failedCalls / $totalCalls) : 0;
            $timeSinceLastUse = time() - $lastUsed;
            
            // Score: failure rate * 1000 - time since last use (prefer less failed and less recently used)
            $score = ($failureRate * 1000) - ($timeSinceLastUse / 60);
            
            if ($score < $bestScore) {
                $bestScore = $score;
                $bestKey = $key;
            }
        }
        
        return $bestKey ?: $apiKeys[0];
    }
    
    /**
     * Track API key usage
     */
    public function trackKeyUsage($apiKey, $success) {
        $keyStatsFile = __DIR__ . '/../logs/api_key_stats.json';
        $keyHash = substr(md5($apiKey), 0, 8);
        
        $keyStats = [];
        if (file_exists($keyStatsFile)) {
            $data = @file_get_contents($keyStatsFile);
            if ($data) {
                $keyStats = json_decode($data, true) ?: [];
            }
        }
        
        if (!isset($keyStats[$keyHash])) {
            $keyStats[$keyHash] = [
                'total' => 0,
                'failed' => 0,
                'last_used' => 0
            ];
        }
        
        $keyStats[$keyHash]['total']++;
        if (!$success) {
            $keyStats[$keyHash]['failed']++;
        }
        $keyStats[$keyHash]['last_used'] = time();
        
        @file_put_contents($keyStatsFile, json_encode($keyStats, JSON_PRETTY_PRINT));
    }
    
    /**
     * Log API request (alias for backward compatibility)
     */
    public function logRequest($apiKey, $endpoint, $success, $error = null, $responseTime = 0) {
        // Track key usage
        $this->trackKeyUsage($apiKey, $success);
        
        // Log the call
        $startTime = microtime(true) - ($responseTime / 1000);
        $endTime = microtime(true);
        $this->logCall($endpoint, 'gemini', $startTime, $endTime, $success, $error);
    }
}
