<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Clearance extends Model
{
    protected $fillable = [
        'student_id',
        'department_id',
        'status',
        'remarks',
        'office_or_instructor',
        'approval_location',
        'clearance_title',
    ];

    protected $casts = [
        'status' => 'string'
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function checklistItems(): HasMany
    {
        return $this->hasMany(ClearanceChecklistItem::class)->orderBy('sort_order')->orderBy('id');
    }
}