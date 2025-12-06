<?php
// GeminiClient.php
// Versi final: prompt diperbaiki + prefilter kata tidak pantas
// Pastikan GEMINI_API_KEYS (array) dan GEMINI_ENDPOINT (string) didefinisikan di config Anda.

class GeminiClient {
    private $apiKeys;
    private $currentKeyIndex = 0;
    private $endpoint;
    private $apiMonitor;

    // Simpan hasil prefilter lokal (jika ditemukan sebelum kirim ke model)
    private $prefiltered_terms = [];

    public function __construct() {
        $this->apiKeys = GEMINI_API_KEYS;
        $this->endpoint = GEMINI_ENDPOINT;
        
        // Initialize API Monitor
        require_once __DIR__ . '/api_monitor.php';
        $this->apiMonitor = new APIMonitor();
        
        // Use best available API key
        $bestKey = $this->apiMonitor->getBestApiKey($this->apiKeys);
        if ($bestKey) {
            // Find index of best key
            $index = array_search($bestKey, $this->apiKeys);
            if ($index !== false) {
                $this->currentKeyIndex = $index;
            }
        }
    }

    /**
     * -----------------------
     * Helper: API Key Rotation
     * -----------------------
     */
    private function getApiKey() {
        return $this->apiKeys[$this->currentKeyIndex];
    }

    private function rotateApiKey() {
        $this->currentKeyIndex = ($this->currentKeyIndex + 1) % count($this->apiKeys);
        return $this->currentKeyIndex > 0;
    }

    /**
     * -----------------------
     * Prefilter: deteksi & ganti kata tidak pantas
     * -----------------------
     *
     * - $text: teks input (CV) yang akan discan
     * - mengembalikan teks yang sudah dibersihkan (kata diganti "[DICENSOR]")
     * - mencatat detail ke $this->prefiltered_terms (original, field placeholder, category, action)
     *
     * Anda dapat memperluas $badWords sesuai kebutuhan / bahasa.
     */
    private function prefilterText(string $text, string $fieldPlaceholder = 'raw_cv_text') : string {
        // Contoh daftar kata tidak pantas sederhana (bisa diperluas)
        $badWords = [
            // profanity (contoh)
            'fuck', 'shit', 'bitch', 'asshole',
            // seksual eksplisit (contoh)
            'porn', 'xxx', 'sex',
            // contoh ujaran kebencian placeholder - tambahkan sesuai kebijakan
            'racistterm1', 'racistterm2'
            // tambahkan kata/regex lain sesuai kebijakan Anda
        ];

        // Kita lakukan case-insensitive replacement
        foreach ($badWords as $word) {
            // gunakan word-boundary-ish replace (case-insensitive)
            $pattern = '/\b' . preg_quote($word, '/') . '\b/i';

            if (preg_match($pattern, $text, $matches)) {
                // catat semua kejadian
                $this->prefiltered_terms[] = [
                    'original' => $matches[0],
                    'field' => $fieldPlaceholder,
                    'category' => $this->guessCategoryFromWord($word),
                    'action' => 'replaced_with_[DICENSOR]'
                ];
                $text = preg_replace($pattern, '[DICENSOR]', $text);
            }
        }

        // Deteksi pola sensitif lain, misal nomor KTP/NPWP (indonesia) atau nomor kartu kredit
        // Cari sequences 15-19 digit (kartu kredit / KTP / NPWP) - regex sederhana
        if (preg_match_all('/\b\d{15,19}\b/', $text, $digitMatches)) {
            foreach ($digitMatches[0] as $num) {
                $this->prefiltered_terms[] = [
                    'original' => $num,
                    'field' => $fieldPlaceholder,
                    'category' => 'too_sensitive_numeric',
                    'action' => 'replaced_with_[DICENSOR]'
                ];
                $text = str_replace($num, '[DICENSOR]', $text);
            }
        }

        return $text;
    }

    private function guessCategoryFromWord(string $word) : string {
        $w = strtolower($word);
        $sexual = ['porn','sex','xxx'];
        $profanity = ['fuck','shit','bitch','asshole'];

        if (in_array($w, $sexual)) return 'sexual';
        if (in_array($w, $profanity)) return 'profanity';
        return 'other';
    }

