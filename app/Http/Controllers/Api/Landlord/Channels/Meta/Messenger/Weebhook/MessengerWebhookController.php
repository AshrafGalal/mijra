<?php

namespace App\Http\Controllers\Api\Landlord\Channels\Meta\Messenger\Weebhook;


use App\Enum\ExternalPlatformEnum;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\Landlord\Channels\Messaging\Mappers\MessengerMessageMapper;
use App\Services\Tenant\ConversationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MessengerWebhookController extends Controller
{
    public function __construct(public ConversationService $conversationService)
    {
    }

    public function verify(Request $request)
    {
        $verifyToken = config('services.facebook.webhook_verify_token');
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        if ($mode == 'subscribe' && $token == $verifyToken) {
            return response($challenge, 200);
        }

        return response()->json(['error' => 'Invalid verify token'], 403);
    }


    public function handle(Request $request)
    {
        $payload = $request->all();

        // Log the webhook for debugging
        Log::info('Facebook Webhook', $payload);

        if (!isset($payload['entry'][0]['messaging'])) {
            return ApiResponse::error(message: 'No messaging data found in the webhook payload');
        }
        $mapped = MessengerMessageMapper::fromWebhookPayload($payload);
        $this->conversationService->receiveMessage(platform: ExternalPlatformEnum::MESSENGER->value, baseDTO: $mapped);
        return ApiResponse::success();
    }

    private function processMessage(array $messaging)
    {
        $senderId = $messaging['sender']['id'];
        $pageId = $messaging['recipient']['id'];
        $message = $messaging['message']['text'] ?? null;

        if ($message) {
            // Here you can process the message
            // For example, save to database, trigger events, etc.
            event(new FacebookMessageReceived($pageId, $senderId, $message));
        }
    }
}
