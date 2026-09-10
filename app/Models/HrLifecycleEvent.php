<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrLifecycleEvent extends BaseModel
{
    public const UPDATED_AT = null;

    protected $guarded = ['id'];

    protected $casts = ['meta' => 'array'];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subject_user_id')->withoutGlobalScopes();
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id')->withoutGlobalScopes();
    }
}
