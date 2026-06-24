<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class DriverDocument extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'driver_id',
        'document_type',
        'file_path',
        'original_name',
        'file_size',
        'uploaded_from',
        'uploaded_by',
        'notes',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'date',
        'file_size' => 'integer',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id')->withoutGlobalScopes();
    }

    public function getFullPathAttribute(): string
    {
        return Storage::disk('driver_documents')->path($this->file_path);
    }
}
