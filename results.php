<?php
require_once __DIR__ . '/app/config.php';
require_once __DIR__ . '/lib/cv_storage.php';

$cvId = $_GET['id'] ?? null;

if (!$cvId) {
    die('Error: CV ID tidak ditemukan');
}

$storage = new CVStorage();
$data = $storage->get($cvId);

if (!$data) {
    die('Error: Data CV tidak ditemukan');
}

$cvData = $data['cv_data'];
$evaluation = $data['evaluation'];
$jobs = $data['jobs'] ?? [];
$hasImprovedCV = isset($data['improved_cv']);

// Ensure gaps have proper structure for insights display
if (!empty($evaluation['gaps'])) {
    // Add type field if missing (for backward compatibility)
    foreach ($evaluation['gaps'] as &$gap) {
        if (!isset($gap['type'])) {
            // Determine type based on keywords in detail
            $detail = strtolower($gap['detail'] ?? '');
            if (strpos($detail, 'experience') !== false || strpos($detail, 'skill') !== false) {
                $gap['type'] = 'strength';
            } else {
                $gap['type'] = 'opportunity';
            }
        }
    }
}

$pageTitle = 'View CV - ' . ($cvData['name'] ?? 'Unknown');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link rel="icon" type="image/png" href="/cverity-ai/logo.png">
    <script>
        // Base URL for API calls - localhost configuration
        window.BASE_URL = '/cverity-ai/';
        
        window.cvData = <?= json_encode($cvData) ?>;
        window.evaluation = <?= json_encode($evaluation) ?>;
    </script>
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        primary: "#7F56D9",
                        "background-light": "#F9FAFB",
                        "background-dark": "#111827",
                        "card-light": "#FFFFFF",
                        "card-dark": "#1F2937",
                        "text-light": "#667085",
                        "text-dark": "#9CA3AF",
                        "heading-light": "#101828",
                        "heading-dark": "#F9FAFB",
                        "border-light": "#EAECF0",
                        "border-dark": "#374151",
                    },
                    fontFamily: {
                        display: ["Plus Jakarta Sans", "sans-serif"],
                    },
                    borderRadius: {
                        DEFAULT: "12px",
                        lg: "12px",
                        xl: "16px",
                    },
                },
            },
        };
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }
        
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        
        /* Optimized for 100% zoom */
        @media (min-width: 1024px) {
            .lg\:col-span-3 {
                max-width: 100%;
            }
            .lg\:col-span-6 {
                max-width: 100%;
            }
        }
        
        /* Smooth scrollbar */
        #job-list::-webkit-scrollbar {
            width: 6px;
        }
        #job-list::-webkit-scrollbar-track {
            background: #F3F4F6;
            border-radius: 3px;
        }
        #job-list::-webkit-scrollbar-thumb {
            background: #D1D5DB;
            border-radius: 3px;
        }
        #job-list::-webkit-scrollbar-thumb:hover {
            background: #9CA3AF;
        }
        
        /* Line clamp utility */
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark font-display">
<div class="min-h-screen">
    <!-- Header -->
    <header class="bg-card-light dark:bg-card-dark border-b border-border-light dark:border-border-dark sticky top-0 z-10">
        <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-3">
                    <button onclick="window.location.href=window.BASE_URL + 'index.php'" class="text-heading-light dark:text-heading-dark p-1.5 rounded-full hover:bg-background-light dark:hover:bg-background-dark">
                        <span class="material-symbols-outlined text-xl">arrow_back</span>
                    </button>
                    <h1 class="text-base font-semibold text-heading-light dark:text-heading-dark">Hasil Analisis CV</h1>
                </div>
                <button onclick="window.location.href=window.BASE_URL + 'index.php'" class="flex items-center gap-1.5 text-xs font-semibold text-heading-light dark:text-heading-dark bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark px-3 py-2 rounded-lg shadow-sm hover:bg-background-light dark:hover:bg-background-dark transition-colors">
                    <span class="material-symbols-outlined text-sm">upload</span>
                    Upload Baru
                </button>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="py-6">
        <div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- Left Column: ATS Score & AI Insights -->
            <div class="lg:col-span-3 space-y-6 lg:sticky lg:top-20">
                <!-- ATS Score Card -->
                <?php 
                $score = $evaluation['job_match_score'] ?? 75;
                $scoreColor = $score >= 80 ? '#10B981' : ($score >= 60 ? '#FF9500' : '#EF4444');
                $scoreLabel = $score >= 80 ? 'Sangat Baik' : ($score >= 60 ? 'Cukup Baik' : 'Perlu Ditingkatkan');
                $circumference = 2 * 3.14159 * 70;
                $offset = $circumference * (1 - $score / 100);
                ?>
                <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-xl">insights</span>
                            <h2 class="text-base font-semibold text-heading-light dark:text-heading-dark">ATS Score</h2>
                        </div>
                        <span class="material-symbols-outlined text-text-light dark:text-text-dark cursor-pointer text-lg">info</span>
                    </div>
                    <div class="flex flex-col justify-center items-center my-3">
                        <div class="relative w-32 h-32">
                            <svg class="w-full h-full" viewBox="0 0 36 36">
                                <path class="stroke-current text-gray-200 dark:text-gray-700" d="M18 2.0845
                                     a 15.9155 15.9155 0 0 1 0 31.831
                                     a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke-width="3.5"></path>
                                <path class="stroke-current" style="color: <?= $scoreColor ?>;" d="M18 2.0845
                                     a 15.9155 15.9155 0 0 1 0 31.831
                                     a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke-dasharray="<?= $score ?>, 100" stroke-linecap="round" stroke-width="3.5"></path>
                            </svg>
                            <div class="absolute inset-0 flex flex-col justify-center items-center">
                                <span class="text-4xl font-bold text-heading-light dark:text-heading-dark"><?= $score ?></span>
                                <span class="text-xs text-text-light dark:text-text-dark -mt-1">/ 100</span>
                            </div>
                        </div>
                    </div>
                    <div class="text-center">
                        <p class="font-semibold text-base" style="color: <?= $scoreColor ?>;"><?= $scoreLabel ?></p>
                        <p class="text-xs text-text-light dark:text-text-dark mt-1">
                            <?php if ($score >= 80): ?>
                                Bagus! CV Anda sudah teroptimasi dengan baik.
                            <?php elseif ($score >= 60): ?>
                                Progres bagus! Beberapa perbaikan dapat meningkatkan skor Anda.
                            <?php else: ?>
                                Mari tingkatkan CV Anda agar lolos screening ATS.
                            <?php endif; ?>
                        </p>
                    </div>
                </div>

                <!-- AI-Powered Insights -->
                <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-5">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="material-symbols-outlined text-primary text-xl">task_alt</span>
                        <h2 class="text-base font-semibold text-heading-light dark:text-heading-dark">Masukan Perbaikan</h2>
                    </div>
                    <div class="space-y-4">
                        <?php 
                        $gaps = $evaluation['gaps'] ?? [];
                        $displayGaps = array_slice($gaps, 0, 3);
                        $gapCount = 0;
                        foreach ($displayGaps as $gap): 
                            $gapCount++;
                            $iconColor = $gapCount <= 2 ? 'text-yellow-500' : 'text-green-500';
                            $iconName = $gapCount <= 2 ? 'warning' : 'check_circle';
                        ?>
                        <div class="flex items-start gap-3">
                            <span class="material-symbols-outlined <?= $iconColor ?> mt-0.5 text-lg" style="font-variation-settings: 'FILL' 1"><?= $iconName ?></span>
                            <div>
                                <h3 class="font-semibold text-sm text-heading-light dark:text-heading-dark"><?= htmlspecialchars(substr($gap['detail'] ?? 'Perbaikan CV', 0, 45)) ?></h3>
                                <p class="text-xs text-text-light dark:text-text-dark mt-0.5"><?= htmlspecialchars(substr($gap['suggestion'] ?? 'Tingkatkan bagian ini untuk hasil lebih baik.', 0, 80)) ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-4 pt-4 border-t border-border-light dark:border-border-dark text-center">
                        <a onclick="showFullAnalysis()" class="text-xs font-semibold text-primary hover:text-purple-800 dark:hover:text-purple-400 transition-colors flex items-center justify-center gap-1 cursor-pointer">
                            <span>Lihat Analisis Lengkap</span>
                            <span class="material-symbols-outlined text-sm">arrow_forward</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Middle Column: Job Recommendations -->
            <div class="lg:col-span-6 bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-5">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-xl">work</span>
                        <div>
                            <h2 class="text-base font-semibold text-heading-light dark:text-heading-dark">Rekomendasi Pekerjaan</h2>
                            <?php 
                            $jobsUpdatedAt = $data['jobs_updated_at'] ?? $data['analyzed_at'] ?? null;
                            if ($jobsUpdatedAt): 
                                $timeAgo = time() - $jobsUpdatedAt;
                                $hours = floor($timeAgo / 3600);
                                $days = floor($timeAgo / 86400);
                                $timeText = $hours < 24 ? $hours . ' jam lalu' : $days . ' hari lalu';
                            ?>
                            <p class="text-xs text-text-light dark:text-text-dark">Diperbarui <?= $timeText ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <button id="refresh-jobs-btn" onclick="refreshJobs()" class="flex items-center gap-1 text-xs font-semibold text-primary hover:text-purple-800 dark:hover:text-purple-400 transition-colors px-3 py-1.5 rounded-lg hover:bg-background-light dark:hover:bg-background-dark">
                        <span class="material-symbols-outlined text-sm">refresh</span>
                        Perbarui
                    </button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
                    <div class="relative md:col-span-2">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-light dark:text-text-dark text-lg">search</span>
                        <input id="job-search" onkeyup="filterJobs()" class="w-full pl-10 pr-4 py-2 text-sm border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark rounded-lg focus:ring-2 focus:ring-primary/50 focus:border-primary" placeholder="Cari pekerjaan..." type="text"/>
                    </div>
                    <select id="filter-experience" onchange="applyJobFilters()" class="w-full text-sm border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark rounded-lg focus:ring-2 focus:ring-primary/50 focus:border-primary py-2">
                        <option value="">Semua Pengalaman</option>
                        <option value="internship">Magang</option>
                        <option value="entry">Entry Level</option>
                        <option value="associate">Associate</option>
                        <option value="mid">Mid-Senior Level</option>
                        <option value="director">Manager/Director</option>
                    </select>
                    <select id="filter-location" onchange="applyJobFilters()" class="w-full text-sm border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark rounded-lg focus:ring-2 focus:ring-primary/50 focus:border-primary py-2">
                        <option value="">Semua Lokasi</option>
                        <option value="jakarta">Jakarta</option>
                        <option value="tangerang">Tangerang</option>
                        <option value="bandung">Bandung</option>
                        <option value="surabaya">Surabaya</option>
                        <option value="yogyakarta">Yogyakarta</option>
                        <option value="bali">Bali</option>
                        <option value="semarang">Semarang</option>
                        <option value="medan">Medan</option>
                        <option value="remote">Remote</option>
                        <option value="indonesia">Indonesia (Umum)</option>
                    </select>
                </div>

                <!-- Job Cards -->
                <div id="job-list" class="space-y-3 max-h-[600px] overflow-y-auto pr-2 -mr-2">
                    <?php 
                    // Use real jobs data - NO FALLBACK to sample data
                    $displayJobs = !empty($jobs) ? $jobs : [];
                    
                    if (empty($displayJobs)): 
                    ?>
                        <!-- Empty State -->
                        <div class="flex flex-col items-center justify-center py-12 px-4 text-center">
                            <span class="material-symbols-outlined text-6xl text-text-light dark:text-text-dark mb-4" style="font-variation-settings: 'FILL' 0">work_off</span>
                            <h3 class="text-base font-semibold text-heading-light dark:text-heading-dark mb-2">Pekerjaan Belum Tersedia</h3>
                            <p class="text-sm text-text-light dark:text-text-dark max-w-md">
                                Maaf, saat ini belum ada lowongan pekerjaan yang sesuai dengan kriteria dan kualifikasi Anda. 
                                Silakan coba lagi nanti atau perluas pencarian Anda.
                            </p>
                            <div class="mt-6 flex flex-col gap-2 w-full max-w-xs">
                                <a href="https://www.linkedin.com/jobs/" target="_blank" class="bg-primary text-white font-semibold py-2 px-4 rounded-lg text-sm flex items-center justify-center gap-2 hover:bg-primary/90 transition-colors">
                                    <span class="material-symbols-outlined text-base">search</span>
                                    Cari di LinkedIn
                                </a>
                                <a href="https://www.jobstreet.co.id/" target="_blank" class="bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark text-heading-light dark:text-heading-dark font-semibold py-2 px-4 rounded-lg text-sm flex items-center justify-center gap-2 hover:bg-background-light dark:hover:bg-background-dark transition-colors">
                                    <span class="material-symbols-outlined text-base">open_in_new</span>
                                    Cari di JobStreet
                                </a>
                            </div>
                        </div>
                    <?php 
                    else:
                        foreach ($displayJobs as $job): 
                    ?>
                    <div class="border border-border-light dark:border-border-dark p-3 rounded-lg hover:shadow-md hover:border-primary dark:hover:border-primary transition-all cursor-pointer job-item"
                         data-title="<?= htmlspecialchars(strtolower($job['title'])) ?>"
                         data-company="<?= htmlspecialchars(strtolower($job['company'])) ?>"
                         data-location="<?= htmlspecialchars(strtolower($job['location'])) ?>"
                         data-experience="<?= htmlspecialchars(strtolower($job['experience_level'] ?? '')) ?>">
                        <div class="flex justify-between items-start gap-2">
                            <div class="flex-1">
                                <h3 class="font-semibold text-sm text-heading-light dark:text-heading-dark"><?= htmlspecialchars($job['title']) ?></h3>
                                <div class="flex gap-1.5 mt-1">
                                    <?php if (!empty($job['is_internship'])): ?>
                                        <span class="text-xs font-semibold text-purple-700 bg-purple-100 dark:bg-purple-900/30 dark:text-purple-300 px-2 py-0.5 rounded-full">🎓 Magang</span>
                                    <?php endif; ?>
                                    <?php if (!empty($job['is_fresh_graduate'])): ?>
                                        <span class="text-xs font-semibold text-green-700 bg-green-100 dark:bg-green-900/30 dark:text-green-300 px-2 py-0.5 rounded-full">🌟 Fresh Graduate</span>
                                    <?php endif; ?>
                                    <?php if (!empty($job['is_remote'])): ?>
                                        <span class="text-xs font-semibold text-orange-700 bg-orange-100 dark:bg-orange-900/30 dark:text-orange-300 px-2 py-0.5 rounded-full">🏠 Remote</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <span class="text-xs font-semibold text-blue-600 bg-blue-100 dark:bg-blue-900/30 dark:text-blue-300 px-2 py-0.5 rounded-full whitespace-nowrap"><?= $job['match_score'] ?>%</span>
                        </div>
                        <div class="flex items-center text-xs text-text-light dark:text-text-dark gap-3 mt-1.5">
                            <span class="flex items-center gap-1"><span class="material-symbols-outlined text-sm">apartment</span> <?= htmlspecialchars($job['company']) ?></span>
                            <span class="flex items-center gap-1"><span class="material-symbols-outlined text-sm">location_on</span> <?= htmlspecialchars($job['location']) ?></span>
                        </div>
                        <p class="text-xs text-text-light dark:text-text-dark mt-2 line-clamp-2"><?= htmlspecialchars(substr($job['description'], 0, 100)) ?>...</p>
                        <div class="flex justify-between items-center mt-3">
                            <p class="text-xs text-text-light dark:text-text-dark">Diposting <?php
                                $posted = strtotime($job['posted']);
                                $diff = time() - $posted;
                                $hours = floor($diff / 3600);
                                $days = floor($diff / 86400);
                                echo $hours < 24 ? $hours . ' jam lalu' : $days . ' hari lalu';
                            ?></p>
                            <a href="<?= htmlspecialchars($job['url']) ?>" target="_blank" class="bg-primary text-white font-semibold py-1.5 px-3 rounded-lg text-xs flex items-center gap-1 hover:bg-primary/90 transition-colors">Lamar <span class="material-symbols-outlined text-sm">arrow_forward</span></a>
                        </div>
                    </div>
                    <?php 
                        endforeach;
                    endif; 
                    ?>
                </div>
            </div>

            <!-- Right Column: Improve Your CV -->
            <div class="lg:col-span-3 space-y-6 lg:sticky lg:top-20">
                <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-5">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="material-symbols-outlined text-primary text-xl">auto_awesome</span>
                        <h2 class="text-base font-semibold text-heading-light dark:text-heading-dark">Tingkatkan CV</h2>
                    </div>
                    <div class="bg-background-light dark:bg-background-dark p-3 rounded-lg border border-border-light dark:border-border-dark">
                        <div class="flex items-start gap-3">
                            <div class="bg-gray-200 dark:bg-gray-700 p-2 rounded-lg flex-shrink-0">
                                <span class="material-symbols-outlined text-heading-light dark:text-heading-dark text-lg">description</span>
                            </div>
                            <div>
                                <p class="font-semibold text-xs text-heading-light dark:text-heading-dark"><?= htmlspecialchars($cvData['name'] ?? 'CV Anda') ?></p>
                                <p class="text-xs text-text-light dark:text-text-dark mt-0.5"><?= htmlspecialchars($cvData['positions'][0]['title'] ?? 'Professional') ?></p>
                                <span class="text-xs font-semibold text-yellow-800 dark:text-yellow-300 bg-yellow-100 dark:bg-yellow-900/40 px-1.5 py-0.5 rounded-full mt-1.5 inline-block">Score: <?= $score ?></span>
                            </div>
                        </div>
                    </div>
                    <ul class="space-y-3 mt-4 text-xs">
                        <li class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-base" style="font-variation-settings: 'FILL' 1">check_circle</span>
                            <span class="text-text-light dark:text-text-dark">Format optimal untuk ATS</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-base" style="font-variation-settings: 'FILL' 1">check_circle</span>
                            <span class="text-text-light dark:text-text-dark">Deskripsi yang ditingkatkan</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-base" style="font-variation-settings: 'FILL' 1">check_circle</span>
                            <span class="text-text-light dark:text-text-dark">Optimasi kata kunci</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-base" style="font-variation-settings: 'FILL' 1">check_circle</span>
                            <span class="text-text-light dark:text-text-dark">Tata letak profesional</span>
                        </li>
                    </ul>
                    <div class="mt-4 space-y-2">
                        <?php if ($hasImprovedCV): ?>
                        <!-- <button type="button" id="download-docx-btn" class="w-full bg-blue-600 text-white font-semibold py-2 rounded-lg flex items-center justify-center gap-1.5 hover:bg-blue-700 transition-colors text-xs">
                            <span class="material-symbols-outlined text-base">download</span>
                            Download DOCX (ATS-Friendly)
                        </button> -->
                        <button type="button" id="view-improved-cv-btn" class="w-full bg-teal-500 text-white font-semibold py-2 rounded-lg flex items-center justify-center gap-1.5 hover:bg-teal-600 transition-colors text-xs">
                            <span class="material-symbols-outlined text-base" style="font-variation-settings: 'FILL' 1">visibility</span>
                            Lihat Preview HTML
                        </button>
                        <button type="button" id="generate-improved-cv-btn" class="w-full bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark text-heading-light dark:text-heading-dark font-semibold py-2 rounded-lg flex items-center justify-center gap-1.5 hover:bg-background-light dark:hover:bg-background-dark transition-colors text-xs">
                            <span class="material-symbols-outlined text-base">refresh</span>
                            Buat Ulang
                        </button>
                        <?php else: ?>
                        <button type="button" id="generate-improved-cv-btn" class="w-full bg-teal-500 text-white font-semibold py-2 rounded-lg flex items-center justify-center gap-1.5 hover:bg-teal-600 transition-colors text-xs">
                            <span class="material-symbols-outlined text-base">auto_awesome</span>
                            Buat CV Ditingkatkan
                        </button>
                        <?php endif; ?>
                    </div>
                    <?php if ($hasImprovedCV): ?>
                    <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                        <div class="flex items-start gap-2">
                            <span class="material-symbols-outlined text-blue-600 dark:text-blue-400 mt-0.5 text-base" style="font-variation-settings: 'FILL' 1">verified</span>
                            <div>
                                <p class="font-semibold text-xs text-blue-800 dark:text-blue-300">✅ 100% ATS-Friendly</p>
                                <p class="text-xs text-blue-700 dark:text-blue-400 mt-0.5">Format DOCX dapat dibaca oleh 95%+ sistem ATS. Siap diupload ke job portal!</p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        </div>

        <!-- Full Analysis Modal (Hidden by default) -->
        <div id="analysis-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden">
                <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-2xl font-bold text-gray-900">Analisis Lengkap & Rekomendasi</h2>
                    <button onclick="closeAnalysisModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="p-6 overflow-y-auto" style="max-height: calc(90vh - 100px);">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Original CV View -->
                        <div>
                            <h3 class="text-lg font-bold text-gray-800 mb-4">CV Anda</h3>
                            <?php include __DIR__ . '/templates/components/cv_view.php'; ?>
                        </div>
                        
                        <!-- Gap Analysis -->
                        <div>
                            <?php 
                            $gaps = $evaluation['gaps'] ?? [];
                            $suggestedActions = $evaluation['suggested_actions'] ?? [];
                            include __DIR__ . '/templates/components/gap_list.php'; 
                            ?>
                            
                            <div class="mt-6 space-y-3">
                                <button type="button" id="generate-improved-cv-modal-btn" class="w-full px-4 py-3 bg-primary text-white rounded-lg hover:bg-primary-dark transition font-medium">
                                    🚀 Buat CV yang Ditingkatkan
                                </button>
                                <?php if ($hasImprovedCV): ?>
                                <button type="button" id="view-improved-cv-modal-btn" class="w-full px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium">
                                    ✓ Lihat CV yang Ditingkatkan
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Improved CV Modal (Hidden by default) -->
        <?php if ($hasImprovedCV): ?>
        <div id="improved-cv-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden">
                <div class="p-6 border-b border-gray-200 flex items-center justify-between bg-gray-50">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">✨ CV ATS-Friendly</h2>
                        <p class="text-sm text-gray-600 mt-1">CV yang sudah dioptimalkan untuk ATS dan recruiter</p>
                    </div>
                    <div class="flex gap-3">
                        <button onclick="copyImprovedCV()" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition font-medium text-sm">
                            📋 Salin
                        </button>
                        <button onclick="printCV()" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition font-medium text-sm">
                            🖨️ Cetak
                        </button>
                        <button onclick="closeImprovedCVModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="p-8 overflow-y-auto" style="max-height: calc(90vh - 120px);">
                    <div id="improved-cv-content" class="bg-white" style="box-shadow: 0 0 20px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden;">
                        <!-- CV content will be loaded here via JavaScript -->
                        <div class="text-center py-8">
                            <p class="text-gray-500">Loading CV...</p>
                        </div>
                    </div>
                </div>
                <!-- Hidden CV data -->
                <script type="text/plain" id="improved-cv-data">
                    <?= htmlspecialchars($data['improved_cv']) ?>
                </script>
            </div>
        </div>
        <?php endif; ?>
    </main>

    <script>
        // Job search and filter
        function filterJobs() {
            const searchTerm = document.getElementById('job-search')?.value.toLowerCase() || '';
            const jobItems = document.querySelectorAll('.job-item');
            
            jobItems.forEach(item => {
                const title = item.dataset.title || '';
                const company = item.dataset.company || '';
                const location = item.dataset.location || '';
                
                const matches = title.includes(searchTerm) || 
                               company.includes(searchTerm) || 
                               location.includes(searchTerm);
                
                item.style.display = matches ? 'block' : 'none';
            });
        }

        function applyJobFilters() {
            const experience = document.getElementById('filter-experience')?.value.toLowerCase() || '';
            const location = document.getElementById('filter-location')?.value.toLowerCase() || '';
            const searchTerm = document.getElementById('job-search')?.value.toLowerCase() || '';
            const jobItems = document.querySelectorAll('.job-item');
            
            let visibleCount = 0;
            
            jobItems.forEach(item => {
                const itemTitle = item.dataset.title || '';
                const itemCompany = item.dataset.company || '';
                const itemLocation = item.dataset.location || '';
                const itemExperience = item.dataset.experience || '';
                
                let matches = true;
                
                // Filter by search term
                if (searchTerm) {
                    matches = matches && (itemTitle.includes(searchTerm) || 
                                         itemCompany.includes(searchTerm) || 
                                         itemLocation.includes(searchTerm));
                }
                
                // Filter by experience level
                if (experience) {
                    matches = matches && itemExperience.includes(experience);
                }
                
                // Filter by location
                if (location) {
                    if (location === 'remote') {
                        // Check if job is remote
                        const badges = item.querySelectorAll('.text-xs.font-semibold');
                        let isRemote = false;
                        badges.forEach(badge => {
                            if (badge.textContent.includes('Remote')) {
                                isRemote = true;
                            }
                        });
                        matches = matches && isRemote;
                    } else {
                        matches = matches && itemLocation.includes(location);
                    }
                }
                
                if (matches) {
                    item.style.display = 'block';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });
            
            // Show message if no results
            const jobList = document.getElementById('job-list');
            let noResultsMsg = jobList.querySelector('.no-results-message');
            
            if (visibleCount === 0 && jobItems.length > 0) {
                if (!noResultsMsg) {
                    noResultsMsg = document.createElement('div');
                    noResultsMsg.className = 'no-results-message text-center py-8 text-text-light dark:text-text-dark';
                    noResultsMsg.innerHTML = '<p class="text-sm">Tidak ada pekerjaan yang sesuai dengan filter Anda.</p>';
                    jobList.appendChild(noResultsMsg);
                }
            } else if (noResultsMsg) {
                noResultsMsg.remove();
            }
        }
    
        function showFullAnalysis() {
            document.getElementById('analysis-modal').classList.remove('hidden');
        }
        
        function closeAnalysisModal() {
            document.getElementById('analysis-modal').classList.add('hidden');
        }
        
        function viewImprovedCV() {
            closeAnalysisModal();
            
            // Load CV content from hidden script tag
            const cvData = document.getElementById('improved-cv-data');
            const cvContent = document.getElementById('improved-cv-content');
            
            if (cvData && cvContent) {
                // Decode HTML entities and set content
                const htmlContent = cvData.textContent;
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = htmlContent;
                cvContent.innerHTML = tempDiv.textContent;
            }
            
            document.getElementById('improved-cv-modal').classList.remove('hidden');
        }
        
        function closeImprovedCVModal() {
            document.getElementById('improved-cv-modal').classList.add('hidden');
        }
        
        // Initialize button event listeners
        document.addEventListener('DOMContentLoaded', () => {
            // Generate improved CV button (main)
            const generateBtn = document.getElementById('generate-improved-cv-btn');
            if (generateBtn) {
                generateBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    generateImprovedCV(e);
                });
            }
            
            // Generate improved CV button (modal)
            const generateModalBtn = document.getElementById('generate-improved-cv-modal-btn');
            if (generateModalBtn) {
                generateModalBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    generateImprovedCV(e);
                });
            }
            
            // View improved CV button (main)
            const viewBtn = document.getElementById('view-improved-cv-btn');
            if (viewBtn) {
                viewBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    viewImprovedCV();
                });
            }
            
            // View improved CV button (modal)
            const viewModalBtn = document.getElementById('view-improved-cv-modal-btn');
            if (viewModalBtn) {
                viewModalBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    viewImprovedCV();
                });
            }
            
            // Download DOCX button
            const downloadDocxBtn = document.getElementById('download-docx-btn');
            if (downloadDocxBtn) {
                downloadDocxBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    downloadDocx();
                });
            }
        });
        
        // Close modals on ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeAnalysisModal();
                closeImprovedCVModal();
            }
        });
        
        // Close modals on backdrop click
        document.getElementById('analysis-modal')?.addEventListener('click', (e) => {
            if (e.target.id === 'analysis-modal') closeAnalysisModal();
        });
        document.getElementById('improved-cv-modal')?.addEventListener('click', (e) => {
            if (e.target.id === 'improved-cv-modal') closeImprovedCVModal();
        });
        

        async function downloadDocx() {
            const cvId = '<?= $cvId ?>';
            const downloadUrl = window.BASE_URL + 'app/download-docx.php?cv_id=' + encodeURIComponent(cvId);
            
            // Show loading
            const btn = document.getElementById('download-docx-btn');
            const originalHTML = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="material-symbols-outlined text-base animate-spin">progress_activity</span> Generating...';
            
            try {
                // Create temporary link and trigger download
                const link = document.createElement('a');
                link.href = downloadUrl;
                link.download = 'CV_ATS_Friendly.docx';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                // Show success message
                setTimeout(() => {
                    btn.disabled = false;
                    btn.innerHTML = originalHTML;
                    alert('✅ File DOCX berhasil didownload! File ini 100% ATS-friendly dan siap diupload ke job portal.');
                }, 1000);
                
            } catch (error) {
                console.error('Download error:', error);
                alert('❌ Error: Gagal download file DOCX');
                btn.disabled = false;
                btn.innerHTML = originalHTML;
            }
        }
        
        async function generateImprovedCV(event) {
            if (!confirm('Buat CV yang ditingkatkan? Proses ini membutuhkan waktu ~30 detik.')) return;
            
            const btn = event ? event.target : document.activeElement;
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="material-symbols-outlined animate-spin">progress_activity</span> Membuat...';
            
            try {
                const cvId = '<?= $cvId ?>';
                const apiUrl = window.BASE_URL + 'app/generate-improved-cv.php';
                
                console.log('Generating improved CV...');
                console.log('CV ID:', cvId);
                console.log('API URL:', apiUrl);
                console.log('Request body:', JSON.stringify({cv_id: cvId}));
                
                const response = await fetch(apiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({cv_id: cvId})
                });
                
                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error(`Server error (${response.status}): ${errorText}`);
                }
                
                const result = await response.json();
                
                if (result.error) {
                    throw new Error(result.message || 'Terjadi kesalahan saat membuat CV');
                }
                
                alert('✅ CV yang ditingkatkan berhasil dibuat!');
                location.reload();
                
            } catch (error) {
                console.error('Generate CV Error:', error);
                alert('Error: ' + error.message);
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        }
        
        function copyImprovedCV() {
            const content = document.getElementById('improved-cv-content');
            if (!content) {
                alert('Konten CV tidak ditemukan');
                return;
            }
            
            // Copy HTML to clipboard
            const html = content.innerHTML;
            navigator.clipboard.writeText(html).then(() => {
                alert('✅ CV HTML berhasil disalin! Paste ke Word/Google Docs.');
            }).catch(err => {
                alert('❌ Gagal menyalin: ' + err.message);
            });
        }
        
        function printCV() {
            const content = document.getElementById('improved-cv-content');
            if (!content) {
                alert('Konten CV tidak ditemukan');
                return;
            }
            
            // Open print dialog
            const printWindow = window.open('', '_blank');
            printWindow.document.write('<html><head><title>CV - <?= htmlspecialchars($cvData['name'] ?? 'CV') ?></title>');
            printWindow.document.write('<style>body { font-family: Arial, sans-serif; margin: 2cm; }</style>');
            printWindow.document.write('</head><body>');
            printWindow.document.write(content.innerHTML);
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            
            setTimeout(() => {
                printWindow.print();
            }, 250);
        }
        
        async function refreshJobs() {
            const btn = document.getElementById('refresh-jobs-btn');
            const originalHTML = btn.innerHTML;
            
            // Disable button and show loading
            btn.disabled = true;
            btn.innerHTML = '<span class="material-symbols-outlined text-sm animate-spin">progress_activity</span> Memperbarui...';
            
            try {
                const cvId = '<?= $cvId ?>';
                const apiUrl = window.BASE_URL + 'app/refresh-jobs.php';
                
                console.log('Refreshing jobs...');
                console.log('CV ID:', cvId);
                console.log('API URL:', apiUrl);
                
                const response = await fetch(apiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'cv_id=' + encodeURIComponent(cvId)
                });
                
                if (!response.ok) {
                    const errorText = await response.text();
                    throw new Error(`Server error (${response.status}): ${errorText}`);
                }
                
                const result = await response.json();
                
                if (!result.success) {
                    throw new Error(result.error || 'Gagal memperbarui rekomendasi pekerjaan');
                }
                
                // Show success message
                alert(`✅ Berhasil! Ditemukan ${result.total_found} lowongan pekerjaan baru.`);
                
                // Reload page to show new jobs
                location.reload();
                
            } catch (error) {
                console.error('Refresh jobs error:', error);
                alert('❌ Error: ' + error.message);
                
                // Restore button
                btn.disabled = false;
                btn.innerHTML = originalHTML;
            }
        }
    </script>

    <!-- Footer -->
    <footer class="bg-card-light dark:bg-card-dark border-t border-border-light dark:border-border-dark mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <img src="/cverity-ai/logo.png" alt="CVerity AI Logo" class="w-8 h-8 object-contain flex-shrink-0">
                    <p class="text-sm text-text-light dark:text-text-dark whitespace-nowrap">© 2025 CVerity AI. Hak cipta dilindungi.</p>
                </div>
                <div class="flex flex-wrap gap-6 items-center">
                    <a href="#" class="text-sm text-text-light dark:text-text-dark hover:text-heading-light dark:hover:text-heading-dark transition-colors whitespace-nowrap">Tentang Kami</a>
                    <a href="#" class="text-sm text-text-light dark:text-text-dark hover:text-heading-light dark:hover:text-heading-dark transition-colors whitespace-nowrap">Bantuan</a>
                    <a href="#" class="text-sm text-text-light dark:text-text-dark hover:text-heading-light dark:hover:text-heading-dark transition-colors whitespace-nowrap">Kebijakan Privasi</a>
                </div>
            </div>
        </div>
    </footer>
</div>
</body>
</html>
