<?php

namespace App\Http\Controllers\Api\Tenant\Channels;

use App\DTOs\Tenant\Conversation\ConversationDTO;
use App\DTOs\Tenant\Conversation\SendMessageDTO;
use App\DTOs\Tenant\Conversation\StartConversationDTO;
use App\Enum\ExternalPlatformEnum;
use App\Enum\MessageStatusEnum;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Whatsapp\ReceiveWhatsappMessageRequest;
use App\Http\Requests\Tenant\Whatsapp\SendWhatsappMessageReactionRequest;
use App\Http\Requests\Tenant\Whatsapp\SendWhatsappMessageRequest;
use App\Http\Requests\Tenant\Whatsapp\StartConversationRequest;
use App\Http\Resources\Tenant\Conversation\ConversationResource;
use App\Jobs\SyncWhatsappChatsJob;
use App\Jobs\UpdateWhatsappMessageAckJob;
use App\Services\Tenant\Actions\Conversation\StartConversationService;
use App\Services\Tenant\ConversationService;
use App\Services\Tenant\MessageService;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class WhatsappController extends Controller
{
    public function __construct(
        protected readonly ConversationService      $conversationService,
        protected readonly MessageService           $messageService,
        protected readonly StartConversationService $startConversationService,
    )
    {
    }

    public function startConversation(StartConversationRequest $request)
    {
        try {
            $startConversationDTO = StartConversationDTO::fromRequest($request);
            $conversation = $this->startConversationService->handle($startConversationDTO);
            return ConversationResource::make($conversation);
        } catch (NotFoundHttpException $e) {
            return ApiResponse::badRequest(message: $e->getMessage());
        }
    }

    public function requestSync($account_id)
    {

        try {
            // Call Node.js service to initialize
            $response = $this->conversationService->requestSync($account_id);
            if ($response->successful()) {
                return ApiResponse::success(message: 'Will notify you after sync finished');
            }

            return ApiResponse::error(message: 'Failed to sync WhatsApp Chats', errors: $response->json('error'));

        } catch (\Exception $e) {
            logger('Error sync WhatsApp conversion', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }

    }

    public function syncChats(ReceiveWhatsappMessageRequest $request)
    {
        $whatsappMessageDTO = ConversationDTO::fromRequest($request);
        SyncWhatsappChatsJob::dispatch($whatsappMessageDTO)->onQueue('whatsapp');
        return ApiResponse::success();
    }

    public function receiveMessage(ReceiveWhatsappMessageRequest $request)
    {

        $whatsappMessageDTO = ConversationDTO::fromRequest($request);
        $this->conversationService->receiveMessage(platform: ExternalPlatformEnum::WHATSAPP->value, baseDTO: $whatsappMessageDTO);
        return ApiResponse::success();
    }

    public function sendMessage($conversation_id, SendWhatsappMessageRequest $request)
    {
        try {
            $request->merge([
                'conversationId' => $conversation_id,
                'platform' => ExternalPlatformEnum::WHATSAPP->value,
            ]);

            $sendMessageDTO = SendMessageDTO::fromRequest($request);

            $this->conversationService->sendMessage(platform: ExternalPlatformEnum::WHATSAPP->value, sendMessageDTO: $sendMessageDTO);

            return ApiResponse::success();
        } catch (\Exception $e) {
            return ApiResponse::error(message: 'there is an error please try again later or contact with support for fast response');
        }
    }

    public function updateMessageAck(Request $request)
    {
        try {
            $payload = [
                'status' => MessageStatusEnum::from($request->ack)->value ?? MessageStatusEnum::RECEIVED->value,
            ];

            logger('whatsapp message ack : ' . $request->external_message_id);
            UpdateWhatsappMessageAckJob::dispatch($request->external_message_id, $payload)
                ->onQueue('whatsapp');

            return ApiResponse::success();
        } catch (\Exception $e) {
            return ApiResponse::error(message: $e->getMessage());
        }
    }

    public function updateMessageReaction(Request $request)
    {
        try {
            $payload = [
                'emoji' => $request->reaction,
            ];

            UpdateWhatsappMessageAckJob::dispatch($request->external_message_id, $payload)
                ->onQueue('whatsapp');

            return ApiResponse::success();
        } catch (\Exception $e) {
            return ApiResponse::error(message: $e->getMessage());
        }
    }

    public function sendReaction($message_id, SendWhatsappMessageReactionRequest $request)
    {

        try {
            $response = $this->messageService->sendWhatsappMessageReaction($message_id, $request->emoji);

            if ($response->successful()) {
                return ApiResponse::success();
            }

            return ApiResponse::error(message: 'Failed to make reaction on message', errors: $response->json('error'));

        } catch (\Exception $e) {
            logger('Error send WhatsApp reaction message', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function syncChatsDone(Request $request)
    {
        // todo broadcast event fro notification that sync finished
    }
}
