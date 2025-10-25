<?php

namespace App\Services;

class ScoringService
{
    // KRA I, Criterion A
    public function calculateTeachingEffectivenessStudentScore(array $data): float
    {
        $average = $this->calculateSpecialAverage(
            $this->getRatingFieldKeys('student'),
            $data,
            (int)($data['student_deducted_semesters'] ?? 0)
        );
        return $average * 0.36;
    }

    public function calculateTeachingEffectivenessSupervisorScore(array $data): float
    {
        $average = $this->calculateSpecialAverage(
            $this->getRatingFieldKeys('supervisor'),
            $data,
            (int)($data['supervisor_deducted_semesters'] ?? 0)
        );
        return $average * 0.24;
    }

    // KRA I, Criterion B
    public function calculateInstructionalMaterialSoleScore(array $data): float
    {
        $materialType = $data['material_type'] ?? null;
        if (!$materialType) return 0.0;
        return $this->getInstructionalMaterialBaseScore($materialType);
    }

    public function calculateInstructionalMaterialCoAuthorScore(array $data): float
    {
        $materialType = $data['material_type'] ?? null;
        $percentage = (float)($data['contribution_percentage'] ?? 0);
        if (!$materialType || $percentage <= 0) return 0.0;
        $baseScore = $this->getInstructionalMaterialBaseScore($materialType);
        return $baseScore * ($percentage / 100.0);
    }

    public function calculateAcademicProgramScore(array $data): float
    {
        $role = $data['role'] ?? null;
        if (!$role) return 0.0;
        return match ($role) {
            'Lead' => 10.0,
            'Contributor' => 5.0,
            default => 0.0,
        };
    }

    // KRA I, Criterion C
    public function calculateMentorshipAdviserScore(array $data): float
    {
        $type = $data['mentorship_type'] ?? null;
        if (!$type) return 0.0;
        $totalCount = $this->getMentorshipAcademicYearTotal($data);
        $multiplier = match ($type) {
            'special_capstone_project' => 3.0,
            'undergrad_thesis' => 5.0,
            'masters_thesis' => 8.0,
            'dissertation' => 10.0,
            default => 0.0,
        };
        return $totalCount * $multiplier;
    }

    public function calculateMentorshipPanelScore(array $data): float
    {
        $type = $data['mentorship_type'] ?? null;
        if (!$type) return 0.0;
        $totalCount = $this->getMentorshipAcademicYearTotal($data);
        $multiplier = match ($type) {
            'special_capstone_project' => 1.0,
            'undergrad_thesis' => 1.0,
            'masters_thesis' => 2.0,
            'dissertation' => 2.0,
            default => 0.0,
        };
        return $totalCount * $multiplier;
    }

    public function calculateMentorshipMentorScore(array $data): float
    {
        $dateAwarded = $data['date_awarded'] ?? null;
        return !empty($dateAwarded) ? 3.0 : 0.0;
    }

    // KRA II, Criterion A: (Published Papers)
    public function calculateResearchSoleScore(array $data): float
    {
        $outputType = $data['output_type'] ?? null;

        if (!$outputType) {
            return 0.0;
        }

        return $this->getResearchBaseScore($outputType);
    }

    public function calculateResearchCoAuthorScore(array $data): float
    {
        $outputType = $data['output_type'] ?? null;
        $percentage = (float)($data['contribution_percentage'] ?? 0);

        if (!$outputType || $percentage <= 0) {
            return 0.0;
        }

        $baseScore = $this->getResearchBaseScore($outputType);

        return $baseScore * ($percentage / 100.0);
    }

    // KRA II, Criterion A: (Translated Outputs)
    public function calculateTranslatedOutputLeadScore(array $data): float
    {
        $dateCompleted = $data['date_completed'] ?? null;
        $dateUtilized = $data['date_utilized'] ?? null;

        return (!empty($dateCompleted) || !empty($dateUtilized)) ? 35.0 : 0.0;
    }

