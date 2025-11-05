<?php

namespace App\Filament\Instructor\Resources\ApplicationResource\Pages;

use App\Filament\Instructor\Resources\ApplicationResource;
use App\Models\PromotionCycle;
use App\Models\Submission;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Get;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Section;
use Illuminate\Support\Str;

use App\Filament\Instructor\Pages\KRA1\InstructionalMaterialsPage;
use App\Filament\Instructor\Pages\KRA1\MentorshipServicesPage;
use App\Filament\Instructor\Pages\KRA1\TeachingEffectivenessPage;
use App\Filament\Instructor\Pages\KRA2\CreativeWorksPage;
use App\Filament\Instructor\Pages\KRA2\InventionsPage;
use App\Filament\Instructor\Pages\KRA2\ResearchOutputsPage;
use App\Filament\Instructor\Pages\KRA3\BonusCriterionPage;
use App\Filament\Instructor\Pages\KRA3\QualityOfExtensionPage;
use App\Filament\Instructor\Pages\KRA3\ServiceToCommunityPage;
use App\Filament\Instructor\Pages\KRA3\ServiceToInstitutionPage;
use App\Filament\Instructor\Pages\KRA4\AwardsRecognitionPage;
use App\Filament\Instructor\Pages\KRA4\BonusIndicatorsPage;
use App\Filament\Instructor\Pages\KRA4\ContinuingDevelopmentPage;
use App\Filament\Instructor\Pages\KRA4\ProfessionalOrganizationsPage;


class CreateApplication extends CreateRecord
{
    use HasWizard;

    protected static string $resource = ApplicationResource::class;

    /**
     * Helper: Creates an empty state with a simple link.
     */
    private function createEmptyState(string $pageUrl, string $ctaText, Collection $submissionCollection): Placeholder
    {
        return Placeholder::make(Str::slug($ctaText) . '_empty_state')
            ->label(false)
            ->content(new HtmlString(
                '<div class="flex items-center justify-between p-4 rounded-lg bg-gray-50 dark:bg-gray-800 dark:border dark:border-gray-700">
                    <p class="text-sm text-gray-500 dark:text-gray-400">You have no unassigned submissions for this criterion.</p>
                    <a href="' . $pageUrl . '" class="inline-flex items-center gap-1 font-medium text-primary-600 hover:text-primary-500 text-sm ring-1 ring-inset ring-primary-500/20 px-3 py-1.5 rounded-lg">
                        ' . $ctaText . '
                    </a>
                </div>'
            ))
            ->hidden($submissionCollection->isNotEmpty());
    }

    /**
     * Helper: Creates an empty state with a link that includes an activeTab.
     */
    private function createEmptyStateWithTab(string $pageUrl, string $tabName, string $ctaText, Collection $submissionCollection): Placeholder
    {
        $url = $pageUrl . '?activeTab=' . $tabName;

        return Placeholder::make(Str::slug($ctaText) . '_empty_state')
            ->label(false)
            ->content(new HtmlString(
                '<div class="flex items-center justify-between p-4 rounded-lg bg-gray-50 dark:bg-gray-800 dark:border dark:border-gray-700">
                    <p class="text-sm text-gray-500 dark:text-gray-400">You have no unassigned submissions for this criterion.</p>
                    <a href="' . $url . '" class="inline-flex items-center gap-1 font-medium text-primary-600 hover:text-primary-500 text-sm ring-1 ring-inset ring-primary-500/20 px-3 py-1.5 rounded-lg">
                        ' . $ctaText . '
                    </a>
                </div>'
            ))
            ->hidden($submissionCollection->isNotEmpty());
    }

    /**
     * Helper: Creates the CheckboxList for a submission group.
     */
    private function createCheckboxList(Collection $submissions, string $name, string $label): CheckboxList
    {
        return CheckboxList::make($name)
            ->label($label)
            ->options(
                $submissions->mapWithKeys(function (Submission $record) {
                    $title = $record->data['title'] ?? $record->data['name'] ?? $record->data['activity_title'] ?? 'Untitled';
                    $type = Str::of($record->type)->replace('-', ' ')->title();
                    return [$record->id => new HtmlString("<div>{$title}</div><div class='text-xs text-gray-500'>{$type}</div>")];
                })
            )
            ->bulkToggleable()
            ->hidden($submissions->isEmpty());
    }

