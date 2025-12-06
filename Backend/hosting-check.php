<?php
/**
 * CVerity AI - Hosting Compatibility Checker
 * Upload file ini ke hosting untuk mengecek apakah server mendukung semua requirement
 */

echo "<!DOCTYPE html>
<html lang='id'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>CVerity AI - Hosting Check</title>
    <script src='https://cdn.tailwindcss.com'></script>
</head>
<body class='bg-gray-100 min-h-screen p-8'>
<div class='max-w-2xl mx-auto'>
    <h1 class='text-3xl font-bold text-purple-600 mb-6'>🔍 CVerity AI - Hosting Compatibility Check</h1>
    <div class='bg-white rounded-xl shadow-lg p-6 space-y-4'>";

$allPassed = true;

// 1. PHP Version
$phpVersion = phpversion();
$phpOk = version_compare($phpVersion, '8.0.0', '>=');
$allPassed = $allPassed && $phpOk;
echo "<div class='flex items-center justify-between p-3 rounded-lg " . ($phpOk ? 'bg-green-50' : 'bg-red-50') . "'>";
echo "<span class='font-medium'>PHP Version</span>";
echo "<span class='" . ($phpOk ? 'text-green-600' : 'text-red-600') . "'>" . ($phpOk ? '✅' : '❌') . " $phpVersion (min: 8.0)</span>";
echo "</div>";

// 2. cURL Extension
$curlOk = extension_loaded('curl');
$allPassed = $allPassed && $curlOk;
echo "<div class='flex items-center justify-between p-3 rounded-lg " . ($curlOk ? 'bg-green-50' : 'bg-red-50') . "'>";
echo "<span class='font-medium'>cURL Extension</span>";
echo "<span class='" . ($curlOk ? 'text-green-600' : 'text-red-600') . "'>" . ($curlOk ? '✅ Installed' : '❌ Not Installed') . "</span>";
echo "</div>";

// 3. ZipArchive (untuk DOCX)
$zipOk = class_exists('ZipArchive');
$allPassed = $allPassed && $zipOk;
echo "<div class='flex items-center justify-between p-3 rounded-lg " . ($zipOk ? 'bg-green-50' : 'bg-red-50') . "'>";
echo "<span class='font-medium'>ZipArchive (untuk DOCX)</span>";
echo "<span class='" . ($zipOk ? 'text-green-600' : 'text-red-600') . "'>" . ($zipOk ? '✅ Available' : '❌ Not Available') . "</span>";
echo "</div>";

// 4. JSON Extension
$jsonOk = extension_loaded('json');
$allPassed = $allPassed && $jsonOk;
echo "<div class='flex items-center justify-between p-3 rounded-lg " . ($jsonOk ? 'bg-green-50' : 'bg-red-50') . "'>";
echo "<span class='font-medium'>JSON Extension</span>";
echo "<span class='" . ($jsonOk ? 'text-green-600' : 'text-red-600') . "'>" . ($jsonOk ? '✅ Installed' : '❌ Not Installed') . "</span>";
echo "</div>";

// 5. mbstring Extension
$mbOk = extension_loaded('mbstring');
$allPassed = $allPassed && $mbOk;
echo "<div class='flex items-center justify-between p-3 rounded-lg " . ($mbOk ? 'bg-green-50' : 'bg-yellow-50') . "'>";
echo "<span class='font-medium'>mbstring Extension</span>";
echo "<span class='" . ($mbOk ? 'text-green-600' : 'text-yellow-600') . "'>" . ($mbOk ? '✅ Installed' : '⚠️ Not Installed (optional)') . "</span>";
echo "</div>";

// 6. SimpleXML (untuk DOCX parsing)
$xmlOk = extension_loaded('simplexml');
$allPassed = $allPassed && $xmlOk;
echo "<div class='flex items-center justify-between p-3 rounded-lg " . ($xmlOk ? 'bg-green-50' : 'bg-red-50') . "'>";
echo "<span class='font-medium'>SimpleXML Extension</span>";
echo "<span class='" . ($xmlOk ? 'text-green-600' : 'text-red-600') . "'>" . ($xmlOk ? '✅ Installed' : '❌ Not Installed') . "</span>";
echo "</div>";

// 7. File Upload
$uploadOk = ini_get('file_uploads');
echo "<div class='flex items-center justify-between p-3 rounded-lg " . ($uploadOk ? 'bg-green-50' : 'bg-red-50') . "'>";
echo "<span class='font-medium'>File Uploads</span>";
echo "<span class='" . ($uploadOk ? 'text-green-600' : 'text-red-600') . "'>" . ($uploadOk ? '✅ Enabled' : '❌ Disabled') . "</span>";
echo "</div>";

// 8. Max Upload Size
$maxUpload = ini_get('upload_max_filesize');
echo "<div class='flex items-center justify-between p-3 rounded-lg bg-blue-50'>";
echo "<span class='font-medium'>Max Upload Size</span>";
echo "<span class='text-blue-600'>📁 $maxUpload (recommended: 10M+)</span>";
echo "</div>";

// 9. Memory Limit
$memLimit = ini_get('memory_limit');
echo "<div class='flex items-center justify-between p-3 rounded-lg bg-blue-50'>";
echo "<span class='font-medium'>Memory Limit</span>";
echo "<span class='text-blue-600'>💾 $memLimit (recommended: 128M+)</span>";
echo "</div>";

