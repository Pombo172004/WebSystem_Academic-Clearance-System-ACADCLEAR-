<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Clearance extends Model
{
    protected $fillable = [
        'student_id',
        'department_id',
        'status',
        'remarks'
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
}