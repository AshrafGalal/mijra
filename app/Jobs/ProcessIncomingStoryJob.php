<?php

namespace App\Jobs;

use App\DTOs\Tenant\Conversation\ConversationDTO;
use App\DTOs\Tenant\StoryDTO;
use App\Services\Tenant\StoryService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Arr;

class ProcessIncomingStoryJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public ConversationDTO $conversationDTO)
    {
        //
    }

    /**
     * Execute the job.
     * @throws \Throwable
     */
    public function handle(StoryService $storyService): void
    {
        $messageData = Arr::first($this->conversationDTO->messages);
        $storyDTO = StoryDTO::fromArray($this->conversationDTO->toArray());
        $storyDTO->external_identifier_id = $messageData['external_message_id'];
        $storyDTO->body = $messageData['body'];
        if ($messageData['has_media']) {
            $storyDTO->has_media = true;
            $storyDTO->body = $messageData['body'] ?? $messageData['caption'] ?? '';
            $storyDTO->mediaData = $messageData['mediaData'];
        }

        $storyService->create(dto: $storyDTO);
    }
}
