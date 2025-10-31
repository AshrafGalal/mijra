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

class ProcessPymobPaymentJob implements ShouldQueue
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
            $transactionId = $this->paymentData['transaction_id'] ?? null;
            $amount = $this->paymentData['amount'] ?? 0;
            $currency = $this->paymentData['currency'] ?? 'EGP';
            $customerEmail = $this->paymentData['customer_email'] ?? null;
            $customerPhone = $this->paymentData['customer_phone'] ?? null;

            if (!$transactionId) {
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

            if ($customer && $this->status === 'success') {
                // Create opportunity for successful payment
                Opportunity::create([
                    'customer_id' => $customer->id,
                    'workflow_id' => 1,
                    'status' => \App\Enum\OpportunityStatusEnum::ACTIVE->value,
                    'source' => 'pymob',
                    'notes' => "Pymob Payment\nTransaction: {$transactionId}\nAmount: {$currency} {$amount}\nStatus: {$this->status}",
                ]);
            }

            Log::info('Pymob payment processed', [
                'transaction_id' => $transactionId,
                'status' => $this->status,
            ]);

        } catch (\Exception $e) {
            Log::error('Error processing Pymob payment', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}

