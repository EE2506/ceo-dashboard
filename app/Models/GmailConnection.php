<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['owner_id', 'email_address', 'access_token', 'refresh_token', 'expires_at', 'requires_reconnect'])]
#[Hidden(['access_token', 'refresh_token', 'email_address'])]
class GmailConnection extends Model
{
    protected function casts(): array
    {
        return [
            'email_address' => 'encrypted',
            'access_token' => 'encrypted',
            'refresh_token' => 'encrypted',
            'expires_at' => 'immutable_datetime',
            'requires_reconnect' => 'boolean',
        ];
    }
}
