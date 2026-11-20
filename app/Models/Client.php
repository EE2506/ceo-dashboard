<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name'])]
class Client extends Model
{
    protected function casts(): array
    {
        return ['name' => 'encrypted'];
    }

    public function sites(): HasMany
    {
        return $this->hasMany(Site::class);
    }
}
