<?php

namespace App\Jobs;

use App\Models\Tenant\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncShopifyCustomerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public array $customerData,
        public string $shopDomain,
        public string $action
    ) {
        //
    }

    public function handle(): void
    {
        try {
            $shopifyCustomerId = $this->customerData['id'] ?? null;
            $email = $this->customerData['email'] ?? null;
            $phone = $this->customerData['phone'] ?? null;

            if (!$shopifyCustomerId) {
                return;
            }

            // Find existing customer by email or phone
            $customer = null;

            if ($email) {
                $customer = Customer::where('email', $email)->first();
            }

            if (!$customer && $phone) {
                $customer = Customer::where('phone', $phone)->first();
            }

            // Prepare customer data
            $customerAttributes = [
                'name' => trim(($this->customerData['first_name'] ?? '') . ' ' . ($this->customerData['last_name'] ?? '')),
                'email' => $email,
                'phone' => $phone,
                'city' => $this->customerData['default_address']['city'] ?? null,
                'country' => $this->customerData['default_address']['country'] ?? null,
                'zipcode' => $this->customerData['default_address']['zip'] ?? null,
                'address' => $this->customerData['default_address']['address1'] ?? null,
                'tags' => [
                    'shopify_customer_id' => $shopifyCustomerId,
                    'total_spent' => $this->customerData['total_spent'] ?? 0,
                    'orders_count' => $this->customerData['orders_count'] ?? 0,
                ],
            ];

            if ($customer) {
                // Update existing customer
                $customer->update($customerAttributes);
                Log::info('Shopify customer updated', ['customer_id' => $customer->id]);
            } else {
                // Create new customer
                $customerAttributes['source'] = \App\Enum\CustomerSourceEnum::SHOPIFY->value;
                $customerAttributes['status'] = \App\Enum\CustomerStatusEnum::CUSTOMER->value;
                
                $customer = Customer::create($customerAttributes);
                Log::info('New customer created from Shopify', ['customer_id' => $customer->id]);
            }

        } catch (\Exception $e) {
            Log::error('Error syncing Shopify customer', [
                'error' => $e->getMessage(),
                'customer_id' => $this->customerData['id'] ?? null,
            ]);
            throw $e;
        }
    }
}