    /**
     * -----------------------
     * TAHAP 1: ekstrak full text dari file (sama seperti sebelumnya)
     * -----------------------
     */
    public function callExtract($fileData, $mimeType) {
        $allowedMimeTypes = [
            'application/pdf',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/octet-stream' // Allow octet-stream as fallback for DOCX
        ];

        if (!in_array($mimeType, $allowedMimeTypes)) {
            return [
                'success' => false,
                'error' => 'Tipe file tidak didukung: ' . $mimeType . '. Hanya PDF dan DOCX yang diperbolehkan.'
            ];
        }

        // Jika DOCX, ekstrak teks terlebih dahulu karena Gemini tidak support DOCX
        if ($mimeType === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' || 
            $mimeType === 'application/octet-stream') {
            
            $textResult = $this->extractTextFromDocx($fileData);
            
            if (!$textResult['success']) {
                return $textResult;
            }
            
            // Strukturkan teks yang sudah diekstrak
            $structureResult = $this->structureCV($textResult['data']);
            return $structureResult;
        }

        // Untuk PDF, gunakan Gemini multimodal
        $extractResult = $this->extractFullText($fileData, $mimeType);

        if (!$extractResult['success']) {
            return $extractResult;
        }

        $fullText = $extractResult['data'];

        // Strukturkan teks ke JSON
        $structureResult = $this->structureCV($fullText);

        return $structureResult;
    }
    
    /**
     * Extract text from DOCX file
     */
    private function extractTextFromDocx($base64Data) {
        try {
            // Decode base64
            $fileContent = base64_decode($base64Data);
            
            // Save to temporary file
            $tempFile = tempnam(sys_get_temp_dir(), 'docx_');
            file_put_contents($tempFile, $fileContent);
            
            // Extract text using ZipArchive
            $zip = new ZipArchive();
            if ($zip->open($tempFile) !== true) {
                @unlink($tempFile);
                return [
                    'success' => false,
                    'error' => 'Gagal membuka file DOCX. File mungkin corrupt atau terproteksi.'
                ];
            }
            
            // Read document.xml which contains the main text
            $xmlContent = $zip->getFromName('word/document.xml');
            $zip->close();
            @unlink($tempFile);
            
            if ($xmlContent === false) {
                return [
                    'success' => false,
                    'error' => 'Gagal membaca konten DOCX. File mungkin tidak valid.'
                ];
            }
            
            // Parse XML and extract text
            $xml = simplexml_load_string($xmlContent);
            if ($xml === false) {
                return [
                    'success' => false,
                    'error' => 'Gagal parsing XML dari DOCX.'
                ];
            }
            
            // Register namespaces
            $xml->registerXPathNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
            
            // Extract all text nodes
            $textNodes = $xml->xpath('//w:t');
            $fullText = '';
            
            if ($textNodes) {
                foreach ($textNodes as $textNode) {
                    $fullText .= (string)$textNode . ' ';
                }
            }
            
            // Clean up text
            $fullText = trim($fullText);
            
            if (empty($fullText)) {
                return [
                    'success' => false,
                    'error' => 'Tidak ada teks yang dapat diekstrak dari file DOCX. File mungkin kosong atau berisi hanya gambar.'
                ];
            }
            
            return [
                'success' => true,
                'data' => $fullText
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Error saat ekstraksi DOCX: ' . $e->getMessage()
            ];
        }
    }

    private function extractFullText($fileData, $mimeType) {
        $systemPrompt = "You are a CV text extractor. Extract ALL text content from the CV document.";

        $userPrompt = "Extract ALL text from this CV document. Include:\n" .
                      "- Personal information (name, contact details, location, etc.)\n" .
                      "- Professional summary or objective\n" .
                      "- Work experience (all positions with full descriptions)\n" .
                      "- Education (all degrees and certifications)\n" .
                      "- Skills (technical, soft skills, languages, etc.)\n" .
                      "- Projects, achievements, awards\n" .
                      "- Certifications, licenses\n" .
                      "- Publications, presentations\n" .
                      "- Volunteer work, extracurricular activities\n" .
                      "- References\n" .
                      "- ANY other information present in the CV\n\n" .
                      "Return the complete text content preserving the structure and formatting as much as possible.";

        $contents = [
            [
                'parts' => [
                    ['text' => $systemPrompt . "\n\n" . $userPrompt],
                    [
                        'inline_data' => [
                            'mime_type' => $mimeType,
                            'data' => $fileData
                        ]
                    ]
                ]
            ]
        ];

        $params = [
            'temperature' => 0.0,
            'maxOutputTokens' => 8192
        ];

        return $this->callAPIWithPayload($contents, $params, false);
    }

