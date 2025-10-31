<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Customer;
use App\Models\Tenant\Conversation;
use App\Models\Tenant\Opportunity;
use App\Models\Tenant\Task;
use Illuminate\Support\Facades\DB;

class CustomerProfileController extends Controller
{
    /**
     * Get complete customer 360 view.
     */
    public function show($id)
    {
        $customer = Customer::with(['groups', 'socialAccounts'])->findOrFail($id);

        $profile = [
            'customer' => $customer,
            'summary' => $this->getSummary($customer),
            'conversations' => $this->getConversations($customer),
            'opportunities' => $this->getOpportunities($customer),
            'tasks' => $this->getTasks($customer),
            'feedback' => $this->getFeedback($customer),
            'campaigns' => $this->getCampaigns($customer),
            'timeline' => $this->getTimeline($customer),
            'statistics' => $this->getStatistics($customer),
        ];

        return ApiResponse::success(data: $profile);
    }

    /**
     * Get customer summary.
     */
    protected function getSummary(Customer $customer): array
    {
        return [
            'total_conversations' => Conversation::where('customer_id', $customer->id)->count(),
            'active_conversations' => Conversation::where('customer_id', $customer->id)
                ->whereIn('status', ['new', 'open'])
                ->count(),
            'total_messages' => DB::connection('tenant')->table('messages')
                ->join('conversations', 'messages.conversation_id', '=', 'conversations.id')
                ->where('conversations.customer_id', $customer->id)
                ->count(),
            'total_opportunities' => Opportunity::where('customer_id', $customer->id)->count(),
            'active_opportunities' => Opportunity::where('customer_id', $customer->id)
                ->where('status', \App\Enum\OpportunityStatusEnum::ACTIVE->value)
                ->count(),
            'total_tasks' => Task::where('customer_id', $customer->id)->count(),
            'pending_tasks' => Task::where('customer_id', $customer->id)
                ->where('status', \App\Enum\TaskStatusEnum::PENDING->value)
                ->count(),
        ];
    }

    /**
     * Get customer conversations.
     */
    protected function getConversations(Customer $customer): \Illuminate\Database\Eloquent\Collection
    {
        return Conversation::where('customer_id', $customer->id)
            ->with(['latestMessage', 'assignedUser', 'tags'])
            ->orderByDesc('last_message_at')
            ->limit(10)
            ->get();
    }

