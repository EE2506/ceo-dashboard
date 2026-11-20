<?php

namespace Tests\Feature;

use App\Filament\Pages\OwnerLogin;
use App\Filament\Resources\Pages\ManageClients;
use App\Filament\Resources\Pages\ManagePayments;
use App\Filament\Resources\Pages\ManageSiteChanges;
use App\Filament\Resources\Pages\ManageSites;
use App\Models\Client;
use App\Models\Payment;
use App\Models\SiteChange;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Tests\TestCase;

class ClientHubTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_sign_in_and_client_cannot_use_the_dashboard(): void
    {
        $owner = User::factory()->owner()->create(['username' => 'test.owner']);

        $this->get('/admin/clients')->assertRedirect('/admin/login');
        $this->get('/admin/register')->assertNotFound();
        $this->get('/admin/login')->assertOk()->assertSee('Username');

        Livewire::test(OwnerLogin::class)->fillForm([
            'username' => 'TEST.OWNER',
            'password' => 'password',
        ])->call('authenticate')->assertHasNoFormErrors();

        $this->assertAuthenticatedAs($owner);
        $this->get('/admin')->assertRedirect('/admin/clients');

        $clientUser = User::factory()->create();

        foreach (['clients', 'sites', 'site-changes', 'payments'] as $resource) {
            $this->actingAs($clientUser)->get('/admin/' . $resource)->assertForbidden();
        }

        Livewire::actingAs($clientUser)->test(ManagePayments::class)->assertForbidden();
    }

    public function test_owner_can_create_and_edit_the_four_hub_records_with_audit_and_encryption(): void
    {
        $owner = User::factory()->owner()->create();
        $this->actingAs($owner);

        Livewire::test(ManageClients::class)->callAction('create', data: ['name' => 'Example client'])
            ->assertHasNoActionErrors();
        $client = Client::query()->sole();

        Livewire::test(ManageSites::class)->callAction('create', data: [
            'client_id' => $client->id,
            'name' => 'Example store',
            'description' => 'Example storefront',
            'technologies' => 'Laravel',
            'repo_url' => 'https://example.com/repo',
        ])->assertHasNoActionErrors();
        $site = $client->sites()->sole();

        Livewire::test(ManageSiteChanges::class)->callAction('create', data: [
            'client_id' => $client->id,
            'site_id' => $site->id,
            'changed_on' => '2026-11-21',
            'title' => 'Updated catalog',
            'description' => 'Added a catalog section',
            'reason' => 'Client request',
            'links' => [['link' => 'https://example.com/change']],
        ])->assertHasNoActionErrors();

        Livewire::test(ManagePayments::class)->callAction('create', data: [
            'client_id' => $client->id,
            'recorded_on' => '2026-11-21',
            'amount' => '1500.25',
            'status' => 'unpaid',
            'purpose' => 'First installment',
            'invoice_url' => 'https://example.com/invoice',
        ])->assertHasNoActionErrors();
        $payment = Payment::query()->sole();

        Livewire::test(ManagePayments::class)
            ->callAction(TestAction::make('edit')->table($payment), data: [
                'site_id' => $site->id,
                'status' => 'paid',
            ])
            ->assertHasNoActionErrors();

        $change = SiteChange::query()->sole();
        $this->assertSame(['https://example.com/change'], $change->links);
        $this->assertFalse($change->is_client_visible);
        $this->assertSame('1500.25', $payment->refresh()->amount);
        $this->assertFalse($payment->is_client_visible);
        $this->assertSame($site->id, $payment->site_id);
        $this->assertSame('paid', $payment->status);
        $this->assertSame('AES-256-GCM', config('app.cipher'));
        $this->assertNotSame('Example client', DB::table('clients')->value('name'));
        $this->assertNotSame('https://example.com/invoice', DB::table('payments')->value('invoice_url'));
        $this->assertDatabaseHas('audit_events', [
            'target_type' => 'payment',
            'target_id' => $payment->id,
            'actor_id' => $owner->id,
            'client_id' => $client->id,
            'operation' => 'update',
            'outcome' => 'succeeded',
        ]);
        $this->assertDatabaseCount('audit_events', 5);

        $clientUser = User::factory()->create();
        $this->assertFalse(Gate::forUser($clientUser)->allows('view', $change));
        $this->assertFalse(Gate::forUser($clientUser)->allows('view', $payment));
        $this->assertFalse(Gate::forUser($clientUser)->allows('delete', $site));
    }

    public function test_forms_reject_cross_client_site_ids_and_invalid_payment_values(): void
    {
        $this->actingAs(User::factory()->owner()->create());
        $client = Client::create(['name' => 'First example']);
        $other = Client::create(['name' => 'Second example']);
        $site = $other->sites()->create(['name' => 'Other site']);

        Livewire::test(ManagePayments::class)->callAction('create', data: [
            'client_id' => $client->id,
            'site_id' => $site->id,
            'recorded_on' => '2026-11-21',
            'amount' => '12.345',
            'status' => 'unknown',
            'purpose' => 'Test only',
            'invoice_url' => 'https://example.com?access_token=placeholder',
        ])->assertHasActionErrors(['site_id', 'amount', 'status', 'invoice_url']);

        $this->assertDatabaseCount('payments', 0);
    }

    public function test_database_rejects_cross_client_site_ids_when_bypassing_forms(): void
    {
        $client = Client::create(['name' => 'First example']);
        $other = Client::create(['name' => 'Second example']);
        $site = $other->sites()->create(['name' => 'Other site']);

        $this->expectException(QueryException::class);

        SiteChange::create([
            'client_id' => $client->id,
            'site_id' => $site->id,
            'changed_on' => '2026-11-21',
            'title' => 'Invalid association',
            'description' => 'Test only',
            'reason' => 'Test only',
        ]);
    }
}