    /**
     * -----------------------
     * TAHAP 2: Strukturkan teks CV ke JSON (dengan prompt yang diperbaiki)
     * -----------------------
     */
    private function structureCV($fullText) {
        // Prefilter lokal (opsional)
        $this->prefiltered_terms = []; // reset
        $cleanText = $this->prefilterText($fullText, 'raw_cv_text');

        // Bangun prompt (gunakan HEREDOC agar mudah dibaca)
        $systemPrompt = "Anda adalah pakar struktur data CV. TUGAS: Ubah teks CV mentah menjadi JSON terstruktur sesuai skema yang diminta.";

        // Ganti placeholder dengan teks CV yang sudah diprefilter
        $userPrompt = <<<'PROMPT'
Anda adalah pakar struktur data CV. TUGAS: Ubah teks CV mentah berikut menjadi JSON terstruktur sesuai skema yang diminta.

Teks CV:
{{CV_TEXT}}

SKEMA JSON yang harus Anda kembalikan (kembalikan HANYA JSON valid tanpa teks lain):

{
  "name": "Full name or null",
  "emails": ["email1@example.com", "..."] or [],
  "phones": ["+62...","..."] or [],
  "location": "City, Country or null",
  "linkedin": "URL or null",
  "website": "URL or null",
  "total_experience_years": number or null,
  "summary": "Professional summary (max 200 chars) or null",
  "positions": [
    {
      "title": "Job title",
      "company": "Company name",
      "location": "City, Country or null",
      "start_date": "YYYY-MM or null",
      "end_date": "YYYY-MM or 'present' or null",
      "months": integer or null,
      "description": "Full job description (preserve bullets) or null",
      "achievements": ["achievement 1", "..."] or []
    }
  ] or [],
  "skills": {
    "technical": ["..."] or [],
    "soft": ["..."] or [],
    "languages": ["Language (level)"] or []
  },
  "education": [
    {
      "degree": "Degree name or null",
      "field": "Field of study or null",
      "institution": "Institution name or null",
      "location": "City, Country or null",
      "year": integer or null,
      "gpa": "GPA if mentioned or null",
      "honors": "Honors or null"
    }
  ] or [],
  "certifications": [ { "name":"", "issuer":"", "date":"YYYY-MM or null", "credential_id": "or null" } ] or [],
  "projects": [ { "name":"", "description":"", "technologies":[], "url":"" } ] or [],
  "awards": [],
  "publications": [],
  "volunteer": [],
  "additional_info": "other info or null",
  "filtered_terms": [
    {
      "original": "kata yang dihapus",
      "field": "field tempat ditemukan (mis. summary/positions[0].description)",
      "category": "reason category (profanity/hate_speech/sexual/illegal/too_sensitive)",
      "action": "replaced_with_[DICENSOR] atau removed"
    }
  ]
}

ATURAN PENYARINGAN (wajib ditaati):
1. Jika menemukan kata-kata KOTOR/SEKSUAL, ujaran kebencian, ancaman, promosi aktivitas ilegal, atau informasi SANGAT SENSITIF yang tidak relevan (nomor identitas, nomor kartu kredit, informasi medis rinci), gantikan teks tersebut pada field yang bersangkutan dengan string "[DICENSOR]" dan catat entri di `filtered_terms`.
2. Jika teks berisi referensi pornografi, tindakan kriminal yang mempromosikan kejahatan, atau bahasa yang menghina kelompok/individu, jangan masukkan langsung ke JSON — gantikan dengan "[DICENSOR]" dan jelaskan alasan di `filtered_terms`.
3. Untuk data kontak yang tampak tidak valid (format email/telepon aneh), masukkan hanya jika sesuai pola umum; jika tidak yakin, letakkan sebagai null dan catat alasan di `filtered_terms`.
4. Jangan ringkas detail—tetapi bersihkan konten yang tidak pantas seperti dijelaskan di atas.
5. Jika field tidak ditemukan, gunakan null atau array kosong [] sesuai tipe.
6. Pastikan semua tanggal diformat ke "YYYY-MM" jika mungkin; jika hanya ada tahun, boleh isi hanya "YYYY" atau null — sertakan apa yang tersedia.
7. KELUARKAN HANYA JSON valid — tanpa komentar, tanpa teks tambahan.

Catatan teknis: ganti token {{CV_TEXT}} dengan teks CV lengkap (sudah diprefilter) sebelum mengirim ke model.
PROMPT;

        // Replace placeholder dengan teks CV yang sudah diprefilter
        $userPrompt = str_replace('{{CV_TEXT}}', $this->sanitizeForPrompt($cleanText), $userPrompt);

        $params = [
            'temperature' => 0.0,
            'maxOutputTokens' => 8192
        ];

        $result = $this->callAPI($systemPrompt, $userPrompt, $params, true);

        // Jika berhasil, gabungkan prefiltered_terms lokal ke hasil (jika model juga menambahkan filtered_terms,
        // kita akan menggabungkan supaya audit lengkap)
        if ($result['success']) {
            // normalisasi
            $result['data'] = $this->normalizeCV($result['data']);

            // gabungkan filtered terms lokal (jika ada)
            if (!empty($this->prefiltered_terms)) {
                if (!isset($result['data']['filtered_terms']) || !is_array($result['data']['filtered_terms'])) {
                    $result['data']['filtered_terms'] = [];
                }
                // tambahkan lokal prefilter entries
                $result['data']['filtered_terms'] = array_merge($result['data']['filtered_terms'], $this->prefiltered_terms);
            }
        }

        return $result;
    }

