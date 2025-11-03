<?php

namespace App\Http\Controllers\Api\Tenant\Conversation;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\Conversation\ConversationMessageResource;
use App\Http\Resources\Tenant\Conversation\ConversationResource;
use App\Services\Tenant\ConversationService;
use App\Services\Tenant\MessageService;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function __construct(protected readonly ConversationService $conversationService, protected readonly MessageService $messageService) {}

    public function index(Request $request)
    {
        $filters = $request->all();
        $limit = $request->input('limit', 15);
        $withRelation = ['latestMessage'];
        $conversations = $this->conversationService->paginateConversations(filters: $filters, withRelations: $withRelation, limit: $limit);

        return ConversationResource::collection($conversations);
    }

    public function messagesByConversationId($id, Request $request)
    {
        $limit = $request->input('limit', 50);
        $messages = $this->messageService->getMessagesbyConversationId(conversation_id: $id, limit: $limit);

        return ConversationMessageResource::collection($messages);
    }
}
