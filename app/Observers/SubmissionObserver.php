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

            // KRA II, Criterion A (Published Outputs)
            'research-sole-authorship' => $this->scoringService
                ->calculateResearchSoleScore($submission->data),

            'research-co-authorship' => $this->scoringService
                ->calculateResearchCoAuthorScore($submission->data),

            // KRA II, Criterion A (Translated Outputs)
            'research-translated-lead' => $this->scoringService
                ->calculateTranslatedOutputLeadScore($submission->data),

            'research-translated-contributor' => $this->scoringService
                ->calculateTranslatedOutputContributorScore($submission->data),

            // KRA II, Criterion A (Citations)
            'research-citation-local' => $this->scoringService
                ->calculateResearchCitationLocalScore($submission->data),

            'research-citation-international' => $this->scoringService
                ->calculateResearchCitationInternationalScore($submission->data),

            // KRA II, Criterion B (Patented Inventions)
            'invention-patent-sole' => $this->scoringService
                ->calculateInventionPatentSoleScore($submission->data),

            'invention-patent-co-inventor' => $this->scoringService
                ->calculateInventionPatentCoInventorScore($submission->data),

            // KRA II, Criterion B (Utility/Design)
            'invention-utility-design-sole' => $this->scoringService
                ->calculateUtilityDesignSoleScore($submission->data),

            'invention-utility-design-co-inventor' => $this->scoringService
                ->calculateUtilityDesignCoInventorScore($submission->data),

            // KRA II, Criterion B (Commercialized)
            'invention-commercialized-local' => $this->scoringService
                ->calculateCommercializedLocalScore($submission->data),

            'invention-commercialized-international' => $this->scoringService
                ->calculateCommercializedInternationalScore($submission->data),

            // KRA II, Criterion B (Non-Patentable: Software)
            'invention-software-new-sole' => $this->scoringService
                ->calculateSoftwareNewSoleScore($submission->data),

            'invention-software-new-co' => $this->scoringService
                ->calculateSoftwareNewCoDeveloperScore($submission->data),

            'invention-software-updated' => $this->scoringService
                ->calculateSoftwareUpdatedScore($submission->data),

            // KRA II, Criterion B (Non-Patentable: Plant/Animal/Microbe)
            'invention-plant-animal-sole' => $this->scoringService
                ->calculatePlantAnimalSoleScore($submission->data),

            'invention-plant-animal-co' => $this->scoringService
                ->calculatePlantAnimalCoDeveloperScore($submission->data),

            // KRA II, Criterion C
            'creative-performing-art' => $this->scoringService
                ->calculateCreativePerformingArtScore($submission->data),

            'creative-exhibition' => $this->scoringService
                ->calculateCreativeExhibitionScore($submission->data),

            'creative-juried-design' => $this->scoringService
                ->calculateCreativeJuriedDesignScore($submission->data),

            'creative-literary-publication' => $this->scoringService
                ->calculateCreativeLiteraryPublicationScore($submission->data),

            // KRA III, Criterion A
            'extension-linkage' => $this->scoringService
                ->calculateExtensionLinkageScore($submission->data),

            'extension-income-generation' => $this->scoringService
                ->calculateExtensionIncomeGenerationScore($submission->data),

            // KRA III, Criterion B
            'accreditation_services' => $this->scoringService
                ->calculateAccreditationServiceScore($submission->data),

            'judge_examiner' => $this->scoringService
                ->calculateJudgeExaminerScore($submission->data),

            'consultant' => $this->scoringService
                ->calculateConsultantScore($submission->data),

            'media_service' => $this->scoringService
                ->calculateMediaServiceScore($submission->data),

            'training_resource_person' => $this->scoringService
                ->calculateTrainingResourcePersonScore($submission->data),

            // KRA III, Criterion C
            'extension-quality-rating' => $this->scoringService
                ->calculateQualityOfExtensionScore($submission->data),

            // KRA III, Criterion D
            'extension-bonus-designation' => $this->scoringService
                ->calculateBonusDesignationScore($submission->data),

            default => 0.0,
        };

        $submission->score = $calculatedScore;
    }
}
