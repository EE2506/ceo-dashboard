<?php

namespace Tests\Feature;

use App\Filament\Resources\Pages\ManageFollowUps;
use App\Models\Client;
use App\Models\FollowUp;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class TodayTest extends TestCase
{
    use RefreshDatabase;

    public function test_today_uses_manila_date_and_limits_prioritized_records(): void
    {
        $this->travelTo(Carbon::parse('2026-11-20 16:00:00', 'UTC'));
        $client = Client::create(['name' => 'Example client']);
        $followUp = fn (string $title, string $date, bool $completed = false): FollowUp =>
            FollowUp::create(['client_id' => $client->id, 'title' => $title, 'due_on' => $date, 'is_completed' => $completed]);
        $payment = fn (string $title, ?string $date, string $status): Payment =>
            Payment::create([
                'client_id' => $client->id,
                'purpose' => $title,
                'due_on' => $date,
                'recorded_on' => '2026-11-01',
                'amount' => '100.00',
                'status' => $status,
            ]);

        $due = $followUp('Summary due today', '2026-11-21');
        $followUp('Overdue renewal check', '2026-11-19');
        $followUp('Completed follow-up', '2026-11-18', true);
        $followUp('Future follow-up', '2026-11-22');
        $payment('Paid item excluded', '2026-11-18', 'paid');
        $payment('Undated item excluded', null, 'unpaid');
        $payment('Future payment', '2026-11-22', 'partial');
        $payment('Oldest outstanding invoice', '2026-11-18', 'partial');
        $payment('Outstanding hosting invoice', '2026-11-19', 'unpaid');
        $payment('Outstanding maintenance invoice', '2026-11-20', 'unpaid');
        $payment('Item beyond the cap', '2026-11-21', 'unpaid');

        $this->actingAs(User::factory()->owner()->create())
            ->get('/admin/today')
            ->assertOk()
            ->assertSee('Saturday, November 21, 2026')
            ->assertSee('tableActionRecord=' . $due->id)
            ->assertSeeInOrder([
                'Overdue renewal check',
                'Summary due today',
                'Oldest outstanding invoice',
                'Outstanding hosting invoice',
                'Outstanding maintenance invoice',
            ])
            ->assertDontSee('Completed follow-up')
            ->assertDontSee('Future follow-up')
            ->assertDontSee('Paid item excluded')
            ->assertDontSee('Undated item excluded')
            ->assertDontSee('Future payment')
            ->assertDontSee('Item beyond the cap');
    }

    public function test_follow_up_creation_and_completion_are_owner_only_and_audited(): void
    {
        $this->get('/admin/today')->assertRedirect('/admin/login');
        $this->get('/admin/follow-ups')->assertRedirect('/admin/login');
        $clientUser = User::factory()->create();
        $this->actingAs($clientUser)->get('/admin/today')->assertForbidden();
        $this->actingAs($clientUser)->get('/admin/follow-ups')->assertForbidden();

        $owner = User::factory()->owner()->create();
        $client = Client::create(['name' => 'Example client']);
        $this->actingAs($owner);

        Livewire::test(ManageFollowUps::class)->callAction('create', data: [
            'client_id' => $client->id,
            'title' => 'Confirm catalogue copy',
            'due_on' => now('Asia/Manila')->toDateString(),
        ])->assertHasNoActionErrors();
        $record = FollowUp::query()->sole();

        Livewire::withQueryParams(['tableAction' => 'edit', 'tableActionRecord' => $record->id])
            ->test(ManageFollowUps::class)
            ->call('mountDefaultActions')
            ->assertSet('mountedActions.0.name', 'edit')
            ->assertSet('mountedActions.0.context.recordKey', $record->id)
            ->fillForm(['is_completed' => true])
            ->callMountedAction()
            ->assertHasNoActionErrors();

        $this->assertTrue($record->refresh()->is_completed);
        $this->assertNotSame('Confirm catalogue copy', $record->getRawOriginal('title'));
        $this->assertDatabaseHas('audit_events', [
            'actor_id' => $owner->id,
            'client_id' => $client->id,
            'target_type' => 'follow_up',
            'target_id' => $record->id,
            'operation' => 'update',
        ]);
        $this->get('/admin/today')->assertOk()->assertSee('No follow-ups or payments are due.');
    }
}
