<?php
/**
 * CV Template Generator - New Design
 * Generate CV dengan template modern dari tes.html
 */

class CVTemplate {
    
    /**
     * Clean markdown formatting dari text
     */
    private function cleanMarkdown($text) {
        if (empty($text)) return $text;
        
        // Remove bold markdown (**text** atau __text__)
        $text = preg_replace('/\*\*(.*?)\*\*/', '$1', $text);
        $text = preg_replace('/__(.*?)__/', '$1', $text);
        
        // Remove italic markdown (*text* atau _text_)
        $text = preg_replace('/\*(.*?)\*/', '$1', $text);
        $text = preg_replace('/_(.*?)_/', '$1', $text);
        
        // Remove code markdown (`text`)
        $text = preg_replace('/`(.*?)`/', '$1', $text);
        
        // Remove strikethrough (~~text~~)
        $text = preg_replace('/~~(.*?)~~/', '$1', $text);
        
        return trim($text);
    }
    
    /**
     * Remove numbers and dates from text (but keep dates in proper format)
     */
    private function removeNumbersAndDates($text) {
        if (empty($text)) return $text;
        
        // Remove standalone numbers (1, 2, 3, etc) but not years
        $text = preg_replace('/\b(?!(19|20)\d{2}\b)\d+\b/', '', $text);
        
        // Remove percentages (85%, 90%, etc)
        $text = preg_replace('/\b\d+%/', '', $text);
        
        // Remove extra spaces
        $text = preg_replace('/\s+/', ' ', $text);
        
        return trim($text);
    }
    
    /**
     * Format date to "Month Year" format
     */
    private function formatDate($date) {
        if (empty($date)) return '';
        
        // If already in good format, return as is
        if (preg_match('/^(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]*\s+(19|20)\d{2}$/i', $date)) {
            return $date;
        }
        
        // Handle "YYYY-MM" format
        if (preg_match('/^(\d{4})-(\d{2})$/', $date, $matches)) {
            $year = $matches[1];
            $month = (int)$matches[2];
            $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            return $monthNames[$month - 1] . ' ' . $year;
        }
        
        // Handle "YYYY" format
        if (preg_match('/^\d{4}$/', $date)) {
            return $date;
        }
        
        // Handle timestamp or other formats
        $timestamp = strtotime($date);
        if ($timestamp !== false) {
            return date('M Y', $timestamp);
        }
        
        return $date;
    }
    
    /**
     * Format date range
     */
    private function formatDateRange($startDate, $endDate) {
        $start = $this->formatDate($startDate);
        $end = $this->formatDate($endDate);
        
        if (empty($start) && empty($end)) {
            return '';
        }
        
        if (empty($end) || strtolower($end) === 'present' || strtolower($end) === 'now') {
            $end = 'Present';
        }
        
        if (empty($start)) {
            return $end;
        }
        
        return $start . ' – ' . $end;
    }
    
    /**
     * Normalize language level to Basic or Advanced only
     */
    private function normalizeLanguageLevel($text) {
        $text = strtolower($text);
        
        // Advanced keywords
        $advancedKeywords = ['advanced', 'fluent', 'native', 'proficient', 'expert', 'lancar', 'mahir', 'fasih'];
        
        foreach ($advancedKeywords as $keyword) {
            if (strpos($text, $keyword) !== false) {
                return 'Advanced';
            }
        }
        
        // Default to Basic for beginner, intermediate, basic, menengah, pemula
        return 'Basic';
    }
    
    /**
     * Generate CV HTML dari template dengan AI enhancement
     */
    public function generateWithAI($cvData, $evaluation, $gemini) {
        // Get improved content from AI
        $improvedData = $this->improveWithAI($cvData, $evaluation, $gemini);
        
        // Generate CV with improved data
        $cvHtml = $this->generate($improvedData, $evaluation);
        
        return $cvHtml;
    }
    
    /**
     * Generate CV HTML dengan data yang sudah di-improve
     */
    public function generate($cvData, $evaluation = null) {
        $html = $this->getTemplateHeader();
        $html .= $this->generateHeader($cvData);
        $html .= $this->generateAbout($cvData);
        $html .= $this->generateEducation($cvData);
        $html .= $this->generateExperience($cvData);
        $html .= $this->generateProjects($cvData);
        $html .= $this->generateSkills($cvData);
        $html .= $this->generateCertifications($cvData);
        $html .= $this->generateAchievements($cvData);
        $html .= $this->generateVolunteer($cvData);
        $html .= $this->generateLanguages($cvData);
        $html .= $this->generateExtras($cvData);
        $html .= $this->getTemplateFooter();
        
        return $html;
    }
    
