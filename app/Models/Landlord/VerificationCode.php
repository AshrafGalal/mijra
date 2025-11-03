<?php

namespace App\Models\Landlord;

class VerificationCode extends BaseLandlordModel
{
    protected $fillable = [
        'email',
        'code',
        'type',
        'attempts',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function hasExceededMaxAttempts(): bool
    {
        return $this->attempts >= 5; // Configure max attempts as needed
    }
}
