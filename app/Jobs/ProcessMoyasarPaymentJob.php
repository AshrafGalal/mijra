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

class ProcessMoyasarPaymentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public array $paymentData,
        public string $status
    ) {
        //
    }

    public function handle(): void
    {
        try {
            $data = $this->paymentData['data'] ?? [];
            $paymentId = $data['id'] ?? null;
            $amount = $data['amount'] ?? 0;
            $currency = $data['currency'] ?? 'SAR';
            $source = $data['source'] ?? [];
            $metadata = $data['metadata'] ?? [];

            $customerEmail = $source['email'] ?? $metadata['customer_email'] ?? null;
            $customerPhone = $source['phone'] ?? $metadata['customer_phone'] ?? null;

            if (!$paymentId) {
                return;
            }

            // Find customer
            $customer = null;
            
            if ($customerEmail) {
                $customer = Customer::where('email', $customerEmail)->first();
            }

            if (!$customer && $customerPhone) {
                $customer = Customer::where('phone', $customerPhone)->first();
            }

            if ($customer && in_array($this->status, ['paid', 'captured'])) {
                // Create opportunity for successful payment
                Opportunity::create([
                    'customer_id' => $customer->id,
                    'workflow_id' => 1,
                    'status' => \App\Enum\OpportunityStatusEnum::ACTIVE->value,
                    'source' => 'moyasar',
                    'notes' => "Moyasar Payment\nPayment ID: {$paymentId}\nAmount: {$currency} " . ($amount / 100) . "\nStatus: {$this->status}",
                ]);
            }

            Log::info('Moyasar payment processed', [
                'payment_id' => $paymentId,
                'status' => $this->status,
            ]);

        } catch (\Exception $e) {
            Log::error('Error processing Moyasar payment', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
