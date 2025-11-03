<?php

namespace App\Services\Tenant\Actions\Conversation;

use App\DTOs\Tenant\Conversation\ConversationDTO;
use App\DTOs\Tenant\Conversation\SendMessageDTO;
use App\DTOs\Tenant\Conversation\StartConversationDTO;
use App\Enum\ConversationTypeEnum;
use App\Enum\ExternalPlatformEnum;
use App\Models\Tenant\Conversation;
use App\Models\Tenant\Customer;
use App\Models\Tenant\Template;
use App\Services\BaseService;
use App\Services\Tenant\ConversationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class StartConversationService extends BaseService
{
    public function __construct(protected ConversationService $conversationService)
    {
    }

    protected function getFilterClass(): ?string
    {
        return null;
    }

    protected function baseQuery(): Builder
    {
        return Conversation::query();
    }


    public function handle(StartConversationDTO $startConversationDTO): Model|Conversation
    {
        $contact = Customer::query()->firstWhere('id', $startConversationDTO->contact_id);
        $template = Template::query()->firstWhere('id', $startConversationDTO->template_id);

        if (!$contact) {
            throw new NotFoundHttpException('contact not found');
        }

        $externalIdentifierId = $contactIdentifierId = $this->prepareChatId(phoneNumber: $contact->phone);

        $conversationData = [
            'contact_id' => $contact->id,
            'external_identifier_id' => $externalIdentifierId,
            'contact_identifier_id' => $contactIdentifierId,
            'contact_name' => $contact->name,
            'title' => $contact->name,
            'type' => ConversationTypeEnum::INDIVIDUAL->value,
            'platform' => ExternalPlatformEnum::WHATSAPP->value,
            'sent_at' => now(),
            'platform_account_id' => $startConversationDTO->platform_account_id,
        ];
        $conversationDTO = ConversationDTO::fromArray($conversationData);


        return DB::connection('tenant')->transaction(function () use ($conversationDTO, $template, $contact) {
            $conversation = $this->conversationService
                ->firstOrCreate(conversationDTO: $conversationDTO);

            $conversation = $conversation->fresh();

            $sendMessageDTO = new SendMessageDTO(
                body: $template->resolveParams(data: ['contacts' => $contact]),
                platform: ExternalPlatformEnum::WHATSAPP->value,
                conversationId: $conversation->id,
            );

            $this->conversationService->sendMessage(platform: ExternalPlatformEnum::WHATSAPP->value, sendMessageDTO: $sendMessageDTO);

            return $conversation;
        });

    }

    public function prepareChatId(string $phoneNumber): ?string
    {
        $clean = preg_replace('/\D/', '', $phoneNumber);
        if (!$clean) {
            return null;
        }

        return "{$clean}@c.us"; // WhatsApp format for individual chat
    }
}