    /**
     * -----------------------
     * Evaluate CV vs ATS (prompt diperbaiki)
     * -----------------------
     */
    public function callEvaluate($candidateJson, $jobRequirements = null) {
        // Pastikan $candidateJson adalah array (jika string, dekode dulu)
        if (is_string($candidateJson)) {
            $candidateJsonArr = json_decode($candidateJson, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $candidateJson = $candidateJsonArr;
            } else {
                // jika invalid JSON, tolak
                return ['success' => false, 'error' => 'candidateJson tidak valid JSON'];
            }
        }

        // Ambil role dari CV kandidat (fallback)
        $candidateRole = 'entry level';
        if (!empty($candidateJson['positions']) && is_array($candidateJson['positions'])) {
            $candidateRole = $candidateJson['positions'][0]['title'] ?? $candidateRole;
        }

        if ($jobRequirements === null) {
            $jobRequirements = "Posisi $candidateRole di pasar Indonesia: requirements umum untuk role ini, skills yang dibutuhkan, pengalaman yang relevan";
        }

        $systemPrompt = "Anda adalah evaluator ATS profesional yang mengevaluasi CV untuk pasar kerja di Indonesia.";

        $userPrompt = <<<'PROMPT'
Anda adalah evaluator ATS profesional yang mengevaluasi CV untuk pasar kerja di Indonesia. TUGAS: Berikan evaluasi objektif, spesifik, dan konstruktif berdasarkan JSON CV kandidat berikut dan job requirements yang disediakan.

Input kandidat (JSON):
{{CANDIDATE_JSON}}

Job requirements / konteks (jika tersedia):
{{JOB_REQUIREMENTS}}

ATURAN EVALUASI:
1. Berikan skor keseluruhan 0-100 pada kriteria "job_match_score" berdasarkan kecocokan pengalaman, skills, dan pendidikan kandidat terhadap job requirements.

2. Jelaskan paling banyak 5 alasan utama (array "reasons") yang SPESIFIK dan RELEVAN dengan data CV:
   - JANGAN generic seperti "Memiliki skills yang baik"
   - HARUS spesifik seperti "Memiliki 5 sertifikasi cybersecurity (CompTIA Security+, CEH, dll) yang relevan dengan posisi Security Analyst"
   - Sebutkan nama tools, teknologi, atau achievement spesifik dari CV

3. Identifikasi gaps/kekurangan RIIL dalam array "gaps" dengan saran yang ACTIONABLE dan SPESIFIK:
   - JANGAN generic seperti "Tingkatkan skills"
   - HARUS spesifik seperti "Tambahkan pengalaman hands-on dengan SIEM tools (Splunk/Wazuh) melalui lab praktik atau proyek pribadi"
   - Setiap gap harus berisi:
     * "type": Experience/Skill/Education/Format/Other
     * "detail": Penjelasan gap yang SPESIFIK (sebutkan skill/tool/experience yang kurang)
     * "suggestion": Saran ACTIONABLE (sebutkan course/certification/project spesifik yang bisa dilakukan)

4. Berikan "suggested_actions" berupa langkah-langkah prioritas (3-6 item) yang KONKRET dan BISA LANGSUNG DILAKUKAN:
   - JANGAN: "Tingkatkan kemampuan cybersecurity"
   - HARUS: "Ikuti course 'Practical Ethical Hacking' di Udemy atau TryHackMe untuk hands-on experience"
   - HARUS: "Buat portfolio GitHub dengan 3-5 security projects (vulnerability scanner, network monitor, dll)"
   - HARUS: "Tambahkan metrics pada deskripsi pengalaman (contoh: 'Mengidentifikasi 15+ vulnerabilities', 'Mengurangi security incidents 30%')"
   - Sesuaikan dengan target role kandidat (jika passion cybersecurity, fokus ke cybersecurity actions)

5. TIPS KOMPETITIF - Berikan saran agar CV bisa bersaing di dunia kerja:
   - Sebutkan certifications yang direkomendasikan untuk role tersebut
   - Sebutkan tools/teknologi yang harus dikuasai
   - Sebutkan project ideas yang bisa ditambahkan ke portfolio
   - Sebutkan keywords ATS yang harus ada di CV
   - Sebutkan format/struktur CV yang optimal

6. Periksa isi CV untuk konten yang tidak pantas atau terlalu sensitif (contoh: kata kotor/porno, ujaran kebencian, ancaman, promosi aktivitas ilegal, nomor identitas, atau data lain yang tidak relevan untuk CV). Jika ditemukan:
   - Tambahkan entri pada "filtered_terms" dengan fields: original, field, category, action.
   - Saran perbaikannya harus mencakup apakah konten harus dihapus, diganti, atau direformulasikan.

7. Gunakan bahasa Indonesia yang baku, profesional, dan konstruktif untuk semua alasan, detail gap, dan suggested_actions.

8. Keluarkan HANYA JSON valid sesuai format di bawah — tanpa teks penjelas lain.

FORMAT OUTPUT YANG WAJIB (kembalikan HANYA JSON):
{
  "job_match_score": integer (0-100),
  "reasons": [
    "SPESIFIK: Memiliki 5 sertifikasi cybersecurity (CompTIA Security+, CEH, Cisco CCNA) yang sangat relevan dengan posisi Security Analyst",
    "SPESIFIK: Pengalaman hands-on dengan security tools (Nmap, Burp Suite, Metasploit, Wireshark) menunjukkan kemampuan praktis",
    "..."
  ],
  "gaps": [
    {
      "type": "Experience",
      "detail": "Pengalaman profesional di bidang cybersecurity masih terbatas pada internship 6 bulan, belum ada full-time experience",
      "suggestion": "Cari posisi Junior Security Analyst atau SOC Analyst entry-level, atau ikuti program magang di perusahaan cybersecurity untuk menambah pengalaman praktis"
    },
    {
      "type": "Skill",
      "detail": "Belum memiliki pengalaman dengan SIEM tools (Splunk, Wazuh, QRadar) yang sering diminta oleh employer",
      "suggestion": "Ikuti course 'Splunk Fundamentals' (gratis di Splunk Education) atau setup Wazuh lab di home environment untuk hands-on practice"
    },
    {
      "type": "Format",
      "detail": "Deskripsi pengalaman kerja kurang quantifiable, tidak ada metrics atau angka yang menunjukkan impact",
      "suggestion": "Tambahkan metrics spesifik seperti 'Mengidentifikasi 20+ vulnerabilities', 'Mengurangi false positive alerts 30%', atau 'Monitoring 50+ endpoints'"
    }
  ],
  "suggested_actions": [
    "Tambahkan certifications: Target CEH (Certified Ethical Hacker) atau OSCP untuk meningkatkan kredibilitas di cybersecurity",
    "Buat portfolio GitHub dengan 3-5 security projects: vulnerability scanner, network packet analyzer, atau security automation scripts",
    "Ikuti platform hands-on: TryHackMe, HackTheBox, atau PentesterLab untuk menambah practical experience dan showcase achievements",
    "Update deskripsi pengalaman dengan metrics quantifiable (jumlah vulnerabilities found, systems monitored, incidents handled)",
    "Tambahkan keywords ATS: 'Threat Intelligence', 'Incident Response', 'Security Operations Center (SOC)', 'Vulnerability Management'",
    "Ikuti komunitas cybersecurity lokal (OWASP Indonesia, ID-CERT) untuk networking dan update tren industri"
  ],
  "filtered_terms": [],
  "notes": "CV menunjukkan passion yang kuat di cybersecurity dengan foundation yang solid. Fokus pada hands-on experience dan quantifiable achievements untuk meningkatkan daya saing."
}

CONTOH BURUK (JANGAN DITIRU - BERLAKU UNTUK SEMUA ROLES):
{
  "reasons": ["Memiliki skills yang baik", "Pengalaman cukup relevan"],
  "gaps": [{"detail": "Kurang pengalaman", "suggestion": "Tingkatkan skills"}],
  "suggested_actions": ["Belajar lebih banyak", "Ikuti training"]
}

CONTOH BAIK - CYBERSECURITY ROLE:
{
  "reasons": ["Memiliki 9 technical skills cybersecurity spesifik (Network Security, Penetration Testing, Nmap, Burp Suite, dll)", "5 sertifikasi dari provider ternama (CompTIA, Cisco, Certiprof)"],
  "gaps": [{"detail": "Belum ada pengalaman dengan cloud security (AWS/Azure/GCP)", "suggestion": "Ikuti AWS Security Fundamentals course dan dapatkan AWS Security Specialty certification"}],
  "suggested_actions": ["Setup home lab dengan Kali Linux dan practice penetration testing di TryHackMe", "Buat blog/writeup tentang CTF challenges untuk showcase problem-solving skills"]
}

CONTOH BAIK - WEB DEVELOPER ROLE:
{
  "reasons": ["Memiliki pengalaman 2 tahun dengan React dan Node.js, menunjukkan kemampuan full-stack development", "Portfolio GitHub dengan 8 projects yang showcase berbagai technologies (React, TypeScript, MongoDB, Express)"],
  "gaps": [{"detail": "Belum ada pengalaman dengan state management libraries modern (Redux Toolkit, Zustand, Recoil)", "suggestion": "Ikuti course 'Redux Toolkit Tutorial' di YouTube atau 'Modern React with Redux' di Udemy, lalu rebuild 1-2 existing projects dengan Redux"}],
  "suggested_actions": ["Buat portfolio website profesional dengan Next.js dan deploy di Vercel untuk showcase projects", "Contribute to 3-5 open source React projects di GitHub untuk build reputation", "Ikuti course TypeScript dan migrate 2-3 projects ke TypeScript untuk improve code quality"]
}

CONTOH BAIK - DATA ANALYST ROLE:
{
  "reasons": ["Memiliki pengalaman praktis dengan Python data libraries (Pandas, NumPy, Matplotlib) dan SQL untuk data manipulation", "Background pendidikan di Statistics memberikan foundation yang kuat untuk data analysis"],
  "gaps": [{"detail": "Belum ada pengalaman dengan BI tools (Tableau, Power BI, Looker) yang sering diminta oleh employer", "suggestion": "Ikuti course 'Tableau Desktop Specialist' (gratis di Tableau eLearning) dan buat 3-5 interactive dashboards untuk portfolio"}],
  "suggested_actions": ["Buat portfolio di Kaggle dengan 5+ data analysis projects dan participate in competitions", "Ikuti course 'Google Data Analytics Professional Certificate' di Coursera untuk structured learning", "Build end-to-end data pipeline project: data collection → cleaning → analysis → visualization → insights"]
}

CONTOH BAIK - UI/UX DESIGNER ROLE:
{
  "reasons": ["Portfolio Behance dengan 12 design projects menunjukkan variety dan creativity", "Pengalaman dengan Figma, Adobe XD, dan prototyping tools menunjukkan technical proficiency"],
  "gaps": [{"detail": "Belum ada case studies yang menjelaskan design process (research, wireframes, testing, iterations)", "suggestion": "Buat 2-3 detailed case studies di portfolio yang showcase design thinking process: problem statement → user research → wireframes → mockups → usability testing → final design"}],
  "suggested_actions": ["Redesign 3 popular apps (Instagram, Gojek, Tokopedia) dan publish case studies di Medium atau Behance", "Ikuti course 'Google UX Design Professional Certificate' untuk structured methodology", "Participate in Daily UI Challenge (100 days) untuk consistent practice dan portfolio building"]
}

PENTING: Sesuaikan contoh dengan ROLE SPESIFIK dari CV kandidat. Jika CV menunjukkan passion untuk Data Science, berikan saran Data Science. Jika passion untuk Mobile Development, berikan saran Mobile Development. JANGAN berikan saran generic yang sama untuk semua roles!

Catatan: jika job requirements tidak diberikan, analisis berdasarkan passion/role teratas dalam CV. Beri prioritas objektivitas dan actionable feedback; hindari spekulasi. SELALU berikan saran yang SPESIFIK, KONKRET, dan ACTIONABLE.
PROMPT;

        // Replace placeholders
        $candidateJsonStr = json_encode($candidateJson, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $userPrompt = str_replace('{{CANDIDATE_JSON}}', $this->sanitizeForPrompt($candidateJsonStr), $userPrompt);
        $userPrompt = str_replace('{{JOB_REQUIREMENTS}}', $this->sanitizeForPrompt($jobRequirements), $userPrompt);

        $params = [
            'temperature' => 0.0,
            'maxOutputTokens' => 2048
        ];

        return $this->callAPI($systemPrompt, $userPrompt, $params, true);
    }

    /**
     * -----------------------
     * Rewrite bullet point (sama seperti sebelumnya, sedikit penyesuaian bahasa)
     * -----------------------
     */
    public function callRewrite($bullet, $jobRequirements = null) {
        if ($jobRequirements === null) {
            $jobRequirements = "Software Engineer position";
        }

        $systemPrompt = "Anda adalah CV writer profesional. Tugas: perbaiki bullet point CV agar ATS-friendly dan action-oriented.";

        $userPrompt = "REWRITE: Bullet asli: '" . $bullet . "'\n\n" .
                      "Target job: '" . $jobRequirements . "'\n\n" .
                      "Berikan 1 versi bullet baru yang:\n" .
                      "- Action-oriented (mulai dengan kata kerja kuat)\n" .
                      "- Spesifik dan terukur\n" .
                      "- ATS-friendly (gunakan keywords relevan)\n" .
                      "- Maksimal 1-2 kalimat\n\n" .
                      "Output plain text tanpa penjelasan.";

        $params = [
            'temperature' => 0.2,
            'maxOutputTokens' => 150
        ];

        return $this->callAPI($systemPrompt, $userPrompt, $params, false);
    }

    /**
     * -----------------------
     * Normalisasi CV (sama seperti sebelumnya)
     * -----------------------
     */
    private function normalizeCV($cvData) {
        if (!is_array($cvData)) return $cvData;

        // Flatten skills jika dalam format baru
        if (isset($cvData['skills']) && is_array($cvData['skills'])) {
            if (isset($cvData['skills']['technical']) || isset($cvData['skills']['soft'])) {
                $allSkills = [];
                if (isset($cvData['skills']['technical'])) {
                    $allSkills = array_merge($allSkills, $cvData['skills']['technical']);
                }
                if (isset($cvData['skills']['soft'])) {
                    $allSkills = array_merge($allSkills, $cvData['skills']['soft']);
                }
                if (isset($cvData['skills']['languages'])) {
                    $allSkills = array_merge($allSkills, $cvData['skills']['languages']);
                }
                $cvData['skills_detail'] = $cvData['skills'];
                $cvData['skills'] = $allSkills;
            }
        }

        // Ensure required fields exist
        $cvData['name'] = $cvData['name'] ?? 'Unknown';
        $cvData['emails'] = $cvData['emails'] ?? [];
        $cvData['phones'] = $cvData['phones'] ?? [];
        $cvData['positions'] = $cvData['positions'] ?? [];
        $cvData['skills'] = $cvData['skills'] ?? [];
        $cvData['education'] = $cvData['education'] ?? [];
        $cvData['summary'] = $cvData['summary'] ?? '';

        return $cvData;
    }

    /**
     * -----------------------
     * API call wrapper (sama seperti sebelumnya)
     * -----------------------
     */
    public function callAPI($systemPrompt, $userPrompt, $params = [], $expectJson = true) {
        $contents = [
            [
                'parts' => [
                    ['text' => $systemPrompt . "\n\n" . $userPrompt]
                ]
            ]
        ];
        return $this->callAPIWithPayload($contents, $params, $expectJson);
    }

    /**
     * -----------------------
     * callAPIWithPayload (cURL)
     * -----------------------
     */
    private function callAPIWithPayload($contents, $params = [], $expectJson = true) {
        $maxRetries = max(3, count($this->apiKeys) * 2); // Increase retry attempts
        $attempt = 0;
        $response = null;
        $result = null;

        while ($attempt < $maxRetries) {
            $currentKey = $this->getApiKey();
            $url = $this->endpoint . '?key=' . $currentKey;

            $payload = [
                'contents' => $contents,
                'generationConfig' => [
                    'temperature' => $params['temperature'] ?? 0.0,
                    'maxOutputTokens' => $params['maxOutputTokens'] ?? 1000
                ]
            ];

            $startTime = microtime(true);
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_TIMEOUT, 60); // Increase timeout

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            $responseTime = round((microtime(true) - $startTime) * 1000); // in milliseconds

            if ($error) {
                // Log failed request
                $this->apiMonitor->logRequest($currentKey, $this->endpoint, false, 'cURL error: ' . $error, $responseTime);
                return ['success' => false, 'error' => 'cURL error: ' . $error];
            }

            if ($httpCode !== 200) {
                // Parse error message for better feedback
                $errorMsg = $response;
                $errorJson = json_decode($response, true);
                if ($errorJson && isset($errorJson['error']['message'])) {
                    $errorMsg = $errorJson['error']['message'];
                }
                
                // Log detailed error
                error_log("GeminiClient - HTTP Error $httpCode");
                error_log("GeminiClient - Error message: " . $errorMsg);
                error_log("GeminiClient - Full response: " . substr($response, 0, 500));
                error_log("GeminiClient - Endpoint: " . $this->endpoint);
                error_log("GeminiClient - API Key (masked): " . substr($currentKey, 0, 10) . '...' . substr($currentKey, -4));
                
                // Log payload untuk debugging (tanpa file data yang besar)
                $debugPayload = $payload;
                if (isset($debugPayload['contents'][0]['parts'])) {
                    foreach ($debugPayload['contents'][0]['parts'] as $idx => $part) {
                        if (isset($part['inline_data']['data'])) {
                            $dataSize = strlen($part['inline_data']['data']);
                            $debugPayload['contents'][0]['parts'][$idx]['inline_data']['data'] = "[BASE64_DATA_SIZE: $dataSize bytes]";
                        }
                    }
                }
                error_log("GeminiClient - Payload structure: " . json_encode($debugPayload, JSON_PRETTY_PRINT));
                
                // Log failed request
                $this->apiMonitor->logRequest($currentKey, $this->endpoint, false, 'HTTP ' . $httpCode . ': ' . $errorMsg, $responseTime);
                
                // Handle HTTP 400 Bad Request specifically
                if ($httpCode == 400) {
                    $detailedError = "❌ HTTP 400 Bad Request - ";
                    
                    // Analisis penyebab spesifik
                    if (stripos($errorMsg, 'API key') !== false || stripos($errorMsg, 'invalid') !== false) {
                        $detailedError .= "API key tidak valid atau expired. Generate API key baru di https://aistudio.google.com/apikey";
                    } elseif (stripos($errorMsg, 'model') !== false) {
                        $detailedError .= "Model tidak tersedia atau nama model salah. Coba ganti ke gemini-1.5-flash di .env.php";
                    } elseif (stripos($errorMsg, 'mime') !== false || stripos($errorMsg, 'file') !== false) {
                        $detailedError .= "Format file tidak didukung atau file corrupt. Pastikan file PDF/DOCX valid";
                    } elseif (stripos($errorMsg, 'size') !== false || stripos($errorMsg, 'too large') !== false) {
                        $detailedError .= "File terlalu besar. Maksimal 20MB untuk Gemini API";
                    } else {
                        $detailedError .= "Request tidak valid. Detail: " . $errorMsg;
                    }
                    
                    error_log("GeminiClient - Detailed error: " . $detailedError);
                    return ['success' => false, 'error' => $detailedError];
                }
                
                if (($httpCode == 429 || $httpCode == 503) && $attempt < $maxRetries - 1) {
                    // Exponential backoff: 2^attempt seconds (1s, 2s, 4s, 8s, etc.)
                    $waitTime = min(pow(2, $attempt), 30); // Max 30 seconds
                    error_log("Rate limit hit (HTTP $httpCode). Waiting {$waitTime}s before retry " . ($attempt + 1) . "/$maxRetries");
                    sleep($waitTime);
                    
                    $this->rotateApiKey();
                    $attempt++;
                    continue;
                }
                
                return ['success' => false, 'error' => 'HTTP ' . $httpCode . ': ' . $errorMsg];
            }
            
            // Log successful request
            $this->apiMonitor->logRequest($currentKey, $this->endpoint, true, null, $responseTime);

            $result = json_decode($response, true);

            if (isset($result['candidates'][0]['finishReason']) &&
                $result['candidates'][0]['finishReason'] === 'MAX_TOKENS' &&
                $attempt < $maxRetries - 1) {
                $this->rotateApiKey();
                $attempt++;
                continue;
            }

            break;
        }

        if (isset($result['candidates'][0]['finishReason']) &&
            $result['candidates'][0]['finishReason'] === 'MAX_TOKENS') {
            return ['success' => false, 'error' => 'Gemini error: MAX_TOKENS - Semua API key sudah mencapai limit. Silakan coba lagi dalam beberapa menit.'];
        }
        
        // Check for RESOURCE_EXHAUSTED after all retries
        if ($attempt >= $maxRetries && !$result) {
            return ['success' => false, 'error' => 'Rate limit tercapai setelah ' . $maxRetries . ' percobaan. Silakan tunggu 1-5 menit dan coba lagi.'];
        }

        if (!isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            if (isset($result['candidates'][0]['finishReason'])) {
                $reason = $result['candidates'][0]['finishReason'];
                if ($reason === 'STOP') {
                    return ['success' => false, 'error' => 'Response tidak lengkap. Coba lagi atau gunakan CV yang lebih singkat.'];
                }
                return ['success' => false, 'error' => 'Gemini error: ' . $reason];
            }
            return ['success' => false, 'error' => 'Invalid response from Gemini'];
        }

        $text = trim($result['candidates'][0]['content']['parts'][0]['text']);

        // Bersihkan code fences
        $text = preg_replace('/```json\s*/', '', $text);
        $text = preg_replace('/```\s*/', '', $text);
        $text = trim($text);

        if ($expectJson) {
            $parsed = json_decode($text, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                // Retry dengan instruksi tegas kembalikan JSON saja
                return $this->retryCallWithJsonOnly($result, $text, $params);
            }
            return ['success' => true, 'data' => $parsed, 'raw' => $text];
        }

        return ['success' => true, 'data' => $text];
    }