// 10. Check uploads folder
$uploadsDir = __DIR__ . '/uploads';
$uploadsWritable = is_dir($uploadsDir) && is_writable($uploadsDir);
echo "<div class='flex items-center justify-between p-3 rounded-lg " . ($uploadsWritable ? 'bg-green-50' : 'bg-yellow-50') . "'>";
echo "<span class='font-medium'>uploads/ Folder</span>";
if (!is_dir($uploadsDir)) {
    echo "<span class='text-yellow-600'>⚠️ Not exists (will be created)</span>";
} elseif (!is_writable($uploadsDir)) {
    echo "<span class='text-red-600'>❌ Not writable (chmod 755)</span>";
} else {
    echo "<span class='text-green-600'>✅ Writable</span>";
}
echo "</div>";

// 11. Check cv_data folder
$cvDataDir = __DIR__ . '/cv_data';
$cvDataWritable = is_dir($cvDataDir) && is_writable($cvDataDir);
echo "<div class='flex items-center justify-between p-3 rounded-lg " . ($cvDataWritable ? 'bg-green-50' : 'bg-yellow-50') . "'>";
echo "<span class='font-medium'>cv_data/ Folder</span>";
if (!is_dir($cvDataDir)) {
    echo "<span class='text-yellow-600'>⚠️ Not exists (will be created)</span>";
} elseif (!is_writable($cvDataDir)) {
    echo "<span class='text-red-600'>❌ Not writable (chmod 755)</span>";
} else {
    echo "<span class='text-green-600'>✅ Writable</span>";
}
echo "</div>";

// 12. Check vendor folder
$vendorExists = is_dir(__DIR__ . '/vendor') && file_exists(__DIR__ . '/vendor/autoload.php');
echo "<div class='flex items-center justify-between p-3 rounded-lg " . ($vendorExists ? 'bg-green-50' : 'bg-red-50') . "'>";
echo "<span class='font-medium'>vendor/ (Composer)</span>";
echo "<span class='" . ($vendorExists ? 'text-green-600' : 'text-red-600') . "'>" . ($vendorExists ? '✅ Installed' : '❌ Not found - upload vendor folder!') . "</span>";
echo "</div>";

// 13. Check .env.php
$envExists = file_exists(__DIR__ . '/.env.php');
echo "<div class='flex items-center justify-between p-3 rounded-lg " . ($envExists ? 'bg-green-50' : 'bg-red-50') . "'>";
echo "<span class='font-medium'>.env.php (API Keys)</span>";
echo "<span class='" . ($envExists ? 'text-green-600' : 'text-red-600') . "'>" . ($envExists ? '✅ Exists' : '❌ Not found - create .env.php!') . "</span>";
echo "</div>";

// 14. Test Gemini API Connection
echo "<div class='mt-6 pt-4 border-t'>";
echo "<h2 class='text-xl font-bold mb-4'>🌐 API Connection Test</h2>";

if ($curlOk && $envExists) {
    $env = include __DIR__ . '/.env.php';
    if (isset($env['GEMINI_API_KEYS'][0])) {
        $apiKey = $env['GEMINI_API_KEYS'][0];
        $endpoint = $env['GEMINI_ENDPOINT'] ?? 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';
        
        $ch = curl_init($endpoint . '?key=' . $apiKey);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'contents' => [['parts' => [['text' => 'Say "OK"']]]],
            'generationConfig' => ['maxOutputTokens' => 10]
        ]));
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $apiOk = $httpCode === 200;
        echo "<div class='flex items-center justify-between p-3 rounded-lg " . ($apiOk ? 'bg-green-50' : 'bg-red-50') . "'>";
        echo "<span class='font-medium'>Gemini API</span>";
        echo "<span class='" . ($apiOk ? 'text-green-600' : 'text-red-600') . "'>" . ($apiOk ? '✅ Connected (HTTP 200)' : "❌ Failed (HTTP $httpCode)") . "</span>";
        echo "</div>";
        
        if (!$apiOk) {
            echo "<div class='mt-2 p-3 bg-red-50 rounded text-sm text-red-700'>";
            echo "<strong>Error:</strong> " . htmlspecialchars(substr($response, 0, 200));
            echo "</div>";
        }
    } else {
        echo "<div class='p-3 bg-yellow-50 rounded'><span class='text-yellow-600'>⚠️ API key not configured in .env.php</span></div>";
    }
} else {
    echo "<div class='p-3 bg-gray-50 rounded'><span class='text-gray-600'>⏭️ Skipped (cURL or .env.php not available)</span></div>";
}
echo "</div>";

// Summary
echo "<div class='mt-6 pt-4 border-t'>";
if ($allPassed) {
    echo "<div class='p-4 bg-green-100 rounded-lg text-center'>";
    echo "<span class='text-2xl'>🎉</span>";
    echo "<p class='text-green-800 font-bold text-lg mt-2'>Hosting Compatible!</p>";
    echo "<p class='text-green-600'>Server ini mendukung semua requirement CVerity AI</p>";
    echo "</div>";
} else {
    echo "<div class='p-4 bg-red-100 rounded-lg text-center'>";
    echo "<span class='text-2xl'>⚠️</span>";
    echo "<p class='text-red-800 font-bold text-lg mt-2'>Ada Requirement yang Belum Terpenuhi</p>";
    echo "<p class='text-red-600'>Hubungi provider hosting untuk mengaktifkan extension yang dibutuhkan</p>";
    echo "</div>";
}
echo "</div>";

echo "</div>
    <p class='text-center text-gray-500 mt-6 text-sm'>CVerity AI - Hosting Compatibility Checker v1.0</p>
</div>
</body>
</html>";
