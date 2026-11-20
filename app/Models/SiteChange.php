<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['client_id', 'site_id', 'changed_on', 'title', 'description', 'reason', 'links', 'is_client_visible'])]
class SiteChange extends Model
{
    protected function casts(): array
    {
        return [
            'changed_on' => 'date:Y-m-d',
            'title' => 'encrypted',
            'description' => 'encrypted',
            'reason' => 'encrypted',
            'links' => 'encrypted:array',
            'is_client_visible' => 'boolean',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
