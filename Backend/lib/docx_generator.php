<?php
/**
 * DOCX Generator - ATS-Friendly CV Generator
 * Generate CV dalam format DOCX yang optimal untuk ATS
 */

require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Style\Font;
use PhpOffice\PhpWord\SimpleType\Jc;

class DocxGenerator {
    
    private $phpWord;
    private $section;
    
    /**
     * Generate DOCX file dari CV data
     */
    public function generate($cvData, $outputPath) {
        $this->phpWord = new PhpWord();
        
        // Set document properties
        $properties = $this->phpWord->getDocInfo();
        $properties->setCreator('CVerity AI');
        $properties->setTitle('Curriculum Vitae');
        
        // Define styles
        $this->defineStyles();
        
        // Add section with margins
        $this->section = $this->phpWord->addSection([
            'marginTop' => 720,    // 0.5 inch
            'marginBottom' => 720,
            'marginLeft' => 720,
            'marginRight' => 720,
        ]);
        
        // Generate content
        $this->addHeader($cvData);
        $this->addSummary($cvData);
        $this->addExperience($cvData);
        $this->addEducation($cvData);
        $this->addSkills($cvData);
        $this->addCertifications($cvData);
        $this->addProjects($cvData);
        $this->addVolunteer($cvData);
        $this->addLanguages($cvData);
        
        // Save file
        $objWriter = IOFactory::createWriter($this->phpWord, 'Word2007');
        $objWriter->save($outputPath);
        
        return true;
    }
    
    /**
     * Define document styles
     */
    private function defineStyles() {
        // Name style
        $this->phpWord->addFontStyle('nameStyle', [
            'name' => 'Calibri',
            'size' => 18,
            'bold' => true,
            'color' => '000000',
        ]);
        
        // Contact style
        $this->phpWord->addFontStyle('contactStyle', [
            'name' => 'Calibri',
            'size' => 10,
            'color' => '333333',
        ]);
        
        // Section heading style
        $this->phpWord->addFontStyle('sectionHeading', [
            'name' => 'Calibri',
            'size' => 12,
            'bold' => true,
            'color' => '000000',
            'allCaps' => true,
        ]);
        
        // Job title style
        $this->phpWord->addFontStyle('jobTitle', [
            'name' => 'Calibri',
            'size' => 11,
            'bold' => true,
            'color' => '000000',
        ]);
        
        // Company/Institution style
        $this->phpWord->addFontStyle('company', [
            'name' => 'Calibri',
            'size' => 10,
            'italic' => true,
            'color' => '333333',
        ]);
        
        // Body text style
        $this->phpWord->addFontStyle('bodyText', [
            'name' => 'Calibri',
            'size' => 10,
            'color' => '333333',
        ]);
        
        // Date style
        $this->phpWord->addFontStyle('dateStyle', [
            'name' => 'Calibri',
            'size' => 10,
            'color' => '666666',
        ]);
    }
    
