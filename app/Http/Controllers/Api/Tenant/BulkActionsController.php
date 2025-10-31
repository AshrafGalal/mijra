<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Conversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BulkActionsController extends Controller
{
    /**
     * Bulk assign conversations.
     */
    public function bulkAssign(Request $request)
    {
        $validated = $request->validate([
            'conversation_ids' => 'required|array',
            'conversation_ids.*' => 'integer|exists:conversations,id',
            'user_id' => 'required|integer|exists:users,id',
        ]);

        DB::connection('tenant')->transaction(function () use ($validated) {
            foreach ($validated['conversation_ids'] as $conversationId) {
                $conversation = Conversation::find($conversationId);
                
                if ($conversation) {
                    $conversation->assignTo(
                        userId: $validated['user_id'],
                        assignedBy: auth()->id(),
                        type: 'manual'
                    );
                }
            }
        });

        Log::info('Bulk assign completed', [
            'count' => count($validated['conversation_ids']),
            'user_id' => $validated['user_id'],
        ]);

        return ApiResponse::success(
            message: count($validated['conversation_ids']) . ' conversations assigned successfully'
        );
    }

    /**
     * Bulk update status.
     */
    public function bulkUpdateStatus(Request $request)
    {
        $validated = $request->validate([
            'conversation_ids' => 'required|array',
            'conversation_ids.*' => 'integer|exists:conversations,id',
            'status' => 'required|string|in:new,open,pending,resolved,archived',
        ]);

        $updated = Conversation::whereIn('id', $validated['conversation_ids'])
            ->update([
                'status' => $validated['status'],
                'resolved_at' => $validated['status'] === 'resolved' ? now() : null,
            ]);

        Log::info('Bulk status update completed', [
            'count' => $updated,
            'status' => $validated['status'],
        ]);

        return ApiResponse::success(
            message: "{$updated} conversations updated successfully"
        );
    }

    /**
     * Bulk add tags.
     */
    public function bulkAddTags(Request $request)
    {
        $validated = $request->validate([
            'conversation_ids' => 'required|array',
            'conversation_ids.*' => 'integer|exists:conversations,id',
            'tag_ids' => 'required|array',
            'tag_ids.*' => 'integer|exists:conversation_tags,id',
        ]);

        foreach ($validated['conversation_ids'] as $conversationId) {
            $conversation = Conversation::find($conversationId);
            if ($conversation) {
                $conversation->tags()->syncWithoutDetaching($validated['tag_ids']);
            }
        }

        return ApiResponse::success(
            message: 'Tags added to ' . count($validated['conversation_ids']) . ' conversations'
        );
    }

    /**
     * Bulk remove tags.
     */
    public function bulkRemoveTags(Request $request)
    {
        $validated = $request->validate([
            'conversation_ids' => 'required|array',
            'conversation_ids.*' => 'integer|exists:conversations,id',
            'tag_ids' => 'required|array',
            'tag_ids.*' => 'integer|exists:conversation_tags,id',
        ]);

        foreach ($validated['conversation_ids'] as $conversationId) {
            $conversation = Conversation::find($conversationId);
            if ($conversation) {
                $conversation->tags()->detach($validated['tag_ids']);
            }
        }

        return ApiResponse::success(
            message: 'Tags removed from ' . count($validated['conversation_ids']) . ' conversations'
        );
    }

    /**
     * Bulk mark as read.
     */
    public function bulkMarkAsRead(Request $request)
    {
        $validated = $request->validate([
            'conversation_ids' => 'required|array',
            'conversation_ids.*' => 'integer|exists:conversations,id',
        ]);

        $updated = Conversation::whereIn('id', $validated['conversation_ids'])
            ->update(['unread_count' => 0]);

        return ApiResponse::success(
            message: "{$updated} conversations marked as read"
        );
    }

    /**
     * Bulk delete/archive conversations.
     */
    public function bulkArchive(Request $request)
    {
        $validated = $request->validate([
            'conversation_ids' => 'required|array',
            'conversation_ids.*' => 'integer|exists:conversations,id',
        ]);

        $updated = Conversation::whereIn('id', $validated['conversation_ids'])
            ->update(['status' => 'archived']);

        return ApiResponse::success(
            message: "{$updated} conversations archived successfully"
        );
    }

    /**
     * Export conversations to CSV.
     */
    public function export(Request $request)
    {
        $validated = $request->validate([
            'conversation_ids' => 'nullable|array',
            'conversation_ids.*' => 'integer|exists:conversations,id',
            'filters' => 'nullable|array',
        ]);

        // If specific IDs provided, use those; otherwise use filters
        if (!empty($validated['conversation_ids'])) {
            $conversations = Conversation::whereIn('id', $validated['conversation_ids'])
                ->with(['customer', 'assignedUser', 'latestMessage'])
                ->get();
        } else {
            // Apply filters similar to index endpoint
            $filters = $validated['filters'] ?? [];
            $conversationService = app(\App\Services\Tenant\ConversationService::class);
            $conversations = $conversationService->getQuery($filters)
                ->with(['customer', 'assignedUser', 'latestMessage'])
                ->get();
        }

        // Generate CSV data
        $csvData = $this->generateCsvData($conversations);

        return ApiResponse::success(data: [
            'csv' => $csvData,
            'count' => $conversations->count(),
        ]);
    }

    /**
     * Generate CSV data from conversations.
     */
    protected function generateCsvData($conversations): array
    {
        $data = [];
        
        // Header row
        $data[] = [
            'ID',
            'Customer Name',
            'Customer Phone',
            'Customer Email',
            'Platform',
            'Status',
            'Assigned To',
            'Message Count',
            'Unread Count',
            'Last Message',
            'Created At',
        ];

        // Data rows
        foreach ($conversations as $conversation) {
            $data[] = [
                $conversation->id,
                $conversation->customer->name,
                $conversation->customer->phone,
                $conversation->customer->email,
                $conversation->platform,
                $conversation->status,
                $conversation->assignedUser?->name ?? 'Unassigned',
                $conversation->message_count,
                $conversation->unread_count,
                $conversation->latestMessage->first()?->content ?? '',
                $conversation->created_at->toDateTimeString(),
            ];
        }

        return $data;
    }
}