    protected function getSteps(): array
    {
        // Get all unassigned submissions
        $allSubmissions = Submission::where('user_id', Auth::id())
            ->whereNull('application_id')
            ->get();

        // Get the URLs for all KRA pages
        $kra1TeUrl = TeachingEffectivenessPage::getUrl();
        $kra1ImUrl = InstructionalMaterialsPage::getUrl();
        $kra1MentorUrl = MentorshipServicesPage::getUrl();

        $kra2ResearchUrl = ResearchOutputsPage::getUrl();
        $kra2InventionUrl = InventionsPage::getUrl();
        $kra2CreativeUrl = CreativeWorksPage::getUrl();

        $kra3ServiceInstUrl = ServiceToInstitutionPage::getUrl();
        $kra3ServiceCommUrl = ServiceToCommunityPage::getUrl();
        $kra3QualityUrl = QualityOfExtensionPage::getUrl();
        $kra3BonusUrl = BonusCriterionPage::getUrl();

        $kra4ProfOrgUrl = ProfessionalOrganizationsPage::getUrl();
        $kra4ContDevUrl = ContinuingDevelopmentPage::getUrl();
        $kra4AwardsUrl = AwardsRecognitionPage::getUrl();
        $kra4BonusIndUrl = BonusIndicatorsPage::getUrl();

        // KRA I
        $teSubs = $allSubmissions->whereIn('type', ['te-student-evaluation', 'te-supervisor-evaluation']);
        $imSubs = $allSubmissions->whereIn('type', ['im-sole-authorship', 'im-co-authorship', 'im-academic-program']);
        $mentorSubs = $allSubmissions->whereIn('type', ['mentorship-adviser', 'mentorship-panel', 'mentorship-mentor']);

        // KRA II
        $papersSubs = $allSubmissions->whereIn('type', ['research-sole-authorship', 'research-co-authorship']);
        $translatedSubs = $allSubmissions->whereIn('type', ['research-translated-lead', 'research-translated-contributor']);
        $citationSubs = $allSubmissions->whereIn('type', ['research-citation-local', 'research-citation-international']);

        $patentedSubs = $allSubmissions->whereIn('type', [
            'invention-patent-sole',
            'invention-patent-co-inventor',
            'invention-utility-design-sole',
            'invention-utility-design-co-inventor',
            'invention-commercialized-local',
            'invention-commercialized-international'
        ]);
        $nonPatentedSubs = $allSubmissions->whereIn('type', [
            'invention-software-new-sole',
            'invention-software-new-co',
            'invention-software-updated',
            'invention-plant-animal-sole',
            'invention-plant-animal-co'
        ]);

        $creativePerformingSubs = $allSubmissions->where('type', 'creative-performing-art');
        $creativeExhibitionSubs = $allSubmissions->where('type', 'creative-exhibition');
        $creativeJuriedSubs = $allSubmissions->where('type', 'creative-juried-design');
        $creativeLiterarySubs = $allSubmissions->where('type', 'creative-literary-publication');

        // KRA III
        $linkageSubs = $allSubmissions->where('type', 'extension-linkage');
        $incomeSubs = $allSubmissions->where('type', 'extension-income-generation');
        $profServiceSubs = $allSubmissions->whereIn('type', [
            'accreditation_services',
            'judge_examiner',
            'consultant',
            'media_service',
            'training_resource_person'
        ]);
        $socialRespSubs = $allSubmissions->where('type', 'social_responsibility');
        $qualitySubs = $allSubmissions->where('type', 'extension-quality-rating');
        $bonusSubs = $allSubmissions->where('type', 'extension-bonus-designation');

        // KRA IV
        $orgSubs = $allSubmissions->where('type', 'profdev-organization');
        $degreeSubs = $allSubmissions->whereIn('type', ['profdev-doctorate', 'profdev-additional-degree']);
        $trainingSubs = $allSubmissions->where('type', 'profdev-conference-training');
        $presentationSubs = $allSubmissions->where('type', 'profdev-paper-presentation');
        $awardSubs = $allSubmissions->where('type', 'profdev-award-recognition');
        $acadServiceSubs = $allSubmissions->where('type', 'profdev-academic-service');
        $industrySubs = $allSubmissions->where('type', 'profdev-industry-experience');


        return [
            Step::make('Application Details')
                ->description('Select the promotion cycle you are applying for.')
                ->schema([
                    Select::make('evaluation_cycle')
                        ->label('Select Promotion Cycle')
                        ->options(
                            PromotionCycle::where('is_active', true)->pluck('name', 'name')
                        )
                        ->searchable()
                        ->required(),
                ]),
            Step::make('Select Evidence')
                ->description('Select all submissions from your portfolio to include in this application.')
                ->schema([
                    Section::make('KRA I: Instruction')
                        ->collapsible()
                        ->schema([
                            $this->createEmptyState($kra1TeUrl, 'Add Teaching Effectiveness', $teSubs),
                            $this->createCheckboxList($teSubs, 'te_submissions', 'Teaching Effectiveness'),

                            $this->createEmptyState($kra1ImUrl, 'Add Instructional Materials', $imSubs),
                            $this->createCheckboxList($imSubs, 'im_submissions', 'Instructional Materials'),

                            $this->createEmptyState($kra1MentorUrl, 'Add Mentorship Submissions', $mentorSubs),
                            $this->createCheckboxList($mentorSubs, 'mentor_submissions', 'Mentorship'),
                        ]),

                    Section::make('KRA II: Research & Innovation')
                        ->collapsible()
                        ->schema([
                            $this->createEmptyStateWithTab($kra2ResearchUrl, 'published_papers', 'Add Published Papers', $papersSubs),
                            $this->createCheckboxList($papersSubs, 'papers_submissions', 'Published Papers'),

                            $this->createEmptyStateWithTab($kra2ResearchUrl, 'translated_outputs', 'Add Translated Outputs', $translatedSubs),
                            $this->createCheckboxList($translatedSubs, 'translated_submissions', 'Translated Outputs'),

                            $this->createEmptyStateWithTab($kra2ResearchUrl, 'citations', 'Add Citations', $citationSubs),
                            $this->createCheckboxList($citationSubs, 'citation_submissions', 'Citations'),

                            $this->createEmptyStateWithTab($kra2InventionUrl, 'patented_inventions', 'Add Patented Inventions', $patentedSubs),
                            $this->createCheckboxList($patentedSubs, 'patented_submissions', 'Patented Inventions'),

                            $this->createEmptyStateWithTab($kra2InventionUrl, 'non_patentable_inventions', 'Add Non-Patentable Inventions', $nonPatentedSubs),
                            $this->createCheckboxList($nonPatentedSubs, 'non_patented_submissions', 'Non-Patentable Inventions'),

                            $this->createEmptyState($kra2CreativeUrl, 'Add Creative Works', $creativePerformingSubs->merge($creativeExhibitionSubs)->merge($creativeJuriedSubs)->merge($creativeLiterarySubs)),
                            $this->createCheckboxList($creativePerformingSubs, 'creative_performing_submissions', 'Performing Arts'),
                            $this->createCheckboxList($creativeExhibitionSubs, 'creative_exhibition_submissions', 'Exhibitions'),
                            $this->createCheckboxList($creativeJuriedSubs, 'creative_juried_submissions', 'Juried Designs'),
                            $this->createCheckboxList($creativeLiterarySubs, 'creative_literary_submissions', 'Literary Publications'),
                        ]),

                    Section::make('KRA III: Extension')
                        ->collapsible()
                        ->schema([
                            $this->createEmptyStateWithTab($kra3ServiceInstUrl, 'linkages', 'Add Linkages', $linkageSubs),
                            $this->createCheckboxList($linkageSubs, 'linkage_submissions', 'Linkages, Networking and Partnership'),

                            $this->createEmptyStateWithTab($kra3ServiceInstUrl, 'income_generation', 'Add Income Generation', $incomeSubs),
                            $this->createCheckboxList($incomeSubs, 'income_submissions', 'Income Generation'),

                            $this->createEmptyStateWithTab($kra3ServiceInstUrl, 'professional_services', 'Add Professional Services', $profServiceSubs),
                            $this->createCheckboxList($profServiceSubs, 'prof_service_submissions', 'Professional Services'),

                            $this->createEmptyState($kra3ServiceCommUrl, 'Add Social Responsibility', $socialRespSubs),
                            $this->createCheckboxList($socialRespSubs, 'social_resp_submissions', 'Social Responsibility'),

                            $this->createEmptyState($kra3QualityUrl, 'Add Quality of Extension', $qualitySubs),
                            $this->createCheckboxList($qualitySubs, 'quality_submissions', 'Quality of Extension'),

                            $this->createEmptyState($kra3BonusUrl, 'Add Bonus Criterion', $bonusSubs),
                            $this->createCheckboxList($bonusSubs, 'bonus_submissions', 'Bonus Criterion'),
                        ]),

                    Section::make('KRA IV: Professional Development')
                        ->collapsible()
                        ->schema([
                            $this->createEmptyState($kra4ProfOrgUrl, 'Add Professional Organizations', $orgSubs),
                            $this->createCheckboxList($orgSubs, 'org_submissions', 'Professional Organizations'),

                            $this->createEmptyStateWithTab($kra4ContDevUrl, 'educational_qualifications', 'Add Educational Qualifications', $degreeSubs),
                            $this->createCheckboxList($degreeSubs, 'degree_submissions', 'Educational Qualifications'),

                            $this->createEmptyStateWithTab($kra4ContDevUrl, 'conference_training', 'Add Conference/Training', $trainingSubs),
                            $this->createCheckboxList($trainingSubs, 'training_submissions', 'Conference/Training'),

                            $this->createEmptyStateWithTab($kra4ContDevUrl, 'paper_presentations', 'Add Paper Presentations', $presentationSubs),
                            $this->createCheckboxList($presentationSubs, 'presentation_submissions', 'Paper Presentations'),

                            $this->createEmptyState($kra4AwardsUrl, 'Add Awards/Recognition', $awardSubs),
                            $this->createCheckboxList($awardSubs, 'award_submissions', 'Awards/Recognition'),

                            $this->createEmptyStateWithTab($kra4BonusIndUrl, 'academic_service', 'Add Academic Service', $acadServiceSubs),
                            $this->createCheckboxList($acadServiceSubs, 'acad_service_submissions', 'Academic Service'),

                            $this->createEmptyStateWithTab($kra4BonusIndUrl, 'industry_experience', 'Add Industry Experience', $industrySubs),
                            $this->createCheckboxList($industrySubs, 'industry_submissions', 'Industry Experience'),
                        ]),
                ]),
            Step::make('Review & Submit')
                ->description('Confirm your submission details.')
                ->schema([
                    Placeholder::make('summary')
                        ->label('Summary of Selected Evidence')
                        ->content(function (Get $get): string {
                            // Merge all checkbox list arrays
                            $allSubs = array_merge(
                                $get('te_submissions') ?? [],
                                $get('im_submissions') ?? [],
                                $get('mentor_submissions') ?? [],
                                $get('papers_submissions') ?? [],
                                $get('translated_submissions') ?? [],
                                $get('citation_submissions') ?? [],
                                $get('patented_submissions') ?? [],
                                $get('non_patented_submissions') ?? [],
                                $get('creative_performing_submissions') ?? [],
                                $get('creative_exhibition_submissions') ?? [],
                                $get('creative_juried_submissions') ?? [],
                                $get('creative_literary_submissions') ?? [],
                                $get('linkage_submissions') ?? [],
                                $get('income_submissions') ?? [],
                                $get('prof_service_submissions') ?? [],
                                $get('social_resp_submissions') ?? [],
                                $get('quality_submissions') ?? [],
                                $get('bonus_submissions') ?? [],
                                $get('org_submissions') ?? [],
                                $get('degree_submissions') ?? [],
                                $get('training_submissions') ?? [],
                                $get('presentation_submissions') ?? [],
                                $get('award_submissions') ?? [],
                                $get('acad_service_submissions') ?? [],
                                $get('industry_submissions') ?? []
                            );
                            $total = count($allSubs);

                            return new HtmlString(
                                "You are about to submit a total of <strong>{$total}</strong> pieces of evidence."
                            );
                        }),
                ]),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();

        $data['user_id'] = $user->id;
        $data['applicant_current_rank'] = $user->facultyRank?->name ?? 'N/A';
        $data['status'] = 'draft';

        return $data;
    }

    protected function afterCreate(): void
    {
        // Merge all submission IDs from the form data
        $allSubmissionIds = array_merge(
            $this->data['te_submissions'] ?? [],
            $this->data['im_submissions'] ?? [],
            $this->data['mentor_submissions'] ?? [],
            $this->data['papers_submissions'] ?? [],
            $this->data['translated_submissions'] ?? [],
            $this->data['citation_submissions'] ?? [],
            $this->data['patented_submissions'] ?? [],
            $this->data['non_patented_submissions'] ?? [],
            $this->data['creative_performing_submissions'] ?? [],
            $this->data['creative_exhibition_submissions'] ?? [],
            $this->data['creative_juried_submissions'] ?? [],
            $this->data['creative_literary_submissions'] ?? [],
            $this->data['linkage_submissions'] ?? [],
            $this->data['income_submissions'] ?? [],
            $this->data['prof_service_submissions'] ?? [],
            $this->data['social_resp_submissions'] ?? [],
            $this->data['quality_submissions'] ?? [],
            $this->data['bonus_submissions'] ?? [],
            $this->data['org_submissions'] ?? [],
            $this->data['degree_submissions'] ?? [],
            $this->data['training_submissions'] ?? [],
            $this->data['presentation_submissions'] ?? [],
            $this->data['award_submissions'] ?? [],
            $this->data['acad_service_submissions'] ?? [],
            $this->data['industry_submissions'] ?? []
        );

        if (!empty($allSubmissionIds)) {
            Submission::whereIn('id', $allSubmissionIds)
                ->whereNull('application_id')
                ->where('user_id', Auth::id())
                ->update(['application_id' => $this->record->id]);
        }
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