    /**
     * Add header with name and contact info
     */
    private function addHeader($cvData) {
        $name = $cvData['name'] ?? 'Professional';
        
        // Name - centered
        $this->section->addText(
            strtoupper($name),
            'nameStyle',
            ['alignment' => Jc::CENTER, 'spaceAfter' => 60]
        );
        
        // Contact info - centered
        $contacts = [];
        if (!empty($cvData['location'])) {
            $contacts[] = $cvData['location'];
        }
        if (!empty($cvData['emails'][0])) {
            $contacts[] = $cvData['emails'][0];
        }
        if (!empty($cvData['phones'][0])) {
            $contacts[] = $cvData['phones'][0];
        }
        if (!empty($cvData['linkedin'])) {
            $contacts[] = 'LinkedIn: ' . $cvData['linkedin'];
        }
        
        if (!empty($contacts)) {
            $contactText = implode(' | ', $contacts);
            $this->section->addText(
                $contactText,
                'contactStyle',
                ['alignment' => Jc::CENTER, 'spaceAfter' => 120]
            );
        }
        
        // Horizontal line using border instead of addLine (more compatible)
        $this->section->addText(
            str_repeat('─', 80),
            ['name' => 'Calibri', 'size' => 8, 'color' => '666666'],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 120]
        );
    }
    
    /**
     * Add summary/about section
     */
    private function addSummary($cvData) {
        if (empty($cvData['summary'])) {
            return;
        }
        
        $this->addSectionHeading('PROFESSIONAL SUMMARY');
        
        $this->section->addText(
            $cvData['summary'],
            'bodyText',
            ['alignment' => Jc::BOTH, 'spaceAfter' => 200]
        );
    }
    
    /**
     * Add experience section
     */
    private function addExperience($cvData) {
        if (empty($cvData['positions']) || !is_array($cvData['positions'])) {
            return;
        }
        
        $this->addSectionHeading('PROFESSIONAL EXPERIENCE');
        
        foreach ($cvData['positions'] as $position) {
            $title = $position['title'] ?? '';
            $company = $position['company'] ?? '';
            $startDate = $this->formatDate($position['start_date'] ?? '');
            $endDate = $this->formatDate($position['end_date'] ?? '');
            
            if (empty($title)) continue;
            
            // Job title and date on same line
            $textRun = $this->section->addTextRun(['spaceAfter' => 0]);
            $textRun->addText($title, 'jobTitle');
            
            if ($startDate || $endDate) {
                $dateRange = $startDate;
                if ($endDate && strtolower($endDate) !== 'present') {
                    $dateRange .= ' – ' . $endDate;
                } else {
                    $dateRange .= ' – Present';
                }
                $textRun->addText(' (' . $dateRange . ')', 'dateStyle');
            }
            
            // Company
            if ($company) {
                $this->section->addText(
                    $company,
                    'company',
                    ['spaceAfter' => 80]
                );
            }
            
            // Description bullets
            $description = $position['description'] ?? '';
            $achievements = $position['achievements'] ?? [];
            
            $bullets = $this->parseBullets($description);
            
            // Add achievements
            if (!empty($achievements) && is_array($achievements)) {
                foreach ($achievements as $achievement) {
                    if (!empty($achievement)) {
                        $bullets[] = $achievement;
                    }
                }
            }
            
            if (!empty($bullets)) {
                foreach ($bullets as $bullet) {
                    $this->section->addListItem(
                        $bullet,
                        0,
                        'bodyText',
                        null,
                        ['spaceAfter' => 60]
                    );
                }
            }
            
            $this->section->addTextBreak(1);
        }
    }
    
    /**
     * Add education section
     */
    private function addEducation($cvData) {
        if (empty($cvData['education']) || !is_array($cvData['education'])) {
            return;
        }
        
        $this->addSectionHeading('EDUCATION');
        
        foreach ($cvData['education'] as $edu) {
            $institution = $edu['institution'] ?? '';
            $degree = $edu['degree'] ?? '';
            $field = $edu['field'] ?? '';
            $year = $this->formatDate($edu['year'] ?? '');
            $gpa = $edu['gpa'] ?? '';
            $honors = $edu['honors'] ?? '';
            
            if (empty($institution)) continue;
            
            // Institution and year
            $textRun = $this->section->addTextRun(['spaceAfter' => 0]);
            $textRun->addText($institution, 'jobTitle');
            if ($year) {
                $textRun->addText(' (' . $year . ')', 'dateStyle');
            }
            
            // Degree and field
            $degreeText = $degree;
            if ($field) {
                $degreeText .= ' in ' . $field;
            }
            if ($degreeText) {
                $this->section->addText(
                    $degreeText,
                    'company',
                    ['spaceAfter' => 60]
                );
            }
            
            // GPA and honors
            $extras = [];
            if ($gpa) $extras[] = 'GPA: ' . $gpa;
            if ($honors) $extras[] = $honors;
            
            if (!empty($extras)) {
                $this->section->addText(
                    implode(' | ', $extras),
                    'bodyText',
                    ['spaceAfter' => 120]
                );
            }
            
            $this->section->addTextBreak(1);
        }
    }
    
    /**
     * Add skills section
     */

    private function addSkills($cvData) {
        $skills = $cvData['skills'] ?? [];
        
        if (empty($skills) || !is_array($skills)) {
            return;
        }
        
        $this->addSectionHeading('SKILLS');
        
        // Group skills in rows of 4
        $skillsText = implode(' • ', $skills);
        
        $this->section->addText(
            $skillsText,
            'bodyText',
            ['spaceAfter' => 200]
        );
    }
    
    /**
     * Add certifications section
     */
    private function addCertifications($cvData) {
        if (empty($cvData['certifications']) || !is_array($cvData['certifications'])) {
            return;
        }
        
        $this->addSectionHeading('CERTIFICATIONS');
        
        foreach ($cvData['certifications'] as $cert) {
            $name = $cert['name'] ?? '';
            $issuer = $cert['issuer'] ?? '';
            $date = $this->formatDate($cert['date'] ?? '');
            
            if (empty($name)) continue;
            
            $certText = $name;
            if ($issuer) {
                $certText .= ' — ' . $issuer;
            }
            if ($date) {
                $certText .= ' (' . $date . ')';
            }
            
            $this->section->addListItem(
                $certText,
                0,
                'bodyText',
                null,
                ['spaceAfter' => 60]
            );
        }
        
        $this->section->addTextBreak(1);
    }
    
    /**
     * Add projects section
     */
    private function addProjects($cvData) {
        if (empty($cvData['projects']) || !is_array($cvData['projects'])) {
            return;
        }
        
        $this->addSectionHeading('PROJECTS');
        
        foreach ($cvData['projects'] as $project) {
            $name = $project['name'] ?? '';
            $description = $project['description'] ?? '';
            $technologies = $project['technologies'] ?? [];
            
            if (empty($name)) continue;
            
            // Project name
            $this->section->addText(
                $name,
                'jobTitle',
                ['spaceAfter' => 60]
            );
            
            // Description
            if ($description) {
                $this->section->addText(
                    $description,
                    'bodyText',
                    ['spaceAfter' => 60]
                );
            }
            
            // Technologies
            if (!empty($technologies) && is_array($technologies)) {
                $techText = 'Technologies: ' . implode(', ', $technologies);
                $this->section->addText(
                    $techText,
                    'bodyText',
                    ['spaceAfter' => 120]
                );
            }
            
            $this->section->addTextBreak(1);
        }
    }
    
    /**
     * Add volunteer section
     */
    private function addVolunteer($cvData) {
        if (empty($cvData['volunteer']) || !is_array($cvData['volunteer'])) {
            return;
        }
        
        $this->addSectionHeading('VOLUNTEER EXPERIENCE');
        
        foreach ($cvData['volunteer'] as $vol) {
            $organization = $vol['organization'] ?? '';
            $role = $vol['role'] ?? '';
            $description = $vol['description'] ?? '';
            $date = $this->formatDate($vol['date'] ?? '');
            
            if (empty($organization)) continue;
            
            // Role and date
            $textRun = $this->section->addTextRun(['spaceAfter' => 0]);
            $textRun->addText($role ?: 'Volunteer', 'jobTitle');
            if ($date) {
                $textRun->addText(' (' . $date . ')', 'dateStyle');
            }
            
            // Organization
            $this->section->addText(
                $organization,
                'company',
                ['spaceAfter' => 80]
            );
            
            // Description
            if ($description) {
                $bullets = $this->parseBullets($description);
                foreach ($bullets as $bullet) {
                    $this->section->addListItem(
                        $bullet,
                        0,
                        'bodyText',
                        null,
                        ['spaceAfter' => 60]
                    );
                }
            }
            
            $this->section->addTextBreak(1);
        }
    }
    
    /**
     * Add languages section
     */
    private function addLanguages($cvData) {
        $languages = [];
        
        // Check in skills_detail first
        if (!empty($cvData['skills_detail']['languages'])) {
            $languages = $cvData['skills_detail']['languages'];
        }
        
        if (empty($languages)) {
            return;
        }
        
        $this->addSectionHeading('LANGUAGES');
        
        $languagesText = implode(' • ', $languages);
        
        $this->section->addText(
            $languagesText,
            'bodyText',
            ['spaceAfter' => 200]
        );
    }
    
    /**
     * Helper: Add section heading
     */
    private function addSectionHeading($text) {
        $this->section->addText(
            $text,
            'sectionHeading',
            ['spaceAfter' => 60, 'spaceBefore' => 120]
        );
        
        // Add horizontal line under heading using text (more compatible than addLine)
        $this->section->addText(
            str_repeat('─', 80),
            ['name' => 'Calibri', 'size' => 6, 'color' => '999999'],
            ['spaceAfter' => 80]
        );
    }
    
    /**
     * Helper: Parse description into bullets
     */
    private function parseBullets($description) {
        if (empty($description)) {
            return [];
        }
        
        $bullets = [];
        $lines = explode("\n", $description);
        
        foreach ($lines as $line) {
            $line = trim($line);
            // Remove bullet markers
            $line = preg_replace('/^[-•*]\s*/', '', $line);
            
            if (!empty($line) && strlen($line) > 3) {
                $bullets[] = $line;
            }
        }
        
        return $bullets;
    }
    
    /**
     * Helper: Format date
     */
    private function formatDate($date) {
        if (empty($date)) {
            return '';
        }
        
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
}