    private function retryCallWithJsonOnly($result, $text, $params) {
        // Untuk menghindari loop tak berujung, kita berikan error yang informatif
        return ['success' => false, 'error' => 'Invalid JSON dari model. Konten model: ' . mb_substr($text, 0, 500)];
    }

    /**
     * -----------------------
     * Utility: sanitize string for embedding into prompt safely
     * (escape sequences that might break prompt formatting)
     * -----------------------
     */
    private function sanitizeForPrompt(string $s) : string {
        // Hapus null-bytes dan pastikan tidak ada control chars yang memecah prompt
        $s = str_replace("\0", '', $s);
        // Jika teks sangat panjang, Anda bisa memotongnya, tetapi di sini kita kembalikan full
        return $s;
    }
}

/**
 * -----------------------
 * Contoh penggunaan singkat:
 * -----------------------
 * require_once 'config.php'; // definisikan GEMINI_API_KEYS dan GEMINI_ENDPOINT
 * require_once 'GeminiClient.php';
 *
 * $client = new GeminiClient();
 *
 * // 1) Ekstrak & strukturkan CV
 * $fileData = file_get_contents('/path/to/cv.pdf'); // ganti sesuai file
 * $res = $client->callExtract(base64_encode($fileData), 'application/pdf');
 * if (!$res['success']) {
 *     var_dump($res);
 * } else {
 *     $cvJson = $res['data'];
 *     // 2) Evaluasi vs ATS
 *     $eval = $client->callEvaluate($cvJson, "Job: Software Engineer - Fullstack (Node, React)");
 *     var_dump($eval);
 * }
 *
 * Catatan: untuk file upload multimodal, endpoint Gemini Anda mungkin memerlukan format berbeda.
 */
