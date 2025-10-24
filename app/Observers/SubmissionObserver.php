<?php

namespace App\Observers;

use App\Models\Submission;
use App\Services\ScoringService;

class SubmissionObserver
{
    private $scoringService;

    public function __construct()
    {
        $this->scoringService = new ScoringService();
    }

    public function saving(Submission $submission): void
    {
        $calculatedScore = match ($submission->type) {

            // KRA I, Criterion A
            'te-student-evaluation' => $this->scoringService
                ->calculateTeachingEffectivenessStudentScore($submission->data),

            'te-supervisor-evaluation' => $this->scoringService
                ->calculateTeachingEffectivenessSupervisorScore($submission->data),

            // KRA I, Criterion B
            'im-sole-authorship' => $this->scoringService
                ->calculateInstructionalMaterialSoleScore($submission->data),

            'im-co-authorship' => $this->scoringService
                ->calculateInstructionalMaterialCoAuthorScore($submission->data),

            'im-academic-program' => $this->scoringService
                ->calculateAcademicProgramScore($submission->data),

            // KRA I, Criterion C
            'mentorship-adviser' => $this->scoringService
                ->calculateMentorshipAdviserScore($submission->data),

            'mentorship-panel' => $this->scoringService
                ->calculateMentorshipPanelScore($submission->data),

            'mentorship-mentor' => $this->scoringService
                ->calculateMentorshipMentorScore($submission->data),

            default => 0.0,
        };

        $submission->score = $calculatedScore;
    }
}
