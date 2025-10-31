<?php

namespace App\Jobs;

use App\Models\Tenant\Customer;
use App\Models\Tenant\Opportunity;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncSallaOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public array $orderData,
        public string $action
    ) {
        //
    }

    public function handle(): void
    {
        try {
            $orderId = $this->orderData['id'] ?? null;
            $customerData = $this->orderData['customer'] ?? [];

            if (!$orderId) {
                return;
            }

            // Sync customer
            $customer = $this->syncCustomer($customerData);

            if (!$customer) {
                return;
            }

            // Sync opportunity
            $this->syncOpportunity($customer, $orderId);

            Log::info('Salla order synced', [
                'order_id' => $orderId,
                'customer_id' => $customer->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Error syncing Salla order', [
                'error' => $e->getMessage(),
                'order_id' => $this->orderData['id'] ?? null,
            ]);
            throw $e;
        }
    }

    protected function syncCustomer(array $customerData): ?Customer
    {
        if (empty($customerData)) {
            return null;
        }

        $email = $customerData['email'] ?? null;
        $phone = $customerData['mobile'] ?? null;

        $customer = null;

        if ($email) {
            $customer = Customer::where('email', $email)->first();
        }

        if (!$customer && $phone) {
            $customer = Customer::where('phone', $phone)->first();
        }

        if (!$customer) {
            $customer = Customer::create([
                'name' => $customerData['first_name'] . ' ' . $customerData['last_name'],
                'email' => $email,
                'phone' => $phone,
                'city' => $customerData['city'] ?? null,
                'country' => 'Saudi Arabia', // Salla is Saudi-based
                'source' => \App\Enum\CustomerSourceEnum::SHOPIFY->value, // Will update enum
                'status' => \App\Enum\CustomerStatusEnum::CUSTOMER->value,
                'tags' => [
                    'salla_customer_id' => $customerData['id'] ?? null,
                ],
            ]);
        }

        return $customer;
    }

    protected function syncOpportunity(Customer $customer, string $sallaOrderId): void
    {
        $totalAmount = $this->orderData['amounts']['total'] ?? 0;
        $status = $this->orderData['status']['name'] ?? 'pending';

        $notes = "Salla Order #{$this->orderData['reference_id'] ?? $sallaOrderId}\n";
        $notes .= "Total: SAR {$totalAmount}\n";
        $notes .= "Status: {$status}\n";
        $notes .= "salla_order_id:{$sallaOrderId}";

        Opportunity::create([
            'customer_id' => $customer->id,
            'workflow_id' => 1,
            'status' => \App\Enum\OpportunityStatusEnum::ACTIVE->value,
            'source' => 'salla',
            'notes' => $notes,
        ]);
    }
}
