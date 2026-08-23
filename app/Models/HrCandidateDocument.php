<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrCandidateDocument extends BaseModel
{
    protected $table = 'hr_candidate_documents';
    protected $guarded = ['id'];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(HrCandidate::class, 'candidate_id');
    }

    public function getFileUrlAttribute()
    {
        return asset_url_local_s3('candidate-documents/' . $this->stored_path);
    }
}
