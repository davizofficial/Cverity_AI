<?php
/**
 * CV Template Generator
 * Populate CV template dengan data user
 */

class CVTemplate {
    private $templatePath;
    
    public function __construct() {
        $this->templatePath = __DIR__ . '/../templates/cv.html';
    }
    
    /**
     * Generate CV HTML dari template dengan AI enhancement
     */
    public function generateWithAI($cvData, $evaluation, $gemini) {
        // Get improved content from AI
        $improvedData = $this->improveWithAI($cvData, $evaluation, $gemini);
        
        // Generate CV with improved data
        $cvHtml = $this->generate($improvedData, $evaluation);
        
        // Let AI add missing sections and improve layout
        $cvHtml = $this->enhanceWithAI($cvHtml, $improvedData, $evaluation, $gemini);
        
        return $cvHtml;
    }
    
    /**
     * Enhance CV HTML dengan AI - tambahkan section yang hilang
     */
    private function enhanceWithAI($cvHtml, $cvData, $evaluation, $gemini) {
        // Analyze what sections are missing or need enhancement
        $missingSections = $this->analyzeMissingSections($cvHtml, $cvData);
        
        if (empty($missingSections)) {
            return $cvHtml; // No enhancement needed
        }
        
        // Prepare data for each missing section
        $sectionsData = $this->prepareSectionsData($cvData, $missingSections);
        
        // Ask AI to generate missing sections - ONLY using REAL data
        $prompt = "You are a professional CV designer. Generate HTML sections using ONLY the provided data.\n\n";
        $prompt .= "IMPORTANT: Use ONLY the data provided below. DO NOT make up or invent any information.\n\n";
        
        $prompt .= "CANDIDATE PROFILE:\n";
        $prompt .= "- Name: " . ($cvData['name'] ?? 'Professional') . "\n";
        $prompt .= "- Role: " . ($cvData['positions'][0]['title'] ?? 'Professional') . "\n";
        $prompt .= "- Experience: " . ($cvData['total_experience_years'] ?? 0) . " years\n\n";
        
        $prompt .= "REAL DATA TO USE (DO NOT ADD ANYTHING NOT IN THIS DATA):\n";
        foreach ($missingSections as $section) {
            $prompt .= "\n" . $section . ":\n";
            if (isset($sectionsData[$section])) {
                $prompt .= json_encode($sectionsData[$section], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
            }
        }
        
        $prompt .= "\nHTML STRUCTURE TO USE:\n";
        $prompt .= "For lists (Skills, Languages):\n";
        $prompt .= "```html\n";
        $prompt .= "<section class=\"section\">\n";
        $prompt .= "    <div class=\"title\">Section Title</div>\n";
        $prompt .= "    <div class=\"hr-line\"></div>\n";
        $prompt .= "    <ul>\n";
        $prompt .= "        <li>Item from data</li>\n";
        $prompt .= "    </ul>\n";
        $prompt .= "</section>\n";
        $prompt .= "```\n\n";
        
        $prompt .= "For detailed entries (Projects, Certifications, Awards, Volunteer, Publications):\n";
        $prompt .= "```html\n";
        $prompt .= "<section class=\"section\">\n";
        $prompt .= "    <div class=\"title\">Section Title</div>\n";
        $prompt .= "    <div class=\"hr-line\"></div>\n";
        $prompt .= "    <div class=\"entry\">\n";
        $prompt .= "        <strong>Title from data</strong><br>\n";
        $prompt .= "        <span class=\"sub\">Description from data</span><br>\n";
        $prompt .= "        Date from data\n";
        $prompt .= "    </div>\n";
        $prompt .= "</section>\n";
        $prompt .= "```\n\n";
        
        $prompt .= "STRICT REQUIREMENTS:\n";
        $prompt .= "1. Use EXACT HTML structure shown above\n";
        $prompt .= "2. Use ONLY data provided - NO fabrication or invention\n";
        $prompt .= "3. If data is empty or null, skip that field\n";
        $prompt .= "4. Keep content professional and factual\n";
        $prompt .= "5. Return ONLY HTML code, no explanations or comments\n";
        $prompt .= "6. Each section must be complete and valid HTML\n";
        $prompt .= "7. DO NOT add placeholder text or example data\n\n";
        
        $prompt .= "Generate HTML sections now using ONLY the real data provided:";
        
        $result = $gemini->callAPI(
            "Professional CV Designer & HTML Expert",
            $prompt,
            ['temperature' => 0.6, 'maxOutputTokens' => 2000],
            false
        );
        
        if ($result['success']) {
            $newSectionsHtml = $this->extractHTMLFromAI($result['data']);
            
            if (!empty($newSectionsHtml)) {
                // Insert new sections before closing body tag
                $cvHtml = str_replace('</body>', "\n    " . $newSectionsHtml . "\n</body>", $cvHtml);
            }
        }
        
        return $cvHtml;
    }
    
    /**
     * Prepare data for missing sections
     */
    private function prepareSectionsData($cvData, $missingSections) {
        $data = [];
        
        foreach ($missingSections as $section) {
            switch ($section) {
                case 'Skills':
                    $data[$section] = array_slice($cvData['skills'] ?? [], 0, 15);
                    break;
                    
                case 'Projects':
                    $data[$section] = array_slice($cvData['projects'] ?? [], 0, 5);
                    break;
                    
                case 'Certifications':
                    $data[$section] = array_slice($cvData['certifications'] ?? [], 0, 5);
                    break;
                    
                case 'Awards':
                    $data[$section] = array_slice($cvData['awards'] ?? [], 0, 5);
                    break;
                    
                case 'Volunteer':
                    $data[$section] = array_slice($cvData['volunteer'] ?? [], 0, 3);
                    break;
                    
                case 'Publications':
                    $data[$section] = array_slice($cvData['publications'] ?? [], 0, 5);
                    break;
                    
                case 'Languages':
                    $data[$section] = $cvData['skills_detail']['languages'] ?? [];
                    break;
            }
        }
        
        return $data;
    }
    
    /**
     * Analyze which sections are missing from CV
     * ONLY add sections if there's REAL data from user's CV
     */
    private function analyzeMissingSections($cvHtml, $cvData) {
        $missing = [];
        
        // Check for common sections - ONLY if data exists and has meaningful content
        $sectionsToCheck = [
            'Skills' => $this->hasRealSkills($cvData),
            'Projects' => $this->hasRealProjects($cvData),
            'Certifications' => $this->hasRealCertifications($cvData),
            'Awards' => $this->hasRealAwards($cvData),
            'Volunteer' => $this->hasRealVolunteer($cvData),
            'Publications' => $this->hasRealPublications($cvData),
            'Languages' => $this->hasRealLanguages($cvData)
        ];
        
        foreach ($sectionsToCheck as $section => $hasRealData) {
            // Skip if no real data
            if (!$hasRealData) {
                continue;
            }
            
            // Check if section exists in HTML
            $sectionExists = (stripos($cvHtml, '<div class="title">' . $section) !== false) ||
                           (stripos($cvHtml, '<div class="title">' . strtolower($section)) !== false);
            
            // Only add if section doesn't exist BUT we have REAL data
            if (!$sectionExists) {
                $missing[] = $section;
            }
        }
        
        return $missing;
    }
    
    /**
     * Check if CV has real skills data (not empty or generic)
     */
    private function hasRealSkills($cvData) {
        if (empty($cvData['skills']) || !is_array($cvData['skills'])) {
            return false;
        }
        
        // Must have at least 3 skills
        if (count($cvData['skills']) < 3) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Check if CV has real projects data
     */
    private function hasRealProjects($cvData) {
        if (empty($cvData['projects']) || !is_array($cvData['projects'])) {
            return false;
        }
        
        // Must have at least 1 project with name
        foreach ($cvData['projects'] as $project) {
            if (!empty($project['name'])) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if CV has real certifications data
     */
    private function hasRealCertifications($cvData) {
        if (empty($cvData['certifications']) || !is_array($cvData['certifications'])) {
            return false;
        }
        
        // Must have at least 1 certification with name
        foreach ($cvData['certifications'] as $cert) {
            if (!empty($cert['name'])) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if CV has real awards data
     */
    private function hasRealAwards($cvData) {
        if (empty($cvData['awards']) || !is_array($cvData['awards'])) {
            return false;
        }
        
        // Must have at least 1 award with title
        foreach ($cvData['awards'] as $award) {
            if (!empty($award['title'])) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if CV has real volunteer experience
     */
    private function hasRealVolunteer($cvData) {
        if (empty($cvData['volunteer']) || !is_array($cvData['volunteer'])) {
            return false;
        }
        
        // Must have at least 1 volunteer experience with role
        foreach ($cvData['volunteer'] as $vol) {
            if (!empty($vol['role']) || !empty($vol['organization'])) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if CV has real publications
     */
    private function hasRealPublications($cvData) {
        if (empty($cvData['publications']) || !is_array($cvData['publications'])) {
            return false;
        }
        
        // Must have at least 1 publication with title
        foreach ($cvData['publications'] as $pub) {
            if (!empty($pub['title'])) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if CV has real language skills
     */
    private function hasRealLanguages($cvData) {
        if (empty($cvData['skills_detail']['languages'])) {
            return false;
        }
        
        $languages = $cvData['skills_detail']['languages'];
        
        if (!is_array($languages) || count($languages) < 1) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Extract HTML code from AI response
     */
    private function extractHTMLFromAI($response) {
        // Remove markdown code blocks
        $html = preg_replace('/```html\s*/i', '', $response);
        $html = preg_replace('/```\s*/', '', $html);
        
        // Remove explanatory text before/after HTML
        $html = trim($html);
        
        // Validate that it contains section tags
        if (stripos($html, '<section') === false) {
            return '';
        }
        
        return $html;
    }
    
    /**
     * Improve CV content dengan Gemini AI
     */
    private function improveWithAI($cvData, $evaluation, $gemini) {
        $role = $cvData['positions'][0]['title'] ?? 'Professional';
        $gaps = $evaluation['gaps'] ?? [];
        
        // Improve each position's description
        $improvedPositions = [];
        foreach ($cvData['positions'] as $pos) {
            $company = $pos['company'] ?? '';
            $title = $pos['title'] ?? '';
            $description = $pos['description'] ?? '';
            
            // Ask AI to improve bullet points as HRD perspective
            $systemPrompt = "Anda adalah HRD Senior di perusahaan multinasional dengan 15+ tahun pengalaman merekrut kandidat. Anda ahli dalam membaca CV dan tahu persis apa yang dicari recruiter.";
            
            $prompt = "Saya akan memberikan deskripsi pengalaman kerja dari CV kandidat. Tugas Anda adalah memperbaiki penulisannya agar lebih profesional dan menarik bagi HRD, TANPA menambahkan informasi yang tidak ada.\n\n";
            $prompt .= "INFORMASI KANDIDAT:\n";
            $prompt .= "Posisi: $title\n";
            $prompt .= "Perusahaan: $company\n";
            $prompt .= "Deskripsi asli:\n$description\n\n";
            
            $prompt .= "ATURAN KETAT (WAJIB DIIKUTI):\n";
            $prompt .= "1. JANGAN menambahkan angka, metrik, atau pencapaian yang tidak ada di deskripsi asli\n";
            $prompt .= "2. JANGAN membuat-buat tanggung jawab yang tidak disebutkan\n";
            $prompt .= "3. HANYA perbaiki cara penulisan dari informasi yang sudah ada\n";
            $prompt .= "4. Jika deskripsi asli sangat singkat, tetap singkat tapi profesional\n";
            $prompt .= "5. Jika tidak ada detail spesifik, jangan tambahkan detail palsu\n\n";
            
            $prompt .= "CARA MEMPERBAIKI:\n";
            $prompt .= "- Gunakan kata kerja aktif yang kuat (Mengelola, Mengembangkan, Melaksanakan, Mengkoordinasi)\n";
            $prompt .= "- Perjelas tanggung jawab yang sudah disebutkan\n";
            $prompt .= "- Tambahkan kata kunci ATS yang relevan dengan posisi (tools/teknologi yang disebutkan)\n";
            $prompt .= "- Susun dalam 3-5 poin bullet yang jelas\n";
            $prompt .= "- Gunakan Bahasa Indonesia yang profesional\n\n";
            
            $prompt .= "CONTOH:\n";
            $prompt .= "Asli: 'Membuat konten sosial media'\n";
            $prompt .= "Diperbaiki: 'Mengelola pembuatan konten untuk platform media sosial perusahaan'\n\n";
            
            $prompt .= "Asli: 'Membantu tim marketing dalam campaign'\n";
            $prompt .= "Diperbaiki: 'Mendukung tim marketing dalam pelaksanaan campaign digital'\n\n";
            
            $prompt .= "OUTPUT: Berikan HANYA poin-poin yang sudah diperbaiki, satu per baris, tanpa penomoran atau tanda dash.";
            
            $result = $gemini->callAPI(
                $systemPrompt,
                $prompt,
                ['temperature' => 0.4, 'maxOutputTokens' => 500],
                false
            );
            
            if ($result['success']) {
                // Clean AI response - remove explanations
                $cleaned = $this->cleanAIResponse($result['data']);
                $pos['description'] = $cleaned;
            }
            
            $improvedPositions[] = $pos;
        }
        
        $cvData['positions'] = $improvedPositions;
        
        // Improve summary/about me dengan AI
        $experience = $cvData['total_experience_years'] ?? 0;
        $skills = array_slice($cvData['skills'] ?? [], 0, 5);
        $positions = $cvData['positions'] ?? [];
        
        // Get industry context
        $companies = [];
        foreach ($positions as $pos) {
            if (!empty($pos['company'])) {
                $companies[] = $pos['company'];
            }
        }
        
        // Build simple, focused summary
        $companyList = implode(', ', array_slice($companies, 0, 2));
        $skillList = implode(', ', array_slice($skills, 0, 5));
        
        $systemPrompt = "Anda adalah HRD Senior yang sudah membaca ribuan CV. Anda tahu persis ringkasan CV seperti apa yang menarik perhatian recruiter dalam 10 detik pertama.";
        
        $summaryPrompt = "Buatkan ringkasan profesional 'Tentang Saya' untuk CV kandidat ini:\n\n";
        $summaryPrompt .= "PROFIL KANDIDAT:\n";
        $summaryPrompt .= "- Posisi yang dilamar: $role\n";
        $summaryPrompt .= "- Pengalaman kerja: $experience tahun\n";
        $summaryPrompt .= "- Riwayat perusahaan: $companyList\n";
        $summaryPrompt .= "- Keahlian utama: $skillList\n\n";
        
        $summaryPrompt .= "ATURAN KETAT:\n";
        $summaryPrompt .= "1. HANYA gunakan informasi di atas, JANGAN menambahkan hal lain\n";
        $summaryPrompt .= "2. JANGAN membuat pencapaian, angka, atau klaim yang tidak ada datanya\n";
        $summaryPrompt .= "3. Fokus ke posisi '$role', bukan hal umum\n";
        $summaryPrompt .= "4. Maksimal 2-3 kalimat yang padat dan jelas\n";
        $summaryPrompt .= "5. Hindari kata-kata klise seperti 'berdedikasi tinggi', 'pekerja keras'\n\n";
        
        $summaryPrompt .= "STRUKTUR YANG BAIK:\n";
        $summaryPrompt .= "- Kalimat 1: Perkenalan posisi dan pengalaman\n";
        $summaryPrompt .= "- Kalimat 2: Keahlian spesifik yang relevan\n";
        $summaryPrompt .= "- Kalimat 3 (opsional): Value yang bisa diberikan\n\n";
        
        $summaryPrompt .= "CONTOH BAIK:\n";
        $summaryPrompt .= "'Digital Marketing Specialist dengan 3 tahun pengalaman di PT ABC dan PT XYZ. Menguasai Google Analytics, SEO, dan Social Media Marketing untuk meningkatkan brand awareness. Siap berkontribusi dalam strategi digital marketing perusahaan.'\n\n";
        
        $summaryPrompt .= "OUTPUT: Tulis ringkasan dalam Bahasa Indonesia yang profesional, singkat, dan to the point.";
        
        $summaryResult = $gemini->callAPI(
            $systemPrompt,
            $summaryPrompt,
            ['temperature' => 0.5, 'maxOutputTokens' => 300],
            false
        );
        
        if ($summaryResult['success']) {
            // Clean AI response - remove explanations
            $cleaned = $this->cleanAIResponse($summaryResult['data']);
            $cvData['summary'] = $cleaned;
        }
        
        return $cvData;
    }
    
    /**
     * Generate CV HTML dari template
     */
    public function generate($cvData, $evaluation) {
        // Load template
        $template = file_get_contents($this->templatePath);
        
        // Extract data
        $name = $cvData['name'] ?? 'NAMA LENGKAP ANDA';
        $emails = $cvData['emails'] ?? [];
        $phones = $cvData['phones'] ?? [];
        $summary = $cvData['summary'] ?? '';
        $positions = $cvData['positions'] ?? [];
        $skills = $cvData['skills'] ?? [];
        $education = $cvData['education'] ?? [];
        
        // Build contact info
        $email = !empty($emails) ? $emails[0] : 'email@example.com';
        $phone = !empty($phones) ? $phones[0] : '+62 812 3456 7890';
        
        // Get location from first position
        $location = 'Indonesia';
        if (!empty($positions)) {
            $location = 'Indonesia'; // Default, bisa diambil dari data lain
        }
        
        // Replace header - ROBUST VERSION
        if (!empty($name) && $name !== 'NAMA LENGKAP ANDA') {
            $template = str_replace('NAMA LENGKAP ANDA', strtoupper($name), $template);
        }
        
        if (!empty($email) && $email !== 'email@example.com') {
            $template = str_replace('email@example.com', $email, $template);
        }
        
        if (!empty($phone) && $phone !== '+62 812 3456 7890') {
            $template = str_replace('+62 812 3456 7890 | linkedin.com/in/username', $phone, $template);
        }
        
        // Replace location
        $template = str_replace('Kota, Negara', $location, $template);
        
        // Build sidebar items dynamically
        $sidebarItems = $this->buildSidebarItems($cvData, $skills);
        $template = str_replace('<!-- Skills, Languages, Certifications akan ditambahkan secara dinamis jika ada data -->', $sidebarItems, $template);
        
        // Replace About Me
        if (!empty($summary)) {
            $aboutMe = htmlspecialchars($summary);
        } else {
            // Generate from evaluation
            $role = $positions[0]['title'] ?? 'Professional';
            $aboutMe = $this->generateAboutMe($role, $cvData, $evaluation);
        }
        $template = preg_replace(
            '/<p>Profesional dengan ketertarikan.*?<\/p>/s',
            '<p>' . $aboutMe . '</p>',
            $template
        );
        
        // Replace Education
        $educationHtml = $this->buildEducation($education);
        $template = preg_replace(
            '/<div class="entry">.*?<strong>Nama Universitas.*?<\/div>/s',
            $educationHtml,
            $template,
            1
        );
        
        // Replace Experience
        $experienceHtml = $this->buildExperience($positions, $evaluation);
        $template = preg_replace(
            '/<div class="entry">.*?<strong>Nama Organisasi.*?<\/div>/s',
            $experienceHtml,
            $template,
            1
        );
        
        // Build dynamic sections - hanya tampilkan jika ada data
        $dynamicSections = $this->buildDynamicSections($cvData, $skills, $positions);
        
        // Insert dynamic sections before closing </main> tag
        $template = str_replace('</main>', $dynamicSections . "\n      </main>", $template);
        
        return $template;
    }
    
    /**
     * Generate About Me dari data CV - PROFESSIONAL VERSION
     */
    private function generateAboutMe($role, $cvData, $evaluation) {
        $experience = $cvData['total_experience_years'] ?? 0;
        $positions = $cvData['positions'] ?? [];
        $skills = $cvData['skills'] ?? [];
        
        // Determine industry from positions
        $industries = [];
        foreach ($positions as $pos) {
            $company = $pos['company'] ?? '';
            $title = $pos['title'] ?? '';
            
            // Extract industry keywords
            if (stripos($title, 'marketing') !== false || stripos($company, 'media') !== false) {
                $industries[] = 'Digital Marketing';
            } elseif (stripos($title, 'developer') !== false || stripos($title, 'engineer') !== false) {
                $industries[] = 'Software Development';
            } elseif (stripos($title, 'manager') !== false) {
                $industries[] = 'Management';
            } elseif (stripos($title, 'design') !== false) {
                $industries[] = 'Creative Design';
            }
        }
        
        $industries = array_unique($industries);
        $industryText = !empty($industries) ? implode(' dan ', $industries) : 'berbagai industri';
        
        // Get top skills
        $topSkills = array_slice($skills, 0, 5);
        $skillsText = implode(', ', $topSkills);
        
        // Get latest company
        $latestCompany = !empty($positions) ? ($positions[0]['company'] ?? '') : '';
        
        // Build professional About Me
        $aboutMe = "Profesional $role dengan pengalaman lebih dari $experience tahun di bidang $industryText. ";
        
        if (!empty($latestCompany)) {
            $aboutMe .= "Saat ini berkontribusi di $latestCompany, ";
        }
        
        $aboutMe .= "dengan fokus pada pengembangan strategi, implementasi solusi inovatif, dan pencapaian target bisnis. ";
        
        if (!empty($skillsText)) {
            $aboutMe .= "Memiliki keahlian mendalam dalam $skillsText. ";
        }
        
        $aboutMe .= "Terbukti mampu memimpin proyek cross-functional, berkolaborasi dengan stakeholder, dan menghasilkan impact yang terukur. ";
        $aboutMe .= "Berkomitmen untuk terus berkembang dan memberikan kontribusi maksimal dalam mencapai visi organisasi.";
        
        return $aboutMe;
    }
    
    /**
     * Build Education HTML
     */
    private function buildEducation($education) {
        if (empty($education)) {
            return '<div class="entry"><strong>Pendidikan</strong><br><span class="sub">Gelar</span><br>Tahun</div>';
        }
        
        $html = '';
        foreach ($education as $edu) {
            $institution = htmlspecialchars($edu['institution'] ?? 'Universitas');
            $degree = htmlspecialchars($edu['degree'] ?? 'Gelar');
            $year = $edu['year'] ?? 'Tahun';
            
            $html .= '<div class="entry">';
            $html .= '<strong>' . $institution . '</strong><br>';
            $html .= '<span class="sub">' . $degree . '</span><br>';
            $html .= $year;
            $html .= '</div>' . "\n\n      ";
        }
        
        return trim($html);
    }
    
    /**
     * Build Experience HTML dengan improved bullet points
     */
    private function buildExperience($positions, $evaluation) {
        if (empty($positions)) {
            return '<div class="entry"><strong>Perusahaan — Posisi</strong><br>Tahun<ul><li>Deskripsi pekerjaan</li></ul></div>';
        }
        
        $html = '';
        foreach ($positions as $pos) {
            $company = htmlspecialchars($pos['company'] ?? 'Perusahaan');
            $title = htmlspecialchars($pos['title'] ?? 'Posisi');
            $startDate = $pos['start_date'] ?? '';
            $endDate = $pos['end_date'] ?? 'Present';
            $description = $pos['description'] ?? '';
            $months = $pos['months'] ?? 0;
            
            // Format duration
            $duration = '';
            if ($months > 0) {
                $years = floor($months / 12);
                $remainingMonths = $months % 12;
                if ($years > 0) {
                    $duration = " ($years tahun" . ($remainingMonths > 0 ? " $remainingMonths bulan" : "") . ")";
                } else {
                    $duration = " ($remainingMonths bulan)";
                }
            }
            
            $html .= '<div class="entry">';
            $html .= '<strong>' . $company . ' — ' . $title . '</strong><br>';
            $html .= $startDate . ' – ' . $endDate . $duration;
            
            // Build bullet points (improved by AI)
            $html .= '<ul>';
            
            if (!empty($description)) {
                $bullets = $this->improveBulletPoints($description, $title);
                foreach ($bullets as $bullet) {
                    $html .= '<li>' . htmlspecialchars($bullet) . '</li>';
                }
            } else {
                // Default bullets
                $html .= '<li>Bertanggung jawab terhadap ' . strtolower($title) . ' dan aktivitas terkait</li>';
                $html .= '<li>Bekerja sama dengan tim dalam mencapai target perusahaan</li>';
                $html .= '<li>Mengembangkan kemampuan profesional dan soft skills</li>';
                $html .= '<li>Mengelola proyek dan memastikan deliverables tepat waktu</li>';
            }
            
            $html .= '</ul>';
            $html .= '</div>' . "\n\n      ";
        }
        
        return trim($html);
    }
    
    /**
     * Improve bullet points dengan action verbs
     */
    private function improveBulletPoints($description, $role) {
        // Split by newline
        $lines = explode("\n", $description);
        $bullets = [];
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Remove bullet markers
            $line = preg_replace('/^[-•*]\s*/', '', $line);
            
            if (empty($line) || strlen($line) < 10) continue;
            
            $bullets[] = $line;
            
            if (count($bullets) >= 4) break; // Max 4 bullets per position
        }
        
        if (empty($bullets)) {
            $bullets = [
                'Bertanggung jawab terhadap ' . strtolower($role) . ' dan aktivitas terkait',
                'Bekerja sama dengan tim dalam mencapai target perusahaan',
                'Mengembangkan kemampuan profesional dan soft skills',
                'Mengelola proyek dan memastikan deliverables tepat waktu'
            ];
        }
        
        return $bullets;
    }
    
    /**
     * Clean AI response - remove explanations and meta text
     */
    private function cleanAIResponse($text) {
        // Remove common AI prefixes
        $patterns = [
            '/^(Absolutely!?|Sure!?|Here\'s?|Here is|Certainly!?|Of course!?)[^\n]*\n*/i',
            '/^(Berikut|Ini adalah|Tentu)[^\n]*\n*/i',
            '/\*\*[^\*]+\*\*/i', // Remove **bold** markers
            '/^[-•]\s*/m', // Remove bullet markers at start of lines
        ];
        
        foreach ($patterns as $pattern) {
            $text = preg_replace($pattern, '', $text);
        }
        
        // Remove lines that look like explanations
        $lines = explode("\n", $text);
        $cleaned = [];
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Skip empty lines
            if (empty($line)) continue;
            
            // Skip lines that are clearly explanations
            if (preg_match('/^(Note:|Catatan:|Tips:|Remember:|Ingat:)/i', $line)) continue;
            if (preg_match('/designed to be|tailored for|CV in Bahasa/i', $line)) continue;
            
            $cleaned[] = $line;
        }
        
        return trim(implode("\n", $cleaned));
    }
    
    /**
     * Categorize skills into Hard Skills and Soft Skills
     */
    private function categorizeSkills($skills, $positions) {
        $hardSkills = [];
        $softSkills = [];
        
        // Common soft skills keywords
        $softSkillsKeywords = [
            'communication', 'komunikasi', 'leadership', 'kepemimpinan', 'teamwork', 'tim',
            'problem solving', 'critical thinking', 'adaptability', 'time management',
            'collaboration', 'kolaborasi', 'presentation', 'presentasi', 'negotiation',
            'analytical', 'analitis', 'creative', 'kreatif', 'interpersonal'
        ];
        
        foreach ($skills as $skill) {
            $skillLower = strtolower($skill);
            $isSoft = false;
            
            foreach ($softSkillsKeywords as $keyword) {
                if (stripos($skillLower, $keyword) !== false) {
                    $softSkills[] = $skill;
                    $isSoft = true;
                    break;
                }
            }
            
            if (!$isSoft) {
                $hardSkills[] = $skill;
            }
        }
        
        // If no soft skills detected, add defaults
        if (empty($softSkills)) {
            $softSkills = [
                'Communication & Presentation',
                'Team Collaboration',
                'Problem Solving',
                'Time Management',
                'Adaptability',
                'Critical Thinking'
            ];
        }
        
        return [
            'hard' => $hardSkills,
            'soft' => $softSkills
        ];
    }
    
    /**
     * Build Hard Skills Section
     */
    private function buildHardSkillsSection($hardSkills) {
        $html = '<section class="section">';
        $html .= '<div class="title">Technical Skills</div>';
        $html .= '<div class="hr-line"></div>';
        $html .= '<ul>';
        
        if (empty($hardSkills)) {
            $html .= '<li>Microsoft Office / Google Workspace</li>';
            $html .= '<li>Project Management Tools</li>';
        } else {
            foreach ($hardSkills as $skill) {
                $html .= '<li>' . htmlspecialchars($skill) . '</li>';
            }
        }
        
        $html .= '</ul>';
        $html .= '</section>';
        
        return $html;
    }
    
    /**
     * Build Soft Skills Section
     */
    private function buildSoftSkillsSection($softSkills) {
        $html = '<section class="section">';
        $html .= '<div class="title">Soft Skills</div>';
        $html .= '<div class="hr-line"></div>';
        $html .= '<ul>';
        
        foreach ($softSkills as $skill) {
            $html .= '<li>' . htmlspecialchars($skill) . '</li>';
        }
        
        $html .= '</ul>';
        $html .= '</section>';
        
        return $html;
    }
    
    /**
     * Build sidebar items dynamically - hanya tampilkan jika ada data
     */
    private function buildSidebarItems($cvData, $skills) {
        $html = '';
        
        // Skills pills (top skills only)
        if (!empty($skills) && count($skills) >= 3) {
            $topSkills = array_slice($skills, 0, 8);
            $html .= "\n\n        <div class=\"box\">";
            $html .= "\n          <div class=\"label\">Top Skills</div>";
            $html .= "\n          <div>";
            foreach ($topSkills as $skill) {
                $html .= "\n            <span class=\"pill\">" . htmlspecialchars($skill) . "</span>";
            }
            $html .= "\n          </div>";
            $html .= "\n        </div>";
        }
        
        // Languages
        if ($this->hasRealLanguages($cvData)) {
            $languages = $cvData['skills_detail']['languages'] ?? [];
            $html .= "\n\n        <div class=\"box\">";
            $html .= "\n          <div class=\"label\">Languages</div>";
            $html .= "\n          <div class=\"muted\">";
            
            $langItems = [];
            foreach ($languages as $lang) {
                if (is_array($lang)) {
                    $name = $lang['name'] ?? '';
                    $level = $lang['level'] ?? '';
                    if (!empty($name)) {
                        $langItems[] = htmlspecialchars($name) . ($level ? ' — ' . htmlspecialchars($level) : '');
                    }
                } else {
                    $langItems[] = htmlspecialchars($lang);
                }
            }
            
            $html .= implode('<br>', array_slice($langItems, 0, 4));
            $html .= "</div>";
            $html .= "\n        </div>";
        }
        
        // Certifications (summary only)
        if ($this->hasRealCertifications($cvData)) {
            $certifications = $cvData['certifications'] ?? [];
            $certCount = 0;
            
            $html .= "\n\n        <div class=\"box\">";
            $html .= "\n          <div class=\"label\">Certifications</div>";
            $html .= "\n          <div class=\"muted\">";
            
            foreach ($certifications as $cert) {
                if (!empty($cert['name']) && $certCount < 3) {
                    $certName = htmlspecialchars($cert['name']);
                    $certDate = !empty($cert['date']) ? ' — ' . htmlspecialchars($cert['date']) : '';
                    $html .= $certName . $certDate . '<br>';
                    $certCount++;
                }
            }
            
            $html .= "</div>";
            $html .= "\n        </div>";
        }
        
        return $html;
    }
    
    /**
     * Build all dynamic sections - hanya tampilkan jika ada data
     */
    private function buildDynamicSections($cvData, $skills, $positions) {
        $sections = '';
        
        // 1. Skills Section (Soft Skills & Hard Skills)
        if (!empty($skills) && count($skills) >= 3) {
            $categorizedSkills = $this->categorizeSkills($skills, $positions);
            $sections .= "\n\n        " . $this->buildSoftSkillsSection($categorizedSkills['soft']);
            $sections .= "\n\n        " . $this->buildHardSkillsSection($categorizedSkills['hard']);
        }
        
        // 2. Certifications Section
        if ($this->hasRealCertifications($cvData)) {
            $sections .= "\n\n        " . $this->buildCertifications($cvData);
        }
        
        // 3. Languages Section
        if ($this->hasRealLanguages($cvData)) {
            $sections .= "\n\n        " . $this->buildLanguagesSection($cvData);
        }
        
        // 4. Projects Section
        if ($this->hasRealProjects($cvData)) {
            $sections .= "\n\n        " . $this->buildProjectsSection($cvData);
        }
        
        // 5. Awards Section
        if ($this->hasRealAwards($cvData)) {
            $sections .= "\n\n        " . $this->buildAwardsSection($cvData);
        }
        
        // 6. Volunteer Section
        if ($this->hasRealVolunteer($cvData)) {
            $sections .= "\n\n        " . $this->buildVolunteerSection($cvData);
        }
        
        // 7. Publications Section
        if ($this->hasRealPublications($cvData)) {
            $sections .= "\n\n        " . $this->buildPublicationsSection($cvData);
        }
        
        return $sections;
    }
    
    /**
     * Build Certifications Section - ONLY if user has real certifications
     */
    private function buildCertifications($cvData) {
        $certifications = $cvData['certifications'] ?? [];
        
        $html = '<section class="section" aria-labelledby="certs">';
        $html .= '<div class="title"><h2 id="certs">Sertifikasi &amp; Pelatihan</h2></div>';
        $html .= '<div class="rule" aria-hidden="true"></div>';
        
        foreach ($certifications as $cert) {
            if (empty($cert['name'])) continue;
            
            $html .= '<div class="entry">';
            $html .= '  <div class="meta">';
            $html .= '    <strong>' . htmlspecialchars($cert['name']) . '</strong><br>';
            
            if (!empty($cert['issuer'])) {
                $html .= '    <div class="sub">' . htmlspecialchars($cert['issuer']) . '</div>';
            }
            
            $html .= '  </div>';
            
            if (!empty($cert['date'])) {
                $html .= '  <div class="date">' . htmlspecialchars($cert['date']) . '</div>';
            }
            
            $html .= '</div>' . "\n          ";
        }
        
        $html .= '</section>';
        
        return $html;
    }
    
    /**
     * Build Languages Section
     */
    private function buildLanguagesSection($cvData) {
        $languages = $cvData['skills_detail']['languages'] ?? [];
        
        $html = '<section class="section" aria-labelledby="languages">';
        $html .= '<div class="title"><h2 id="languages">Languages</h2></div>';
        $html .= '<div class="rule" aria-hidden="true"></div>';
        $html .= '<ul>';
        
        foreach ($languages as $lang) {
            if (is_array($lang)) {
                $name = $lang['name'] ?? '';
                $level = $lang['level'] ?? '';
                if (!empty($name)) {
                    $html .= '<li>' . htmlspecialchars($name);
                    if (!empty($level)) {
                        $html .= ' — ' . htmlspecialchars($level);
                    }
                    $html .= '</li>';
                }
            } else {
                $html .= '<li>' . htmlspecialchars($lang) . '</li>';
            }
        }
        
        $html .= '</ul>';
        $html .= '</section>';
        
        return $html;
    }
    
    /**
     * Build Projects Section
     */
    private function buildProjectsSection($cvData) {
        $projects = $cvData['projects'] ?? [];
        
        $html = '<section class="section" aria-labelledby="projects">';
        $html .= '<div class="title"><h2 id="projects">Projects</h2></div>';
        $html .= '<div class="rule" aria-hidden="true"></div>';
        
        foreach ($projects as $project) {
            if (empty($project['name'])) continue;
            
            $html .= '<div class="entry">';
            $html .= '  <div class="meta">';
            $html .= '    <strong>' . htmlspecialchars($project['name']) . '</strong><br>';
            
            if (!empty($project['description'])) {
                $html .= '    <div class="sub">' . htmlspecialchars($project['description']) . '</div>';
            }
            
            $html .= '  </div>';
            
            if (!empty($project['date'])) {
                $html .= '  <div class="date">' . htmlspecialchars($project['date']) . '</div>';
            }
            
            $html .= '</div>' . "\n          ";
        }
        
        $html .= '</section>';
        
        return $html;
    }
    
    /**
     * Build Awards Section
     */
    private function buildAwardsSection($cvData) {
        $awards = $cvData['awards'] ?? [];
        
        $html = '<section class="section" aria-labelledby="awards">';
        $html .= '<div class="title"><h2 id="awards">Awards &amp; Achievements</h2></div>';
        $html .= '<div class="rule" aria-hidden="true"></div>';
        
        foreach ($awards as $award) {
            if (empty($award['title'])) continue;
            
            $html .= '<div class="entry">';
            $html .= '  <div class="meta">';
            $html .= '    <strong>' . htmlspecialchars($award['title']) . '</strong><br>';
            
            if (!empty($award['description'])) {
                $html .= '    <div class="sub">' . htmlspecialchars($award['description']) . '</div>';
            }
            
            $html .= '  </div>';
            
            if (!empty($award['date'])) {
                $html .= '  <div class="date">' . htmlspecialchars($award['date']) . '</div>';
            }
            
            $html .= '</div>' . "\n          ";
        }
        
        $html .= '</section>';
        
        return $html;
    }
    
    /**
     * Build Volunteer Section
     */
    private function buildVolunteerSection($cvData) {
        $volunteer = $cvData['volunteer'] ?? [];
        
        $html = '<section class="section" aria-labelledby="volunteer">';
        $html .= '<div class="title"><h2 id="volunteer">Volunteer Experience</h2></div>';
        $html .= '<div class="rule" aria-hidden="true"></div>';
        
        foreach ($volunteer as $vol) {
            if (empty($vol['role']) && empty($vol['organization'])) continue;
            
            $html .= '<div class="entry">';
            $html .= '  <div class="meta">';
            
            $title = [];
            if (!empty($vol['organization'])) $title[] = htmlspecialchars($vol['organization']);
            if (!empty($vol['role'])) $title[] = htmlspecialchars($vol['role']);
            
            $html .= '    <strong>' . implode(' — ', $title) . '</strong><br>';
            
            if (!empty($vol['description'])) {
                $html .= '    <div class="sub">' . htmlspecialchars($vol['description']) . '</div>';
            }
            
            $html .= '  </div>';
            
            if (!empty($vol['date'])) {
                $html .= '  <div class="date">' . htmlspecialchars($vol['date']) . '</div>';
            }
            
            $html .= '</div>' . "\n          ";
        }
        
        $html .= '</section>';
        
        return $html;
    }
    
    /**
     * Build Publications Section
     */
    private function buildPublicationsSection($cvData) {
        $publications = $cvData['publications'] ?? [];
        
        $html = '<section class="section" aria-labelledby="publications">';
        $html .= '<div class="title"><h2 id="publications">Publications</h2></div>';
        $html .= '<div class="rule" aria-hidden="true"></div>';
        
        foreach ($publications as $pub) {
            if (empty($pub['title'])) continue;
            
            $html .= '<div class="entry">';
            $html .= '  <div class="meta">';
            $html .= '    <strong>' . htmlspecialchars($pub['title']) . '</strong><br>';
            
            if (!empty($pub['publisher'])) {
                $html .= '    <div class="sub">' . htmlspecialchars($pub['publisher']) . '</div>';
            }
            
            $html .= '  </div>';
            
            if (!empty($pub['date'])) {
                $html .= '  <div class="date">' . htmlspecialchars($pub['date']) . '</div>';
            }
            
            $html .= '</div>' . "\n          ";
        }
        
        $html .= '</section>';
        
        return $html;
    }
    
    /**
     * Build Skills HTML (legacy - kept for compatibility)
     */
    private function buildSkills($skills) {
        if (empty($skills)) {
            return '<ul><li>Communication</li><li>Teamwork</li><li>Problem Solving</li></ul>';
        }
        
        $html = '<ul>';
        foreach ($skills as $skill) {
            $html .= '<li>' . htmlspecialchars($skill) . '</li>';
        }
        $html .= '</ul>';
        
        return $html;
    }
}
