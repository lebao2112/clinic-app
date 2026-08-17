<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrescriptionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'prescription_id',
        'medicine_id',
        'quantity',
        'dosage',
        'usage_instruction',
    ];

    /**
     * Get the prescription that owns the item.
     */
    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }

    /**
     * Get the medicine referenced by this item.
     */
    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }
}