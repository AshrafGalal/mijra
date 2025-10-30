<?php

namespace App\Http\Controllers\Api\Landlord\Stripe;

use App\Http\Controllers\Controller;
use App\Models\Landlord\Invoice;
use App\Services\Landlord\Invoice\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook as StripeWebhook;

class StripeWebhookController extends Controller
{
    public function __construct(public InvoiceService $invoiceService) {}

    public function __invoke(Request $request)
    {
        logger()->info('Stripe webhook received');
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');
        $webhook_secret = config('services.stripe.webhook_secret');
        try {
            $event = StripeWebhook::constructEvent($payload, $sig_header, $webhook_secret);
        } catch (\UnexpectedValueException $e) {
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }
        $intent = $event->data->object;
        // Find local invoice by PaymentIntent ID
        $invoice = Invoice::query()->where('payment_reference', $intent->id)->first();
        switch ($event->type) {
            case 'payment_intent.succeeded':
            case 'invoice.payment_succeeded':
                if ($invoice) {
                    $this->invoiceService->markAsPaid($invoice);
                }
                break;
            case 'payment_intent.payment_failed':
            case 'invoice.payment_failed':
                if ($invoice) {
                    $invoice->markAsFailed();
                }
                break;

            case 'customer.subscription.deleted':
                Log::info('Stripe event:deleted');
                break;
            default:
                Log::info("Unhandled Stripe event: {$event->type}");
        }
    }
}
