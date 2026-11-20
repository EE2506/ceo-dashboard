<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\FollowUp;
use App\Models\Payment;
use App\Models\User;
use App\Services\DashboardAnalytics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DashboardAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_analytics_explains_an_empty_payment_trend(): void
    {
        $this->actingAs(User::factory()->owner()->create())
            ->get('/admin/analytics')->assertOk()
            ->assertSee('No paid records in the last six months.');
    }

    public function test_dashboard_analytics_excludes_future_and_unsettled_amounts_from_manila_months(): void
    {
        $this->travelTo(Carbon::parse('2026-11-30 16:00:00', 'UTC'));
        $today = now('Asia/Manila');
        $client = Client::create(['name' => 'Demo client']);
        $client->sites()->create(['name' => 'Demo site']);

        foreach ([
            ['2026-06-30', 'paid', '11.00'],
            ['2026-07-01', 'paid', '20.00'],
            ['2026-11-30', 'paid', '100.25'],
            ['2026-12-01', 'paid', '250.75'],
            ['2026-12-02', 'paid', '200.00'],
            ['2026-12-01', 'unpaid', '300.00'],
            ['2026-12-01', 'partial', '400.00'],
            ['2026-12-01', 'void', '500.00'],
        ] as [$date, $status, $amount]) {
            Payment::create([
                'client_id' => $client->id,
                'recorded_on' => $date,
                'amount' => $amount,
                'purpose' => 'Demo payment',
                'status' => $status,
            ]);
        }

        foreach ([
            ['2026-11-30', false],
            ['2026-12-01', false],
            ['2026-12-02', false],
            ['2026-11-29', true],
        ] as [$date, $completed]) {
            FollowUp::create([
                'client_id' => $client->id,
                'title' => 'Demo follow-up',
                'due_on' => $date,
                'is_completed' => $completed,
            ]);
        }

        $analytics = app(DashboardAnalytics::class);
        $this->assertSame([
            'paid' => 250.75,
            'outstanding' => 2,
            'sites' => 1,
            'open' => 3,
            'overdue' => 1,
        ], $analytics->snapshot($today));
        $months = $analytics->monthlyPaid($today);
        $this->assertSame(['Jul 2026', 'Aug 2026', 'Sep 2026', 'Oct 2026', 'Nov 2026', 'Dec 2026'], array_column($months, 'label'));
        $this->assertSame([20.0, 0.0, 0.0, 0.0, 100.25, 250.75], array_column($months, 'amount'));

        $this->get('/admin/analytics')->assertRedirect('/admin/login');
        $this->actingAs(User::factory()->create())->get('/admin/analytics')->assertForbidden();
        $this->actingAs(User::factory()->owner()->create())
            ->get('/admin/analytics')->assertOk()->assertSee('Paid records by month')->assertSee('250.75');
        $this->get('/admin/today')->assertOk()->assertSee('View analytics')->assertSee('250.75');
    }
}
