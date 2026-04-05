<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClearanceChecklistItem extends Model
{
	protected $fillable = [
		'clearance_id',
		'item_name',
		'office_role',
		'contact_person',
		'location',
		'status',
		'approved_at',
		'sort_order',
	];

	protected $casts = [
		'status' => 'string',
		'approved_at' => 'datetime',
		'sort_order' => 'integer',
	];

	public function clearance(): BelongsTo
	{
		return $this->belongsTo(Clearance::class);
	}
}
