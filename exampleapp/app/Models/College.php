<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class College extends Model
{
    protected $fillable = ['name'];

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function students()
    {
        return $this->hasMany(User::class)->where('role', 'student');
    }
}