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

class SyncShopifyOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public array $orderData,
        public string $shopDomain,
        public string $action
    ) {
        //
    }

    public function handle(): void
    {
        try {
            $orderId = $this->orderData['id'] ?? null;
            $orderNumber = $this->orderData['order_number'] ?? $this->orderData['name'] ?? null;
            $customerData = $this->orderData['customer'] ?? [];

            if (!$orderId) {
                Log::warning('Shopify order missing ID', ['order' => $this->orderData]);
                return;
            }

            // Find or create customer
            $customer = $this->syncCustomer($customerData);

            if (!$customer) {
                Log::warning('Could not sync Shopify customer', ['order_id' => $orderId]);
                return;
            }

            // Create or update opportunity (order as opportunity)
            $this->syncOpportunity($customer, $orderId, $orderNumber);

            Log::info('Shopify order synced successfully', [
                'order_id' => $orderId,
                'customer_id' => $customer->id,
                'action' => $this->action,
            ]);

        } catch (\Exception $e) {
            Log::error('Error syncing Shopify order', [
                'error' => $e->getMessage(),
                'order_id' => $this->orderData['id'] ?? null,
            ]);
            throw $e;
        }
    }

    /**
     * Sync customer from Shopify data.
     */
    protected function syncCustomer(array $customerData): ?Customer
    {
        if (empty($customerData)) {
            return null;
        }

        $shopifyCustomerId = $customerData['id'] ?? null;
        $email = $customerData['email'] ?? null;
        $phone = $customerData['phone'] ?? null;

        // Try to find existing customer
        $customer = null;

        if ($email) {
            $customer = Customer::where('email', $email)->first();
        }

        if (!$customer && $phone) {
            $customer = Customer::where('phone', $phone)->first();
        }

        if (!$customer) {
            // Create new customer
            $customer = Customer::create([
                'name' => $customerData['first_name'] . ' ' . $customerData['last_name'],
                'email' => $email,
                'phone' => $phone,
                'address' => $customerData['default_address']['address1'] ?? null,
                'city' => $customerData['default_address']['city'] ?? null,
                'country' => $customerData['default_address']['country'] ?? null,
                'zipcode' => $customerData['default_address']['zip'] ?? null,
                'source' => \App\Enum\CustomerSourceEnum::SHOPIFY->value,
                'status' => \App\Enum\CustomerStatusEnum::CUSTOMER->value,
            ]);

            Log::info('New customer created from Shopify', ['customer_id' => $customer->id]);
        }

        // Store Shopify customer ID in metadata
        if ($shopifyCustomerId) {
            $customer->update([
                'tags' => array_merge($customer->tags ?? [], [
                    'shopify_customer_id' => $shopifyCustomerId,
                ]),
            ]);
        }

        return $customer;
    }

    /**
     * Sync opportunity from order.
     */
    protected function syncOpportunity(Customer $customer, string $shopifyOrderId, ?string $orderNumber): void
    {
        $totalPrice = $this->orderData['total_price'] ?? 0;
        $currency = $this->orderData['currency'] ?? 'USD';
        $financialStatus = $this->orderData['financial_status'] ?? 'pending';
        $fulfillmentStatus = $this->orderData['fulfillment_status'] ?? 'unfulfilled';

        // Find existing opportunity by Shopify order ID in notes
        $opportunity = Opportunity::where('notes', 'like', "%shopify_order_id:{$shopifyOrderId}%")->first();

        $notes = "Shopify Order\n";
        $notes .= "Order: {$orderNumber}\n";
        $notes .= "Total: {$currency} {$totalPrice}\n";
        $notes .= "Payment: {$financialStatus}\n";
        $notes .= "Fulfillment: {$fulfillmentStatus}\n";
        $notes .= "shopify_order_id:{$shopifyOrderId}";

        if (!$opportunity) {
            // Create new opportunity
            Opportunity::create([
                'customer_id' => $customer->id,
                'workflow_id' => 1, // Default workflow - should be configurable
                'stage_id' => null,
                'status' => $this->mapShopifyStatusToOpportunityStatus($financialStatus),
                'source' => 'shopify',
                'notes' => $notes,
                'priority' => \App\Enum\PriorityEnum::MEDIUM->value,
            ]);
        } else {
            // Update existing opportunity
            $opportunity->update([
                'status' => $this->mapShopifyStatusToOpportunityStatus($financialStatus),
                'notes' => $notes,
            ]);
        }
    }

    /**
     * Map Shopify financial status to opportunity status.
     */
    protected function mapShopifyStatusToOpportunityStatus(string $financialStatus): int
    {
        return match ($financialStatus) {
            'paid' => \App\Enum\OpportunityStatusEnum::ACTIVE->value,
            'pending' => \App\Enum\OpportunityStatusEnum::ACTIVE->value,
            'refunded' => \App\Enum\OpportunityStatusEnum::LOST->value,
            'voided' => \App\Enum\OpportunityStatusEnum::LOST->value,
            default => \App\Enum\OpportunityStatusEnum::ACTIVE->value,
        };
    }
}

