<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SsoToken extends Model
{
    protected $fillable = [
        'token', 'employee_id', 'target_system',
        'system_user_id', 'expires_at', 'used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at'    => 'datetime',
    ];

    public static function generate(int $employeeId, string $system, int $systemUserId): self
    {
        return self::create([
            'token'          => Str::random(64),
            'employee_id'    => $employeeId,
            'target_system'  => $system,
            'system_user_id' => $systemUserId,
            'expires_at'     => now()->addSeconds(60),
        ]);
    }
}