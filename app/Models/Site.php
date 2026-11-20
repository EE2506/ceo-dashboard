<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['client_id', 'name', 'description', 'technologies', 'repo_url', 'hosting_url', 'design_url'])]
class Site extends Model
{
    protected function casts(): array
    {
        return [
            'name' => 'encrypted',
            'description' => 'encrypted',
            'technologies' => 'encrypted',
            'repo_url' => 'encrypted',
            'hosting_url' => 'encrypted',
            'design_url' => 'encrypted',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
