<?php

namespace App\Console\Commands;

use App\Mail\SubscriptionReminderMail;
use App\Models\Landlord\Subscription;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendSubscriptionReminders extends Command
{
    protected $signature = 'subscriptions:reminders';

    protected $description = 'Send subscription expiration reminders 3 days and 1 day before';

    public function handle()
    {
        $today = Carbon::today();

        // 3 days before
        $threeDays = $today->copy()->addDays(3);
        // 1 day before
        $oneDay = $today->copy()->addDay();

        $subscriptions = Subscription::query()->active()
            ->whereIn('ends_at', [$threeDays->toDateString(), $oneDay->toDateString()])
            ->get();

        foreach ($subscriptions as $subscription) {
            // Send email or notification
            Mail::to($subscription->tenant->email)
                ->queue(new SubscriptionReminderMail($subscription));

            $this->info("Reminder sent to {$subscription->tenant->email} for subscription #{$subscription->id}");
        }

        return 0;
    }
}
