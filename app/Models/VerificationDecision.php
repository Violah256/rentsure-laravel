<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerificationDecision extends Model
{
    use HasFactory;

    protected $fillable = [
        'verification_document_id',
        'administrator_id',
        'decision',
        'notes',
    ];

    public function document()
    {
        return $this->belongsTo(
            VerificationDocument::class,
            'verification_document_id'
        );
    }

    public function administrator()
    {
        return $this->belongsTo(
            User::class,
            'administrator_id'
        );
    }
}