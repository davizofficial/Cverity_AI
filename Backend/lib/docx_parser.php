<?php
// DOCX Parser wrapper menggunakan phpoffice/phpword

require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpWord\IOFactory;

class DOCXParser {
    
    /**
     * Ekstrak teks dari file DOCX
     * @param string $filePath Path ke file DOCX
     * @return array ['success' => bool, 'text' => string, 'error' => string]
     */
    public function extractText($filePath) {
        try {
            $phpWord = IOFactory::load($filePath);
            $text = '';
            
            foreach ($phpWord->getSections() as $section) {
                $elements = $section->getElements();
                foreach ($elements as $element) {
                    // Ekstrak text dari berbagai tipe element
                    if (method_exists($element, 'getText')) {
                        $text .= $element->getText() . "\n";
                    } elseif (method_exists($element, 'getElements')) {
                        foreach ($element->getElements() as $childElement) {
                            if (method_exists($childElement, 'getText')) {
                                $text .= $childElement->getText() . "\n";
                            }
                        }
                    }
                }
            }
            
            $cleanText = trim($text);
            if (empty($cleanText)) {
                return [
                    'success' => false,
                    'text' => '',
                    'error' => 'File DOCX kosong atau tidak dapat dibaca.'
                ];
            }
            
            return [
                'success' => true,
                'text' => $cleanText,
                'error' => null
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'text' => '',
                'error' => 'Gagal membaca DOCX: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Cek apakah file adalah DOCX valid
     */
    public function isValidDOCX($filePath) {
        try {
            IOFactory::load($filePath);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
