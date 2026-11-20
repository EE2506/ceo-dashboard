<?php

namespace App\Observers;

use App\Models\Client;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ClientHubAudit
{
    public function created(Model $record): void
    {
        $this->record($record, 'create');
    }

    public function updated(Model $record): void
    {
        $this->record($record, 'update');
    }

    public function deleted(Model $record): void
    {
        $this->record($record, 'delete');
    }

    private function record(Model $record, string $operation): void
    {
        $record->getConnection()->table('audit_events')->insert([
            'actor_id' => Auth::id(),
            'client_id' => $record instanceof Client ? $record->getKey() : $record->getAttribute('client_id'),
            'operation' => $operation,
            'target_type' => Str::snake(class_basename($record)),
            'target_id' => $record->getKey(),
            'outcome' => 'succeeded',
            'occurred_at' => now(),
        ]);
    }
}
