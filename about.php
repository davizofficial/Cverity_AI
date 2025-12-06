<?php
$pageTitle = 'Tentang Kami - CVerity AI';
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
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50 backdrop-blur-sm bg-white/95">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-3">
                    <img src="logo.png" alt="CVerity AI Logo" class="w-12 h-12 object-contain">
                    <div>
                        <span class="text-2xl font-bold gradient-text">CVerity AI</span>
                        <p class="text-xs text-gray-500">Platform Evaluasi CV Berbasis Kecerdasan Buatan</p>
                    </div>
                </div>
                <nav class="hidden md:flex items-center space-x-2">
                    <a href="index.php" class="px-4 py-2 text-gray-700 hover:text-primary rounded-lg font-medium transition-colors">
                        <i class="fas fa-home mr-2"></i>Beranda
                    </a>
                    <a href="about.php" class="px-4 py-2 text-white bg-primary rounded-lg font-medium shadow-md">
                        <i class="fas fa-info-circle mr-2"></i>Tentang Kami
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="gradient-bg py-20">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="animate-fade-in">
                <h1 class="text-4xl md:text-5xl font-bold text-white mb-6">
                    Tentang CVerity AI
                </h1>
                <p class="text-xl text-purple-100 max-w-2xl mx-auto">
                    Solusi Inovatif untuk Mengatasi Kesenjangan antara Kandidat dan Peluang Kerja
                </p>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        
        <!-- Latar Belakang & Masalah -->
        <section class="mb-16 animate-slide-up">
            <div class="bg-white rounded-2xl shadow-lg p-8 md:p-12">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                    <h2 class="text-3xl font-bold text-gray-900">Masalah yang Kami Hadapi</h2>
                </div>
                <div class="prose prose-lg max-w-none text-gray-600">
                    <p class="mb-4 leading-relaxed">
                        Di era digital saat ini, pasar kerja menghadapi paradoks yang menarik: <strong class="text-gray-900">jutaan lowongan pekerjaan tersedia</strong>, namun tingkat pengangguran tetap tinggi. Mengapa? Karena terdapat <strong class="text-gray-900">kesenjangan signifikan</strong> antara kualifikasi kandidat dengan persyaratan yang dibutuhkan perusahaan.
                    </p>
                    <div class="grid md:grid-cols-3 gap-6 my-8">
                        <div class="bg-red-50 border-l-4 border-red-500 p-5 rounded-lg">
                            <div class="text-3xl font-bold text-red-600 mb-2">75%</div>
                            <p class="text-sm text-gray-700">CV ditolak oleh sistem ATS sebelum dilihat recruiter</p>
                        </div>
                        <div class="bg-orange-50 border-l-4 border-orange-500 p-5 rounded-lg">
                            <div class="text-3xl font-bold text-orange-600 mb-2">60%</div>
                            <p class="text-sm text-gray-700">Kandidat tidak tahu kelemahan CV mereka</p>
                        </div>
                        <div class="bg-yellow-50 border-l-4 border-yellow-500 p-5 rounded-lg">
                            <div class="text-3xl font-bold text-yellow-600 mb-2">45%</div>
                            <p class="text-sm text-gray-700">Pelamar tidak sesuai dengan posisi yang dilamar</p>
                        </div>
                    </div>
                    <p class="leading-relaxed">
                        Banyak kandidat berkualitas gagal mendapatkan pekerjaan bukan karena kurangnya kompetensi, tetapi karena <strong class="text-gray-900">CV mereka tidak dioptimalkan</strong> untuk sistem screening modern atau <strong class="text-gray-900">melamar posisi yang tidak sesuai</strong> dengan profil mereka. Ini mengakibatkan pemborosan waktu, energi, dan peluang karir yang berharga.
                    </p>
                </div>
            </div>
        </section>

        <!-- Solusi Kami -->
        <section class="mb-16">
            <div class="bg-white rounded-2xl shadow-lg p-8 md:p-12">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-lightbulb text-green-600 text-xl"></i>
                    </div>
                    <h2 class="text-3xl font-bold text-gray-900">Solusi Kami</h2>
                </div>
                <div class="prose prose-lg max-w-none text-gray-600">
                    <p class="mb-6 leading-relaxed">
                        <strong class="text-gray-900">CVerity AI</strong> hadir sebagai solusi komprehensif yang memanfaatkan kekuatan <strong class="text-primary">Artificial Intelligence</strong> untuk menjembatani kesenjangan ini. Kami mengembangkan platform yang tidak hanya mengevaluasi CV, tetapi juga memberikan panduan strategis untuk meningkatkan peluang karir Anda.
                    </p>
                    
                    <div class="grid md:grid-cols-2 gap-6 my-8">
                        <div class="bg-gradient-to-br from-purple-50 to-blue-50 p-6 rounded-xl border border-purple-200">
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 bg-primary rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-search text-white"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-900 mb-2">Analisis CV Mendalam</h3>
                                    <p class="text-sm text-gray-600">Evaluasi komprehensif menggunakan NLP dan Machine Learning untuk mengidentifikasi kekuatan dan kelemahan CV Anda</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gradient-to-br from-purple-50 to-blue-50 p-6 rounded-xl border border-purple-200">
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 bg-primary rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-chart-line text-white"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-900 mb-2">Skor ATS Real-Time</h3>
                                    <p class="text-sm text-gray-600">Simulasi screening ATS untuk memprediksi peluang CV Anda lolos tahap awal rekrutmen</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gradient-to-br from-purple-50 to-blue-50 p-6 rounded-xl border border-purple-200">
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 bg-primary rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-tasks text-white"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-900 mb-2">Rekomendasi Actionable</h3>
                                    <p class="text-sm text-gray-600">Saran perbaikan spesifik dan prioritas yang dapat langsung diterapkan untuk meningkatkan kualitas CV</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gradient-to-br from-purple-50 to-blue-50 p-6 rounded-xl border border-purple-200">
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 bg-primary rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-briefcase text-white"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-900 mb-2">Job Matching Cerdas</h3>
                                    <p class="text-sm text-gray-600">Rekomendasi lowongan kerja yang sesuai dengan profil, kompetensi, dan pengalaman Anda</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Tujuan Proyek -->
        <section class="mb-16">
            <div class="bg-white rounded-2xl shadow-lg p-8 md:p-12">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-bullseye text-blue-600 text-xl"></i>
                    </div>
                    <h2 class="text-3xl font-bold text-gray-900">Tujuan Proyek</h2>
                </div>
                <div class="prose prose-lg max-w-none text-gray-600">
                    <p class="mb-6 leading-relaxed">
                        Tujuan utama dari proyek <strong class="text-gray-900">CVerity AI</strong> adalah mengatasi masalah fundamental dalam proses rekrutmen modern—yaitu <strong class="text-gray-900">kesulitan dalam menilai kualitas CV</strong> dan <strong class="text-gray-900">ketidaksesuaian kandidat dengan posisi yang dilamar</strong>. Kami percaya bahwa dengan bantuan <strong class="text-primary">Artificial Intelligence yang komprehensif</strong>, setiap pencari kerja dapat memaksimalkan potensi mereka.
                    </p>
                    
                    <div class="bg-gradient-to-r from-purple-100 to-blue-100 p-8 rounded-xl my-8">
                        <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <i class="fas fa-quote-left text-primary"></i>
                            Visi Kami
                        </h3>
                        <p class="text-lg text-gray-700 italic leading-relaxed">
                            "Menciptakan ekosistem rekrutmen yang lebih adil dan efisien, di mana setiap kandidat memiliki kesempatan yang sama untuk menunjukkan potensi terbaik mereka, dan setiap perusahaan dapat menemukan talenta yang tepat dengan lebih cepat."
                        </p>
                    </div>

                    <h3 class="text-2xl font-bold text-gray-900 mb-4 mt-8">Misi Kami</h3>
                    <div class="space-y-4">
                        <div class="flex items-start gap-4 bg-gray-50 p-5 rounded-lg">
                            <div class="w-8 h-8 bg-primary rounded-full flex items-center justify-center flex-shrink-0 text-white font-bold">1</div>
                            <div>
                                <h4 class="font-semibold text-gray-900 mb-1">Demokratisasi Akses Evaluasi CV Profesional</h4>
                                <p class="text-gray-600">Memberikan akses gratis atau terjangkau ke layanan evaluasi CV berkualitas tinggi yang biasanya hanya tersedia melalui konsultan karir mahal</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start gap-4 bg-gray-50 p-5 rounded-lg">
                            <div class="w-8 h-8 bg-primary rounded-full flex items-center justify-center flex-shrink-0 text-white font-bold">2</div>
                            <div>
                                <h4 class="font-semibold text-gray-900 mb-1">Meningkatkan Tingkat Keberhasilan Pencari Kerja</h4>
                                <p class="text-gray-600">Membantu kandidat mengoptimalkan CV mereka untuk lolos screening ATS dan menarik perhatian recruiter, meningkatkan peluang interview hingga 3x lipat</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start gap-4 bg-gray-50 p-5 rounded-lg">
                            <div class="w-8 h-8 bg-primary rounded-full flex items-center justify-center flex-shrink-0 text-white font-bold">3</div>
                            <div>
                                <h4 class="font-semibold text-gray-900 mb-1">Mengurangi Mismatch Kandidat-Posisi</h4>
                                <p class="text-gray-600">Memberikan rekomendasi lowongan kerja yang sesuai dengan profil kandidat, mengurangi pemborosan waktu dan meningkatkan kepuasan karir jangka panjang</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start gap-4 bg-gray-50 p-5 rounded-lg">
                            <div class="w-8 h-8 bg-primary rounded-full flex items-center justify-center flex-shrink-0 text-white font-bold">4</div>
                            <div>
                                <h4 class="font-semibold text-gray-900 mb-1">Edukasi Standar Industri Rekrutmen</h4>
                                <p class="text-gray-600">Memberikan wawasan tentang bagaimana sistem ATS bekerja dan apa yang dicari recruiter, mengurangi kesenjangan informasi</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Teknologi -->
        <section class="mb-16">
            <div class="bg-white rounded-2xl shadow-lg p-8 md:p-12">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-robot text-purple-600 text-xl"></i>
                    </div>
                    <h2 class="text-3xl font-bold text-gray-900">Teknologi AI Kami</h2>
                </div>
                <div class="prose prose-lg max-w-none text-gray-600">
                    <p class="mb-6 leading-relaxed">
                        CVerity AI ditenagai oleh <strong class="text-gray-900">Google Gemini AI</strong>, model kecerdasan buatan generatif terdepan yang mampu memahami dan menganalisis dokumen dengan tingkat akurasi tinggi. Sistem kami mengintegrasikan teknologi canggih untuk memberikan evaluasi CV yang komprehensif dan objektif.
                    </p>
                    
                    <div class="grid md:grid-cols-3 gap-6">
                        <div class="text-center p-6 bg-gradient-to-br from-blue-50 to-purple-50 rounded-xl border-2 border-blue-200">
                            <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                                <svg class="w-9 h-9 text-white" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z"/>
                                </svg>
                            </div>
                            <h3 class="font-bold text-gray-900 mb-2">Google Gemini AI</h3>
                            <p class="text-sm text-gray-600">Model AI generatif terbaru dari Google dengan kemampuan pemahaman konteks yang superior</p>
                        </div>
                        
                        <div class="text-center p-6 bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl border-2 border-purple-200">
                            <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                                <i class="fas fa-file-alt text-white text-2xl"></i>
                            </div>
                            <h3 class="font-bold text-gray-900 mb-2">Document Analysis</h3>
                            <p class="text-sm text-gray-600">Ekstraksi dan analisis otomatis dari file PDF dan DOCX dengan akurasi tinggi</p>
                        </div>
                        
                        <div class="text-center p-6 bg-gradient-to-br from-pink-50 to-red-50 rounded-xl border-2 border-pink-200">
                            <div class="w-16 h-16 bg-gradient-to-br from-pink-500 to-pink-600 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                                <i class="fas fa-database text-white text-2xl"></i>
                            </div>
                            <h3 class="font-bold text-gray-900 mb-2">LinkedIn Data</h3>
                            <p class="text-sm text-gray-600">Database 135+ profil pekerjaan dari LinkedIn untuk job matching yang akurat</p>
                        </div>
                    </div>

                    <div class="mt-8 bg-gradient-to-r from-blue-50 to-purple-50 border-l-4 border-blue-500 p-6 rounded-lg">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900 mb-2">Powered by Gemini 2.0 Flash</h4>
                                <p class="text-gray-700 text-sm leading-relaxed">
                                    Kami menggunakan <strong>Gemini 2.0 Flash Experimental</strong>, model AI terbaru dari Google yang menawarkan kecepatan pemrosesan tinggi dengan hasil analisis yang mendalam. Model ini mampu memahami konteks kompleks dalam CV, mengidentifikasi skill dan pengalaman yang relevan, serta memberikan rekomendasi yang actionable berdasarkan standar industri rekrutmen global.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pengembangan Masa Depan -->
        <section class="mb-16">
            <div class="bg-white rounded-2xl shadow-lg p-8 md:p-12">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-rocket text-indigo-600 text-xl"></i>
                    </div>
                    <h2 class="text-3xl font-bold text-gray-900">Roadmap & Pengembangan Masa Depan</h2>
                </div>
                <div class="prose prose-lg max-w-none text-gray-600">
                    <p class="mb-6 leading-relaxed">
                        CVerity AI adalah proyek yang terus berkembang. Saat ini, sistem rekomendasi lowongan kerja kami menggunakan <strong class="text-gray-900">database lokal hasil scraping dari 135+ profil pekerjaan LinkedIn</strong>. Meskipun efektif, kami menyadari bahwa pasar kerja bergerak sangat dinamis dan membutuhkan data yang lebih real-time.
                    </p>

                    <div class="bg-gradient-to-br from-yellow-50 to-orange-50 border-l-4 border-yellow-500 p-6 rounded-lg mb-6">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                <i class="fas fa-info-circle text-yellow-600 text-2xl"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900 mb-2">Status Saat Ini</h4>
                                <p class="text-gray-700 text-sm leading-relaxed">
                                    Fitur job matching saat ini menggunakan data statis yang di-scraping dari LinkedIn dan platform pencari kerja lainnya. Data ini disimpan secara lokal dan diperbarui secara berkala. Meskipun memberikan hasil yang relevan, sistem ini memiliki keterbatasan dalam hal kesegaran data dan cakupan lowongan.
                                </p>
                            </div>
                        </div>
                    </div>

                    <h3 class="text-2xl font-bold text-gray-900 mb-4 mt-8">Rencana Pengembangan</h3>
                    
                    <div class="space-y-4 mb-8">
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-6 rounded-xl border-2 border-blue-200">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center flex-shrink-0 shadow-md">
                                    <i class="fas fa-plug text-white text-xl"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <h4 class="font-bold text-gray-900 text-lg">Integrasi API Lowongan Kerja Real-Time</h4>
                                        <span class="px-3 py-1 bg-blue-500 text-white rounded-full text-xs font-bold">PRIORITAS TINGGI</span>
                                    </div>
                                    <p class="text-gray-700 mb-3 leading-relaxed">
                                        Mengintegrasikan API dari platform pencari kerja terkemuka seperti <strong>LinkedIn Jobs API</strong>, <strong>Indeed API</strong>, <strong>Glints API</strong>, dan <strong>JobStreet API</strong> untuk memberikan rekomendasi lowongan yang selalu up-to-date dan relevan dengan kondisi pasar kerja terkini.
                                    </p>
                                    <ul class="space-y-2 text-sm text-gray-600">
                                        <li class="flex items-start gap-2">
                                            <i class="fas fa-check text-green-500 mt-1"></i>
                                            <span>Data lowongan kerja real-time dari berbagai platform</span>
                                        </li>
                                        <li class="flex items-start gap-2">
                                            <i class="fas fa-check text-green-500 mt-1"></i>
                                            <span>Informasi gaji, benefit, dan requirement yang akurat</span>
                                        </li>
                                        <li class="flex items-start gap-2">
                                            <i class="fas fa-check text-green-500 mt-1"></i>
                                            <span>Direct apply link ke platform original</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gradient-to-r from-purple-50 to-pink-50 p-6 rounded-xl border-2 border-purple-200">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center flex-shrink-0 shadow-md">
                                    <i class="fas fa-bell text-white text-xl"></i>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-bold text-gray-900 text-lg mb-2">Job Alert & Notification System</h4>
                                    <p class="text-gray-700 mb-3 leading-relaxed">
                                        Sistem notifikasi otomatis yang mengirimkan alert ke email atau WhatsApp ketika ada lowongan baru yang sesuai dengan profil CV pengguna.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gradient-to-r from-green-50 to-teal-50 p-6 rounded-xl border-2 border-green-200">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center flex-shrink-0 shadow-md">
                                    <i class="fas fa-user-circle text-white text-xl"></i>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-bold text-gray-900 text-lg mb-2">User Account & CV History</h4>
                                    <p class="text-gray-700 mb-3 leading-relaxed">
                                        Fitur akun pengguna untuk menyimpan riwayat analisis CV, tracking progress perbaikan, dan membandingkan versi CV yang berbeda.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gradient-to-r from-orange-50 to-red-50 p-6 rounded-xl border-2 border-orange-200">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 bg-orange-500 rounded-lg flex items-center justify-center flex-shrink-0 shadow-md">
                                    <i class="fas fa-edit text-white text-xl"></i>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-bold text-gray-900 text-lg mb-2">AI-Powered CV Builder</h4>
                                    <p class="text-gray-700 mb-3 leading-relaxed">
                                        Tool untuk membuat atau memperbaiki CV langsung di platform dengan bantuan AI, termasuk template ATS-friendly dan suggestion real-time.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gradient-to-r from-pink-50 to-rose-50 p-6 rounded-xl border-2 border-pink-200">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 bg-pink-500 rounded-lg flex items-center justify-center flex-shrink-0 shadow-md">
                                    <i class="fas fa-comments text-white text-xl"></i>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-bold text-gray-900 text-lg mb-2">Interview Preparation Assistant</h4>
                                    <p class="text-gray-700 mb-3 leading-relaxed">
                                        Fitur simulasi interview dengan AI yang memberikan pertanyaan umum berdasarkan posisi yang dilamar dan feedback terhadap jawaban kandidat.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gradient-to-r from-indigo-50 to-blue-50 p-6 rounded-xl border-2 border-indigo-200">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 bg-indigo-500 rounded-lg flex items-center justify-center flex-shrink-0 shadow-md">
                                    <i class="fas fa-mobile-alt text-white text-xl"></i>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-bold text-gray-900 text-lg mb-2">Mobile Application</h4>
                                    <p class="text-gray-700 mb-3 leading-relaxed">
                                        Aplikasi mobile (iOS & Android) untuk akses yang lebih mudah dan notifikasi push untuk job alerts.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-r from-purple-600 to-blue-600 p-8 rounded-xl text-white">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                <i class="fas fa-lightbulb text-yellow-300 text-3xl"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-xl mb-3">Komitmen Kami</h4>
                                <p class="leading-relaxed mb-4">
                                    Kami berkomitmen untuk terus mengembangkan CVerity AI menjadi platform yang lebih komprehensif dan bermanfaat. Dengan integrasi API real-time dan fitur-fitur baru, kami ingin menjadi <strong>one-stop solution</strong> untuk semua kebutuhan pencarian kerja—dari evaluasi CV, perbaikan dokumen, hingga mendapatkan pekerjaan impian.
                                </p>
                                <p class="text-purple-100 text-sm italic">
                                    "Masa depan CVerity AI adalah masa depan yang lebih cerah bagi para pencari kerja di Indonesia dan dunia."
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Tim Developer -->
        <section class="mb-16">
            <div class="bg-white rounded-2xl shadow-lg p-8 md:p-12">
                <div class="text-center mb-10">
                    <div class="inline-flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-users text-purple-600 text-xl"></i>
                        </div>
                        <h2 class="text-3xl font-bold text-gray-900">Tim Developer</h2>
                    </div>
                    <p class="text-gray-600 text-lg max-w-2xl mx-auto">
                        Proyek CVerity AI dikembangkan oleh tim developer yang berdedikasi untuk menciptakan solusi inovatif dalam dunia rekrutmen
                    </p>
                </div>

                <div class="grid md:grid-cols-3 gap-6 max-w-5xl mx-auto">
                    <!-- Developer 1 -->
                    <div class="bg-gradient-to-br from-blue-50 to-purple-50 rounded-xl p-6 border-2 border-blue-200 hover:shadow-xl transition-all duration-300 hover:-translate-y-2">
                        <div class="text-center">
                            <div class="w-24 h-24 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                                <span class="text-white text-3xl font-bold">DA</span>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Davis Arvaputra Dwiansyah</h3>
                            <p class="text-sm text-gray-600 mb-4">Full Stack Developer</p>
                            <a href="https://github.com/davizofficial" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-800 transition-colors text-sm font-medium">
                                <i class="fab fa-github"></i>
                                <span>GitHub Profile</span>
                            </a>
                        </div>
                    </div>

                    <!-- Developer 2 -->
                    <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl p-6 border-2 border-purple-200 hover:shadow-xl transition-all duration-300 hover:-translate-y-2">
                        <div class="text-center">
                            <div class="w-24 h-24 bg-gradient-to-br from-purple-500 to-pink-600 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                                <span class="text-white text-3xl font-bold">TH</span>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Tony Hendrawan</h3>
                            <p class="text-sm text-gray-600 mb-4">Backend Developer</p>
                            <a href="https://github.com/Tony-Hendrawan" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-800 transition-colors text-sm font-medium">
                                <i class="fab fa-github"></i>
                                <span>GitHub Profile</span>
                            </a>
                        </div>
                    </div>

                    <!-- Developer 3 -->
                    <div class="bg-gradient-to-br from-pink-50 to-red-50 rounded-xl p-6 border-2 border-pink-200 hover:shadow-xl transition-all duration-300 hover:-translate-y-2">
                        <div class="text-center">
                            <div class="w-24 h-24 bg-gradient-to-br from-pink-500 to-red-600 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                                <span class="text-white text-3xl font-bold">RS</span>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Rafael Putra Septava</h3>
                            <p class="text-sm text-gray-600 mb-4">AI/ML Developer</p>
                            <a href="https://github.com/RafaelSeptava" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-800 transition-colors text-sm font-medium">
                                <i class="fab fa-github"></i>
                                <span>GitHub Profile</span>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="mt-10 bg-gradient-to-r from-gray-50 to-gray-100 border-2 border-gray-200 rounded-xl p-6">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <i class="fas fa-heart text-red-500 text-2xl"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900 mb-2">Kontribusi Open Source</h4>
                            <p class="text-gray-600 text-sm leading-relaxed">
                                Kami percaya pada kekuatan kolaborasi dan open source. Proyek CVerity AI dikembangkan dengan semangat berbagi pengetahuan dan membantu komunitas developer serta pencari kerja di Indonesia. Jika Anda tertarik untuk berkontribusi atau memiliki saran, jangan ragu untuk menghubungi kami melalui GitHub!
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="text-center">
            <div class="bg-gradient-to-r from-purple-600 to-blue-600 rounded-2xl shadow-xl p-12">
                <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">
                    Siap Meningkatkan Peluang Karir Anda?
                </h2>
                <p class="text-xl text-purple-100 mb-8 max-w-2xl mx-auto">
                    Bergabunglah dengan ribuan pencari kerja yang telah meningkatkan kualitas CV mereka dengan CVerity AI
                </p>
                <a href="index.php" class="inline-flex items-center px-8 py-4 bg-white text-primary font-bold rounded-lg hover:bg-gray-100 transition-colors shadow-lg text-lg">
                    <i class="fas fa-rocket mr-2"></i>
                    Mulai Analisis CV Sekarang
                </a>
            </div>
        </section>

    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-16">
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

</body>
</html>