    /**
     * Get customer opportunities.
     */
    protected function getOpportunities(Customer $customer): \Illuminate\Database\Eloquent\Collection
    {
        return Opportunity::where('customer_id', $customer->id)
            ->with(['user', 'workflow', 'stage'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();
    }

    /**
     * Get customer tasks.
     */
    protected function getTasks(Customer $customer): \Illuminate\Database\Eloquent\Collection
    {
        return Task::where('customer_id', $customer->id)
            ->with('user')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();
    }

    /**
     * Get customer feedback.
     */
    protected function getFeedback(Customer $customer): \Illuminate\Database\Eloquent\Collection
    {
        return $customer->feedback()
            ->with('feedbackCategory')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();
    }

    /**
     * Get campaigns received by customer.
     */
    protected function getCampaigns(Customer $customer): \Illuminate\Database\Eloquent\Collection
    {
        return DB::connection('tenant')->table('campaign_messages')
            ->join('campaigns', 'campaign_messages.campaign_id', '=', 'campaigns.id')
            ->where('campaign_messages.customer_id', $customer->id)
            ->select('campaigns.*', 'campaign_messages.status as message_status', 'campaign_messages.sent_at')
            ->orderByDesc('campaign_messages.sent_at')
            ->limit(10)
            ->get();
    }

    /**
     * Get customer activity timeline.
     */
    protected function getTimeline(Customer $customer): array
    {
        $timeline = [];

        // Get recent conversations
        $conversations = Conversation::where('customer_id', $customer->id)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        foreach ($conversations as $conv) {
            $timeline[] = [
                'type' => 'conversation',
                'platform' => $conv->platform,
                'status' => $conv->status,
                'message_count' => $conv->message_count,
                'timestamp' => $conv->created_at,
            ];
        }

        // Get recent opportunities
        $opportunities = Opportunity::where('customer_id', $customer->id)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        foreach ($opportunities as $opp) {
            $timeline[] = [
                'type' => 'opportunity',
                'status' => $opp->status,
                'stage' => $opp->stage?->name,
                'timestamp' => $opp->created_at,
            ];
        }

        // Get recent tasks
        $tasks = Task::where('customer_id', $customer->id)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        foreach ($tasks as $task) {
            $timeline[] = [
                'type' => 'task',
                'title' => $task->title,
                'status' => $task->status,
                'timestamp' => $task->created_at,
            ];
        }

        // Sort by timestamp descending
        usort($timeline, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);

        return array_slice($timeline, 0, 20);
    }

    /**
     * Get customer statistics.
     */
    protected function getStatistics(Customer $customer): array
    {
        $messages = DB::connection('tenant')->table('messages')
            ->join('conversations', 'messages.conversation_id', '=', 'conversations.id')
            ->where('conversations.customer_id', $customer->id)
            ->select('messages.*')
            ->get();

        $conversations = Conversation::where('customer_id', $customer->id)->get();

        return [
            'first_contact' => $customer->created_at,
            'last_contact' => $conversations->max('last_message_at'),
            'total_conversations' => $conversations->count(),
            'conversations_by_platform' => $conversations->groupBy('platform')->map->count(),
            'total_messages' => $messages->count(),
            'messages_sent' => $messages->where('direction', 'outbound')->count(),
            'messages_received' => $messages->where('direction', 'inbound')->count(),
            'avg_response_time_minutes' => $this->calculateAvgResponseTime($customer->id),
            'customer_lifetime_days' => $customer->created_at->diffInDays(now()),
        ];
    }

    /**
     * Calculate average response time for customer.
     */
    protected function calculateAvgResponseTime(int $customerId): float
    {
        $conversations = Conversation::where('customer_id', $customerId)
            ->whereNotNull('first_response_at')
            ->get();

        if ($conversations->isEmpty()) {
            return 0;
        }

        $avgMinutes = $conversations->avg(function ($conv) {
            return $conv->created_at->diffInMinutes($conv->first_response_at);
        });

        return round($avgMinutes, 2);
    }

    /**
     * Get customer engagement score.
     */
    public function engagementScore($id)
    {
        $customer = Customer::findOrFail($id);

        $metrics = [
            'message_count' => DB::connection('tenant')->table('messages')
                ->join('conversations', 'messages.conversation_id', '=', 'conversations.id')
                ->where('conversations.customer_id', $customer->id)
                ->where('messages.direction', 'inbound')
                ->count(),
            'conversation_count' => Conversation::where('customer_id', $customer->id)->count(),
            'response_rate' => $this->calculateResponseRate($customer->id),
            'days_since_last_contact' => $this->getDaysSinceLastContact($customer->id),
            'campaigns_read' => DB::connection('tenant')->table('campaign_messages')
                ->where('customer_id', $customer->id)
                ->where('status', 'read')
                ->count(),
            'campaigns_received' => DB::connection('tenant')->table('campaign_messages')
                ->where('customer_id', $customer->id)
                ->count(),
        ];

        // Calculate engagement score (0-100)
        $score = 0;
        $score += min($metrics['message_count'] * 2, 30); // Max 30 points
        $score += min($metrics['conversation_count'] * 5, 20); // Max 20 points
        $score += $metrics['response_rate'] * 25; // Max 25 points
        $score += max(25 - $metrics['days_since_last_contact'], 0); // Max 25 points (recent = higher)

        return ApiResponse::success(data: [
            'score' => min(round($score), 100),
            'metrics' => $metrics,
            'level' => $this->getEngagementLevel($score),
        ]);
    }

    /**
     * Calculate customer response rate.
     */
    protected function calculateResponseRate(int $customerId): float
    {
        $outboundCount = DB::connection('tenant')->table('messages')
            ->join('conversations', 'messages.conversation_id', '=', 'conversations.id')
            ->where('conversations.customer_id', $customerId)
            ->where('messages.direction', 'outbound')
            ->count();

        $inboundCount = DB::connection('tenant')->table('messages')
            ->join('conversations', 'messages.conversation_id', '=', 'conversations.id')
            ->where('conversations.customer_id', $customerId)
            ->where('messages.direction', 'inbound')
            ->count();

        if ($outboundCount === 0) {
            return 0;
        }

        return round($inboundCount / $outboundCount, 2);
    }

    /**
     * Get days since last contact.
     */
    protected function getDaysSinceLastContact(int $customerId): int
    {
        $lastMessage = Conversation::where('customer_id', $customerId)
            ->max('last_message_at');

        if (!$lastMessage) {
            return 9999;
        }

        return now()->diffInDays($lastMessage);
    }

    /**
     * Get engagement level label.
     */
    protected function getEngagementLevel(float $score): string
    {
        return match (true) {
            $score >= 80 => 'Very High',
            $score >= 60 => 'High',
            $score >= 40 => 'Medium',
            $score >= 20 => 'Low',
            default => 'Very Low',
        };
    }
}

