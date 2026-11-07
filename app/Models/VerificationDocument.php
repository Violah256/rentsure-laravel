<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerificationDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'user_id',
        'document_type',
        'original_name',
        'file_path',
        'status',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function submitter()
    {
        return $this->belongsTo(
            User::class,
            'user_id'
        );
    }

    public function decisions()
    {
        return $this->hasMany(
            VerificationDecision::class
        );
    }

    public function latestDecision()
    {
        return $this->hasOne(
            VerificationDecision::class
        )->latestOfMany();
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}