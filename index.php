<?php
require_once __DIR__ . '/app/config.php';
$pageTitle = 'CVerity AI - Platform Evaluasi CV Profesional';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link rel="icon" type="image/png" href="logo.png">
    <script>
        // Base URL for API calls - localhost configuration
        window.BASE_URL = '/cverity-ai/';
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#7C3AED',
                        'primary-dark': '#6D28D9',
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.6s ease-in-out',
                        'slide-up': 'slideUp 0.6s ease-out',
                        'bounce-slow': 'bounce 3s infinite',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                        slideUp: {
                            '0%': { transform: 'translateY(30px)', opacity: '0' },
                            '100%': { transform: 'translateY(0)', opacity: '1' },
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .upload-zone-hover:hover {
            transform: scale(1.02);
            transition: all 0.3s ease;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen flex flex-col">
    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50 backdrop-blur-sm bg-white/95 animate-fade-in">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-3">
                    <img src="logo.png" alt="CVerity AI Logo" class="w-12 h-12 object-contain">
                    <div>
                        <span class="text-2xl font-bold gradient-text">CVerity AI</span>
                        <p class="text-xs text-gray-500">Platform Evaluasi CV Berbasis Kecerdasan Buatan</p>
                    </div>
                </div>
                <!-- Desktop Nav -->
                <nav class="hidden md:flex items-center space-x-2">
                    <a href="index.php" class="px-4 py-2 text-white bg-primary rounded-lg font-medium shadow-md">
                        <i class="fas fa-home mr-2"></i>Beranda
                    </a>
                    <a href="about.php" class="px-4 py-2 text-white bg-primary rounded-lg font-medium shadow-md">
                        <i class="fas fa-info-circle mr-2"></i>Tentang Kami
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12 w-full flex-1">
        <!-- Hero Text -->
        <div class="text-center mb-12 animate-slide-up w-full">
            <div class="inline-block mb-4">
                <span class="px-4 py-2 bg-purple-100 text-primary rounded-full text-sm font-semibold">
                    <i class="fas fa-sparkles mr-2"></i>Powered by Google Gemini AI
                </span>
            </div>
            <h1 class="text-4xl md:text-6xl font-bold text-gray-900 mb-6 leading-tight">
                Optimalkan CV Anda untuk<br/>
                <span class="gradient-text">Kesuksesan Karir Profesional</span>
            </h1>
            <p class="text-lg md:text-xl text-gray-600 max-w-2xl mx-auto mb-8">
                Platform evaluasi CV berbasis <strong class="text-primary">AI</strong> yang memberikan analisis mendalam, skor ATS real-time, dan rekomendasi strategis untuk meningkatkan daya saing Anda di pasar kerja. Solusi profesional untuk kandidat yang serius dalam pengembangan karir.
            </p>
            
            <!-- Features -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 max-w-3xl mx-auto mb-12">
                <div class="flex items-center justify-center gap-2 text-gray-700">
                    <i class="fas fa-chart-line text-green-500"></i>
                    <span class="text-sm font-medium">Skor ATS Real-Time</span>
                </div>
                <div class="flex items-center justify-center gap-2 text-gray-700">
                    <i class="fas fa-lightbulb text-green-500"></i>
                    <span class="text-sm font-medium">Insight Mendalam & Actionable</span>
                </div>
                <div class="flex items-center justify-center gap-2 text-gray-700">
                    <i class="fas fa-briefcase text-green-500"></i>
                    <span class="text-sm font-medium">Matching Lowongan Kerja</span>
                </div>
            </div>
        </div>

        <!-- Upload Area -->
        <div class="bg-white rounded-2xl shadow-sm border-2 border-gray-200 p-8 mb-8">
            <div id="drop-zone" class="border-2 border-dashed border-primary rounded-xl p-16 text-center cursor-pointer hover:bg-primary hover:bg-opacity-5 transition-all duration-200">
                <div class="flex flex-col items-center">
                    <div class="w-20 h-20 bg-primary bg-opacity-10 rounded-full flex items-center justify-center mb-6">
                        <svg class="w-10 h-10 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Mulai Evaluasi CV Profesional Anda</h3>
                    <p class="text-gray-500 mb-6">Unggah dokumen CV Anda dan dapatkan analisis komprehensif dalam hitungan detik</p>
                    <label for="file-input" class="inline-flex items-center px-6 py-3 bg-primary text-white font-medium rounded-lg hover:bg-primary-dark cursor-pointer transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                        </svg>
                        Pilih Berkas
                    </label>
                    <input type="file" id="file-input" accept=".pdf,.docx" class="hidden">
                </div>
            </div>
            
            <p class="text-center text-sm text-gray-500 mt-4">
                <i class="fas fa-shield-alt text-primary mr-1"></i>
                Data Anda aman dan terenkripsi • Format: PDF & DOCX • Maks: 5 MB
            </p>
        </div>

        <!-- Messages -->
        <div id="messages" class="mb-6"></div>
        
        <!-- Analyze Button -->
        <button id="analyze-btn" style="display:none" class="w-full px-6 py-4 bg-primary text-white text-lg font-semibold rounded-lg hover:bg-primary-dark transition-colors shadow-lg hover:shadow-xl">
            <i class="fas fa-robot mr-2"></i>Analisis dengan AI Sekarang
        </button>
    </main>

    <!-- Info Section -->
    <section class="bg-gradient-to-br from-gray-50 to-gray-100 py-16 w-full">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    Kenali Lebih Dalam Tentang <span class="gradient-text">CVerity AI</span>
                </h2>
                <p class="text-gray-600 text-lg">Informasi penting yang perlu Anda ketahui untuk memaksimalkan peluang karir</p>
            </div>

            <div class="space-y-4">
                <!-- Item 1 -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <button onclick="toggleAccordion(1)" class="w-full px-6 py-5 text-left flex items-center justify-between hover:bg-gray-50 transition-colors">
                        <span class="text-lg font-semibold text-gray-900">Apa Itu CVerity AI?</span>
                        <svg id="icon-1" class="w-6 h-6 text-gray-500 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div id="content-1" class="hidden px-6 pb-5">
                        <p class="text-gray-600 leading-relaxed">
                            CVerity AI adalah platform evaluasi CV berbasis kecerdasan buatan yang dirancang untuk membantu profesional mengoptimalkan dokumen lamaran kerja mereka. Ditenagai oleh <strong class="text-primary">Google Gemini AI</strong>, sistem kami melakukan analisis komprehensif terhadap setiap aspek CV—mulai dari struktur dokumen, kualitas konten, hingga kesesuaian dengan standar industri rekrutmen. Platform ini memberikan evaluasi objektif dan rekomendasi strategis yang dapat langsung diimplementasikan untuk meningkatkan peluang lolos screening ATS (Applicant Tracking System) dan menarik perhatian recruiter profesional.
                        </p>
                    </div>
                </div>

                <!-- Item 2 -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <button onclick="toggleAccordion(2)" class="w-full px-6 py-5 text-left flex items-center justify-between hover:bg-gray-50 transition-colors">
                        <span class="text-lg font-semibold text-gray-900">Apa Tujuan Proyek CVerity AI?</span>
                        <svg id="icon-2" class="w-6 h-6 text-gray-500 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div id="content-2" class="hidden px-6 pb-5">
                        <p class="text-gray-600 leading-relaxed mb-3">
                            CVerity AI dikembangkan dengan misi mendemokratisasi akses terhadap layanan evaluasi CV profesional yang berkualitas tinggi. Kami memahami bahwa layanan konsultasi karir konvensional seringkali memiliki hambatan biaya yang signifikan. Oleh karena itu, platform ini hadir dengan tujuan:
                        </p>
                        <ul class="space-y-2 text-gray-600">
                            <li class="flex items-start gap-2">
                                <i class="fas fa-check-circle text-primary mt-1 flex-shrink-0"></i>
                                <span>Menyediakan evaluasi CV yang objektif dan terukur dengan standar profesional</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <i class="fas fa-check-circle text-primary mt-1 flex-shrink-0"></i>
                                <span>Meningkatkan tingkat keberhasilan kandidat dalam melewati screening ATS</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <i class="fas fa-check-circle text-primary mt-1 flex-shrink-0"></i>
                                <span>Memberikan rekomendasi lowongan kerja yang sesuai dengan profil dan kompetensi</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <i class="fas fa-check-circle text-primary mt-1 flex-shrink-0"></i>
                                <span>Meningkatkan pemahaman kandidat terhadap standar industri rekrutmen</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Item 3 -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <button onclick="toggleAccordion(3)" class="w-full px-6 py-5 text-left flex items-center justify-between hover:bg-gray-50 transition-colors">
                        <span class="text-lg font-semibold text-gray-900">Apa Itu ATS dan Mengapa Penting?</span>
                        <svg id="icon-3" class="w-6 h-6 text-gray-500 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div id="content-3" class="hidden px-6 pb-5">
                        <p class="text-gray-600 leading-relaxed mb-3">
                            ATS (Applicant Tracking System) adalah sistem perangkat lunak yang digunakan oleh 98% perusahaan Fortune 500 dan 66% perusahaan besar untuk melakukan screening otomatis terhadap CV kandidat sebelum ditinjau oleh recruiter. Sistem ini beroperasi dengan mekanisme:
                        </p>
                        <ul class="space-y-2 text-gray-600 mb-3">
                            <li class="flex items-start gap-2">
                                <i class="fas fa-robot text-primary mt-1 flex-shrink-0"></i>
                                <span>Memindai CV untuk mencari kata kunci (keywords) yang relevan dengan job description</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <i class="fas fa-robot text-primary mt-1 flex-shrink-0"></i>
                                <span>Memberikan skor berdasarkan kesesuaian pengalaman, skill, dan kualifikasi</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <i class="fas fa-robot text-primary mt-1 flex-shrink-0"></i>
                                <span>Menyaring CV yang tidak memenuhi kriteria minimum secara otomatis</span>
                            </li>
                        </ul>
                        <p class="text-gray-600 leading-relaxed">
                            <strong class="text-gray-900">Statistik penting:</strong> Rata-rata 75% CV ditolak oleh sistem ATS sebelum mencapai tahap review manual oleh recruiter. CVerity AI membantu Anda mengoptimalkan CV untuk memenuhi kriteria ATS, sehingga meningkatkan peluang lolos screening dan mendapatkan undangan interview hingga 3x lipat.
                        </p>
                    </div>
                </div>

                <!-- Item 4 -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <button onclick="toggleAccordion(4)" class="w-full px-6 py-5 text-left flex items-center justify-between hover:bg-gray-50 transition-colors">
                        <span class="text-lg font-semibold text-gray-900">Bagaimana Cara Kerja dan Alur CVerity AI?</span>
                        <svg id="icon-4" class="w-6 h-6 text-gray-500 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div id="content-4" class="hidden px-6 pb-5">
                        <p class="text-gray-600 leading-relaxed mb-4">
                            CVerity AI bekerja dengan alur yang sederhana namun powerful menggunakan <strong class="text-primary">Google Gemini AI</strong>:
                        </p>
                        <div class="space-y-3">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center flex-shrink-0 shadow-md">
                                    <i class="fas fa-upload text-white"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900 mb-1">1. Upload CV</h4>
                                    <p class="text-gray-600 text-sm">Anda mengunggah file CV dalam format PDF atau DOCX (maksimal 5 MB)</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center flex-shrink-0 shadow-md">
                                    <i class="fas fa-robot text-white"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900 mb-1">2. Ekstraksi Data oleh Gemini AI</h4>
                                    <p class="text-gray-600 text-sm">Gemini AI membaca dan mengekstrak semua informasi penting: nama, kontak, ringkasan, pengalaman kerja, pendidikan, dan keahlian</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 bg-pink-500 rounded-lg flex items-center justify-center flex-shrink-0 shadow-md">
                                    <i class="fas fa-search text-white"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900 mb-1">3. Analisis Mendalam</h4>
                                    <p class="text-gray-600 text-sm">AI menganalisis kualitas konten, struktur CV, keyword optimization, dan kesesuaian dengan standar ATS</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center flex-shrink-0 shadow-md">
                                    <i class="fas fa-chart-line text-white"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900 mb-1">4. Scoring & Evaluasi</h4>
                                    <p class="text-gray-600 text-sm">Sistem memberikan skor ATS (0-100) dan mengidentifikasi kekuatan serta area yang perlu diperbaiki</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 bg-orange-500 rounded-lg flex items-center justify-center flex-shrink-0 shadow-md">
                                    <i class="fas fa-lightbulb text-white"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900 mb-1">5. Rekomendasi Perbaikan</h4>
                                    <p class="text-gray-600 text-sm">AI menghasilkan saran spesifik dan prioritas untuk meningkatkan kualitas CV Anda</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 bg-red-500 rounded-lg flex items-center justify-center flex-shrink-0 shadow-md">
                                    <i class="fas fa-briefcase text-white"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900 mb-1">6. Job Matching</h4>
                                    <p class="text-gray-600 text-sm">Sistem mencocokkan profil Anda dengan 135+ data pekerjaan LinkedIn dan memberikan rekomendasi lowongan yang sesuai</p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                            <p class="text-sm text-gray-700"><strong>⚡ Proses cepat:</strong> Seluruh analisis selesai dalam 30-60 detik!</p>
                        </div>
                    </div>
                </div>

                <!-- Item 5 -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <button onclick="toggleAccordion(5)" class="w-full px-6 py-5 text-left flex items-center justify-between hover:bg-gray-50 transition-colors">
                        <span class="text-lg font-semibold text-gray-900">Apakah CVerity AI Hanya untuk Pekerjaan Teknis Saja?</span>
                        <svg id="icon-5" class="w-6 h-6 text-gray-500 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div id="content-5" class="hidden px-6 pb-5">
                        <p class="text-gray-600 leading-relaxed mb-4">
                            <strong class="text-gray-900">Tidak!</strong> CVerity AI mendukung <strong class="text-primary">135+ profil pekerjaan</strong> dari berbagai industri dan level, baik teknis maupun non-teknis. Data ini di-scraping secara terbaru dari <strong class="text-gray-900">LinkedIn</strong> dan platform pencari kerja terkemuka lainnya.
                        </p>
                        
                        <div class="grid md:grid-cols-2 gap-4 mb-4">
                            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg">
                                <h4 class="font-bold text-gray-900 mb-2 flex items-center gap-2">
                                    <i class="fas fa-code text-blue-600"></i>
                                    Pekerjaan Teknis
                                </h4>
                                <ul class="text-sm text-gray-700 space-y-1">
                                    <li>• Software Engineer</li>
                                    <li>• Data Scientist</li>
                                    <li>• DevOps Engineer</li>
                                    <li>• UI/UX Designer</li>
                                    <li>• Cloud Architect</li>
                                    <li>• Machine Learning Engineer</li>
                                    <li>• Dan 60+ role teknis lainnya</li>
                                </ul>
                            </div>
                            
                            <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
                                <h4 class="font-bold text-gray-900 mb-2 flex items-center gap-2">
                                    <i class="fas fa-briefcase text-green-600"></i>
                                    Pekerjaan Non-Teknis
                                </h4>
                                <ul class="text-sm text-gray-700 space-y-1">
                                    <li>• Marketing Manager</li>
                                    <li>• Human Resources</li>
                                    <li>• Sales Executive</li>
                                    <li>• Content Writer</li>
                                    <li>• Customer Service</li>
                                    <li>• Business Analyst</li>
                                    <li>• Dan 75+ role non-teknis lainnya</li>
                                </ul>
                            </div>
                        </div>

                        <div class="bg-gradient-to-r from-purple-50 to-blue-50 border border-purple-200 p-5 rounded-xl">
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-database text-primary text-2xl"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-900 mb-2">Database Selalu Terupdate</h4>
                                    <p class="text-sm text-gray-700 leading-relaxed">
                                        Kami secara berkala melakukan scraping data terbaru dari LinkedIn dan platform pencari kerja seperti JobStreet, Glints, dan Kalibrr untuk memastikan rekomendasi lowongan kerja yang kami berikan selalu relevan dengan kondisi pasar kerja terkini. Database kami mencakup berbagai level dari entry-level hingga executive positions.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <span class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-medium">IT & Technology</span>
                            <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">Marketing & Sales</span>
                            <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">Finance & Accounting</span>
                            <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-medium">Human Resources</span>
                            <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs font-medium">Operations</span>
                            <span class="px-3 py-1 bg-pink-100 text-pink-700 rounded-full text-xs font-medium">Design & Creative</span>
                            <span class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-xs font-medium">Healthcare</span>
                            <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-medium">Dan banyak lagi...</span>
                        </div>
                    </div>
                </div>


                <!-- Item 6 -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <button onclick="toggleAccordion(6)" class="w-full px-6 py-5 text-left flex items-center justify-between hover:bg-gray-50 transition-colors">
                        <span class="text-lg font-semibold text-gray-900">Apakah Data CV Saya Aman?</span>
                        <svg id="icon-6" class="w-6 h-6 text-gray-500 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div id="content-6" class="hidden px-6 pb-5">
                        <p class="text-gray-600 leading-relaxed mb-4">
                            <strong class="text-gray-900">Keamanan data adalah prioritas utama kami.</strong> CVerity AI menerapkan standar keamanan tinggi untuk melindungi informasi pribadi Anda:
                        </p>
                        <div class="grid md:grid-cols-2 gap-4">
                            <div class="bg-green-50 border border-green-200 p-4 rounded-lg">
                                <div class="flex items-center gap-2 mb-2">
                                    <i class="fas fa-shield-alt text-green-600"></i>
                                    <h4 class="font-semibold text-gray-900">Enkripsi Data</h4>
                                </div>
                                <p class="text-sm text-gray-600">Semua data dienkripsi saat transit dan penyimpanan menggunakan protokol SSL/TLS</p>
                            </div>
                            <div class="bg-blue-50 border border-blue-200 p-4 rounded-lg">
                                <div class="flex items-center gap-2 mb-2">
                                    <i class="fas fa-trash-alt text-blue-600"></i>
                                    <h4 class="font-semibold text-gray-900">Auto-Delete</h4>
                                </div>
                                <p class="text-sm text-gray-600">File CV otomatis dihapus dari server setelah 30 hari untuk menjaga privasi</p>
                            </div>
                            <div class="bg-purple-50 border border-purple-200 p-4 rounded-lg">
                                <div class="flex items-center gap-2 mb-2">
                                    <i class="fas fa-user-secret text-purple-600"></i>
                                    <h4 class="font-semibold text-gray-900">Tidak Dibagikan</h4>
                                </div>
                                <p class="text-sm text-gray-600">Data CV Anda tidak pernah dibagikan ke pihak ketiga tanpa persetujuan</p>
                            </div>
                            <div class="bg-orange-50 border border-orange-200 p-4 rounded-lg">
                                <div class="flex items-center gap-2 mb-2">
                                    <i class="fas fa-server text-orange-600"></i>
                                    <h4 class="font-semibold text-gray-900">Server Aman</h4>
                                </div>
                                <p class="text-sm text-gray-600">Infrastruktur server dengan standar keamanan enterprise-grade</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Item 7 -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <button onclick="toggleAccordion(7)" class="w-full px-6 py-5 text-left flex items-center justify-between hover:bg-gray-50 transition-colors">
                        <span class="text-lg font-semibold text-gray-900">Tips Membuat CV yang Lolos ATS</span>
                        <svg id="icon-7" class="w-6 h-6 text-gray-500 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div id="content-7" class="hidden px-6 pb-5">
                        <p class="text-gray-600 leading-relaxed mb-3">
                            Berikut adalah tips praktis yang dapat langsung Anda terapkan untuk meningkatkan skor ATS CV Anda:
                        </p>
                        <div class="space-y-3">
                            <div class="bg-purple-50 border-l-4 border-primary p-4 rounded">
                                <h4 class="font-semibold text-gray-900 mb-2"><i class="fas fa-key text-primary mr-2"></i>Gunakan Keywords yang Relevan</h4>
                                <p class="text-gray-600 text-sm">Sesuaikan CV dengan job description. Gunakan istilah teknis dan skill yang disebutkan dalam lowongan.</p>
                            </div>
                            <div class="bg-purple-50 border-l-4 border-primary p-4 rounded">
                                <h4 class="font-semibold text-gray-900 mb-2"><i class="fas fa-file-alt text-primary mr-2"></i>Format Sederhana dan Bersih</h4>
                                <p class="text-gray-600 text-sm">Hindari tabel, kolom, header/footer, dan grafik kompleks. Gunakan format standar yang mudah dibaca ATS.</p>
                            </div>
                            <div class="bg-purple-50 border-l-4 border-primary p-4 rounded">
                                <h4 class="font-semibold text-gray-900 mb-2"><i class="fas fa-chart-line text-primary mr-2"></i>Kuantifikasi Pencapaian</h4>
                                <p class="text-gray-600 text-sm">Gunakan angka dan metrik konkret. Contoh: "Meningkatkan penjualan 35%" lebih baik dari "Meningkatkan penjualan".</p>
                            </div>
                            <div class="bg-purple-50 border-l-4 border-primary p-4 rounded">
                                <h4 class="font-semibold text-gray-900 mb-2"><i class="fas fa-heading text-primary mr-2"></i>Gunakan Heading Standar</h4>
                                <p class="text-gray-600 text-sm">Gunakan judul section standar seperti "Pengalaman Kerja", "Pendidikan", "Keahlian" agar mudah dikenali ATS.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        function toggleAccordion(id) {
            const content = document.getElementById(`content-${id}`);
            const icon = document.getElementById(`icon-${id}`);
            
            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                icon.style.transform = 'rotate(180deg)';
            } else {
                content.classList.add('hidden');
                icon.style.transform = 'rotate(0deg)';
            }
        }
    </script>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-auto w-full flex-shrink-0">
        <div class="w-full px-4 py-6">
            <div class="flex flex-col md:flex-row items-center justify-center md:justify-between gap-4 max-w-7xl mx-auto">
                <div class="flex items-center gap-2 flex-shrink-0">
                    <img src="logo.png" alt="CVerity AI Logo" class="w-8 h-8 object-contain">
                    <p class="text-sm text-gray-600">© 2025 CVerity AI — Platform Evaluasi CV Profesional Berbasis Kecerdasan Buatan</p>
                </div>
                <div class="flex flex-wrap items-center justify-center gap-4 md:gap-6 flex-shrink-0">
                    <a href="about.php" class="text-sm text-gray-600 hover:text-gray-900 transition-colors">Tentang Kami</a>
                    <a href="#" class="text-sm text-gray-600 hover:text-gray-900 transition-colors">Pusat Bantuan</a>
                    <a href="#" class="text-sm text-gray-600 hover:text-gray-900 transition-colors">Kebijakan Privasi</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="assets/js/app-simple.js"></script>
</body>
</html>
