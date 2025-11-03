<?php

namespace App\Jobs;

use App\Exceptions\NoAssignableUsersException;
use App\Models\Tenant\Conversation;
use App\Services\Tenant\Actions\Conversation\ConversationAssignmentService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class AssignConversationJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Conversation $conversation)
    {
        //
    }

    /**
     * Execute the job.
     *
     * @throws NoAssignableUsersException
     * @throws Throwable
     */
    public function handle(ConversationAssignmentService $conversationAssignmentService)
    {
        try {
            return $conversationAssignmentService->handle(conversation: $this->conversation);
        } catch (NoAssignableUsersException $e) {
            logger()->error("No assignable users found for conversation {$this->conversation->id}");
            throw $e; // rethrow for retry logic
        } catch (Throwable $e) {
            logger()->error("Assignment failed for conversation {$this->conversation->id}");
            throw $e;
        }
    }
}
