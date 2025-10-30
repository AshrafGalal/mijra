<?php

namespace App\Jobs;

use App\Models\Tenant\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DetectAbandonedCartJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Delay before sending abandoned cart notification (1 hour)
    public $delay = 3600;

    public function __construct(
        public array $cartData,
        public string $shopDomain
    ) {
        //
    }

    public function handle(): void
    {
        try {
            $customerId = $this->cartData['customer']['id'] ?? null;
            $email = $this->cartData['email'] ?? $this->cartData['customer']['email'] ?? null;
            $updatedAt = $this->cartData['updated_at'] ?? null;
            $lineItems = $this->cartData['line_items'] ?? [];

            if (!$email || empty($lineItems)) {
                return;
            }

            // Check if cart is still abandoned (not completed)
            $cartAge = $updatedAt ? Carbon::parse($updatedAt)->diffInHours(now()) : 0;

            if ($cartAge < 1) {
                // Re-queue this job to check later
                dispatch($this)->delay(now()->addHour());
                return;
            }

            // Find customer
            $customer = Customer::where('email', $email)->first();

            if (!$customer) {
                return;
            }

            // Create opportunity for abandoned cart
            $totalPrice = $this->cartData['total_price'] ?? array_sum(array_column($lineItems, 'price'));
            
            $notes = "Abandoned Cart\n";
            $notes .= "Cart ID: " . ($this->cartData['id'] ?? 'N/A') . "\n";
            $notes .= "Total: {$totalPrice}\n";
            $notes .= "Items: " . count($lineItems) . "\n";
            $notes .= "Last Updated: {$updatedAt}\n\n";
            $notes .= "Items:\n";
            
            foreach ($lineItems as $item) {
                $notes .= "- " . ($item['title'] ?? 'Product') . " x" . ($item['quantity'] ?? 1) . "\n";
            }

            // TODO: Create opportunity or trigger abandoned cart campaign
            // For now, just log it
            Log::info('Abandoned cart detected', [
                'customer_id' => $customer->id,
                'total_price' => $totalPrice,
                'items_count' => count($lineItems),
            ]);

            // TODO: Dispatch abandoned cart recovery campaign
            // dispatch(new SendAbandonedCartCampaignJob($customer, $cartData));

        } catch (\Exception $e) {
            Log::error('Error detecting abandoned cart', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}

