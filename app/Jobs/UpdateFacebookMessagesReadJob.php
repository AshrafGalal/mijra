<?php

namespace App\Jobs;

use App\Enum\MessageStatusEnum;
use App\Models\Tenant\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateFacebookMessagesReadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $watermark
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Find all outbound messages before watermark timestamp and mark as read
            $watermarkDate = \Carbon\Carbon::createFromTimestamp($this->watermark);

            $messages = Message::where('direction', 'outbound')
                ->where('created_at', '<=', $watermarkDate)
                ->whereIn('status', [MessageStatusEnum::SENT->value, MessageStatusEnum::DELIVERED->value])
                ->get();

            foreach ($messages as $message) {
                $message->markAsRead();
            }

            Log::info('Facebook messages marked as read', [
                'count' => $messages->count(),
                'watermark' => $watermarkDate,
            ]);

        } catch (\Exception $e) {
            Log::error('Error marking Facebook messages as read', [
                'error' => $e->getMessage(),
                'watermark' => $this->watermark,
            ]);
            throw $e;
        }
    }
}

