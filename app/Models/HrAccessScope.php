<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Explicit cross-branch access for HR modules.
 *
 * This table is intentionally HR-specific. It must not be used as a shortcut
 * for DMS or DOBS authorization.
 */
class HrAccessScope extends BaseModel
{
    protected $table = 'hr_access_scopes';

    protected $fillable = [
        'company_id',
        'user_id',
        'module',
        'scope',
        'is_active',
        'starts_at',
        'ends_at',
        'granted_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function grantedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by');
    }
}
