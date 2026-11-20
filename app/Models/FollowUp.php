<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['client_id', 'site_id', 'title', 'due_on', 'is_completed'])]
class FollowUp extends Model
{
    protected function casts(): array
    {
        return [
            'title' => 'encrypted',
            'due_on' => 'date:Y-m-d',
            'is_completed' => 'boolean',
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