    /**
     * Template Header dengan CSS
     */
    private function getTemplateHeader() {
        return <<<'HTML'
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Curriculum Vitae</title>
<style>
  :root{
    --page-width:760px;
    --accent:#000;
    --muted:#333;
    --link:#0b66c3;
    --hr:#000;
    --font-family: "Calibri", "Arial", sans-serif;
  }

  html,body{margin:0;padding:0;background:#fff;font-family:var(--font-family);color:var(--muted);}

  .cv{
    width:var(--page-width);
    margin:18px auto;
    padding:20px 26px;
    box-sizing:border-box;
    font-size:12px;
    line-height:1.36;
  }

  /* Header */
  .header{ text-align:center; margin-bottom:8px; }
  .name{ font-size:20px; font-weight:800; color:#000; letter-spacing:0.6px; text-transform:uppercase; margin:0;}
  .contacts{ margin-top:6px; font-size:12px; color:var(--muted); }
  .contacts a{ color:var(--link); text-decoration:none; }
  .hr-top{ border-top:2px solid var(--hr); margin:12px 0; }

  /* Sections */
  .section{ margin-bottom:14px; }
  .section-title{ font-size:12px; font-weight:700; text-transform:uppercase; color:#000; margin:6px 0 4px 0; }
  .section-line{ border-top:1px solid #000; margin-top:-3px; margin-bottom:8px; }

  /* Entry layout with date on right */
  .entry{ display:flex; justify-content:space-between; gap:12px; align-items:flex-start; margin-bottom:8px; }
  .entry .left{ width: calc(100% - 140px); }
  .entry .right{ width:120px; text-align:right; font-size:11px; color:var(--muted); }

  .title{ font-weight:700; font-size:12px; color:#000; margin:0 0 3px 0; }
  .subtitle{ margin:0; font-size:11px; color:var(--muted); }

  ul.bullets{ margin:6px 0 0 18px; padding:0; color:var(--muted); font-size:11px; }
  ul.bullets li{ margin-bottom:6px; }

  /* Projects grid */
  .projects{ display:grid; grid-template-columns:repeat(2, 1fr); gap:12px; }
  .project{ border-left:3px solid #eee; padding-left:10px; }

  /* Skills */
  .skills-grid{ display:flex; gap:8px; flex-wrap:wrap; margin-top:6px; }
  .skill-tag{ background:#f5f5f5; padding:6px 8px; border-radius:4px; font-size:11px; color:var(--muted); }
  .skill-row{ margin-bottom:8px; }
  .skill-name{ font-size:11px; margin:0 0 4px 0; }
  .skill-bar{ background:#eee; height:8px; border-radius:6px; overflow:hidden; }
  .skill-bar > span{ display:block; height:100%; background:#111; }

  /* Languages & small lists */
  .small-list{ margin:6px 0 0 18px; font-size:11px; color:var(--muted); }
  .note{ font-size:11px; color:var(--muted); margin-top:8px; }

  /* Footer/print */
  @media print {
    .cv{ padding:12mm; width:auto; }
  }

  @media (max-width:800px){
    .cv{ width:auto; margin:10px; padding:12px; }
    .projects{ grid-template-columns:1fr; }
    .entry .right{ width:140px; }
  }
</style>
</head>
<body>
  <div class="cv" role="document" aria-label="Curriculum Vitae">
HTML;
    }
    
    /**
     * Generate Header Section
     */
    private function generateHeader($cvData) {
        $name = $this->cleanMarkdown($cvData['name'] ?? 'Professional');
        $name = htmlspecialchars($name);
        $location = $this->cleanMarkdown($cvData['location'] ?? '');
        $location = htmlspecialchars($location);
        $email = $cvData['emails'][0] ?? '';
        $phone = $cvData['phones'][0] ?? '';
        $linkedin = htmlspecialchars($cvData['linkedin'] ?? '');
        
        $contacts = [];
        if ($location) $contacts[] = $location;
        if ($email) $contacts[] = '<a href="mailto:' . htmlspecialchars($email) . '">' . htmlspecialchars($email) . '</a>';
        if ($phone) $contacts[] = htmlspecialchars($phone);
        if ($linkedin) $contacts[] = '<a href="' . $linkedin . '" target="_blank" rel="noopener">LinkedIn</a>';
        
        $contactsHtml = implode(' &nbsp;|&nbsp; ', $contacts);
        
        return <<<HTML
    <header class="header">
      <h1 class="name">{$name}</h1>
      <div class="contacts">{$contactsHtml}</div>
      <div class="hr-top" aria-hidden="true"></div>
    </header>

HTML;
    }
    
    /**
     * Generate About/Summary Section
     */
    private function generateAbout($cvData) {
        $summary = $this->cleanMarkdown($cvData['summary'] ?? '');
        $summary = htmlspecialchars($summary);
        
        if (empty($summary)) {
            return '';
        }
        
        return <<<HTML
    <section class="section" id="about">
      <h2 class="section-title">About Me</h2>
      <div class="section-line" aria-hidden="true"></div>
      <p class="subtitle">{$summary}</p>
    </section>

HTML;
    }
    
    /**
     * Generate Education Section
     */
    private function generateEducation($cvData) {
        if (empty($cvData['education']) || !is_array($cvData['education'])) {
            return '';
        }
        
        $html = <<<HTML
    <section class="section" id="education">
      <h2 class="section-title">Education</h2>
      <div class="section-line" aria-hidden="true"></div>

HTML;
        
        foreach ($cvData['education'] as $edu) {
            $institution = $this->cleanMarkdown($edu['institution'] ?? '');
            $institution = $this->removeNumbersAndDates($institution);
            $institution = htmlspecialchars($institution);
            
            $degree = $this->cleanMarkdown($edu['degree'] ?? '');
            $degree = $this->removeNumbersAndDates($degree);
            $degree = htmlspecialchars($degree);
            
            $field = $this->cleanMarkdown($edu['field'] ?? '');
            $field = $this->removeNumbersAndDates($field);
            $field = htmlspecialchars($field);
            
            $honors = $this->cleanMarkdown($edu['honors'] ?? '');
            $honors = $this->removeNumbersAndDates($honors);
            $honors = htmlspecialchars($honors);
            
            // Format year
            $year = $this->formatDate($edu['year'] ?? '');
            $year = htmlspecialchars($year);
            
            if (empty($institution)) continue;
            
            $degreeText = $degree;
            if ($field) $degreeText .= ' — ' . $field;
            
            $notes = [];
            if ($honors) $notes[] = $honors;
            $notesText = !empty($notes) ? '<br>' . implode(' | ', $notes) : '';
            
            $html .= <<<HTML
      <article class="entry">
        <div class="left">
          <p class="title">{$institution}</p>
          <p class="subtitle"><em>{$degreeText}</em>{$notesText}</p>
        </div>
        <div class="right">{$year}</div>
      </article>

HTML;
        }
        
        $html .= "    </section>\n\n";
        return $html;
    }
    
    /**
     * Generate Experience Section
     */
    private function generateExperience($cvData) {
        if (empty($cvData['positions']) || !is_array($cvData['positions'])) {
            return '';
        }
        
        $html = <<<HTML
    <section class="section" id="experience">
      <h2 class="section-title">Experience</h2>
      <div class="section-line" aria-hidden="true"></div>

HTML;
        
        foreach ($cvData['positions'] as $pos) {
            $title = $this->cleanMarkdown($pos['title'] ?? '');
            $title = $this->removeNumbersAndDates($title);
            $title = htmlspecialchars($title);
            
            $company = $this->cleanMarkdown($pos['company'] ?? '');
            $company = $this->removeNumbersAndDates($company);
            $company = htmlspecialchars($company);
            
            $description = $pos['description'] ?? '';
            $achievements = $pos['achievements'] ?? [];
            
            // Format date range
            $startDate = $pos['start_date'] ?? '';
            $endDate = $pos['end_date'] ?? '';
            $dateRange = $this->formatDateRange($startDate, $endDate);
            $dateRange = htmlspecialchars($dateRange);
            
            if (empty($title)) continue;
            
            // Parse description into bullets
            $bullets = [];
            if (!empty($description)) {
                $lines = explode("\n", $description);
                foreach ($lines as $line) {
                    $line = trim($line);
                    $line = preg_replace('/^[-•*]\s*/', '', $line);
                    $line = $this->cleanMarkdown($line);
                    $line = $this->removeNumbersAndDates($line);
                    if (!empty($line) && strlen($line) > 3) {
                        $bullets[] = htmlspecialchars($line);
                    }
                }
            }
            
            // Add achievements
            if (!empty($achievements) && is_array($achievements)) {
                foreach ($achievements as $achievement) {
                    if (!empty($achievement)) {
                        $achievement = $this->cleanMarkdown($achievement);
                        $achievement = $this->removeNumbersAndDates($achievement);
                        if (strlen($achievement) > 3) {
                            $bullets[] = htmlspecialchars($achievement);
                        }
                    }
                }
            }
            
            $bulletsHtml = '';
            if (!empty($bullets)) {
                $bulletsHtml = "<ul class=\"bullets\">\n";
                foreach ($bullets as $bullet) {
                    $bulletsHtml .= "            <li>{$bullet}</li>\n";
                }
                $bulletsHtml .= "          </ul>";
            }
            
            $html .= <<<HTML
      <article class="entry">
        <div class="left">
          <p class="title">{$title}</p>
          <p class="subtitle"><em>{$company}</em></p>
          {$bulletsHtml}
        </div>
        <div class="right">{$dateRange}</div>
      </article>

HTML;
        }
        
        $html .= "    </section>\n\n";
        return $html;
    }
    
    /**
     * Generate Projects Section
     */
    private function generateProjects($cvData) {
        if (empty($cvData['projects']) || !is_array($cvData['projects'])) {
            return '';
        }
        
        $html = <<<HTML
    <section class="section" id="projects">
      <h2 class="section-title">Projects</h2>
      <div class="section-line" aria-hidden="true"></div>
      <div class="projects">

HTML;
        
        foreach ($cvData['projects'] as $project) {
            $name = $this->cleanMarkdown($project['name'] ?? '');
            $name = htmlspecialchars($name);
            $description = $this->cleanMarkdown($project['description'] ?? '');
            $description = htmlspecialchars($description);
            $technologies = $project['technologies'] ?? [];
            
            if (empty($name)) continue;
            
            $techText = '';
            if (!empty($technologies) && is_array($technologies)) {
                $cleanTech = array_map([$this, 'cleanMarkdown'], $technologies);
                $techText = '<br><strong>Tech:</strong> ' . htmlspecialchars(implode(', ', $cleanTech));
            }
            
            $html .= <<<HTML
        <div class="project">
          <p class="title">{$name}</p>
          <p class="subtitle">{$description}{$techText}</p>
        </div>

HTML;
        }
        
        $html .= "      </div>\n    </section>\n\n";
        return $html;
    }
    
    /**
     * Generate Skills Section
     */
    private function generateSkills($cvData) {
        $skills = $cvData['skills'] ?? [];
        $skillsDetail = $cvData['skills_detail'] ?? null;
        
        if (empty($skills) && empty($skillsDetail)) {
            return '';
        }
        
        $html = <<<HTML
    <section class="section" id="skills">
      <h2 class="section-title">Skills</h2>
      <div class="section-line" aria-hidden="true"></div>

HTML;
        
        // Skill tags only (no bars, no numbers)
        if (!empty($skills) && is_array($skills)) {
            $html .= "      <div class=\"skills-grid\">\n";
            
            $cleanedSkills = [];
            foreach ($skills as $skill) {
                // Clean skill name from markdown and numbers
                $skillName = $this->cleanMarkdown($skill);
                $skillName = $this->removeNumbersAndDates($skillName);
                $skillName = trim($skillName);
                
                // Skip empty or very short skills
                if (!empty($skillName) && strlen($skillName) > 1) {
                    $cleanedSkills[] = $skillName;
                }
            }
            
            // Remove duplicates and limit to 25 skills
            $cleanedSkills = array_unique($cleanedSkills);
            $cleanedSkills = array_slice($cleanedSkills, 0, 25);
            
            foreach ($cleanedSkills as $skillName) {
                $skillName = htmlspecialchars($skillName);
                $html .= "        <div class=\"skill-tag\">{$skillName}</div>\n";
            }
            $html .= "      </div>\n";
        }
        
        $html .= "    </section>\n\n";
        return $html;
    }
    
    /**
     * Generate Certifications Section
     */
    private function generateCertifications($cvData) {
        if (empty($cvData['certifications']) || !is_array($cvData['certifications'])) {
            return '';
        }
        
        $html = <<<HTML
    <section class="section" id="certifications">
      <h2 class="section-title">Certifications &amp; Courses</h2>
      <div class="section-line" aria-hidden="true"></div>

HTML;
        
        foreach ($cvData['certifications'] as $cert) {
            $name = $this->cleanMarkdown($cert['name'] ?? '');
            $name = $this->removeNumbersAndDates($name);
            $name = htmlspecialchars($name);
            
            $issuer = $this->cleanMarkdown($cert['issuer'] ?? '');
            $issuer = $this->removeNumbersAndDates($issuer);
            $issuer = htmlspecialchars($issuer);
            
            // Format date
            $date = $this->formatDate($cert['date'] ?? '');
            $date = htmlspecialchars($date);
            
            if (empty($name)) continue;
            
            $html .= <<<HTML
      <article class="entry">
        <div class="left">
          <p class="title">{$issuer}</p>
          <p class="subtitle">{$name}</p>
        </div>
        <div class="right">{$date}</div>
      </article>

HTML;
        }
        
        $html .= "    </section>\n\n";
        return $html;
    }
    
    /**
     * Generate Achievements Section
     */
    private function generateAchievements($cvData) {
        if (empty($cvData['awards']) || !is_array($cvData['awards'])) {
            return '';
        }
        
        $html = <<<HTML
    <section class="section" id="achievements">
      <h2 class="section-title">Achievements</h2>
      <div class="section-line" aria-hidden="true"></div>
      <ul class="small-list">

HTML;
        
        foreach ($cvData['awards'] as $award) {
            if (is_string($award)) {
                $awardText = $this->cleanMarkdown($award);
                $awardText = $this->removeNumbersAndDates($awardText);
                $awardText = htmlspecialchars($awardText);
                if (!empty($awardText) && strlen($awardText) > 3) {
                    $html .= "        <li>{$awardText}</li>\n";
                }
            } elseif (is_array($award)) {
                $name = $this->cleanMarkdown($award['name'] ?? '');
                $name = $this->removeNumbersAndDates($name);
                $name = htmlspecialchars($name);
                if (!empty($name) && strlen($name) > 3) {
                    $html .= "        <li>{$name}</li>\n";
                }
            }
        }
        
        $html .= "      </ul>\n    </section>\n\n";
        return $html;
    }
    
    /**
     * Generate Volunteer Section
     */
    private function generateVolunteer($cvData) {
        if (empty($cvData['volunteer']) || !is_array($cvData['volunteer'])) {
            return '';
        }
        
        $html = <<<HTML
    <section class="section" id="organization">
      <h2 class="section-title">Organization / Volunteer</h2>
      <div class="section-line" aria-hidden="true"></div>

HTML;
        
        foreach ($cvData['volunteer'] as $vol) {
            $organization = $this->cleanMarkdown($vol['organization'] ?? '');
            $organization = $this->removeNumbersAndDates($organization);
            $organization = htmlspecialchars($organization);
            
            $role = $this->cleanMarkdown($vol['role'] ?? '');
            $role = $this->removeNumbersAndDates($role);
            $role = htmlspecialchars($role);
            
            $description = $vol['description'] ?? '';
            
            // Format date
            $date = $this->formatDate($vol['date'] ?? '');
            $date = htmlspecialchars($date);
            
            if (empty($organization)) continue;
            
            // Parse description into bullets
            $bullets = [];
            if (!empty($description)) {
                $lines = explode("\n", $description);
                foreach ($lines as $line) {
                    $line = trim($line);
                    $line = preg_replace('/^[-•*]\s*/', '', $line);
                    $line = $this->cleanMarkdown($line);
                    $line = $this->removeNumbersAndDates($line);
                    if (!empty($line) && strlen($line) > 3) {
                        $bullets[] = htmlspecialchars($line);
                    }
                }
            }
            
            $bulletsHtml = '';
            if (!empty($bullets)) {
                $bulletsHtml = "<ul class=\"bullets\">\n";
                foreach ($bullets as $bullet) {
                    $bulletsHtml .= "            <li>{$bullet}</li>\n";
                }
                $bulletsHtml .= "          </ul>";
            }
            
            $html .= <<<HTML
      <article class="entry">
        <div class="left">
          <p class="title">{$organization}</p>
          <p class="subtitle"><em>{$role}</em></p>
          {$bulletsHtml}
        </div>
        <div class="right">{$date}</div>
      </article>

HTML;
        }
        
        $html .= "    </section>\n\n";
        return $html;
    }
    
    /**
     * Generate Languages Section
     */
    private function generateLanguages($cvData) {
        $languages = [];
        
        // Check in skills_detail first
        if (!empty($cvData['skills_detail']['languages'])) {
            $languages = $cvData['skills_detail']['languages'];
        }
        // Fallback to skills array
        elseif (!empty($cvData['skills'])) {
            foreach ($cvData['skills'] as $skill) {
                $skillLower = strtolower($skill);
                if (in_array($skillLower, ['english', 'indonesian', 'bahasa indonesia', 'mandarin', 'japanese', 'korean', 'french', 'german', 'spanish', 'arabic', 'portuguese', 'russian', 'dutch', 'italian'])) {
                    $languages[] = $skill;
                }
            }
        }
        
        if (empty($languages)) {
            return '';
        }
        
        $html = <<<HTML
    <section class="section" id="languages">
      <h2 class="section-title">Languages</h2>
      <div class="section-line" aria-hidden="true"></div>
      <ul class="small-list">

HTML;
        
        foreach ($languages as $lang) {
            // Clean markdown
            $langText = $this->cleanMarkdown($lang);
            
            // Extract language name and level
            $parts = preg_split('/[-—–()\[\]]/', $langText);
            $languageName = trim($parts[0]);
            $level = isset($parts[1]) ? trim($parts[1]) : '';
            
            // Normalize level to Basic or Advanced only
            if (!empty($level)) {
                $normalizedLevel = $this->normalizeLanguageLevel($level);
                $langText = $languageName . ' — ' . $normalizedLevel;
            } else {
                // If no level specified, default to Basic
                $langText = $languageName . ' — Basic';
            }
            
            $langText = htmlspecialchars($langText);
            $html .= "        <li>{$langText}</li>\n";
        }
        
        $html .= "      </ul>\n    </section>\n\n";
        return $html;
    }
    
    /**
     * Generate Extras (Interests & References)
     */
    private function generateExtras($cvData) {
        $additionalInfo = $this->cleanMarkdown($cvData['additional_info'] ?? '');
        $additionalInfo = htmlspecialchars($additionalInfo);
        
        if (empty($additionalInfo)) {
            return '';
        }
        
        return <<<HTML
    <section class="section" id="extras">
      <h2 class="section-title">Additional Information</h2>
      <div class="section-line" aria-hidden="true"></div>
      <p class="subtitle">{$additionalInfo}</p>
      <p class="note"><strong>References:</strong> Available upon request.</p>
    </section>

HTML;
    }
    
    /**
     * Template Footer
     */
    private function getTemplateFooter() {
        return <<<'HTML'
  </div>
</body>
</html>
HTML;
    }
    
    /**
     * Improve CV content dengan Gemini AI
     */
    private function improveWithAI($cvData, $evaluation, $gemini) {
        $role = $cvData['positions'][0]['title'] ?? 'Professional';
        $gaps = $evaluation['gaps'] ?? [];
        
        // Improve positions/experience descriptions
        if (!empty($cvData['positions'])) {
            foreach ($cvData['positions'] as &$position) {
                if (!empty($position['description'])) {
                    $improved = $this->improveDescription($position['description'], $role, $gemini);
                    if ($improved) {
                        $position['description'] = $improved;
                    }
                }
            }
        }
        
        // Improve summary if exists
        if (!empty($cvData['summary'])) {
            $improvedSummary = $this->improveSummary($cvData['summary'], $role, $cvData, $gemini);
            if ($improvedSummary) {
                $cvData['summary'] = $improvedSummary;
            }
        } else {
            // Generate summary if not exists
            $cvData['summary'] = $this->generateSummary($cvData, $role, $gemini);
        }
        
        return $cvData;
    }
    
    /**
     * Improve job description
     */
    private function improveDescription($description, $role, $gemini) {
        $systemPrompt = "Anda adalah CV writer profesional yang ahli dalam menulis deskripsi pekerjaan yang ATS-friendly dan impactful.";
        
        $prompt = "Perbaiki deskripsi pekerjaan berikut untuk role '{$role}':\n\n";
        $prompt .= $description . "\n\n";
        $prompt .= "ATURAN KETAT:\n";
        $prompt .= "1. Gunakan action verbs yang kuat (Memimpin, Mengembangkan, Mengimplementasikan, Meningkatkan)\n";
        $prompt .= "2. Jika informasi minim, kembangkan dengan professional namun tetap jujur dan realistis\n";
        $prompt .= "3. Fokus pada achievements dan impact, bukan hanya responsibilities\n";
        $prompt .= "4. Maksimal 3-4 bullet points\n";
        $prompt .= "5. Setiap bullet 1-2 kalimat, concise dan impactful\n";
        $prompt .= "6. Gunakan Bahasa Indonesia yang profesional dan formal\n";
        $prompt .= "7. JANGAN berikan multiple opsi atau pilihan\n";
        $prompt .= "8. JANGAN gunakan kata 'Opsi', 'Pilihan', 'Alternatif'\n";
        $prompt .= "9. Berikan HANYA 1 versi final yang terbaik\n\n";
        $prompt .= "OUTPUT FORMAT:\n";
        $prompt .= "- Berikan HANYA poin-poin yang sudah diperbaiki\n";
        $prompt .= "- Satu poin per baris\n";
        $prompt .= "- TANPA penomoran, tanda dash, atau bullet\n";
        $prompt .= "- TANPA penjelasan tambahan\n";
        $prompt .= "- LANGSUNG ke konten";
        
        $result = $gemini->callAPI(
            $systemPrompt,
            $prompt,
            ['temperature' => 0.2, 'maxOutputTokens' => 500],
            false
        );
        
        return $result['success'] ? $result['data'] : null;
    }
    
    /**
     * Improve summary
     */
    private function improveSummary($summary, $role, $cvData, $gemini) {
        $systemPrompt = "Anda adalah CV writer profesional yang ahli menulis professional summary yang menarik dan ATS-friendly.";
        
        $yearsExp = $cvData['total_experience_years'] ?? 0;
        $skills = !empty($cvData['skills']) ? implode(', ', array_slice($cvData['skills'], 0, 5)) : '';
        
        $prompt = "Perbaiki professional summary berikut:\n\n";
        $prompt .= $summary . "\n\n";
        $prompt .= "KONTEKS:\n";
        $prompt .= "- Role: {$role}\n";
        $prompt .= "- Experience: {$yearsExp} tahun\n";
        $prompt .= "- Key Skills: {$skills}\n\n";
        $prompt .= "ATURAN KETAT:\n";
        $prompt .= "1. Maksimal 2-3 kalimat yang padat dan bermakna\n";
        $prompt .= "2. Highlight expertise, pengalaman, dan value proposition\n";
        $prompt .= "3. Professional, formal, dan to the point\n";
        $prompt .= "4. Gunakan Bahasa Indonesia yang baku\n";
        $prompt .= "5. Jika informasi minim, kembangkan dengan professional namun tetap jujur\n";
        $prompt .= "6. JANGAN berikan multiple opsi atau pilihan\n";
        $prompt .= "7. JANGAN gunakan kata 'Opsi', 'Pilihan', 'Alternatif', atau penjelasan dalam kurung\n";
        $prompt .= "8. JANGAN gunakan simbol > atau formatting markdown\n";
        $prompt .= "9. Berikan HANYA 1 versi final yang terbaik\n\n";
        $prompt .= "OUTPUT FORMAT:\n";
        $prompt .= "- Berikan HANYA teks summary yang sudah diperbaiki\n";
        $prompt .= "- TANPA penjelasan, label, atau keterangan tambahan\n";
        $prompt .= "- LANGSUNG ke konten summary";
        
        $result = $gemini->callAPI(
            $systemPrompt,
            $prompt,
            ['temperature' => 0.2, 'maxOutputTokens' => 200],
            false
        );
        
        return $result['success'] ? $result['data'] : null;
    }
    
    /**
     * Generate summary if not exists
     */
    private function generateSummary($cvData, $role, $gemini) {
        $systemPrompt = "Anda adalah CV writer profesional yang ahli menulis professional summary yang menarik dan ATS-friendly.";
        
        $yearsExp = $cvData['total_experience_years'] ?? 0;
        $skills = !empty($cvData['skills']) ? implode(', ', array_slice($cvData['skills'], 0, 5)) : '';
        $education = !empty($cvData['education'][0]['degree']) ? $cvData['education'][0]['degree'] : '';
        
        // Determine experience level with professional terminology
        $expLevel = 'berpengalaman';
        if ($yearsExp == 0) {
            $expLevel = 'entry-level professional dengan latar belakang akademis yang kuat';
        } elseif ($yearsExp < 2) {
            $expLevel = 'profesional muda dengan pengalaman praktis';
        } elseif ($yearsExp >= 5) {
            $expLevel = 'profesional berpengalaman dengan track record yang solid';
        }
        
        $prompt = "Buat professional summary untuk CV dengan data berikut:\n\n";
        $prompt .= "- Role/Minat: {$role}\n";
        $prompt .= "- Experience: {$yearsExp} tahun ({$expLevel})\n";
        $prompt .= "- Education: {$education}\n";
        $prompt .= "- Key Skills: {$skills}\n\n";
        $prompt .= "ATURAN KETAT:\n";
        $prompt .= "1. Maksimal 2-3 kalimat yang padat dan bermakna\n";
        $prompt .= "2. Sesuaikan dengan level pengalaman (entry-level, junior, senior)\n";
        $prompt .= "3. Jika pengalaman minim, fokus pada kompetensi, pendidikan, dan technical skills\n";
        $prompt .= "4. Jika pengalaman banyak, fokus pada expertise dan achievements\n";
        $prompt .= "5. Professional, formal, dan to the point\n";
        $prompt .= "6. Gunakan Bahasa Indonesia yang baku dan profesional\n";
        $prompt .= "7. Kembangkan dengan professional namun tetap jujur dan realistis\n";
        $prompt .= "8. JANGAN berikan multiple opsi atau pilihan\n";
        $prompt .= "9. JANGAN gunakan kata 'Opsi', 'Pilihan', 'Alternatif'\n";
        $prompt .= "10. JANGAN gunakan simbol > atau formatting markdown\n";
        $prompt .= "11. Berikan HANYA 1 versi final yang terbaik\n";
        $prompt .= "12. HINDARI istilah informal seperti 'lulusan baru', 'bersemangat', 'termotivasi'\n";
        $prompt .= "13. GUNAKAN istilah profesional seperti 'entry-level professional', 'memiliki kompetensi', 'siap berkontribusi'\n";
        $prompt .= "14. Fokus pada VALUE yang bisa diberikan, bukan status sebagai fresh graduate\n\n";
        $prompt .= "CONTOH YANG BAIK (untuk fresh graduate/entry-level):\n";
        $prompt .= "\"Entry-level Cybersecurity Professional dengan pemahaman mendalam tentang network security, penetration testing, dan vulnerability assessment. Memiliki pengalaman praktis dalam SIEM monitoring (Splunk, Wazuh) dan security tools (Nmap, Burp Suite, Metasploit). Siap berkontribusi dalam mengidentifikasi dan menanggulangi ancaman keamanan dengan pendekatan analitis dan proaktif.\"\n\n";
        $prompt .= "CONTOH YANG BURUK (JANGAN DITIRU):\n";
        $prompt .= "\"Lulusan baru yang bersemangat dan termotivasi untuk belajar cybersecurity. Saya sangat antusias dan ingin berkontribusi di perusahaan Anda.\"\n\n";
        $prompt .= "OUTPUT FORMAT:\n";
        $prompt .= "- Berikan HANYA teks summary\n";
        $prompt .= "- TANPA penjelasan, label, atau keterangan\n";
        $prompt .= "- LANGSUNG ke konten summary\n";
        $prompt .= "- Fokus pada KOMPETENSI dan VALUE, bukan status atau emosi";
        
        $result = $gemini->callAPI(
            $systemPrompt,
            $prompt,
            ['temperature' => 0.3, 'maxOutputTokens' => 200],
            false
        );
        
        if ($result['success']) {
            // Clean any remaining markdown or formatting
            $summary = $result['data'];
            $summary = preg_replace('/^>\s*/', '', $summary); // Remove > at start
            $summary = preg_replace('/Opsi\s+\d+[:\s\(].*?[\):]?\s*/i', '', $summary); // Remove "Opsi 1:", etc
            $summary = trim($summary);
            return $summary;
        }
        
        // Fallback based on experience level
        if ($yearsExp == 0) {
            return "Lulusan {$education} yang termotivasi dengan minat tinggi di bidang {$role}. Memiliki kemampuan {$skills} dan siap berkontribusi dalam lingkungan profesional.";
        } else {
            return "Profesional berpengalaman {$yearsExp} tahun di bidang {$role} dengan keahlian dalam {$skills}. Mampu memberikan kontribusi signifikan dan bekerja dalam tim.";
        }
    }
}
