<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FraudReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'reported_by',
        'property_id',
        'advertiser_name',
        'reason',
        'description',
        'evidence_path',
        'status',
        'administrator_notes',
        'handled_by',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
    }

    public function reporter()
    {
        return $this->belongsTo(
            User::class,
            'reported_by'
        );
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function handler()
    {
        return $this->belongsTo(
            User::class,
            'handled_by'
        );
    }
}