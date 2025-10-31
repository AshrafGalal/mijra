<?php

namespace App\Http\Controllers\Api\Webhooks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GoogleBusinessMessagesWebhookController extends Controller
{
    /**
     * Handle Google Business Messages webhook.
     */
    public function handle(Request $request)
    {
        $payload = $request->all();

        Log::info('Google Business Messages webhook received', ['payload' => $payload]);

        try {
            $message = $payload['message'] ?? null;
            $conversationId = $payload['conversationId'] ?? null;
            $requestId = $payload['requestId'] ?? null;

            if ($message) {
                dispatch(new \App\Jobs\ProcessGoogleBusinessMessageJob($payload));
            }

            return response()->json(['status' => 'ok'], 200);

        } catch (\Exception $e) {
            Log::error('Error processing Google Business Messages webhook', [
                'error' => $e->getMessage(),
            ]);

            return response()->json(['status' => 'error'], 200);
        }
    }
}

