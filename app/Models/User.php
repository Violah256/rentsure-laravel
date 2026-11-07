<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;


use App\Models\Property;
use App\Models\VerificationDocument;
use App\Models\Tenancy;
use App\Models\PaymentReceipt;
use App\Models\FraudReport;
use App\Models\AuditLog;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_TENANT = 'tenant';
    public const ROLE_LANDLORD = 'landlord';
    public const ROLE_AGENT = 'agent';
    public const ROLE_ADMINISTRATOR = 'administrator';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'neighbourhood',
        'account_status',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function properties()
    {
        return $this->hasMany(Property::class);
    }

    public function verificationDocuments()
    {
        return $this->hasMany(VerificationDocument::class);
    }

    public function tenantTenancies()
    {
        return $this->hasMany(Tenancy::class, 'tenant_id');
    }

    public function landlordTenancies()
    {
        return $this->hasMany(Tenancy::class, 'landlord_id');
    }

    public function paymentReceipts()
    {
        return $this->hasMany(
            PaymentReceipt::class,
            'recorded_by'
        );
    }

    public function fraudReports()
    {
        return $this->hasMany(
            FraudReport::class,
            'reported_by'
        );
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    public function isTenant(): bool
    {
        return $this->role === self::ROLE_TENANT;
    }

    public function isLandlord(): bool
    {
        return $this->role === self::ROLE_LANDLORD;
    }

    public function isAgent(): bool
    {
        return $this->role === self::ROLE_AGENT;
    }

    public function isAdministrator(): bool
    {
        return $this->role === self::ROLE_ADMINISTRATOR;
    }

    public function canAdvertiseProperties(): bool
    {
        return in_array($this->role, [
            self::ROLE_LANDLORD,
            self::ROLE_AGENT,
            self::ROLE_ADMINISTRATOR,
        ], true);
    }

    public function isActive(): bool
    {
        return $this->account_status === 'active';
    }

    public function roleLabel(): string
    {
        return match ($this->role) {
            self::ROLE_TENANT => 'Tenant',
            self::ROLE_LANDLORD => 'Landlord',
            self::ROLE_AGENT => 'Property Agent',
            self::ROLE_ADMINISTRATOR => 'Administrator',
            default => 'User',
        };
    }
}

