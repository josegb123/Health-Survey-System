<?php

namespace App\Livewire\Admin;

use App\Models\Survey;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Contracts\View\View;

class SurveyIndex extends Component
{
    use WithPagination;

    public ?Survey $viewingSurvey = null;

    public function viewSurvey(int $id): void
    {
        $this->viewingSurvey = Survey::with([
            'patient.insurer',
            'template',
            'answers.question',
        ])->findOrFail($id);

        $this->modal('view-survey-flyout')->show();
    }

    public function render(): View
    {
        return view('livewire.admin.survey-index', [
            'surveys' => Survey::with(['patient', 'template'])
                ->where('status', 'completed')
                ->latest()
                ->paginate(10),
        ]);
    }
}
