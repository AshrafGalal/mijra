<?php

namespace App\Services\Tenant;

use App\DTOs\Abstract\BaseDTO;
use App\DTOs\Tenant\Conversation\ConversationDTO;
use App\DTOs\Tenant\Conversation\SendMessageDTO;
use App\Models\Landlord\Tenant;
use App\Models\Tenant\Conversation;
use App\Services\BaseService;
use App\Services\Tenant\Actions\Conversation\Platforms\PlatformFactory;
use App\Services\Tenant\Actions\Conversation\Platforms\PlatformInterface;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ConversationService extends BaseService
{
    public function __construct(protected PlatformFactory $platformFactory)
    {
    }

    protected string $cachePrefix = 'conversation:';

    public function getCacheKey(string $platform, string $externalChatId): string
    {
        return "{$this->cachePrefix}{$platform}:{$externalChatId}";
    }

    protected function getFilterClass(): ?string
    {
        return null;
    }

    protected function baseQuery(): Builder
    {
        return Conversation::query();
    }

    public function firstOrCreate(ConversationDTO $conversationDTO): Model|Conversation
    {
        $cacheKey = $this->getCacheKey($conversationDTO->platform, $conversationDTO->external_identifier_id);

        // Try cache first
        // ✅ Try Redis cache first
        $cached = Cache::get($cacheKey);
        if ($cached) {
            $conversation = new Conversation;
            $conversation->forceFill($cached);
            $conversation->exists = true; // ✅ Mark as existing model

            return $conversation;
        }
        // ✅ Use firstOrCreate (runs only one query)
        $conversation = $this->baseQuery()
            ->firstOrCreate(
                [
                    'external_identifier_id' => $conversationDTO->external_identifier_id,
                    'platform_account_id' => $conversationDTO->platform_account_id,
                ],
                [
                    'contact_id' => $conversationDTO->contact_id,
                    'contact_identifier_id' => $conversationDTO->contact_identifier_id,
                    'contact_name' => $conversationDTO->contact_name,
                    'title' => $conversationDTO->title,
                    'platform_account_number' => $conversationDTO->platform_account_number,
                    'platform' => $conversationDTO->platform,
                    'metadata' => $conversationDTO->metadata,
                ]
            );

        // Cache it
        Cache::put($cacheKey, ['id' => $conversation->id]);

        return $conversation;
    }

    public function paginateConversations($filters = [], $withRelations = [], $limit = 15): CursorPaginator
    {
        return $this->getQuery($filters, $withRelations)->cursorPaginate($limit);
    }

    public function sendMessage(string $platform, SendMessageDTO $sendMessageDTO)
    {
        /** @var PlatformInterface $service */
        $service = $this->platformFactory->make($platform);

        return $service->sendMessage($sendMessageDTO);
    }
    public function receiveMessage(string $platform, BaseDTO $baseDTO)
    {
        /** @var PlatformInterface $service */
        $service = $this->platformFactory->make($platform);

        return $service->receiveMessage($baseDTO);
    }

    public function requestSync($account_id)
    {
        $tenant = Tenant::current();
        // Call Node.js service to initialize
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.whatsapp.api_secret_token'),
        ])->post(config('services.whatsapp.node_service_url') . '/chats/sync', [
            'tenant_id' => $tenant->id,
            'account_id' => $account_id, // unique ID for this connection
        ]);

    }
}
