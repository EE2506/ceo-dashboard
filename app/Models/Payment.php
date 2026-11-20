<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['client_id', 'site_id', 'recorded_on', 'due_on', 'amount', 'purpose', 'status', 'invoice_url', 'is_client_visible'])]
class Payment extends Model
{
    public const STATUSES = [
        'unpaid' => 'Unpaid',
        'partial' => 'Partially paid',
        'paid' => 'Paid',
        'void' => 'Void',
    ];

    protected function casts(): array
    {
        return [
            'recorded_on' => 'date:Y-m-d',
            'due_on' => 'date:Y-m-d',
            'amount' => 'decimal:2',
            'purpose' => 'encrypted',
            'invoice_url' => 'encrypted',
            'is_client_visible' => 'boolean',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
