<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParentRegistrationVerification extends Model
{
    protected $fillable = [
        'token',
        'name',
        'email',
        'password',
        'code',
        'expires_at',
        'attempts',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }
}
