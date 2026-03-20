<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $fillable = ['college_id', 'name'];

    public function college(): BelongsTo
    {
        return $this->belongsTo(College::class);
    }

    public function staff(): HasMany
    {
        return $this->hasMany(User::class)->where('role', 'staff');
    }

    public function clearances(): HasMany
    {
        return $this->hasMany(Clearance::class);
    }
}