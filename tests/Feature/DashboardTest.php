<?php

namespace Tests\Feature;

use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Dashboard\ChartsSection;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_dashboard(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertOk();
    }

    public function test_week_period_covers_exactly_the_last_seven_days(): void
    {
        Carbon::setTestNow('2026-10-17 16:00:00');

        try {
            Livewire::test(Dashboard::class)
                ->set('period', 'week')
                ->assertSet('startDate', '2026-10-11 00:00:00')
                ->assertSet('endDate', '2026-10-17 23:59:59');
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_quarter_period_covers_exactly_the_last_ninety_days(): void
    {
        Carbon::setTestNow('2026-10-17 16:00:00');

        try {
            Livewire::test(Dashboard::class)
                ->set('period', 'quarter')
                ->assertSet('startDate', '2026-07-20 00:00:00')
                ->assertSet('endDate', '2026-10-17 23:59:59');
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_chart_data_refreshes_for_the_selected_period(): void
    {
        Livewire::test(ChartsSection::class, [
            'startDate' => '2026-10-01 00:00:00',
            'endDate' => '2026-10-31 23:59:59',
            'period' => 'month',
        ])
            ->dispatch('dashboard-filter-updated', dates: [
                'startDate' => '2026-10-11 00:00:00',
                'endDate' => '2026-10-17 23:59:59',
                'period' => 'week',
            ])
            ->assertSet('period', 'week')
            ->assertSet('dateRange', '11 oct. 2026 - 17 oct. 2026')
            ->assertSet('dailyTrend.data', array_fill(0, 7, 0))
            ->assertSet('ratingTrend.data', array_fill(0, 7, 0))
            ->assertSet('templateRanking', []);
    }
}
