<?php

namespace App\Console\Commands;

use App\Models\Landlord\Invoice;
use App\Models\Landlord\Subscription;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DeletePendingInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-pending-invoices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all invoices and subscriptions that are still pending after 24 hours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cutoff = Carbon::now()->subHours(24);

        DB::connection('landlord')
            ->transaction(function () use ($cutoff) {
                // Get IDs of old pending invoices
                $invoiceIds = Invoice::where('status', 'pending')
                    ->where('created_at', '<', $cutoff)
                    ->pluck('id');

                if ($invoiceIds->isEmpty()) {
                    $this->info('No pending invoices older than 24 hours found.');

                    return;
                }

                // Get subscription IDs linked to these invoices
                $subscriptionIds = Invoice::whereIn('id', $invoiceIds)
                    ->pluck('subscription_id')
                    ->filter(); // remove nulls

                // Delete subscriptions in bulk
                if ($subscriptionIds->isNotEmpty()) {
                    Subscription::whereIn('id', $subscriptionIds)->delete();
                }

                // Delete invoices in bulk
                Invoice::whereIn('id', $invoiceIds)->delete();

                $this->info("Deleted {$invoiceIds->count()} pending invoices and related subscriptions older than 24 hours.");
            });
    }
}
