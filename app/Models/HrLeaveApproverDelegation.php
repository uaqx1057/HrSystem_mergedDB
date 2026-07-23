<?php

namespace App\Models;

class HrLeaveApproverDelegation extends BaseModel
{
    protected $table = 'hr_leave_approver_delegations';
    protected $guarded = ['id'];
    protected $casts = ['starts_at' => 'date', 'ends_at' => 'date', 'is_active' => 'boolean'];

    public function manager() { return $this->belongsTo(User::class, 'manager_id'); }
    public function delegate() { return $this->belongsTo(User::class, 'delegate_id'); }
}
