<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Property extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reference',
        'title',
        'type',
        'neighbourhood',
        'address',
        'monthly_rent',
        'bedrooms',
        'bathrooms',
        'description',
        'image_path',
        'availability_status',
        'verification_status',
        'verification_notes',
    ];

    protected function casts(): array
    {
        return [
            'monthly_rent' => 'decimal:2',
        ];
    }

    public function advertiser()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function verificationDocuments()
    {
        return $this->hasMany(VerificationDocument::class);
    }

    public function tenancies()
    {
        return $this->hasMany(Tenancy::class);
    }

    public function fraudReports()
    {
        return $this->hasMany(FraudReport::class);
    }

    public function imageUrl(): ?string
    {
        return $this->image_path
            ? Storage::url($this->image_path)
            : null;
    }

    public function isVerified(): bool
    {
        return $this->verification_status === 'verified';
    }

    public function isAvailable(): bool
    {
        return $this->availability_status === 'available';
    }
}