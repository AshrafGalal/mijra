<?php

namespace App\Services\Tenant\Actions\Conversation\Platforms;

use App\DTOs\Abstract\BaseDTO;
use App\DTOs\Tenant\Conversation\SendMessageDTO;
use App\DTOs\Tenant\MessageDTO;
use App\Enum\ExternalPlatformEnum;
use App\Enum\MessageDirectionEnum;
use App\Enum\MessageStatusEnum;
use App\Jobs\ProcessIncomingMessageJob;
use App\Jobs\ProcessIncomingStoryJob;
use App\Jobs\SendWhatsappMessageJob;
use App\Models\Landlord\Tenant;
use App\Models\Tenant\Conversation;
use App\Models\Tenant\TempFile;
use App\Services\Tenant\MessageService;
use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

readonly class WhatsappService implements PlatformInterface
{
    public function __construct(protected MessageService $messageService)
    {
    }

    public function sendMessage(SendMessageDTO $messageDTO): bool
    {
        $conversation = $this->getConversation($messageDTO->conversationId);

        if (!$conversation) {
            throw new NotFoundHttpException('Conversation not found');
        }

        $tenant = Tenant::current();
        $media = $this->getMedia($messageDTO->mediaIds);

        if (!empty($media)) {
            $this->sendMediaMessages($media, $messageDTO, $conversation, $tenant);
            return true;
        }

        if (!empty($messageDTO->body)) {
            $this->sendTextMessage($messageDTO, $conversation, $tenant);
            return true;
        }

        return false;
    }

    public function getPlatformName(): string
    {
        return ExternalPlatformEnum::WHATSAPP->value;
    }

    private function getConversation(string $conversationId): ?Conversation
    {
        return Conversation::query()
            ->select([
                'id',
                'platform_account_id',
                'contact_identifier_id',
                'platform_account_number',
            ])
            ->where('id', $conversationId)
            ->first();
    }

    private function getMedia(?array $mediaIds): Collection
    {
        if (empty($mediaIds)) {
            return collect();
        }

        return TempFile::query()->whereIn('file_id', $mediaIds)->get();
    }

    private function prepareMessageDTO(
        Conversation   $conversation,
        SendMessageDTO $messageDTO,
        ?array         $mediaData = null
    ): MessageDTO
    {
        return new MessageDTO(
            conversation_id: $conversation->id,
            external_message_id: null,
            sender: $conversation->whatsapp_account_number,
            receiver: $conversation->contact_identifier_id,
            reply_to_external_message_id: $messageDTO->replyToMessageId,
            body: $messageDTO->body,
            direction: MessageDirectionEnum::OUTGOING->value,
            has_media: !empty($mediaData),
            sent_at: now(),
            mediaData: $mediaData,
            status: MessageStatusEnum::PENDING->value
        );
    }

    private function sendTextMessage(
        SendMessageDTO $messageDTO,
        Conversation   $conversation,
        Tenant         $tenant
    ): void
    {
        $messageData = $this->prepareMessageDTO($conversation, $messageDTO);
        $message = $this->messageService->create($messageData);

        SendWhatsappMessageJob::dispatch($message, $conversation, $tenant)
            ->onQueue('whatsapp');
    }

    private function sendMediaMessages(
        Collection     $media,
        SendMessageDTO $messageDTO,
        Conversation   $conversation,
        Tenant         $tenant
    ): void
    {
        $media->each(function ($media) use ($messageDTO, $conversation, $tenant) {
            $mediaData = [
                'local_path' => 'app/' . $media->path,
            ];
            $messageData = $this->prepareMessageDTO($conversation, $messageDTO, mediaData: $mediaData);
            $message = $this->messageService->create($messageData);
            SendWhatsappMessageJob::dispatch($message, $conversation, $tenant)
                ->onQueue('whatsapp');
        });
    }

    public function receiveMessage(BaseDTO $dto): mixed
    {
        if ($dto->is_story) {
            ProcessIncomingStoryJob::dispatch($dto)->onQueue('whatsapp');
        } else {
            ProcessIncomingMessageJob::dispatch($dto)->onQueue('whatsapp');
        }
        return true;
    }
}
