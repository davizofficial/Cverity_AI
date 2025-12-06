<?php
// PDF Parser wrapper menggunakan smalot/pdfparser

require_once __DIR__ . '/../vendor/autoload.php';

use Smalot\PdfParser\Parser;

class PDFParser {
    private $parser;
    
    public function __construct() {
        $this->parser = new Parser();
    }
    
    /**
     * Ekstrak teks dari file PDF
     * @param string $filePath Path ke file PDF
     * @return array ['success' => bool, 'text' => string, 'error' => string, 'is_scan' => bool]
     */
    public function extractText($filePath) {
        try {
            // Cek apakah file ada
            if (!file_exists($filePath)) {
                return [
                    'success' => false,
                    'text' => '',
                    'error' => 'File tidak ditemukan',
                    'is_scan' => false
                ];
            }
            
            // Cek apakah file bisa dibaca
            if (!is_readable($filePath)) {
                return [
                    'success' => false,
                    'text' => '',
                    'error' => 'File tidak dapat dibaca',
                    'is_scan' => false
                ];
            }
            
            $pdf = $this->parser->parseFile($filePath);
            $text = $pdf->getText();
            
            // Cek apakah PDF berisi teks atau hanya gambar (scan)
            $cleanText = trim($text);
            if (empty($cleanText) || strlen($cleanText) < 50) {
                return [
                    'success' => false,
                    'text' => '',
                    'error' => 'PDF tampaknya berupa scan/gambar. Silakan upload CV berbentuk teks untuk hasil optimal.',
                    'is_scan' => true
                ];
            }
            
            return [
                'success' => true,
                'text' => $cleanText,
                'error' => null,
                'is_scan' => false
            ];
            
        } catch (Exception $e) {
            // Tangkap error spesifik
            $errorMsg = $e->getMessage();
            
            // Deteksi jenis error
            if (strpos($errorMsg, 'password') !== false || strpos($errorMsg, 'encrypted') !== false) {
                return [
                    'success' => false,
                    'text' => '',
                    'error' => 'PDF terproteksi password. Silakan upload PDF tanpa password.',
                    'is_scan' => false
                ];
            }
            
            if (strpos($errorMsg, 'corrupted') !== false || strpos($errorMsg, 'invalid') !== false) {
                return [
                    'success' => false,
                    'text' => '',
                    'error' => 'File PDF corrupt atau tidak valid.',
                    'is_scan' => false
                ];
            }
            
            return [
                'success' => false,
                'text' => '',
                'error' => 'Gagal membaca PDF: ' . $errorMsg,
                'is_scan' => false
            ];
        }
    }
    
    /**
     * Cek apakah file adalah PDF valid
     */
    public function isValidPDF($filePath) {
        try {
            $this->parser->parseFile($filePath);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
