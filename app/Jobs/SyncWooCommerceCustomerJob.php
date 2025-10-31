<?php

namespace App\Jobs;

use App\Models\Tenant\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncWooCommerceCustomerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public array $customerData,
        public string $source,
        public string $action
    ) {
        //
    }

    public function handle(): void
    {
        $email = $this->customerData['email'] ?? null;
        $billing = $this->customerData['billing'] ?? [];

        if (!$email) {
            return;
        }

        $customer = Customer::where('email', $email)->first();

        $customerAttributes = [
            'name' => ($billing['first_name'] ?? '') . ' ' . ($billing['last_name'] ?? ''),
            'email' => $email,
            'phone' => $billing['phone'] ?? null,
            'address' => $billing['address_1'] ?? null,
            'city' => $billing['city'] ?? null,
            'country' => $billing['country'] ?? null,
            'zipcode' => $billing['postcode'] ?? null,
            'tags' => [
                'woocommerce_customer_id' => $this->customerData['id'] ?? null,
            ],
        ];

        if ($customer) {
            $customer->update($customerAttributes);
        } else {
            $customerAttributes['source'] = \App\Enum\CustomerSourceEnum::SHOPIFY->value;
            $customerAttributes['status'] = \App\Enum\CustomerStatusEnum::CUSTOMER->value;
            Customer::create($customerAttributes);
        }
    }
}