    public function calculateTranslatedOutputContributorScore(array $data): float
    {
        $dateCompleted = $data['date_completed'] ?? null;
        $dateUtilized = $data['date_utilized'] ?? null;
        $percentage = (float)($data['contribution_percentage'] ?? 0);

        if ((empty($dateCompleted) && empty($dateUtilized)) || $percentage <= 0) {
            return 0.0;
        }

        return 35.0 * ($percentage / 100.0);
    }

    // KRA II, Criterion A: (Citations)
    public function calculateResearchCitationLocalScore(array $data): float
    {
        $citationCount = (int)($data['citation_count'] ?? 0);

        if ($citationCount <= 0) {
            return 0.0;
        }

        $rawScore = $citationCount * 5.0;
        $maxScore = 40.0;

        return min($rawScore, $maxScore);
    }

    public function calculateResearchCitationInternationalScore(array $data): float
    {
        $citationCount = (int)($data['citation_count'] ?? 0);

        if ($citationCount <= 0) {
            return 0.0;
        }

        $rawScore = $citationCount * 10.0;
        $maxScore = 60.0;

        return min($rawScore, $maxScore);
    }

    // KRA II, Criterion B (Patented Inventions)
    public function calculateInventionPatentSoleScore(array $data): float
    {
        $stage = $data['patent_stage'] ?? null;

        if (!$stage) {
            return 0.0;
        }

        return $this->getInventionPatentBaseScore($stage);
    }

    public function calculateInventionPatentCoInventorScore(array $data): float
    {
        $stage = $data['patent_stage'] ?? null;
        $percentage = (float)($data['contribution_percentage'] ?? 0);

        if (!$stage || $percentage <= 0) {
            return 0.0;
        }

        $baseScore = $this->getInventionPatentBaseScore($stage);

        return $baseScore * ($percentage / 100.0);
    }

    // KRA II, Criterion B (Utility/Design)
    public function calculateUtilityDesignSoleScore(array $data): float
    {
        $type = $data['patent_type'] ?? null;
        $dateGranted = $data['date_granted'] ?? null;

        if (!$type || empty($dateGranted)) {
            return 0.0;
        }

        return match ($type) {
            'utility_model' => 10.0,
            'industrial_design' => 5.0,
            default => 0.0,
        };
    }

    public function calculateUtilityDesignCoInventorScore(array $data): float
    {
        $type = $data['patent_type'] ?? null;
        $dateGranted = $data['date_granted'] ?? null;
        $percentage = (float)($data['contribution_percentage'] ?? 0);

        if (!$type || empty($dateGranted) || $percentage <= 0) {
            return 0.0;
        }

        $baseScore = match ($type) {
            'utility_model' => 10.0,
            'industrial_design' => 5.0,
            default => 0.0,
        };

        return $baseScore * ($percentage / 100.0);
    }

    // KRA II, Criterion B (Commercialized)
    public function calculateCommercializedLocalScore(array $data): float
    {
        $datePatented = $data['date_patented'] ?? null;
        $dateCommercialized = $data['date_commercialized'] ?? null;
        $area = $data['area_commercialized'] ?? null;

        if (empty($datePatented) || empty($dateCommercialized) || empty($area)) {
            return 0.0;
        }

        $rawScore = 5.0;
        $maxScore = 20.0;

        return min($rawScore, $maxScore);
    }

    public function calculateCommercializedInternationalScore(array $data): float
    {
        $datePatented = $data['date_patented'] ?? null;
        $dateCommercialized = $data['date_commercialized'] ?? null;
        $area = $data['area_commercialized'] ?? null;

        if (empty($datePatented) || empty($dateCommercialized) || empty($area)) {
            return 0.0;
        }

        $rawScore = 10.0;
        $maxScore = 30.0;

        return min($rawScore, $maxScore);
    }

