<?php

namespace App\Filament\Resources\CandidateResource\Pages;

use App\Filament\Resources\CandidateResource;
use Filament\Resources\Pages\Page;
use App\Models\Candidate;

class ViewCandidate extends Page
{
    protected static string $resource = CandidateResource::class;

    protected static string $view = 'filament.resources.candidate-resource.pages.view-candidate';

    public Candidate $record;

    public function mount(Candidate $record): void
    {
        $this->record = $record;
    }

    protected function getViewData(): array
    {
        return ['candidate' => $this->record];
    }

    public static function getSlug(): string
    {
        return 'view/{record}';
    }
}
