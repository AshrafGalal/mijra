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

class SyncWooCommerceOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public array $orderData,
        public string $source,
        public string $action
    ) {
        //
    }

    public function handle(): void
    {
        try {
            $orderId = $this->orderData['id'] ?? null;
            $billing = $this->orderData['billing'] ?? [];

            if (!$orderId) {
                return;
            }

            // Find or create customer
            $customer = $this->syncCustomer($billing, $this->orderData);

            if ($customer) {
                $this->syncOpportunity($customer, $orderId);
            }

            Log::info('WooCommerce order synced', ['order_id' => $orderId]);

        } catch (\Exception $e) {
            Log::error('Error syncing WooCommerce order', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    protected function syncCustomer(array $billing, array $orderData): ?Customer
    {
        $email = $billing['email'] ?? null;
        $phone = $billing['phone'] ?? null;

        if (!$email && !$phone) {
            return null;
        }

        $customer = null;

        if ($email) {
            $customer = Customer::where('email', $email)->first();
        }

        if (!$customer && $phone) {
            $customer = Customer::where('phone', $phone)->first();
        }

        if (!$customer) {
            $customer = Customer::create([
                'name' => $billing['first_name'] . ' ' . $billing['last_name'],
                'email' => $email,
                'phone' => $phone,
                'address' => $billing['address_1'] ?? null,
                'city' => $billing['city'] ?? null,
                'country' => $billing['country'] ?? null,
                'zipcode' => $billing['postcode'] ?? null,
                'source' => \App\Enum\CustomerSourceEnum::SHOPIFY->value,
                'status' => \App\Enum\CustomerStatusEnum::CUSTOMER->value,
                'tags' => [
                    'woocommerce_customer_id' => $orderData['customer_id'] ?? null,
                ],
            ]);
        }

        return $customer;
    }

    protected function syncOpportunity(Customer $customer, string $wooOrderId): void
    {
        $total = $this->orderData['total'] ?? 0;
        $currency = $this->orderData['currency'] ?? 'USD';
        $status = $this->orderData['status'] ?? 'pending';

        $notes = "WooCommerce Order #{$this->orderData['number'] ?? $wooOrderId}\n";
        $notes .= "Total: {$currency} {$total}\n";
        $notes .= "Status: {$status}\n";
        $notes .= "woocommerce_order_id:{$wooOrderId}";

        Opportunity::create([
            'customer_id' => $customer->id,
            'workflow_id' => 1,
            'status' => \App\Enum\OpportunityStatusEnum::ACTIVE->value,
            'source' => 'woocommerce',
            'notes' => $notes,
        ]);
    }
}

