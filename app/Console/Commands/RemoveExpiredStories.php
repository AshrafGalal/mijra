<?php

namespace App\Console\Commands;

use App\Models\Tenant\Story;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RemoveExpiredStories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stories:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove or clean up expired WhatsApp stories (statuses)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = now();

        // Get expired stories
        $expiredStories = Story::query()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', $now)
            ->get();

        if ($expiredStories->isEmpty()) {
            $this->info('✅ No expired stories found.');
            return Command::SUCCESS;
        }

        $count = $expiredStories->count();
        $this->warn("⚠️ Found {$count} expired stories.");

        // Perform deletion
        Story::whereIn('id', $expiredStories->pluck('id'))->delete();

        Log::info("🧹 {$count} expired stories deleted at {$now}.");

        $this->info("🧹 Successfully deleted {$count} expired stories.");

        return Command::SUCCESS;

    }
}
