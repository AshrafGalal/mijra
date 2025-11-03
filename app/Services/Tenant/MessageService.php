<?php

namespace App\Services\Tenant;

use App\DTOs\Tenant\MessageDTO;
use App\Models\Landlord\Tenant;
use App\Models\Tenant\Message;
use App\Services\BaseService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class MessageService extends BaseService
{
    protected function getFilterClass(): ?string
    {
        return null;
    }

    protected function baseQuery(): Builder
    {
        return Message::query();
    }

    public function create(MessageDTO $messageDTO): Model|Message
    {
        return DB::connection('tenant')->transaction(function () use ($messageDTO) {
            $message = $this->baseQuery()->create($messageDTO->toArray());
            $this->handleMedia($message, $messageDTO);

            return $message;
        });
    }

    private function handleMedia(Message $message, MessageDTO $messageDTO): void
    {
        // ✅ If media file exists, attach via Spatie Media Library
        if (empty($messageDTO->mediaData)) {
            return;
        }
        $path = storage_path(str_replace('storage/', '', $messageDTO->mediaData['local_path']));
        $message
            ->addMedia($path)
            ->toMediaCollection('whatsapp');
    }

    public function getMessagesbyConversationId(string $conversation_id, ?array $filters = [], ?array $withRelations = [], int $limit = 50)
    {
        $withRelations = array_merge($withRelations, ['replyTo']);

        return $this->getQuery($filters, $withRelations)
            ->where('conversation_id', $conversation_id)
            ->orderByDesc('id')
            ->limit($limit)
            ->cursorPaginate($limit);
    }

    public function updateMessage(string $external_message_id, $payload): void
    {
        $message = $this->baseQuery()
            ->where('external_message_id', $external_message_id)
            ->first();
        if (! $message) {
            logger()->error('Message not found for external_message_id: '.$external_message_id);

            return;
        }
        $message->update($payload);
        //        broadcast(new MessageStatusUpdated($message));
    }

    public function bulkCreate(array $messageData): bool
    {
        return $this->baseQuery()->upsert($messageData, ['external_message_id']);
    }

    public function sendWhatsappMessageReaction($message_id, $reaction)
    {
        $tenant = Tenant::current();

        $message = $this->baseQuery()
            ->with('conversation:id,platform_account_id')
            ->where('id', $message_id)
            ->first(['id', 'external_message_id', 'conversation_id']);

        if (! $message) {
            return false;
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.config('services.whatsapp.api_secret_token'),
        ])->post(config('services.whatsapp.node_service_url').'/messages/react', [
            'tenant_id' => $tenant->id,
            'account_id' => $message->conversation->platform_account_id, // unique ID for this connection
            'externalMessageId' => $message->external_message_id,
            'reaction' => $reaction,
        ]);

        if ($response->successful()) {
            $message->update(['emoji' => $reaction]);
        }

        return $response;

    }
}