    // KRA II, Criterion B (Non-Patentable: New Software)
    public function calculateSoftwareNewSoleScore(array $data): float
    {
        $copyrightNo = $data['copyright_no'] ?? null;
        $dateCopyrighted = $data['date_copyrighted'] ?? null;
        $dateUtilized = $data['date_utilized'] ?? null;

        return (!empty($copyrightNo) && (!empty($dateCopyrighted) || !empty($dateUtilized))) ? 10.0 : 0.0;
    }

    public function calculateSoftwareNewCoDeveloperScore(array $data): float
    {
        $copyrightNo = $data['copyright_no'] ?? null;
        $dateCopyrighted = $data['date_copyrighted'] ?? null;
        $dateUtilized = $data['date_utilized'] ?? null;
        $percentage = (float)($data['contribution_percentage'] ?? 0);

        if (empty($copyrightNo) || (empty($dateCopyrighted) && empty($dateUtilized)) || $percentage <= 0) {
            return 0.0;
        }

        return 10.0 * ($percentage / 100.0);
    }

    // KRA II, Criterion B (Non-Patentable: Updated Software)
    public function calculateSoftwareUpdatedScore(array $data): float
    {
        $copyrightNo = $data['copyright_no'] ?? null;
        $dateCopyrighted = $data['date_copyrighted'] ?? null;
        $dateUtilized = $data['date_utilized'] ?? null;
        $updateDetails = $data['update_details'] ?? null;
        $role = $data['developer_role'] ?? null;

        if (empty($copyrightNo) || empty($dateCopyrighted) || empty($dateUtilized) || empty($updateDetails) || empty($role)) {
            return 0.0;
        }

        return match (strtolower($role)) {
            'sole developer' => 4.0,
            'co-developer' => 2.0,
            default => 0.0,
        };
    }

    // KRA II, Criterion B (Non-Patentable: Plant/Animal/Microbe)
    public function calculatePlantAnimalSoleScore(array $data): float
    {
        $dateCompleted = $data['date_completed'] ?? null;
        $dateRegistered = $data['date_registered'] ?? null;
        $datePropagation = $data['date_propagation'] ?? null;

        return (!empty($dateCompleted) && !empty($dateRegistered) && !empty($datePropagation)) ? 10.0 : 0.0;
    }

    public function calculatePlantAnimalCoDeveloperScore(array $data): float
    {
        $dateCompleted = $data['date_completed'] ?? null;
        $dateRegistered = $data['date_registered'] ?? null;
        $datePropagation = $data['date_propagation'] ?? null;
        $percentage = (float)($data['contribution_percentage'] ?? 0);

        if (empty($dateCompleted) || empty($dateRegistered) || empty($datePropagation) || $percentage <= 0) {
            return 0.0;
        }

        return 10.0 * ($percentage / 100.0);
    }

    // KRA II, Criterion C
    public function calculateCreativePerformingArtScore(array $data): float
    {
        $title = $data['title'] ?? null;
        $artType = $data['art_type'] ?? null;
        $datePerformed = $data['date_performed'] ?? null;
        $classification = $data['classification'] ?? null;

        if (empty($title) || empty($datePerformed) || empty($artType) || $artType === 'select_option') {
            return 0.0;
        }

        return match ($classification) {
            'new_creation' => 20.0,
            'own_work' => 10.0,
            'work_of_others' => 10.0,
            default => 0.0,
        };
    }

    public function calculateCreativeExhibitionScore(array $data): float
    {
        $title = $data['title'] ?? null;
        $classification = $data['classification'] ?? null;
        $creativeType = $data['creative_type'] ?? null;
        $dateExhibited = $data['date_exhibited'] ?? null;
        $organizer = $data['organizer'] ?? null;
        $venue = $data['venue'] ?? null;

        if (
            empty($title) ||
            empty($classification) || $classification === 'select_option' ||
            empty($creativeType) || $creativeType === 'select_option' ||
            empty($dateExhibited) ||
            empty($organizer) ||
            empty($venue)
        ) {
            return 0.0;
        }

        return 20.0;
    }

