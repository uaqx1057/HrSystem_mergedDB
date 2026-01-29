<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [ 
        'name',
        'registration_date',
        'is_active'
    ];

    protected $casts = [
        'registration_date' => 'datetime'
    ];
    
    public function drivers(): HasMany
    {
        return $this->hasMany(Driver::class);
    }
}
