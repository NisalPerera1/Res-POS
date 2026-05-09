<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffAdvance extends Model
{
    /**
     * Explicitly set the table name.
     * If your existing migration created the table as 'staff_advances'
     * this matches. If it was created as something else (e.g. 'advances')
     * change this to match.
     */
    protected $table = 'staff_advances';

    /**
     * All columns must be listed here. A missing column in $fillable
     * causes create() to silently skip it — the record saves but the
     * field is null, which looks like "not saved" from the front end.
     */
    protected $fillable = [
        'staff_id',
        'date',
        'amount',
        'reason',
        'status',
        'deduct_month',  // ← required — make sure this column exists (run the fix migration)
        'deduct_year',   // ← required
        'notes',         // ← required
    ];

    protected $casts = [
        'date'         => 'date',
        'amount'       => 'decimal:2',
        'deduct_month' => 'integer',
        'deduct_year'  => 'integer',
    ];

    // Default attribute values
    protected $attributes = [
        'status' => 'approved',
    ];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }
}
