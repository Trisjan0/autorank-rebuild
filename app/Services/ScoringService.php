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

    // --- Private Helpers ---
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
}
