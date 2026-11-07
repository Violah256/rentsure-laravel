<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentReceipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenancy_id',
        'recorded_by',
        'receipt_number',
        'amount',
        'payment_date',
        'payment_type',
        'payment_method',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_date' => 'date',
        ];
    }

    public function tenancy()
    {
        return $this->belongsTo(Tenancy::class);
    }

    public function recorder()
    {
        return $this->belongsTo(
            User::class,
            'recorded_by'
        );
    }
}