    public function calculateCreativeJuriedDesignScore(array $data): float
    {
        $title = $data['title'] ?? null;
        $classification = $data['classification'] ?? null;
        $reviewer = $data['reviewer'] ?? null;
        $dateActivity = $data['date_activity'] ?? null;
        $venue = $data['venue'] ?? null;
        $organizer = $data['organizer'] ?? null;

        if (
            empty($title) ||
            empty($classification) || $classification === 'select_option' ||
            empty($reviewer) ||
            empty($dateActivity) ||
            empty($venue) ||
            empty($organizer)
        ) {
            return 0.0;
        }

        return 20.0;
    }

    public function calculateCreativeLiteraryPublicationScore(array $data): float
    {
        $publisher = $data['publisher'] ?? null;
        $datePublished = $data['date_published'] ?? null;
        $literaryType = $data['literary_type'] ?? null;

        if (empty($publisher) || empty($datePublished)) {
            return 0.0;
        }

        return match ($literaryType) {
            'novel' => 20.0,
            'short_story' => 10.0,
            'essay' => 10.0,
            'poetry' => 10.0,
            'others' => 10.0,
            default => 0.0,
        };
    }

    // KRA I, Private Helpers
    private function calculateSpecialAverage(array $ratingKeys, array $data, int $deductedSemesters): float
    {
        $ratings = [];
        foreach ($ratingKeys as $key) {
            if (isset($data[$key]) && is_numeric($data[$key])) {
                $ratings[] = (float)$data[$key];
            }
        }

        if (empty($ratings)) {
            return 0.0;
        }

        $totalSemesters = 8;

        $isOnLeave = isset($data['reason_for_deducting']) && $data['reason_for_deducting'] !== 'NOT APPLICABLE' && $data['reason_for_deducting'] !== 'SELECT OPTION';

        if ($isOnLeave && $deductedSemesters > 0 && $deductedSemesters < $totalSemesters) {
            $divisor = $totalSemesters - $deductedSemesters;
            return array_sum($ratings) / $divisor;
        } else {
            return array_sum($ratings) / count($ratings);
        }
    }

    private function getRatingFieldKeys(string $prefix): array
    {
        $keys = [];
        for ($year = 1; $year <= 4; $year++) {
            for ($sem = 1; $sem <= 2; $sem++) {
                $keys[] = "{$prefix}_ay{$year}_sem{$sem}";
            }
        }
        return $keys;
    }

    private function getMentorshipAcademicYearKeys(): array
    {
        return ['ay_1_count', 'ay_2_count', 'ay_3_count', 'ay_4_count'];
    }

    private function getMentorshipAcademicYearTotal(array $data): int
    {
        $total = 0;
        foreach ($this->getMentorshipAcademicYearKeys() as $key) {
            $total += (int)($data[$key] ?? 0);
        }
        return $total;
    }

    private function getInstructionalMaterialBaseScore(?string $materialType): float
    {
        if (!$materialType) return 0.0;
        return match ($materialType) {
            'textbook' => 30.0,
            'textbook_chapter' => 10.0,
            'manual_module' => 16.0,
            'multimedia_material' => 16.0,
            'testing_material' => 10.0,
            default => 0.0,
        };
    }

    // KRA II, Private Helpers
    private function getResearchBaseScore(?string $outputType): float
    {
        if (!$outputType) {
            return 0.0;
        }

        return match ($outputType) {
            'book' => 100.0,
            'journal_article' => 50.0,
            'book_chapter' => 35.0,
            'monograph' => 100.0,
            'other_peer_reviewed_output' => 10.0,
            default => 0.0,
        };
    }

    private function getInventionPatentBaseScore(?string $stage): float
    {
        if (!$stage) {
            return 0.0;
        }

        return match (strtolower($stage)) {
            'accepted' => 10.0,
            'published' => 20.0,
            'granted' => 80.0,
            default => 0.0,
        };
    }
}
