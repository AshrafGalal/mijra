<?php

namespace App\Console\Commands;

use App\Models\Landlord\VerificationCode;
use Illuminate\Console\Command;

class CleanupVerificationCodes extends Command
{
    protected $signature = 'verification-codes:cleanup';

    protected $description = 'Clean up expired verification codes';

    public function handle()
    {
        $deleted = VerificationCode::where('expires_at', '<', now()->subDays(2))->delete();
        $this->info("Deleted {$deleted} expired verification codes.");
    }
}
