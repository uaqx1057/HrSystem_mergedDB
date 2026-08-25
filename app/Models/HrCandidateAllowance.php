<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrCandidateAllowance extends Model
{
    use HasFactory;
    protected $fillable = ['candidate_id', 'name', 'amount'];

    public function candidate()
    {
        return $this->belongsTo(HrCandidate::class, 'candidate_id');
    }
}
