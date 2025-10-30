<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\AddConversationNoteRequest;
use App\Http\Requests\Tenant\AssignConversationRequest;
use App\Http\Requests\Tenant\ManageConversationTagsRequest;
use App\Http\Requests\Tenant\SendMessageRequest;
use App\Http\Requests\Tenant\UpdateConversationStatusRequest;
use App\Http\Resources\Tenant\ConversationDetailResource;
use App\Http\Resources\Tenant\ConversationResource;
use App\Http\Resources\Tenant\MessageResource;
use App\Services\Tenant\ConversationService;
use App\Services\Tenant\MessageService;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function __construct(
        protected ConversationService $conversationService,
        protected MessageService $messageService
    ) {}

    /**
     * Get paginated list of conversations with filters.
     */
    public function index(Request $request)
    {
        $filters = $request->only([
            'status',
            'platform',
            'assigned_to',
            'customer_id',
            'tag',
            'unread',
            'search',
            'date_from',
            'date_to',
            'sort',
        ]);

        $limit = $request->input('limit', 15);

        $conversations = $this->conversationService->paginate($filters, $limit);

        return ConversationResource::collection($conversations);
    }

    /**
     * Get single conversation with details.
     */
    public function show($id)
    {
        $conversation = $this->conversationService->findById(
            id: $id,
            withRelation: ['customer', 'assignedUser', 'tags', 'notes.user']
        );

        return ApiResponse::success(data: new ConversationDetailResource($conversation));
    }

    /**
     * Get messages for a conversation.
     */
    public function messages(Request $request, $id)
    {
        $limit = $request->input('limit', 50);
        $messages = $this->messageService->getConversationMessages($id, $limit);

        return MessageResource::collection($messages);
    }

    /**
     * Send a message in a conversation.
     */
    public function sendMessage(SendMessageRequest $request, $id)
    {
        $conversation = $this->conversationService->findById($id);

        $message = $this->messageService->createOutboundMessage(
            conversationId: $id,
            content: $request->content,
            userId: auth()->id(),
            type: $request->input('type', 'text'),
            metadata: $request->input('metadata', []),
            attachments: $request->input('attachments', [])
        );

        // Dispatch job to send message to platform based on conversation platform
        match ($conversation->platform) {
            'whatsapp' => dispatch(new \App\Jobs\SendWhatsAppMessageJob($message)),
            'facebook' => dispatch(new \App\Jobs\SendFacebookMessageJob($message)),
            'instagram' => dispatch(new \App\Jobs\SendInstagramMessageJob($message)),
            default => null,
        };

        return ApiResponse::success(
            message: 'Message sent successfully',
            data: new MessageResource($message)
        );
    }

    /**
     * Assign conversation to a user.
     */
    public function assign(AssignConversationRequest $request, $id)
    {
        $conversation = $this->conversationService->assign(
            conversationId: $id,
            userId: $request->user_id,
            assignedBy: auth()->id(),
            type: 'manual'
        );

        return ApiResponse::success(
            message: 'Conversation assigned successfully',
            data: new ConversationResource($conversation)
        );
    }

    /**
     * Unassign conversation.
     */
    public function unassign($id)
    {
        $conversation = $this->conversationService->unassign($id);

        return ApiResponse::success(
            message: 'Conversation unassigned successfully',
            data: new ConversationResource($conversation)
        );
    }

    /**
     * Update conversation status.
     */
    public function updateStatus(UpdateConversationStatusRequest $request, $id)
    {
        $conversation = $this->conversationService->changeStatus($id, $request->status);

        return ApiResponse::success(
            message: 'Conversation status updated successfully',
            data: new ConversationResource($conversation)
        );
    }

    /**
     * Mark conversation as read.
     */
    public function markAsRead($id)
    {
        $this->conversationService->markAsRead($id);

        return ApiResponse::success(message: 'Conversation marked as read');
    }

    /**
     * Add note to conversation.
     */
    public function addNote(AddConversationNoteRequest $request, $id)
    {
        $this->conversationService->addNote(
            conversationId: $id,
            userId: auth()->id(),
            content: $request->content,
            isPinned: $request->input('is_pinned', false)
        );

        return ApiResponse::success(message: 'Note added successfully');
    }

    /**
     * Add tags to conversation.
     */
    public function addTags(ManageConversationTagsRequest $request, $id)
    {
        $this->conversationService->addTags($id, $request->tag_ids);

        return ApiResponse::success(message: 'Tags added successfully');
    }

    /**
     * Remove tags from conversation.
     */
    public function removeTags(ManageConversationTagsRequest $request, $id)
    {
        $this->conversationService->removeTags($id, $request->tag_ids);

        return ApiResponse::success(message: 'Tags removed successfully');
    }

    /**
     * Get conversation statistics.
     */
    public function statistics()
    {
        $stats = $this->conversationService->getStatistics();

        return ApiResponse::success(data: $stats);
    }
}

