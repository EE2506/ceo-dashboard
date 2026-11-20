<?php

namespace App\Providers;

use App\Models\Client;
use App\Models\FollowUp;
use App\Models\Payment;
use App\Models\Site;
use App\Models\SiteChange;
use App\Models\User;
use App\Observers\ClientHubAudit;
use App\Policies\ClientHubPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::define('connect-gmail', fn (User $user): bool => $user->isOwner());

        foreach ([Client::class, Site::class, SiteChange::class, Payment::class, FollowUp::class] as $model) {
            Gate::policy($model, ClientHubPolicy::class);
            $model::observe(ClientHubAudit::class);
        }
    }
}
