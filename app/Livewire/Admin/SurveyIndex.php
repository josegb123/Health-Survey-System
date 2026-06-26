<?php

namespace App\Livewire\Admin;

use App\Models\Survey;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Contracts\View\View;

class SurveyIndex extends Component
{
    use WithPagination;

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
