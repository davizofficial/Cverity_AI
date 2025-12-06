<?php
// Job Generator menggunakan LinkedIn Scraping Data

class JobGenerator {
    private $dataPath;
    private $allJobsData;
    
    public function __construct() {
        $this->dataPath = __DIR__ . '/../data-linkedin/';
        $this->loadAllJobs();
    }
    
    /**
     * Load all jobs from LinkedIn scraping data
     */
    private function loadAllJobs() {
        $jobListFile = $this->dataPath . 'json/all_jobs.json';
        
        if (file_exists($jobListFile)) {
            $jsonData = file_get_contents($jobListFile);
            $this->allJobsData = json_decode($jsonData, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log('Error loading all_jobs.json: ' . json_last_error_msg());
                $this->allJobsData = [];
            }
        } else {
            error_log('all_jobs.json not found at: ' . $jobListFile);
            $this->allJobsData = [];
        }
    }
    
    /**
     * Search jobs dari LinkedIn scraping data
     */
    public function searchJobs($cvData, $location = 'Indonesia', $limit = 15) {
        try {
            // Extract keywords dari CV data
            $targetRole = $this->determineTargetRole($cvData);
            
            // CRITICAL: If no role detected from summary, return error
            if (empty($targetRole)) {
                error_log("CRITICAL: No target role detected from summary");
                return [
                    'success' => false,
                    'jobs' => [],
                    'search_category' => '',
                    'search_location' => $cvData['location'] ?? 'Indonesia',
                    'total_found' => 0,
                    'message' => 'Tidak dapat menentukan kategori pekerjaan dari summary CV Anda. Mohon sebutkan dengan jelas posisi/bidang pekerjaan yang Anda inginkan di bagian summary/about me.'
                ];
            }
            
            $targetCategory = $this->mapRoleToCategory($targetRole);
            
            // CRITICAL: If still no category, return error
            if (empty($targetCategory)) {
                error_log("CRITICAL: No target category found for role: $targetRole");
                return [
                    'success' => false,
                    'jobs' => [],
                    'search_category' => '',
                    'search_location' => $cvData['location'] ?? 'Indonesia',
                    'total_found' => 0,
                    'message' => "Tidak dapat menemukan kategori pekerjaan untuk role: '$targetRole'. Mohon gunakan istilah yang lebih umum seperti 'customer service', 'admin', 'sales', dll."
                ];
            }
            
            // Log untuk debugging
            error_log("Job Generator - Target Role: $targetRole, Category: $targetCategory");
            
            // Deteksi lokasi dari CV jika ada
            $preferredLocation = null;
            if (!empty($cvData['location'])) {
                $preferredLocation = $cvData['location'];
            }
            
            // Deteksi experience level kandidat
            $candidateExperience = $this->calculateTotalExperience($cvData);
            $isFreshGraduate = $candidateExperience < 1; // < 1 tahun = fresh graduate
            
            // Search jobs dari data LinkedIn
            $matchedJobs = $this->searchLinkedInJobs($targetCategory, $preferredLocation, $cvData);
            
            // Filter jobs berdasarkan experience level kandidat
            if ($isFreshGraduate) {
                // Prioritaskan internship dan entry level untuk fresh graduate
                $matchedJobs = $this->prioritizeInternshipJobs($matchedJobs);
            }
            
            // Calculate match score untuk setiap job
            $jobsWithScore = [];
            foreach ($matchedJobs as $job) {
                $matchScore = $this->calculateMatchScore($job, $cvData);
                
                // Boost score untuk internship jika fresh graduate
                if ($isFreshGraduate && ($job['metadata']['is_internship'] ?? false)) {
                    $matchScore = min($matchScore + 10, 95); // Boost +10 points
                }
                
                // Boost score untuk fresh graduate positions
                if ($isFreshGraduate && ($job['metadata']['is_fresh_graduate'] ?? false)) {
                    $matchScore = min($matchScore + 5, 95); // Boost +5 points
                }
                
                $jobsWithScore[] = array_merge($job, ['match_score' => $matchScore]);
            }
            
            // Filter jobs dengan match score minimal 50% (lebih rendah agar lebih banyak job muncul)
            $jobsWithScore = array_filter($jobsWithScore, function($job) {
                return $job['match_score'] >= 50;
            });
            
            // Sort by match score (highest first)
            usort($jobsWithScore, function($a, $b) {
                return $b['match_score'] - $a['match_score'];
            });
            
            // Limit results
            $jobs = array_slice($jobsWithScore, 0, $limit);
            
            // Jika tidak ada job yang cocok, return empty dengan pesan
            if (empty($jobs)) {
                return [
                    'success' => true, 
                    'jobs' => [],
                    'search_category' => $targetCategory,
                    'search_location' => $preferredLocation ?? 'Indonesia',
                    'total_found' => 0,
                    'message' => 'Pekerjaan yang sesuai dengan kriteria kandidat belum tersedia saat ini. Silakan coba lagi nanti atau perluas pencarian Anda.'
                ];
            }
            
            // Transform ke format yang diharapkan
            $transformedJobs = $this->transformLinkedInJobs($jobs);
            
            return [
                'success' => true, 
                'jobs' => $transformedJobs,
                'search_category' => $targetCategory,
                'search_location' => $preferredLocation ?? 'Indonesia',
                'total_found' => count($jobs)
            ];
            
        } catch (Exception $e) {
            error_log('Job generator error: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Job generator error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Calculate total experience dalam tahun
     */
    private function calculateTotalExperience($cvData) {
        if (empty($cvData['positions']) || !is_array($cvData['positions'])) {
            return 0;
        }
        
        $totalMonths = 0;
        
        foreach ($cvData['positions'] as $position) {
            // Jika ada field months, gunakan itu
            if (isset($position['months']) && is_numeric($position['months'])) {
                $totalMonths += $position['months'];
                continue;
            }
            
            // Jika tidak, hitung dari start_date dan end_date
            $startDate = $position['start_date'] ?? null;
            $endDate = $position['end_date'] ?? 'present';
            
            if (!$startDate) {
                continue;
            }
            
            // Parse dates
            $start = strtotime($startDate);
            if ($start === false) {
                continue;
            }
            
            if ($endDate === 'present' || strtolower($endDate) === 'present') {
                $end = time();
            } else {
                $end = strtotime($endDate);
                if ($end === false) {
                    $end = time();
                }
            }
            
            // Calculate months
            $diff = $end - $start;
            $months = floor($diff / (30 * 24 * 60 * 60)); // Approximate
            $totalMonths += max($months, 0);
        }
        
        // Convert to years
        return round($totalMonths / 12, 1);
    }
    
    /**
     * Prioritaskan internship jobs untuk fresh graduate
     */
    private function prioritizeInternshipJobs($jobs) {
        $internships = [];
        $entryLevel = [];
        $others = [];
        
        foreach ($jobs as $job) {
            $isInternship = $job['metadata']['is_internship'] ?? false;
            $isFreshGrad = $job['metadata']['is_fresh_graduate'] ?? false;
            $experienceLevel = strtolower($job['experience_level'] ?? '');
            
            if ($isInternship) {
                $internships[] = $job;
            } elseif ($isFreshGrad || strpos($experienceLevel, 'entry') !== false || 
                      strpos($experienceLevel, 'internship') !== false) {
                $entryLevel[] = $job;
            } else {
                $others[] = $job;
            }
        }
        
        // Merge: internships first, then entry level, then others
        return array_merge($internships, $entryLevel, $others);
    }
    
    /**
     * Search jobs dari LinkedIn data berdasarkan category dan location
     */
    private function searchLinkedInJobs($targetCategory, $preferredLocation, $cvData) {
        $matchedJobs = [];
        
        // Handle empty category
        if (empty($targetCategory)) {
            error_log("Warning: Empty target category, cannot search jobs");
            return [];
        }
        
        // 1. Cari di kategori yang sesuai (kategori utama)
        $categoryFile = $this->dataPath . 'by_role/' . $targetCategory . '.json';
        if (file_exists($categoryFile)) {
            $categoryJobs = json_decode(file_get_contents($categoryFile), true);
            if (is_array($categoryJobs)) {
                $matchedJobs = array_merge($matchedJobs, $categoryJobs);
                error_log("Found " . count($categoryJobs) . " jobs in category: $targetCategory");
            }
        } else {
            error_log("Category file not found: $categoryFile");
        }
        
        // 2. Jika hasil kurang dari 30, cari di related categories
        if (count($matchedJobs) < 30) {
            $relatedCategories = $this->getRelatedCategories($targetCategory);
            error_log("Searching related categories: " . implode(', ', $relatedCategories));
            
            foreach ($relatedCategories as $relatedCat) {
                $relatedFile = $this->dataPath . 'by_role/' . $relatedCat . '.json';
                if (file_exists($relatedFile)) {
                    $relatedJobs = json_decode(file_get_contents($relatedFile), true);
                    if (is_array($relatedJobs)) {
                        $matchedJobs = array_merge($matchedJobs, $relatedJobs);
                        error_log("Added " . count($relatedJobs) . " jobs from: $relatedCat");
                    }
                }
                if (count($matchedJobs) >= 50) break; // Increased limit
            }
        }
        
        // Remove duplicates berdasarkan link
        $uniqueJobs = [];
        $seenLinks = [];
        foreach ($matchedJobs as $job) {
            $link = $job['link'] ?? '';
            if (!in_array($link, $seenLinks)) {
                $uniqueJobs[] = $job;
                $seenLinks[] = $link;
            }
        }
        
        error_log("Total unique jobs found: " . count($uniqueJobs));
        return $uniqueJobs;
    }
    
    /**
     * Map role ke category file name
     */
    private function mapRoleToCategory($role) {
        $role = strtolower(trim($role));
        
        // Direct mapping
        $categoryMap = [
            // Security & Cybersecurity (PRIORITAS TINGGI)
            'cyber security' => 'cybersecurity',
            'cybersecurity' => 'cybersecurity',
            'information security' => 'cybersecurity',
            'infosec' => 'cybersecurity',
            'security analyst' => 'cybersecurity',
            'security engineer' => 'cybersecurity',
            'penetration tester' => 'cybersecurity',
            'pentester' => 'cybersecurity',
            'ethical hacker' => 'cybersecurity',
            'security researcher' => 'cybersecurity',
            'soc analyst' => 'cybersecurity',
            
            // Customer Service & Support
            'customer service representative' => 'customer_service',
            'customer service' => 'customer_service',
            'customer care' => 'customer_service',
            'call center agent' => 'call_center',
            'call center' => 'call_center',
            'customer support specialist' => 'customer_support',
            'customer support' => 'customer_support',
            'technical support' => 'it_support',
            'it support' => 'it_support',
            'helpdesk' => 'helpdesk',
            
            // Administrative & Office
            'administrative assistant' => 'administrative',
            'admin assistant' => 'admin',
            'admin' => 'admin',
            'administrative' => 'administrative',
            'secretary' => 'secretary',
            'receptionist' => 'receptionist',
            'office manager' => 'manager',
            'office staff' => 'office_staff',
            'executive assistant' => 'administrative',
            'personal assistant' => 'administrative',
            'data entry' => 'admin',
            'clerical' => 'administrative',
            
            // Sales & Marketing
            'sales manager' => 'sales',
            'sales executive' => 'sales_executive',
            'sales' => 'sales',
            'marketing manager' => 'marketing',
            'marketing executive' => 'marketing',
            'marketing' => 'marketing',
            'digital marketing specialist' => 'digital_marketing',
            'digital marketing' => 'digital_marketing',
            'content marketing' => 'content_marketing',
            'social media specialist' => 'social_media',
            'social media' => 'social_media',
            'seo specialist' => 'seo',
            'seo' => 'seo',
            'account manager' => 'account_executive',
            'account executive' => 'account_executive',
            'business development manager' => 'business_development',
            'business development' => 'business_development',
            
            // Retail & Hospitality
            'cashier' => 'cashier',
            'barista' => 'barista',
            'waiter' => 'waiter',
            'waitress' => 'waiter',
            'bartender' => 'bartender',
            'chef' => 'chef',
            'hotel staff' => 'hotel_staff',
            'front office' => 'front_office',
            'housekeeping' => 'housekeeping',
            'shop assistant' => 'shop_assistant',
            'retail' => 'retail_supervisor',
            'store manager' => 'store_manager',
            
            // Tech roles
            'software engineer' => 'software_engineer',
            'software developer' => 'software_engineer',
            'full stack developer' => 'web_developer',
            'fullstack developer' => 'web_developer',
            'frontend developer' => 'web_developer',
            'backend developer' => 'web_developer',
            'web developer' => 'web_developer',
            'mobile developer' => 'mobile_developer',
            'programmer' => 'programmer',
            'developer' => 'programmer',
            
            // Data roles
            'data scientist' => 'data_scientist',
            'data analyst' => 'analyst',
            'data engineer' => 'data_scientist',
            'analyst' => 'analyst',
            
            // DevOps & Infrastructure
            'devops engineer' => 'devops_engineer',
            'devops' => 'devops_engineer',
            'system administrator' => 'system_administrator',
            'network engineer' => 'network_engineer',
            'it support' => 'IT_support',
            'technical support' => 'technical_support',
            
            // QA & Testing
            'qa engineer' => 'quality_assurance',
            'quality assurance' => 'quality_assurance',
            'tester' => 'quality_assurance',
            
            // Design (URUTAN PENTING: Yang lebih spesifik di atas!)
            'ui/ux designer' => 'ui_ux_designer',
            'ui ux designer' => 'ui_ux_designer',
            'ux designer' => 'ui_ux_designer',
            'ui designer' => 'ui_ux_designer',
            'product designer' => 'ui_ux_designer',
            'graphic designer' => 'graphic_designer',
            'designer' => 'designer',
            
            // Management
            'product manager' => 'product_manager',
            'project manager' => 'project_manager',
            'manager' => 'operations_manager',
            
            // Marketing & Sales
            'digital marketing specialist' => 'digital_marketing',
            'digital marketing' => 'digital_marketing',
            'marketing specialist' => 'marketing',
            'marketing executive' => 'marketing',
            'marketing' => 'marketing',
            'content marketing' => 'content_marketing',
            'social media specialist' => 'social_media',
            'social media manager' => 'social_media',
            'sales' => 'sales',
            'sales executive' => 'sales_executive',
            'account manager' => 'account_manager',
            'account executive' => 'account_executive',
            
            // Business
            'business analyst' => 'analyst',
            'business development' => 'business_development',
            'consultant' => 'business_consultant',
            
            // HR
            'hr' => 'hr',
            'human resources' => 'human_resources',
            'recruiter' => 'recruiter',
            'talent acquisition' => 'talent_acquisition',
            
            // Finance
            'accountant' => 'accountant',
            'financial analyst' => 'financial_analyst',
            'finance' => 'finance_manager',
            
            // Content & Creative
            'content creator' => 'content_creator',
            'copywriter' => 'copywriter',
            'social media' => 'social_media',
            'social media specialist' => 'social_media_specialist',
            'videographer' => 'videographer',
            'photographer' => 'photographer',
            'writer' => 'writer',
            'editor' => 'editor',
            'journalist' => 'journalist',
            
            // Additional mappings for all 135 roles
            'agent' => 'agent',
            'backend developer' => 'backend_developer',
            'frontend developer' => 'frontend_developer',
            'fullstack developer' => 'fullstack_developer',
            'web designer' => 'web_designer',
            'interior designer' => 'interior_designer',
            'consultant' => 'consultant',
            'data analyst' => 'data_analyst',
            'database administrator' => 'database_administrator',
            'developer' => 'developer',
            'engineer' => 'engineer',
            'finance' => 'finance',
            'finance manager' => 'finance_manager',
            'fitness' => 'fitness',
            'fitness trainer' => 'fitness',
            'government' => 'government',
            'government officer' => 'government',
            'it support' => 'it_support',
            'lab technician' => 'lab_technician',
            'manager' => 'manager',
            'non profit' => 'non_profit',
            'office staff' => 'office_staff',
            'program manager' => 'program_manager',
            'property' => 'property',
            'property manager' => 'property',
            'public servant' => 'public_servant',
            'real estate' => 'real_estate',
            'real estate agent' => 'real_estate',
            'researcher' => 'researcher',
            'retail' => 'retail',
            'retail supervisor' => 'retail_supervisor',
            'scientist' => 'scientist',
            'security' => 'security',
            'security officer' => 'security',
            'service advisor' => 'service_advisor',
            'social worker' => 'social_worker',
            'supply chain' => 'supply_chain',
            'supply chain manager' => 'supply_chain',
            'trainer' => 'trainer',
            'training' => 'training',
            'technician' => 'technician',
            'maintenance technician' => 'maintenance',
            'lab technician' => 'lab_technician',
            'civil engineer' => 'civil_engineer',
            'mechanical engineer' => 'mechanical_engineer',
            'electrical engineer' => 'electrical_engineer',
            'architect' => 'architect',
            'quantity surveyor' => 'quantity_surveyor',
            'broker' => 'broker',
            'advisor' => 'advisor',
            'strategist' => 'strategist',
            'cleaner' => 'cleaner',
            'maintenance' => 'maintenance',
            'operator' => 'operator',
            'driver' => 'driver',
            'warehouse' => 'warehouse',
            'logistics' => 'logistics',
            'procurement' => 'procurement',
            'inventory' => 'inventory',
            'production' => 'production',
            'quality control' => 'quality_control',
            'nurse' => 'nurse',
            'doctor' => 'doctor',
            'pharmacist' => 'pharmacist',
            'therapist' => 'therapist',
            'medical' => 'medical',
            'teacher' => 'teacher',
            'lecturer' => 'lecturer',
            'instructor' => 'instructor',
            'tutor' => 'tutor',
            'coach' => 'coach',
            'lawyer' => 'lawyer',
            'paralegal' => 'paralegal',
            'legal' => 'legal',
            'notary' => 'notary',
            'civil servant' => 'civil_servant',
            'community' => 'community',
            'community organizer' => 'community',
            'ngo' => 'ngo',
            'ngo worker' => 'ngo',
            'environmental' => 'environmental',
            'environmental specialist' => 'environmental',
            'sustainability' => 'sustainability',
            'sustainability specialist' => 'sustainability',
            'construction' => 'construction',
            'construction worker' => 'construction',
            'artist' => 'artist',
            'illustrator' => 'illustrator',
            'auditor' => 'auditor',
            'bookkeeper' => 'bookkeeper',
            'tax' => 'tax',
            'tax specialist' => 'tax',
            'bartender' => 'bartender',
            'hotel staff' => 'hotel_staff',
            'safety officer' => 'safety_officer',
            'team lead' => 'team_lead',
            'supervisor' => 'supervisor',
            'director' => 'director',
            'head of' => 'head_of',
        ];
        
        // CRITICAL: First check if role file exists directly (1:1 mapping)
        // This ensures ALL 135 roles in data-linkedin/by_role/ are supported
        $roleFile = __DIR__ . '/../data-linkedin/by_role/' . $role . '.json';
        if (file_exists($roleFile)) {
            error_log("Role Mapping - Direct 1:1 file match: $role");
            return $role;
        }
        
        // Normalize role: replace underscore with space for matching
        $normalizedRole = str_replace('_', ' ', $role);
        
        // Check exact match with normalized role
        if (isset($categoryMap[$normalizedRole])) {
            error_log("Role Mapping - Exact match (normalized): $normalizedRole -> " . $categoryMap[$normalizedRole]);
            return $categoryMap[$normalizedRole];
        }
        
        // Check exact match with original role
        if (isset($categoryMap[$role])) {
            error_log("Role Mapping - Exact match (original): $role -> " . $categoryMap[$role]);
            return $categoryMap[$role];
        }
        
        // Try with normalized role as filename (space to underscore)
        $normalizedFile = __DIR__ . '/../data-linkedin/by_role/' . str_replace(' ', '_', $normalizedRole) . '.json';
        if (file_exists($normalizedFile)) {
            $categoryName = str_replace(' ', '_', $normalizedRole);
            error_log("Role Mapping - Normalized file match: $categoryName");
            return $categoryName;
        }
        
        // Check partial match (more intelligent) - ONLY if no direct file match
        foreach ($categoryMap as $key => $value) {
            // Check if role contains the key or key contains the role
            if (strpos($normalizedRole, $key) !== false || strpos($key, $normalizedRole) !== false) {
                error_log("Role Mapping - Partial match: $normalizedRole matched with $key -> $value");
                return $value;
            }
        }
        
        // Try fuzzy matching with available files
        $availableRoles = $this->getAllAvailableRoles();
        foreach ($availableRoles as $availableRole) {
            $availableRoleNormalized = str_replace('_', ' ', $availableRole);
            // Check similarity
            if (stripos($availableRoleNormalized, $normalizedRole) !== false || 
                stripos($normalizedRole, $availableRoleNormalized) !== false) {
                error_log("Role Mapping - Fuzzy match: $normalizedRole matched with $availableRole");
                return $availableRole;
            }
        }
        
        // CRITICAL: NO DEFAULT FALLBACK TO PROGRAMMER!
        // Return empty string and let the system handle it gracefully
        error_log("WARNING: No category mapping found for role: '$role' (normalized: '$normalizedRole')");
        error_log("Available roles count: " . count($availableRoles));
        return ''; // Return empty instead of wrong category
    }
    
    /**
     * Get related categories untuk expand search
     */
    private function getRelatedCategories($category) {
        $relatedMap = [
            // Security & Cybersecurity
            'cybersecurity' => ['network_administrator', 'IT_support', 'system_analyst'],
            
            // Customer Service & Support
            'customer_service' => ['customer_support', 'call_center', 'receptionist', 'admin'],
            'customer_support' => ['customer_service', 'call_center', 'it_support', 'helpdesk'],
            'call_center' => ['customer_service', 'customer_support', 'receptionist'],
            'it_support' => ['helpdesk', 'customer_support', 'technical_support'],
            'helpdesk' => ['it_support', 'customer_support', 'customer_service'],
            
            // Administrative
            'administrative_assistant' => ['secretary', 'receptionist', 'office_manager'],
            'secretary' => ['administrative_assistant', 'executive_assistant', 'receptionist'],
            'receptionist' => ['administrative_assistant', 'front_office', 'customer_service_representative'],
            'data_entry' => ['administrative_assistant', 'clerical_officer'],
            
            // Sales & Marketing
            'digital_marketing' => ['marketing', 'content_marketing', 'social_media', 'seo'],
            'marketing' => ['digital_marketing', 'content_marketing', 'social_media'],
            'content_marketing' => ['digital_marketing', 'marketing', 'content_writer', 'copywriter'],
            'social_media' => ['digital_marketing', 'marketing', 'content_marketing'],
            'sales' => ['sales_executive', 'account_executive', 'business_development'],
            'sales_executive' => ['sales', 'account_executive', 'account_manager'],
            'account_manager' => ['account_executive', 'sales_executive', 'business_development'],
            'account_executive' => ['account_manager', 'sales_executive', 'sales'],
            
            // Retail & Hospitality
            'cashier' => ['sales_assistant', 'shop_assistant', 'retail_supervisor'],
            'barista' => ['waiter', 'bartender', 'restaurant_crew'],
            'waiter' => ['barista', 'bartender', 'restaurant_crew'],
            'front_office' => ['receptionist', 'hotel_staff', 'customer_service_representative'],
            
            // Tech roles
            'software_engineer' => ['programmer', 'web_developer', 'devops_engineer'],
            'web_developer' => ['software_engineer', 'programmer', 'graphic_designer'],
            'programmer' => ['software_engineer', 'web_developer', 'IT_support'],
            'data_scientist' => ['analyst', 'research_analyst', 'data_entry'],
            'analyst' => ['data_scientist', 'financial_analyst', 'research_analyst'],
            'devops_engineer' => ['software_engineer', 'network_administrator', 'system_analyst'],
            'quality_assurance' => ['software_engineer', 'programmer', 'quality_control'],
            'graphic_designer' => ['web_developer', 'content_creator', 'photographer'],
            'project_manager' => ['operations_manager', 'team_leader', 'business_development'],
            'HR_manager' => ['recruiter', 'talent_acquisition', 'training_officer'],
        ];
        
        return $relatedMap[$category] ?? [];
    }
    
    /**
     * Filter jobs by skills dari CV
     */
    private function filterJobsBySkills($jobs, $cvData) {
        if (empty($cvData['skills'])) {
            return array_slice($jobs, 0, 20);
        }
        
        $skills = array_map('strtolower', $cvData['skills']);
        $filteredJobs = [];
        
        foreach ($jobs as $job) {
            $jobTitle = strtolower($job['title'] ?? '');
            $jobCategory = strtolower($job['category'] ?? '');
            
            // Check if any skill matches job title or category
            foreach ($skills as $skill) {
                if (strlen($skill) > 3 && 
                    (strpos($jobTitle, $skill) !== false || strpos($jobCategory, $skill) !== false)) {
                    $filteredJobs[] = $job;
                    break;
                }
            }
            
            if (count($filteredJobs) >= 20) break;
        }
        
        return $filteredJobs;
    }
    
    /**
     * Extract keywords dari CV data dengan analisis yang lebih pintar
     */
    private function extractKeywords($cvData) {
        $keywords = [];
        
        // 1. Analisis posisi terakhir untuk menentukan role yang cocok
        $targetRole = $this->determineTargetRole($cvData);
        if (!empty($targetRole)) {
            $keywords[] = $targetRole;
        }
        
        // 2. Ambil top skills yang relevan (max 2-3 skills paling penting)
        $topSkills = $this->getTopSkills($cvData);
        if (!empty($topSkills)) {
            $keywords = array_merge($keywords, $topSkills);
        }
        
        // 3. Fallback jika tidak ada data
        if (empty($keywords)) {
            $keywords[] = 'professional';
        }
        
        return implode(' ', $keywords);
    }
    
    /**
     * Get all available roles from data-linkedin/by_role/
     */
    private function getAllAvailableRoles() {
        static $cachedRoles = null;
        
        if ($cachedRoles !== null) {
            return $cachedRoles;
        }
        
        $rolesDir = $this->dataPath . 'by_role/';
        $files = glob($rolesDir . '*.json');
        $roles = [];
        
        foreach ($files as $file) {
            $roleName = basename($file, '.json');
            $roles[] = $roleName;
        }
        
        $cachedRoles = $roles;
        return $roles;
    }
    
    /**
     * Generate keyword variations for a role
     */
    private function generateRoleVariations($role) {
        // Convert underscore to space
        $roleDisplay = str_replace('_', ' ', $role);
        
        $variations = [];
        
        // Exact match (highest priority)
        $variations[] = $roleDisplay;
        
        // With "di bidang" (Indonesian)
        $variations[] = "minat tinggi di bidang $roleDisplay";
        $variations[] = "minat di bidang $roleDisplay";
        $variations[] = "bidang $roleDisplay";
        $variations[] = "di bidang $roleDisplay";
        
        // With "sebagai"
        $variations[] = "sebagai $roleDisplay";
        $variations[] = "bekerja sebagai $roleDisplay";
        
        // With "posisi"
        $variations[] = "posisi $roleDisplay";
        
        // Specialist/Manager/Executive variations
        $variations[] = "$roleDisplay specialist";
        $variations[] = "$roleDisplay manager";
        $variations[] = "$roleDisplay executive";
        $variations[] = "$roleDisplay officer";
        $variations[] = "$roleDisplay coordinator";
        $variations[] = "$roleDisplay representative";
        
        // Sort by length (longest first for better matching)
        usort($variations, function($a, $b) {
            return strlen($b) - strlen($a);
        });
        
        return $variations;
    }
    
    /**
     * Deteksi role dari About Me / Summary (PRIORITAS TERTINGGI)
     * DYNAMIC MATCHING - Automatically covers ALL 140+ roles
     */
    private function detectRoleFromAboutMe($cvData) {
        if (empty($cvData['summary'])) {
            return '';
        }
        
        $summary = strtolower($cvData['summary']);
        
        // Special handling for UI/UX with slash - normalize it
        $summary = str_replace('ui/ux', 'ui ux', $summary);
        $summary = str_replace('desainer ui/ux', 'ui ux designer', $summary);
        $summary = str_replace('desainer ui ux', 'ui ux designer', $summary);
        
        // CRITICAL: Check for exact phrases FIRST to avoid false matches
        $exactPhrases = [
            // Tech - Engineers (most specific first)
            'machine learning engineer' => 'machine_learning_engineer',
            'software engineer' => 'software_engineer',
            'ai engineer' => 'ai_engineer',
            'data engineer' => 'data_engineer',
            'devops engineer' => 'devops_engineer',
            'cloud engineer' => 'cloud_engineer',
            'network engineer' => 'network_engineer',
            'qa engineer' => 'quality_assurance',
            'quality assurance' => 'quality_assurance',
            
            // Tech - Developers (most specific first)
            'mobile developer' => 'mobile_developer',
            'web developer' => 'web_developer',
            'frontend developer' => 'frontend_developer',
            'backend developer' => 'backend_developer',
            'fullstack developer' => 'fullstack_developer',
            'web designer' => 'web_designer',
            
            // Tech - Admin (most specific first)
            'system administrator' => 'system_administrator',
            'database administrator' => 'database_administrator',
            
            // Management (most specific first)
            'product manager' => 'product_manager',
            'project manager' => 'project_manager',
            'program manager' => 'program_manager',
            'property manager' => 'property',
            
            // Design (most specific first)
            'interior designer' => 'interior_designer',
            'web designer' => 'web_designer',
            
            // Content & Writing (check before "content marketing")
            'content writer' => 'content_writer',
            
            // Retail & Hospitality (check before "customer service")
            'sebagai kasir' => 'cashier',
            'sebagai barista' => 'barista',
            
            // Real Estate & Property (most specific first)
            'real estate agent' => 'real_estate',
            'property manager' => 'property',
            'real estate' => 'real_estate',
            
            // Operations & Logistics (most specific first)
            'logistics coordinator' => 'logistics',
            'supply chain manager' => 'supply_chain',
            'supply chain' => 'supply_chain',
            
            // Services & Other (most specific first)
            'service advisor' => 'service_advisor',
            'sales agent' => 'agent',
            'insurance agent' => 'agent',
            'insurance broker' => 'broker',
            'real estate broker' => 'broker',
            'ngo worker' => 'ngo',
            'non profit' => 'non_profit',
            
            // Government & NGO
            'public servant' => 'public_servant',
            'government officer' => 'government',
            'social worker' => 'social_worker',
            'non profit' => 'non_profit',
            'non-profit' => 'non_profit',
            
            // Operations
            'supply chain manager' => 'supply_chain',
            'supply chain' => 'supply_chain',
            
            // Healthcare
            'lab technician' => 'lab_technician',
            
            // Other
            'fitness trainer' => 'fitness',
            'office staff' => 'office_staff',
            
            // Marketing (after content writer)
            'content marketing' => 'content_marketing',
            
            // Security (check "cybersecurity" before "security")
            'cybersecurity professional' => 'cybersecurity',
            'cybersecurity' => 'cybersecurity',
        ];
        
        // Sort by length (longest first)
        uksort($exactPhrases, function($a, $b) {
            return strlen($b) - strlen($a);
        });
        
        foreach ($exactPhrases as $phrase => $role) {
            if (strpos($summary, $phrase) !== false) {
                error_log("Role Detection - EXACT PHRASE match: '$phrase' -> $role");
                return $role;
            }
        }
        
        // Get all available roles dynamically
        $allRoles = $this->getAllAvailableRoles();
        
        // Priority roles (check these first)
        // MOST SPECIFIC roles should be checked FIRST to avoid false matches
        $priorityRoles = [
            // TECH ROLES - SPECIFIC (HIGHEST PRIORITY to avoid "engineer" false match)
            'software_engineer',
            'machine_learning_engineer',
            'ai_engineer',
            'data_engineer',
            'devops_engineer',
            'cloud_engineer',
            'network_engineer',
            'system_administrator', // Before 'admin'
            'database_administrator',
            'qa_engineer',
            'quality_assurance',
            'lab_technician', // Before 'technician'
            
            // TECH ROLES - DEVELOPMENT
            'frontend_developer',
            'backend_developer',
            'fullstack_developer',
            'web_developer',
            'mobile_developer',
            'programmer',
            'developer',
            
            // TECH ROLES - DATA & AI
            'data_scientist',
            'data_analyst',
            
            // MANAGEMENT - SPECIFIC (before generic "manager")
            'project_manager',
            'product_manager',
            'program_manager',
            
            // Customer Service & Support
            'customer_service',
            'customer_support',
            'call_center',
            'receptionist',
            
            // Administrative & Office (after system_administrator)
            'administrative',
            'secretary',
            'office_staff',
            'admin', // After system_administrator
            
            // Retail & Hospitality - SPECIFIC
            'barista', // Before customer_service
            'cashier',
            'waiter',
            'chef',
            'store_manager',
            'hotel_staff',
            'retail',
            
            // Sales & Marketing
            'sales_executive',
            'account_executive',
            'business_development',
            'digital_marketing',
            'content_marketing',
            'social_media',
            'seo',
            'sales',
            'marketing',
            
            // HR & Finance
            'human_resources',
            'recruiter',
            'talent_acquisition',
            'accountant',
            'financial_analyst',
            'bookkeeper',
            'finance',
            'hr',
            
            // Healthcare & Medical
            'nurse',
            'doctor',
            'pharmacist',
            'therapist',
            'medical',
            
            // Education
            'teacher',
            'lecturer',
            'instructor',
            'tutor',
            'trainer',
            
            // Legal
            'lawyer',
            'paralegal',
            'legal',
            
            // Operations & Logistics
            'supply_chain', // Before 'logistics'
            'driver',
            'warehouse',
            'logistics',
            'procurement',
            
            // Maintenance & Service
            'cleaner',
            'maintenance',
            'technician',
            'safety_officer',
            'security',
            
            // Design & Creative
            'graphic_designer',
            'ui_ux_designer',
            'interior_designer', // Before 'designer'
            'web_designer', // Before 'designer'
            'designer',
            'photographer',
            'videographer',
            'illustrator',
            
            // Content & Writing - SPECIFIC
            'content_writer', // Before marketing
            'copywriter',
            'writer',
            'editor',
            'journalist',
            
            // Management - GENERIC (checked last)
            'operations_manager',
            'manager',
            'director',
            
            // Engineering (Non-Software)
            'civil_engineer',
            'mechanical_engineer',
            'electrical_engineer',
            'architect',
            
            // TECH ROLES (LOWEST PRIORITY - checked last)
            // These are checked last to avoid false matches
        ];
        
        // Check priority roles first
        foreach ($priorityRoles as $role) {
            if (!in_array($role, $allRoles)) continue;
            
            $variations = $this->generateRoleVariations($role);
            foreach ($variations as $variation) {
                if (strpos($summary, $variation) !== false) {
                    error_log("Role Detection - Matched '$variation' -> $role");
                    return $role;
                }
            }
        }
        
        // Then check all other roles
        foreach ($allRoles as $role) {
            if (in_array($role, $priorityRoles)) continue; // Skip already checked
            
            $variations = $this->generateRoleVariations($role);
            foreach ($variations as $variation) {
                if (strpos($summary, $variation) !== false) {
                    error_log("Role Detection - Matched '$variation' -> $role");
                    return $role;
                }
            }
        }
        
        // Role keywords dengan prioritas tinggi - LENGKAP SEMUA ROLE
        // URUTAN PENTING: Yang lebih spesifik/panjang harus di atas!
        $roleKeywords = [
            // Customer Service & Support (PRIORITAS TINGGI - DI ATAS SEMUA)
            'minat tinggi di bidang customer service' => 'customer_service',
            'minat di bidang customer service' => 'customer_service',
            'bidang customer service' => 'customer_service',
            'di bidang customer service' => 'customer_service',
            'customer service representative' => 'customer_service',
            'customer service specialist' => 'customer_service',
            'customer service' => 'customer_service',
            'pelayanan pelanggan' => 'customer_service',
            'customer support specialist' => 'customer_support',
            'customer support' => 'customer_support',
            'call center agent' => 'call_center',
            'call center' => 'call_center',
            'customer care' => 'customer_service',
            
            // Cybersecurity & Security
            'cybersecurity professional' => 'cybersecurity',
            'security professional' => 'cybersecurity',
            'cybersecurity specialist' => 'cybersecurity',
            'security specialist' => 'security',
            'cybersecurity analyst' => 'cybersecurity',
            'security analyst' => 'cybersecurity',
            'penetration tester' => 'cybersecurity',
            'ethical hacker' => 'cybersecurity',
            'security officer' => 'security',
            'safety officer' => 'safety_officer',
            
            // Marketing & Sales
            'digital marketing specialist' => 'digital_marketing',
            'digital marketing manager' => 'digital_marketing',
            'digital marketing' => 'digital_marketing',
            'marketing specialist' => 'marketing',
            'marketing manager' => 'marketing',
            'marketing executive' => 'marketing',
            'content marketing specialist' => 'content_marketing',
            'content marketing manager' => 'content_marketing',
            'content marketing' => 'content_marketing',
            'social media specialist' => 'social_media',
            'social media manager' => 'social_media',
            'seo specialist' => 'seo',
            'seo analyst' => 'seo',
            'sales executive' => 'sales_executive',
            'sales manager' => 'sales',
            'account executive' => 'account_executive',
            'account manager' => 'account_executive',
            'business development manager' => 'business_development',
            'business development' => 'business_development',
            
            // Data & Analytics
            'data scientist' => 'data_scientist',
            'data analyst' => 'data_analyst',
            'data engineer' => 'data_scientist',
            'machine learning engineer' => 'machine_learning_engineer',
            'ai engineer' => 'ai_engineer',
            'business analyst' => 'analyst',
            'financial analyst' => 'financial_analyst',
            
            // Software Development
            'software engineer' => 'software_engineer',
            'software developer' => 'software_engineer',
            'full stack developer' => 'fullstack_developer',
            'fullstack developer' => 'fullstack_developer',
            'frontend developer' => 'frontend_developer',
            'backend developer' => 'backend_developer',
            'web developer' => 'web_developer',
            'web designer' => 'web_designer',
            'mobile developer' => 'mobile_developer',
            'programmer' => 'programmer',
            'developer' => 'developer',
            
            // DevOps & Infrastructure
            'devops engineer' => 'devops_engineer',
            'cloud engineer' => 'cloud_engineer',
            'system administrator' => 'system_administrator',
            'network engineer' => 'network_engineer',
            'database administrator' => 'database_administrator',
            'it support specialist' => 'it_support',
            'it support' => 'it_support',
            'helpdesk' => 'helpdesk',
            'technical support' => 'it_support',
            
            // Design & Creative
            'ui/ux designer' => 'ui_ux_designer',
            'ui ux designer' => 'ui_ux_designer',
            'graphic designer' => 'graphic_designer',
            'designer' => 'designer',
            'illustrator' => 'illustrator',
            'photographer' => 'photographer',
            'videographer' => 'videographer',
            'interior designer' => 'interior_designer',
            'artist' => 'artist',
            
            // Content & Writing
            'content writer' => 'content_writer',
            'copywriter' => 'copywriter',
            'editor' => 'editor',
            'journalist' => 'journalist',
            
            // Management
            'product manager' => 'product_manager',
            'project manager' => 'project_manager',
            'program manager' => 'program_manager',
            'operations manager' => 'operations_manager',
            'store manager' => 'store_manager',
            'manager' => 'manager',
            'director' => 'director',
            'head of' => 'head_of',
            'team lead' => 'team_lead',
            'supervisor' => 'supervisor',
            
            // HR & Recruitment
            'hr manager' => 'hr',
            'human resources manager' => 'human_resources',
            'human resources' => 'human_resources',
            'recruiter' => 'recruiter',
            'talent acquisition specialist' => 'talent_acquisition',
            'talent acquisition' => 'talent_acquisition',
            'trainer' => 'trainer',
            'training specialist' => 'training',
            
            // Finance & Accounting
            'accountant' => 'accountant',
            'financial analyst' => 'financial_analyst',
            'finance manager' => 'finance',
            'bookkeeper' => 'bookkeeper',
            'auditor' => 'auditor',
            'tax specialist' => 'tax',
            
            // Administrative
            'administrative assistant' => 'administrative',
            'admin assistant' => 'admin',
            'secretary' => 'secretary',
            'receptionist' => 'receptionist',
            'office staff' => 'office_staff',
            
            // Engineering (Non-Software)
            'civil engineer' => 'civil_engineer',
            'mechanical engineer' => 'mechanical_engineer',
            'electrical engineer' => 'electrical_engineer',
            'engineer' => 'engineer',
            'architect' => 'architect',
            
            // Healthcare & Medical
            'doctor' => 'doctor',
            'nurse' => 'nurse',
            'pharmacist' => 'pharmacist',
            'therapist' => 'therapist',
            'medical officer' => 'medical',
            'lab technician' => 'lab_technician',
            
            // Education
            'teacher' => 'teacher',
            'lecturer' => 'lecturer',
            'instructor' => 'instructor',
            'tutor' => 'tutor',
            'coach' => 'coach',
            
            // Legal
            'lawyer' => 'lawyer',
            'paralegal' => 'paralegal',
            'legal officer' => 'legal',
            'notary' => 'notary',
            
            // Consulting & Advisory
            'consultant' => 'consultant',
            'advisor' => 'advisor',
            'strategist' => 'strategist',
            
            // Operations & Logistics
            'logistics coordinator' => 'logistics',
            'logistics' => 'logistics',
            'supply chain manager' => 'supply_chain',
            'supply chain' => 'supply_chain',
            'procurement specialist' => 'procurement',
            'procurement' => 'procurement',
            'warehouse supervisor' => 'warehouse',
            'warehouse' => 'warehouse',
            'inventory specialist' => 'inventory',
            
            // Quality & Production
            'quality control specialist' => 'quality_control',
            'quality control' => 'quality_control',
            'production supervisor' => 'production',
            'production' => 'production',
            
            // Hospitality & Service
            'chef' => 'chef',
            'waiter' => 'waiter',
            'waitress' => 'waiter',
            'cashier' => 'cashier',
            'hotel staff' => 'hotel_staff',
            'service advisor' => 'service_advisor',
            
            // Retail
            'retail supervisor' => 'retail',
            'retail' => 'retail',
            
            // Real Estate & Property
            'real estate agent' => 'real_estate',
            'real estate' => 'real_estate',
            'property manager' => 'property',
            'broker' => 'broker',
            'quantity surveyor' => 'quantity_surveyor',
            
            // Research & Science
            'researcher' => 'researcher',
            'scientist' => 'scientist',
            
            // Government & Public Service
            'civil servant' => 'civil_servant',
            'public servant' => 'public_servant',
            'government officer' => 'government',
            
            // NGO & Non-Profit
            'social worker' => 'social_worker',
            'community organizer' => 'community',
            'ngo worker' => 'ngo',
            
            // Other Specialized Roles
            'driver' => 'driver',
            'technician' => 'technician',
            'operator' => 'operator',
            'maintenance technician' => 'maintenance',
            'cleaner' => 'cleaner',
            'construction worker' => 'construction',
            'agent' => 'agent',
            'fitness trainer' => 'fitness',
            'environmental specialist' => 'environmental',
            'sustainability specialist' => 'sustainability',
        ];
        
        // Check for exact role mentions in summary
        foreach ($roleKeywords as $keyword => $role) {
            if (strpos($summary, $keyword) !== false) {
                return $role;
            }
        }
        
        // Check for domain mentions (broader keywords)
        $domainKeywords = [
            // Tech & Security
            'cybersecurity' => 'cybersecurity',
            'cyber security' => 'cybersecurity',
            'information security' => 'cybersecurity',
            'network security' => 'cybersecurity',
            'data science' => 'data_scientist',
            'machine learning' => 'machine_learning_engineer',
            'artificial intelligence' => 'ai_engineer',
            'web development' => 'web_developer',
            'mobile development' => 'mobile_developer',
            'cloud computing' => 'cloud_engineer',
            'devops' => 'devops_engineer',
            
            // Marketing & Sales
            'digital marketing' => 'digital_marketing',
            'content marketing' => 'content_marketing',
            'social media marketing' => 'social_media',
            'search engine optimization' => 'seo',
            
            // Business & Finance
            'accounting' => 'accountant',
            'finance' => 'finance',
            'human resources' => 'human_resources',
            'business development' => 'business_development',
            
            // Design & Creative
            'graphic design' => 'graphic_designer',
            'ui/ux design' => 'ui_ux_designer',
            'interior design' => 'interior_designer',
            
            // Healthcare
            'healthcare' => 'medical',
            'nursing' => 'nurse',
            
            // Education
            'teaching' => 'teacher',
            'education' => 'teacher',
            
            // Legal
            'law' => 'lawyer',
            'legal services' => 'legal',
            
            // Operations
            'logistics' => 'logistics',
            'supply chain' => 'supply_chain',
            'operations' => 'operations_manager',
        ];
        
        foreach ($domainKeywords as $keyword => $role) {
            if (strpos($summary, $keyword) !== false) {
                return $role;
            }
        }
        
        return '';
    }
    
    /**
     * Verify role dengan pengalaman kerja
     * Jika ada pengalaman kerja yang relevan dengan role yang terdeteksi, tonjolkan itu
     */
    private function verifyRoleWithExperience($detectedRole, $cvData) {
        if (empty($cvData['positions']) || !is_array($cvData['positions'])) {
            return $detectedRole;
        }
        
        // Check if any position matches the detected role
        foreach ($cvData['positions'] as $position) {
            $positionTitle = strtolower($position['title'] ?? '');
            $positionDesc = strtolower($position['description'] ?? '');
            
            // Map detected role to keywords
            $roleKeywords = $this->getRoleKeywords($detectedRole);
            
            // Check if position title or description contains role keywords
            foreach ($roleKeywords as $keyword) {
                if (strpos($positionTitle, $keyword) !== false || 
                    strpos($positionDesc, $keyword) !== false) {
                    // Found relevant experience, return detected role with confidence
                    return $detectedRole;
                }
            }
        }
        
        // No relevant experience found, but still return detected role
        // (About Me/Passion is still strong indicator)
        return $detectedRole;
    }
    
    /**
     * Get keywords for a role (for verification)
     */
    private function getRoleKeywords($role) {
        $keywordMap = [
            // Tech & Security
            'cybersecurity' => ['security', 'cyber', 'penetration', 'vulnerability', 'firewall', 'siem'],
            'security' => ['security', 'guard', 'surveillance', 'protection'],
            'safety_officer' => ['safety', 'health', 'hse', 'occupational'],
            
            // Marketing & Sales
            'digital_marketing' => ['marketing', 'digital', 'seo', 'sem', 'social media', 'content', 'campaign'],
            'marketing' => ['marketing', 'campaign', 'brand', 'promotion', 'advertising'],
            'content_marketing' => ['content', 'marketing', 'copywriting', 'blog', 'article'],
            'social_media' => ['social media', 'instagram', 'facebook', 'twitter', 'linkedin', 'tiktok'],
            'seo' => ['seo', 'search engine', 'optimization', 'google', 'ranking'],
            'sales' => ['sales', 'selling', 'revenue', 'target', 'customer'],
            'sales_executive' => ['sales', 'executive', 'client', 'deal', 'negotiation'],
            'account_executive' => ['account', 'client', 'relationship', 'business'],
            'business_development' => ['business', 'development', 'partnership', 'growth'],
            
            // Data & Analytics
            'data_scientist' => ['data', 'analytics', 'machine learning', 'statistics', 'python'],
            'data_analyst' => ['data', 'analytics', 'analysis', 'reporting', 'sql'],
            'analyst' => ['analysis', 'research', 'reporting', 'insights'],
            'financial_analyst' => ['financial', 'analysis', 'budget', 'forecast'],
            'machine_learning_engineer' => ['machine learning', 'ml', 'ai', 'deep learning'],
            'ai_engineer' => ['ai', 'artificial intelligence', 'neural network', 'nlp'],
            
            // Software Development
            'software_engineer' => ['software', 'engineer', 'development', 'programming', 'coding'],
            'developer' => ['development', 'coding', 'programming', 'software'],
            'programmer' => ['programming', 'coding', 'software', 'development'],
            'web_developer' => ['web', 'frontend', 'backend', 'fullstack', 'html', 'css', 'javascript'],
            'web_designer' => ['web', 'design', 'html', 'css', 'ui'],
            'frontend_developer' => ['frontend', 'react', 'vue', 'angular', 'javascript'],
            'backend_developer' => ['backend', 'api', 'server', 'database', 'node'],
            'fullstack_developer' => ['fullstack', 'full stack', 'frontend', 'backend'],
            'mobile_developer' => ['mobile', 'android', 'ios', 'app', 'flutter', 'react native'],
            
            // DevOps & Infrastructure
            'devops_engineer' => ['devops', 'ci/cd', 'docker', 'kubernetes', 'jenkins'],
            'cloud_engineer' => ['cloud', 'aws', 'azure', 'gcp'],
            'system_administrator' => ['system', 'administrator', 'server', 'network', 'linux'],
            'network_engineer' => ['network', 'cisco', 'routing', 'switching', 'firewall'],
            'database_administrator' => ['database', 'sql', 'mysql', 'postgresql', 'oracle'],
            'it_support' => ['it', 'support', 'helpdesk', 'troubleshooting', 'technical'],
            'helpdesk' => ['helpdesk', 'support', 'ticket', 'troubleshooting'],
            
            // Design & Creative
            'ui_ux_designer' => ['ui', 'ux', 'user interface', 'user experience', 'figma'],
            'graphic_designer' => ['graphic', 'design', 'photoshop', 'illustrator', 'visual'],
            'designer' => ['design', 'creative', 'visual', 'layout'],
            'illustrator' => ['illustration', 'drawing', 'artwork', 'graphic'],
            'photographer' => ['photography', 'photo', 'camera', 'shooting'],
            'videographer' => ['video', 'filming', 'editing', 'production'],
            'interior_designer' => ['interior', 'design', 'space', 'furniture'],
            'artist' => ['art', 'creative', 'artwork', 'design'],
            
            // Content & Writing
            'content_writer' => ['content', 'writing', 'article', 'blog'],
            'copywriter' => ['copywriting', 'copy', 'advertising', 'creative'],
            'editor' => ['editing', 'proofreading', 'content', 'publication'],
            'journalist' => ['journalism', 'news', 'reporting', 'media'],
            
            // Management
            'product_manager' => ['product', 'management', 'roadmap', 'feature'],
            'project_manager' => ['project', 'management', 'planning', 'coordination'],
            'program_manager' => ['program', 'management', 'portfolio', 'strategy'],
            'operations_manager' => ['operations', 'management', 'process', 'efficiency'],
            'store_manager' => ['store', 'retail', 'management', 'sales'],
            'manager' => ['management', 'team', 'leadership', 'coordination'],
            'director' => ['director', 'leadership', 'strategy', 'executive'],
            'head_of' => ['head', 'leadership', 'department', 'management'],
            'team_lead' => ['team', 'lead', 'leadership', 'coordination'],
            'supervisor' => ['supervisor', 'oversight', 'team', 'management'],
            
            // HR & Recruitment
            'hr' => ['hr', 'human resources', 'recruitment', 'employee'],
            'human_resources' => ['human resources', 'hr', 'recruitment', 'employee'],
            'recruiter' => ['recruitment', 'hiring', 'talent', 'candidate'],
            'talent_acquisition' => ['talent', 'acquisition', 'recruitment', 'hiring'],
            'trainer' => ['training', 'teaching', 'development', 'workshop'],
            'training' => ['training', 'development', 'learning', 'education'],
            
            // Finance & Accounting
            'accountant' => ['accounting', 'bookkeeping', 'financial', 'ledger'],
            'finance' => ['finance', 'financial', 'budget', 'accounting'],
            'bookkeeper' => ['bookkeeping', 'accounting', 'ledger', 'financial'],
            'auditor' => ['audit', 'auditing', 'compliance', 'financial'],
            'tax' => ['tax', 'taxation', 'compliance', 'filing'],
            
            // Customer Service & Support
            'customer_service' => ['customer', 'service', 'support', 'client'],
            'customer_support' => ['customer', 'support', 'service', 'assistance'],
            'call_center' => ['call center', 'phone', 'customer', 'support'],
            
            // Administrative
            'administrative' => ['administrative', 'admin', 'office', 'clerical'],
            'admin' => ['admin', 'administrative', 'office', 'support'],
            'secretary' => ['secretary', 'administrative', 'office', 'clerical'],
            'receptionist' => ['receptionist', 'front desk', 'reception', 'office'],
            'office_staff' => ['office', 'administrative', 'clerical', 'support'],
            
            // Engineering (Non-Software)
            'civil_engineer' => ['civil', 'engineering', 'construction', 'structural'],
            'mechanical_engineer' => ['mechanical', 'engineering', 'machine', 'design'],
            'electrical_engineer' => ['electrical', 'engineering', 'circuit', 'power'],
            'engineer' => ['engineering', 'technical', 'design', 'development'],
            'architect' => ['architecture', 'design', 'building', 'construction'],
            
            // Healthcare & Medical
            'doctor' => ['doctor', 'medical', 'physician', 'healthcare'],
            'nurse' => ['nursing', 'nurse', 'healthcare', 'patient'],
            'pharmacist' => ['pharmacy', 'pharmacist', 'medication', 'drug'],
            'therapist' => ['therapy', 'therapist', 'counseling', 'treatment'],
            'medical' => ['medical', 'healthcare', 'clinical', 'patient'],
            'lab_technician' => ['laboratory', 'lab', 'testing', 'analysis'],
            
            // Education
            'teacher' => ['teaching', 'teacher', 'education', 'classroom'],
            'lecturer' => ['lecturer', 'teaching', 'university', 'academic'],
            'instructor' => ['instructor', 'teaching', 'training', 'education'],
            'tutor' => ['tutoring', 'tutor', 'teaching', 'education'],
            'coach' => ['coaching', 'coach', 'training', 'development'],
            
            // Legal
            'lawyer' => ['lawyer', 'legal', 'law', 'attorney'],
            'paralegal' => ['paralegal', 'legal', 'assistant', 'law'],
            'legal' => ['legal', 'law', 'compliance', 'contract'],
            'notary' => ['notary', 'legal', 'document', 'certification'],
            
            // Consulting & Advisory
            'consultant' => ['consulting', 'consultant', 'advisory', 'strategy'],
            'advisor' => ['advisor', 'advisory', 'consulting', 'guidance'],
            'strategist' => ['strategy', 'strategist', 'planning', 'business'],
            
            // Operations & Logistics
            'logistics' => ['logistics', 'supply', 'distribution', 'transportation'],
            'supply_chain' => ['supply chain', 'logistics', 'procurement', 'inventory'],
            'procurement' => ['procurement', 'purchasing', 'sourcing', 'vendor'],
            'warehouse' => ['warehouse', 'storage', 'inventory', 'logistics'],
            'inventory' => ['inventory', 'stock', 'warehouse', 'management'],
            
            // Quality & Production
            'quality_control' => ['quality', 'control', 'inspection', 'testing'],
            'production' => ['production', 'manufacturing', 'assembly', 'operations'],
            
            // Hospitality & Service
            'chef' => ['chef', 'cooking', 'culinary', 'kitchen'],
            'waiter' => ['waiter', 'waitress', 'server', 'restaurant'],
            'cashier' => ['cashier', 'cash', 'register', 'payment'],
            'hotel_staff' => ['hotel', 'hospitality', 'guest', 'service'],
            'service_advisor' => ['service', 'advisor', 'customer', 'consultation'],
            
            // Retail
            'retail' => ['retail', 'store', 'sales', 'customer'],
            
            // Real Estate & Property
            'real_estate' => ['real estate', 'property', 'broker', 'agent'],
            'property' => ['property', 'real estate', 'management', 'leasing'],
            'broker' => ['broker', 'agent', 'sales', 'property'],
            'quantity_surveyor' => ['quantity', 'surveyor', 'construction', 'cost'],
            
            // Research & Science
            'researcher' => ['research', 'researcher', 'study', 'analysis'],
            'scientist' => ['scientist', 'science', 'research', 'laboratory'],
            
            // Government & Public Service
            'civil_servant' => ['civil', 'servant', 'government', 'public'],
            'public_servant' => ['public', 'servant', 'government', 'service'],
            'government' => ['government', 'public', 'administration', 'policy'],
            
            // NGO & Non-Profit
            'social_worker' => ['social', 'worker', 'community', 'welfare'],
            'community' => ['community', 'social', 'development', 'outreach'],
            'ngo' => ['ngo', 'non-profit', 'charity', 'social'],
            'non_profit' => ['non-profit', 'ngo', 'charity', 'social'],
            
            // Other Specialized Roles
            'driver' => ['driver', 'driving', 'transportation', 'delivery'],
            'technician' => ['technician', 'technical', 'maintenance', 'repair'],
            'operator' => ['operator', 'operation', 'machine', 'equipment'],
            'maintenance' => ['maintenance', 'repair', 'technical', 'facility'],
            'cleaner' => ['cleaning', 'cleaner', 'housekeeping', 'janitorial'],
            'construction' => ['construction', 'building', 'site', 'contractor'],
            'agent' => ['agent', 'representative', 'sales', 'service'],
            'fitness' => ['fitness', 'trainer', 'gym', 'exercise'],
            'environmental' => ['environmental', 'sustainability', 'ecology', 'conservation'],
            'sustainability' => ['sustainability', 'environmental', 'green', 'conservation'],
        ];
        
        return $keywordMap[$role] ?? [];
    }
    
    /**
     * Deteksi role dari certifications
     */
    private function detectRoleFromCertifications($cvData) {
        if (empty($cvData['certifications']) || !is_array($cvData['certifications'])) {
            return '';
        }
        
        $certKeywords = [];
        foreach ($cvData['certifications'] as $cert) {
            $certName = strtolower($cert['name'] ?? '');
            $certIssuer = strtolower($cert['issuer'] ?? '');
            $certKeywords[] = $certName . ' ' . $certIssuer;
        }
        
        $allCerts = implode(' ', $certKeywords);
        
        // Count certifications by domain
        $domainCounts = [
            'cybersecurity' => 0,
            'data_scientist' => 0,
            'cloud_engineer' => 0,
            'web_developer' => 0,
            'project_manager' => 0,
        ];
        
        // Cybersecurity keywords
        $cyberKeywords = ['cyber', 'security', 'penetration', 'ethical hacking', 'hacking', 
                          'ctf', 'nmap', 'infosec', 'soc', 'vulnerability'];
        foreach ($cyberKeywords as $keyword) {
            if (strpos($allCerts, $keyword) !== false) {
                $domainCounts['cybersecurity']++;
            }
        }
        
        // Data Science keywords
        $dataKeywords = ['data science', 'machine learning', 'data analyst', 'python', 'tensorflow'];
        foreach ($dataKeywords as $keyword) {
            if (strpos($allCerts, $keyword) !== false) {
                $domainCounts['data_scientist']++;
            }
        }
        
        // Cloud keywords
        $cloudKeywords = ['aws', 'azure', 'gcp', 'cloud', 'kubernetes', 'docker'];
        foreach ($cloudKeywords as $keyword) {
            if (strpos($allCerts, $keyword) !== false) {
                $domainCounts['cloud_engineer']++;
            }
        }
        
        // Web Dev keywords
        $webKeywords = ['web development', 'javascript', 'react', 'frontend', 'backend'];
        foreach ($webKeywords as $keyword) {
            if (strpos($allCerts, $keyword) !== false) {
                $domainCounts['web_developer']++;
            }
        }
        
        // Find domain with most certifications
        arsort($domainCounts);
        $topDomain = array_key_first($domainCounts);
        $topCount = $domainCounts[$topDomain];
        
        // If 3+ certifications in one domain, that's the target role
        if ($topCount >= 3) {
            return $topDomain;
        }
        
        return '';
    }
    
    /**
     * Deteksi passion/interest dari summary
     */
    private function detectPassionFromSummary($cvData) {
        if (empty($cvData['summary'])) {
            return '';
        }
        
        $summary = strtolower($cvData['summary']);
        
        // Passion indicators dengan berbagai variasi
        $passionPhrases = [
            'passion for' => '',
            'passionate about' => '',
            'strong passion for' => '',
            'interested in' => '',
            'deeply interested in' => '',
            'expertise in' => '',
            'specializing in' => '',
            'focus on' => '',
            'dedicated to' => '',
            'particularly' => '', // "particularly Cyber Security"
        ];
        
        // Find what comes after passion phrases
        foreach ($passionPhrases as $phrase => $value) {
            $pos = strpos($summary, $phrase);
            if ($pos !== false) {
                // Extract text after the phrase (next 80 chars for better context)
                $afterPhrase = substr($summary, $pos + strlen($phrase), 80);
                
                // Check for role keywords (PRIORITAS TINGGI KE RENDAH) - LENGKAP
                $roleKeywords = [
                    // Cybersecurity & Security
                    'cyber security' => 'cybersecurity',
                    'cybersecurity' => 'cybersecurity',
                    'information security' => 'cybersecurity',
                    'network security' => 'cybersecurity',
                    'security' => 'security',
                    
                    // Marketing & Sales
                    'digital marketing' => 'digital_marketing',
                    'marketing' => 'marketing',
                    'content marketing' => 'content_marketing',
                    'social media' => 'social_media',
                    'seo' => 'seo',
                    'sem' => 'digital_marketing',
                    'sales' => 'sales',
                    
                    // Data & Analytics
                    'data science' => 'data_scientist',
                    'data analysis' => 'data_analyst',
                    'machine learning' => 'machine_learning_engineer',
                    'artificial intelligence' => 'ai_engineer',
                    'ai' => 'ai_engineer',
                    
                    // Development
                    'web development' => 'web_developer',
                    'mobile development' => 'mobile_developer',
                    'software development' => 'software_engineer',
                    'full stack' => 'fullstack_developer',
                    'frontend' => 'frontend_developer',
                    'backend' => 'backend_developer',
                    'programming' => 'programmer',
                    
                    // Cloud & DevOps
                    'cloud computing' => 'cloud_engineer',
                    'cloud' => 'cloud_engineer',
                    'devops' => 'devops_engineer',
                    
                    // Design & Creative
                    'ui/ux' => 'ui_ux_designer',
                    'graphic design' => 'graphic_designer',
                    'design' => 'designer',
                    'photography' => 'photographer',
                    'videography' => 'videographer',
                    
                    // Management
                    'project management' => 'project_manager',
                    'product management' => 'product_manager',
                    'operations' => 'operations_manager',
                    'management' => 'manager',
                    
                    // HR & Recruitment
                    'human resources' => 'human_resources',
                    'recruitment' => 'recruiter',
                    'talent acquisition' => 'talent_acquisition',
                    
                    // Finance & Accounting
                    'accounting' => 'accountant',
                    'finance' => 'finance',
                    'auditing' => 'auditor',
                    
                    // Customer Service
                    'customer service' => 'customer_service',
                    'customer support' => 'customer_support',
                    
                    // Healthcare
                    'healthcare' => 'medical',
                    'nursing' => 'nurse',
                    'medical' => 'medical',
                    
                    // Education
                    'teaching' => 'teacher',
                    'education' => 'teacher',
                    'training' => 'trainer',
                    
                    // Legal
                    'law' => 'lawyer',
                    'legal' => 'legal',
                    
                    // Operations & Logistics
                    'logistics' => 'logistics',
                    'supply chain' => 'supply_chain',
                    'procurement' => 'procurement',
                    
                    // Engineering
                    'civil engineering' => 'civil_engineer',
                    'mechanical engineering' => 'mechanical_engineer',
                    'electrical engineering' => 'electrical_engineer',
                    'engineering' => 'engineer',
                    
                    // Content & Writing
                    'writing' => 'content_writer',
                    'copywriting' => 'copywriter',
                    'journalism' => 'journalist',
                    
                    // Consulting
                    'consulting' => 'consultant',
                    'advisory' => 'advisor',
                ];
                
                foreach ($roleKeywords as $keyword => $role) {
                    if (strpos($afterPhrase, $keyword) !== false) {
                        return $role;
                    }
                }
            }
        }
        
        return '';
    }
    
    /**
     * Deteksi role dari cluster skills (jika ada banyak skills di satu bidang)
     */
    private function detectRoleFromSkillsCluster($cvData) {
        if (empty($cvData['skills']) || !is_array($cvData['skills'])) {
            return '';
        }
        
        $allSkills = implode(' ', array_map('strtolower', $cvData['skills']));
        
        // Count skills by domain
        $domainCounts = [
            'cybersecurity' => 0,
            'digital_marketing' => 0,
            'data_scientist' => 0,
            'web_developer' => 0,
            'mobile_developer' => 0,
            'devops_engineer' => 0,
            'cloud_engineer' => 0,
        ];
        
        // Cybersecurity skills
        $cyberSkills = ['security', 'penetration', 'ethical hacking', 'nmap', 'vulnerability',
                        'firewall', 'encryption', 'network security', 'web security', 'exploit',
                        'ctf', 'burp suite', 'metasploit', 'wireshark', 'kali linux'];
        foreach ($cyberSkills as $skill) {
            if (strpos($allSkills, $skill) !== false) {
                $domainCounts['cybersecurity']++;
            }
        }
        
        // Digital Marketing skills
        $marketingSkills = ['seo', 'sem', 'google analytics', 'social media', 'content marketing',
                           'digital marketing', 'facebook ads', 'google ads', 'email marketing',
                           'copywriting', 'marketing automation', 'conversion', 'ppc'];
        foreach ($marketingSkills as $skill) {
            if (strpos($allSkills, $skill) !== false) {
                $domainCounts['digital_marketing']++;
            }
        }
        
        // Data Science skills
        $dataSkills = ['python', 'machine learning', 'data analysis', 'pandas', 'numpy',
                       'tensorflow', 'pytorch', 'scikit-learn', 'statistics', 'sql'];
        foreach ($dataSkills as $skill) {
            if (strpos($allSkills, $skill) !== false) {
                $domainCounts['data_scientist']++;
            }
        }
        
        // Web Dev skills
        $webSkills = ['html', 'css', 'javascript', 'react', 'vue', 'angular', 'node',
                      'php', 'laravel', 'django', 'flask', 'frontend', 'backend'];
        foreach ($webSkills as $skill) {
            if (strpos($allSkills, $skill) !== false) {
                $domainCounts['web_developer']++;
            }
        }
        
        // Mobile Dev skills
        $mobileSkills = ['android', 'ios', 'swift', 'kotlin', 'react native', 'flutter'];
        foreach ($mobileSkills as $skill) {
            if (strpos($allSkills, $skill) !== false) {
                $domainCounts['mobile_developer']++;
            }
        }
        
        // DevOps skills
        $devopsSkills = ['docker', 'kubernetes', 'jenkins', 'ci/cd', 'terraform', 'ansible'];
        foreach ($devopsSkills as $skill) {
            if (strpos($allSkills, $skill) !== false) {
                $domainCounts['devops_engineer']++;
            }
        }
        
        // Cloud skills
        $cloudSkills = ['aws', 'azure', 'gcp', 'cloud', 's3', 'ec2', 'lambda'];
        foreach ($cloudSkills as $skill) {
            if (strpos($allSkills, $skill) !== false) {
                $domainCounts['cloud_engineer']++;
            }
        }
        
        // Find domain with most skills
        arsort($domainCounts);
        $topDomain = array_key_first($domainCounts);
        $topCount = $domainCounts[$topDomain];
        
        // If 3+ skills in one domain, that's the target role (lowered from 5 to 3)
        if ($topCount >= 3) {
            return $topDomain;
        }
        
        return '';
    }
    
    /**
     * Tentukan target role berdasarkan CV data
     * PRIORITY: SUMMARY ONLY - Hanya gunakan summary untuk menentukan role
     * Tidak ada fallback ke skills/education/certifications
     */
    private function determineTargetRole($cvData) {
        // HANYA GUNAKAN SUMMARY - Ini adalah sumber kebenaran utama
        if (empty($cvData['summary'])) {
            error_log("CRITICAL: No summary found in CV data");
            return '';
        }
        
        $summary = strtolower($cvData['summary']);
        error_log("Role Detection - Analyzing summary: " . substr($summary, 0, 100) . "...");
        
        // STEP 1: Deteksi dari ABOUT ME / SUMMARY menggunakan dynamic matching
        $roleFromAboutMe = $this->detectRoleFromAboutMe($cvData);
        if (!empty($roleFromAboutMe)) {
            error_log("Role Detection - SUCCESS from About Me: $roleFromAboutMe");
            return $roleFromAboutMe;
        }
        
        // STEP 2: DISABLED - detectPassionFromSummary terlalu agresif dan menyebabkan false positive
        // Hanya gunakan exact keyword matching di step berikutnya
        
        // STEP 3: Coba deteksi dari posisi terakhir JIKA disebutkan di summary
        if (!empty($cvData['positions']) && is_array($cvData['positions'])) {
            $latestPosition = $cvData['positions'][0];
            $latestRole = strtolower($latestPosition['title'] ?? '');
            
            if (!empty($latestRole)) {
                // Cek apakah role ini disebutkan di summary
                if (strpos($summary, $latestRole) !== false) {
                    error_log("Role Detection - SUCCESS from position mentioned in summary: $latestRole");
                    return $this->normalizeRole($latestRole);
                }
            }
        }
        
        // STEP 4: Scan summary untuk keywords role yang umum
        // Get all available roles dynamically
        $availableRoles = $this->getAllAvailableRoles();
        
        foreach ($availableRoles as $role) {
            $roleDisplay = str_replace('_', ' ', $role);
            if (strpos($summary, $roleDisplay) !== false) {
                error_log("Role Detection - SUCCESS from available role match: $role");
                return $role;
            }
        }
        
        // STEP 5: Deteksi dari keywords Indonesia/English di summary
        $keywordToRole = [
            // TECH - Most specific first
            'software engineer' => 'software_engineer',
            'machine learning engineer' => 'machine_learning_engineer',
            'ai engineer' => 'ai_engineer',
            'data engineer' => 'data_engineer',
            'devops engineer' => 'devops_engineer',
            'cloud engineer' => 'cloud_engineer',
            'network engineer' => 'network_engineer',
            'qa engineer' => 'quality_assurance',
            'quality assurance' => 'quality_assurance',
            'system administrator' => 'system_administrator',
            'database administrator' => 'database_administrator',
            
            // Indonesian keywords
            'pelayanan pelanggan' => 'customer_service',
            'customer service' => 'customer_service',
            'layanan pelanggan' => 'customer_service',
            'call center' => 'call_center',
            'administrasi perkantoran' => 'admin',
            'administrasi' => 'admin',
            'sekretaris' => 'secretary',
            'resepsionis' => 'receptionist',
            'penjualan' => 'sales',
            'pemasaran' => 'marketing',
            'kasir' => 'cashier',
            'barista' => 'barista',
            'pelayan' => 'waiter',
            'koki' => 'chef',
            'akuntan' => 'accountant',
            'keuangan' => 'accountant',
            'desain' => 'designer',
            'programmer' => 'programmer',
            'developer' => 'programmer',
            'software engineer' => 'software_engineer',
            'supir' => 'driver',
            'pengemudi' => 'driver',
            
            // English keywords
            'customer support' => 'customer_support',
            'receptionist' => 'receptionist',
            'secretary' => 'secretary',
            'administrative' => 'admin',
            'sales' => 'sales',
            'marketing' => 'marketing',
            'cashier' => 'cashier',
            'waiter' => 'waiter',
            'waitress' => 'waiter',
            'chef' => 'chef',
            'accountant' => 'accountant',
            'ui/ux designer' => 'ui_ux_designer',
            'ui ux designer' => 'ui_ux_designer',
            'ux designer' => 'ui_ux_designer',
            'ui designer' => 'ui_ux_designer',
            'product designer' => 'ui_ux_designer',
            'graphic designer' => 'graphic_designer',
            'designer' => 'designer',
            'driver' => 'driver',
            
            // Operations & Logistics
            'logistics coordinator' => 'logistics',
            'logistics' => 'logistics',
            'supply chain' => 'supply_chain',
            
            // Services & Other
            'agent' => 'agent',
            'broker' => 'broker',
            'ngo worker' => 'ngo',
            'ngo' => 'ngo',
            'non profit' => 'non_profit',
        ];
        
        // Sort by length (longest first for better matching)
        uksort($keywordToRole, function($a, $b) {
            return strlen($b) - strlen($a);
        });
        
        foreach ($keywordToRole as $keyword => $role) {
            if (strpos($summary, $keyword) !== false) {
                error_log("Role Detection - SUCCESS from keyword match: '$keyword' -> $role");
                return $role;
            }
        }
        
        // CRITICAL: Jika tidak ada yang match di summary, return empty
        // JANGAN gunakan skills, education, atau certifications sebagai fallback
        error_log("CRITICAL: Could not determine role from summary. Summary content: $summary");
        return '';
    }
    
    /**
     * Normalisasi role untuk pencarian yang lebih efektif
     */
    private function normalizeRole($role) {
        $role = strtolower(trim($role));
        
        // Mapping role variations ke standard terms
        $roleMapping = [
            // Developer roles
            'full stack developer' => 'full stack developer',
            'fullstack developer' => 'full stack developer',
            'frontend developer' => 'frontend developer',
            'front-end developer' => 'frontend developer',
            'backend developer' => 'backend developer',
            'back-end developer' => 'backend developer',
            'web developer' => 'web developer',
            'mobile developer' => 'mobile developer',
            'software developer' => 'software developer',
            'software engineer' => 'software engineer',
            
            // Specific tech roles
            'php developer' => 'php developer',
            'javascript developer' => 'javascript developer',
            'react developer' => 'react developer',
            'node.js developer' => 'nodejs developer',
            'python developer' => 'python developer',
            'java developer' => 'java developer',
            
            // Data roles
            'data scientist' => 'data scientist',
            'data analyst' => 'data analyst',
            'data engineer' => 'data engineer',
            'machine learning engineer' => 'machine learning engineer',
            
            // Other roles
            'devops engineer' => 'devops engineer',
            'qa engineer' => 'qa engineer',
            'quality assurance' => 'qa engineer',
            'ui/ux designer' => 'ui ux designer',
            'product manager' => 'product manager',
            'project manager' => 'project manager'
        ];
        
        // Check exact match
        if (isset($roleMapping[$role])) {
            return $roleMapping[$role];
        }
        
        // Check partial match
        foreach ($roleMapping as $key => $value) {
            if (strpos($role, $key) !== false) {
                return $value;
            }
        }
        
        // Return original if no mapping found
        return $role;
    }
    
    /**
     * Ambil top skills yang paling relevan
     */
    private function getTopSkills($cvData) {
        if (empty($cvData['skills']) || !is_array($cvData['skills'])) {
            return [];
        }
        
        $skills = $cvData['skills'];
        
        // Prioritas skills yang lebih spesifik dan marketable
        $prioritySkills = [
            // Programming languages
            'php', 'javascript', 'python', 'java', 'typescript', 'go', 'rust', 'c++', 'c#',
            // Frameworks
            'react', 'vue', 'angular', 'laravel', 'django', 'spring', 'express', 'nodejs',
            // Databases
            'mysql', 'postgresql', 'mongodb', 'redis', 'elasticsearch',
            // Cloud & DevOps
            'aws', 'azure', 'gcp', 'docker', 'kubernetes', 'jenkins', 'terraform',
            // Data & AI
            'machine learning', 'deep learning', 'tensorflow', 'pytorch', 'data analysis',
            // Other
            'git', 'agile', 'scrum', 'rest api', 'graphql', 'microservices'
        ];
        
        $topSkills = [];
        
        // Ambil skills yang ada di priority list dulu
        foreach ($skills as $skill) {
            $skillLower = strtolower(trim($skill));
            if (in_array($skillLower, $prioritySkills)) {
                $topSkills[] = $skill;
                if (count($topSkills) >= 2) {
                    break;
                }
            }
        }
        
        // Jika belum cukup, ambil dari skills lainnya
        if (count($topSkills) < 2) {
            foreach ($skills as $skill) {
                if (!in_array($skill, $topSkills)) {
                    $topSkills[] = $skill;
                    if (count($topSkills) >= 2) {
                        break;
                    }
                }
            }
        }
        
        return array_slice($topSkills, 0, 2);
    }
    
    /**
     * Transform LinkedIn jobs ke format aplikasi
     */
    private function transformLinkedInJobs($linkedInJobs) {
        $jobs = [];
        
        foreach ($linkedInJobs as $job) {
            // Generate description dari search_role dan metadata
            $description = $this->generateJobDescription($job);
            
            // Format posted date
            $postedDate = $this->formatPostedDate($job['posted_time'] ?? '');
            
            // Determine category and sector from new format
            $category = $job['search_role'] ?? $job['category'] ?? null;
            $sector = $this->determineSector($job);
            
            // Extract metadata
            $isInternship = $job['metadata']['is_internship'] ?? false;
            $isFreshGrad = $job['metadata']['is_fresh_graduate'] ?? false;
            $isRemote = $job['metadata']['is_remote'] ?? false;
            
            $jobs[] = [
                'title' => $job['title'] ?? 'Job Opening',
                'company' => $job['company'] ?? 'Company',
                'location' => $job['location'] ?? 'Indonesia',
                'description' => $description,
                'posted' => $postedDate,
                'match_score' => $job['match_score'] ?? 70,
                'url' => $job['link'] ?? '#',
                'source' => 'LinkedIn',
                'category' => $category,
                'sector' => $sector,
                'job_type' => $job['job_type'] ?? null,
                'experience_level' => $job['experience_level'] ?? null,
                'work_arrangement' => $job['work_arrangement'] ?? null,
                'is_internship' => $isInternship,
                'is_fresh_graduate' => $isFreshGrad,
                'is_remote' => $isRemote
            ];
        }
        
        return $jobs;
    }
    
    /**
     * Determine sector from job data
     */
    private function determineSector($job) {
        // Check if old format has sector
        if (isset($job['sector'])) {
            return $job['sector'];
        }
        
        // Determine sector from search_role or metadata
        $role = strtolower($job['search_role'] ?? '');
        
        // Tech roles
        $techRoles = ['developer', 'engineer', 'programmer', 'devops', 'data', 'cybersecurity', 
                      'software', 'web', 'mobile', 'frontend', 'backend', 'fullstack', 'ai', 
                      'machine learning', 'cloud', 'system administrator', 'network', 'database'];
        
        foreach ($techRoles as $techRole) {
            if (strpos($role, $techRole) !== false) {
                return 'technology';
            }
        }
        
        // Business roles
        $businessRoles = ['manager', 'director', 'business', 'consultant', 'analyst', 'account'];
        foreach ($businessRoles as $bizRole) {
            if (strpos($role, $bizRole) !== false) {
                return 'business';
            }
        }
        
        // Creative roles
        $creativeRoles = ['designer', 'writer', 'content', 'marketing', 'social media', 'copywriter'];
        foreach ($creativeRoles as $creativeRole) {
            if (strpos($role, $creativeRole) !== false) {
                return 'creative';
            }
        }
        
        // Administrative roles
        $adminRoles = ['admin', 'secretary', 'receptionist', 'office', 'hr', 'human resources'];
        foreach ($adminRoles as $adminRole) {
            if (strpos($role, $adminRole) !== false) {
                return 'administrative';
            }
        }
        
        // Default
        return 'operational';
    }
    
    /**
     * Generate job description dari category dan sector
     */
    private function generateJobDescription($job) {
        $category = $job['search_role'] ?? $job['category'] ?? '';
        $sector = $job['sector'] ?? $this->determineSector($job);
        $company = $job['company'] ?? 'Company';
        $jobType = $job['job_type'] ?? '';
        $experienceLevel = $job['experience_level'] ?? '';
        
        // Build description parts
        $descParts = [];
        
        // Main description based on sector
        $descriptions = [
            'operational' => "Join {$company} as a {$category}. Contribute to operational excellence and process optimization.",
            'technology' => "Exciting opportunity at {$company} for {$category}. Work with cutting-edge technology and innovative solutions.",
            'creative' => "Creative role at {$company} for {$category}. Bring your creativity and make an impact.",
            'business' => "Strategic position at {$company} for {$category}. Drive business growth and development.",
            'administrative' => "Professional role at {$company} for {$category}. Support organizational efficiency.",
        ];
        
        $mainDesc = $descriptions[$sector] ?? "Great opportunity at {$company} for {$category}. Join a dynamic team and grow your career.";
        $descParts[] = str_replace(['{$company}', '{$category}'], [$company, $category], $mainDesc);
        
        // Add job type and experience level if available
        if ($jobType) {
            $descParts[] = "Position type: {$jobType}.";
        }
        if ($experienceLevel) {
            $descParts[] = "Experience level: {$experienceLevel}.";
        }
        
        return implode(' ', $descParts);
    }
    
    /**
     * Calculate match score berdasarkan CV data dengan analisis mendalam
     */
    private function calculateMatchScore($job, $cvData) {
        $score = 50; // Base score (lowered to allow more jobs to pass threshold)
        
        $jobTitle = strtolower($job['title'] ?? '');
        $jobCategory = strtolower($job['search_role'] ?? $job['category'] ?? '');
        $jobLocation = strtolower($job['location'] ?? '');
        
        // 1. Role/Position Match (bobot tertinggi: +20 points)
        if (!empty($cvData['positions']) && is_array($cvData['positions'])) {
            $latestRole = strtolower($cvData['positions'][0]['title'] ?? '');
            
            if (!empty($latestRole)) {
                // Exact match atau sangat mirip
                if (strpos($jobTitle, $latestRole) !== false || strpos($latestRole, $jobTitle) !== false) {
                    $score += 20;
                } 
                // Check category match
                elseif (strpos($jobCategory, $latestRole) !== false || strpos($latestRole, $jobCategory) !== false) {
                    $score += 15;
                }
                else {
                    // Partial match dengan keywords
                    $roleKeywords = explode(' ', $latestRole);
                    $matchCount = 0;
                    foreach ($roleKeywords as $keyword) {
                        if (strlen($keyword) > 3 && 
                            (strpos($jobTitle, $keyword) !== false || strpos($jobCategory, $keyword) !== false)) {
                            $matchCount++;
                        }
                    }
                    if ($matchCount > 0) {
                        $score += min($matchCount * 5, 15);
                    }
                }
            }
        }
        
        // 2. Skills Match (bobot tinggi: +3 per skill, max +15)
        $skillMatches = 0;
        if (!empty($cvData['skills']) && is_array($cvData['skills'])) {
            foreach ($cvData['skills'] as $skill) {
                $skillLower = strtolower(trim($skill));
                
                // Skip skills yang terlalu umum
                if (in_array($skillLower, ['microsoft office', 'ms office', 'email', 'internet'])) {
                    continue;
                }
                
                // Check di title atau category
                if (strpos($jobTitle, $skillLower) !== false || strpos($jobCategory, $skillLower) !== false) {
                    $skillMatches++;
                    $score += 3;
                }
                
                if ($skillMatches >= 5) break; // Max 5 skills match
            }
        }
        
        // 3. Experience Level Match (+5 points)
        if (!empty($cvData['positions']) && is_array($cvData['positions'])) {
            $yearsOfExperience = count($cvData['positions']);
            
            // Detect level dari job title
            $isSenior = strpos($jobTitle, 'senior') !== false || strpos($jobTitle, 'lead') !== false;
            $isJunior = strpos($jobTitle, 'junior') !== false || strpos($jobTitle, 'entry') !== false;
            $isMid = !$isSenior && !$isJunior;
            
            // Match experience level
            if ($yearsOfExperience >= 5 && $isSenior) {
                $score += 5;
            } elseif ($yearsOfExperience >= 2 && $yearsOfExperience < 5 && $isMid) {
                $score += 5;
            } elseif ($yearsOfExperience < 2 && $isJunior) {
                $score += 5;
            }
        }
        
        // 4. Education Match (+3 points)
        if (!empty($cvData['education']) && is_array($cvData['education'])) {
            $hasRelevantEducation = false;
            foreach ($cvData['education'] as $edu) {
                $field = strtolower($edu['field'] ?? '');
                
                // Check if education field relevant to job
                if (!empty($field) && strlen($field) > 3 &&
                    (strpos($jobTitle, $field) !== false || strpos($jobCategory, $field) !== false)) {
                    $hasRelevantEducation = true;
                    break;
                }
            }
            if ($hasRelevantEducation) {
                $score += 3;
            }
        }
        
        // 5. Location Preference (+2 points)
        if (!empty($cvData['location'])) {
            $cvLocation = strtolower($cvData['location']);
            // Extract city names
            $cities = ['jakarta', 'bandung', 'surabaya', 'medan', 'semarang', 'tangerang', 'bekasi', 'bogor'];
            foreach ($cities as $city) {
                if (strpos($cvLocation, $city) !== false && strpos($jobLocation, $city) !== false) {
                    $score += 2;
                    break;
                }
            }
        }
        
        // Ensure score is between 60-95
        return max(60, min($score, 95));
    }
    
    /**
     * Format posted date
     */
    private function formatPostedDate($dateString) {
        if (empty($dateString)) {
            return date('Y-m-d');
        }
        
        // Handle format YYYY-MM-DD
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateString)) {
            return $dateString;
        }
        
        $timestamp = strtotime($dateString);
        if ($timestamp === false) {
            return date('Y-m-d');
        }
        
        return date('Y-m-d', $timestamp);
    }
    
}
