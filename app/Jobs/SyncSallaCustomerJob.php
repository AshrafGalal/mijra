<?php

namespace App\Jobs;

use App\Models\Tenant\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncSallaCustomerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public array $customerData,
        public string $action
    ) {
        //
    }

    public function handle(): void
    {
        try {
            $sallaCustomerId = $this->customerData['id'] ?? null;
            $email = $this->customerData['email'] ?? null;
            $phone = $this->customerData['mobile'] ?? null;

            $customer = null;

            if ($email) {
                $customer = Customer::where('email', $email)->first();
            }

            if (!$customer && $phone) {
                $customer = Customer::where('phone', $phone)->first();
            }

            $customerAttributes = [
                'name' => trim(($this->customerData['first_name'] ?? '') . ' ' . ($this->customerData['last_name'] ?? '')),
                'email' => $email,
                'phone' => $phone,
                'city' => $this->customerData['city'] ?? null,
                'country' => 'Saudi Arabia',
                'tags' => [
                    'salla_customer_id' => $sallaCustomerId,
                ],
            ];

            if ($customer) {
                $customer->update($customerAttributes);
            } else {
                $customerAttributes['source'] = \App\Enum\CustomerSourceEnum::SHOPIFY->value;
                $customerAttributes['status'] = \App\Enum\CustomerStatusEnum::CUSTOMER->value;
                Customer::create($customerAttributes);
            }

        } catch (\Exception $e) {
            Log::error('Error syncing Salla customer', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
