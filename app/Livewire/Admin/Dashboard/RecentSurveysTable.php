<?php

namespace App\Livewire\Admin\Dashboard;

use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class RecentSurveysTable extends Component
{
    public Collection $recentSurveys;

    public function render()
    {
        return view('livewire.admin.dashboard.recent-surveys-table');
    }
}
