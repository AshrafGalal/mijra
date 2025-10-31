<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Campaign;
use App\Models\Tenant\Conversation;
use App\Models\Tenant\Customer;
use App\Models\Tenant\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    /**
     * Get overall dashboard analytics.
     */
    public function dashboard(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->subDays(30));
        $dateTo = $request->input('date_to', now());

        $analytics = [
            'overview' => $this->getOverviewMetrics($dateFrom, $dateTo),
            'conversations' => $this->getConversationMetrics($dateFrom, $dateTo),
            'messages' => $this->getMessageMetrics($dateFrom, $dateTo),
            'campaigns' => $this->getCampaignMetrics($dateFrom, $dateTo),
            'platforms' => $this->getPlatformMetrics($dateFrom, $dateTo),
            'agents' => $this->getAgentMetrics($dateFrom, $dateTo),
        ];

        return ApiResponse::success(data: $analytics);
    }

    /**
     * Get overview metrics.
     */
    protected function getOverviewMetrics($dateFrom, $dateTo): array
    {
        return [
            'total_conversations' => Conversation::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'active_conversations' => Conversation::whereIn('status', ['new', 'open'])->count(),
            'total_messages' => Message::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'total_customers' => Customer::count(),
            'new_customers' => Customer::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
        ];
    }

    /**
     * Get conversation metrics.
     */
    protected function getConversationMetrics($dateFrom, $dateTo): array
    {
        $conversations = Conversation::whereBetween('created_at', [$dateFrom, $dateTo])->get();

        // Calculate average response time
        $avgFirstResponseTime = Conversation::whereNotNull('first_response_at')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->get()
            ->avg(function ($conv) {
                return $conv->created_at->diffInMinutes($conv->first_response_at);
            });

        // Calculate average resolution time
        $avgResolutionTime = Conversation::whereNotNull('resolved_at')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->get()
            ->avg(function ($conv) {
                return $conv->created_at->diffInHours($conv->resolved_at);
            });

        return [
            'total' => $conversations->count(),
            'by_status' => $conversations->groupBy('status')->map->count(),
            'by_platform' => $conversations->groupBy('platform')->map->count(),
            'unassigned' => $conversations->whereNull('assigned_to')->count(),
            'with_unread' => $conversations->where('unread_count', '>', 0)->count(),
            'avg_first_response_time_minutes' => round($avgFirstResponseTime ?? 0, 2),
            'avg_resolution_time_hours' => round($avgResolutionTime ?? 0, 2),
        ];
    }

    /**
     * Get message metrics.
     */
    protected function getMessageMetrics($dateFrom, $dateTo): array
    {
        $messages = Message::whereBetween('created_at', [$dateFrom, $dateTo])->get();

        return [
            'total' => $messages->count(),
            'inbound' => $messages->where('direction', 'inbound')->count(),
            'outbound' => $messages->where('direction', 'outbound')->count(),
            'by_type' => $messages->groupBy('type')->map->count(),
            'by_status' => $messages->where('direction', 'outbound')->groupBy('status')->map->count(),
            'with_attachments' => Message::has('attachments')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->count(),
        ];
    }

    /**
     * Get campaign metrics.
     */
    protected function getCampaignMetrics($dateFrom, $dateTo): array
    {
        $campaigns = Campaign::whereBetween('created_at', [$dateFrom, $dateTo])->get();

        return [
            'total' => $campaigns->count(),
            'active' => $campaigns->where('status', \App\Enum\CampaignStatusEnum::ACTIVE->value)->count(),
            'completed' => $campaigns->where('status', \App\Enum\CampaignStatusEnum::COMPLETED->value)->count(),
            'scheduled' => $campaigns->where('status', \App\Enum\CampaignStatusEnum::SCHEDULED->value)->count(),
            'by_channel' => $campaigns->groupBy('channel')->map->count(),
        ];
    }

    /**
     * Get platform metrics.
     */
    protected function getPlatformMetrics($dateFrom, $dateTo): array
    {
        $platforms = ['whatsapp', 'facebook', 'instagram'];
        $metrics = [];

        foreach ($platforms as $platform) {
            $conversations = Conversation::where('platform', $platform)
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->get();

            $messages = Message::whereHas('conversation', function ($q) use ($platform) {
                $q->where('platform', $platform);
            })
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->get();

            $metrics[$platform] = [
                'conversations' => $conversations->count(),
                'messages' => $messages->count(),
                'inbound' => $messages->where('direction', 'inbound')->count(),
                'outbound' => $messages->where('direction', 'outbound')->count(),
                'avg_response_time_minutes' => $this->calculateAvgResponseTime($platform, $dateFrom, $dateTo),
            ];
        }

        return $metrics;
    }

    /**
     * Get agent performance metrics.
     */
    protected function getAgentMetrics($dateFrom, $dateTo): array
    {
        $agents = DB::connection('tenant')->table('users')
            ->join('conversations', 'users.id', '=', 'conversations.assigned_to')
            ->whereBetween('conversations.created_at', [$dateFrom, $dateTo])
            ->select('users.id', 'users.name')
            ->selectRaw('COUNT(conversations.id) as conversation_count')
            ->selectRaw('SUM(conversations.message_count) as total_messages')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, conversations.created_at, conversations.first_response_at)) as avg_response_time')
            ->groupBy('users.id', 'users.name')
            ->get();

        return $agents->map(function ($agent) {
            return [
                'user_id' => $agent->id,
                'name' => $agent->name,
                'conversations_handled' => $agent->conversation_count,
                'messages_sent' => $agent->total_messages,
                'avg_response_time_minutes' => round($agent->avg_response_time ?? 0, 2),
            ];
        })->toArray();
    }

    /**
     * Calculate average response time for a platform.
     */
    protected function calculateAvgResponseTime(string $platform, $dateFrom, $dateTo): float
    {
        $avgTime = Conversation::where('platform', $platform)
            ->whereNotNull('first_response_at')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->get()
            ->avg(function ($conv) {
                return $conv->created_at->diffInMinutes($conv->first_response_at);
            });

        return round($avgTime ?? 0, 2);
    }

    /**
     * Get time series data for charts.
     */
    public function timeSeries(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->subDays(30));
        $dateTo = $request->input('date_to', now());
        $metric = $request->input('metric', 'conversations'); // conversations, messages, customers
        $groupBy = $request->input('group_by', 'day'); // hour, day, week, month

        $data = $this->getTimeSeriesData($metric, $dateFrom, $dateTo, $groupBy);

        return ApiResponse::success(data: $data);
    }

    /**
     * Get time series data.
     */
    protected function getTimeSeriesData(string $metric, $dateFrom, $dateTo, string $groupBy): array
    {
        $dateFormat = match ($groupBy) {
            'hour' => '%Y-%m-%d %H:00:00',
            'day' => '%Y-%m-%d',
            'week' => '%Y-%U',
            'month' => '%Y-%m',
            default => '%Y-%m-%d',
        };

        $table = match ($metric) {
            'conversations' => 'conversations',
            'messages' => 'messages',
            'customers' => 'customers',
            default => 'conversations',
        };

        $data = DB::connection('tenant')->table($table)
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw("DATE_FORMAT(created_at, '{$dateFormat}') as period")
            ->selectRaw('COUNT(*) as count')
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        return [
            'metric' => $metric,
            'group_by' => $groupBy,
            'data' => $data->map(function ($item) {
                return [
                    'period' => $item->period,
                    'count' => $item->count,
                ];
            })->toArray(),
        ];
    }

    /**
     * Get customer lifecycle analytics.
     */
    public function customerLifecycle(Request $request)
    {
        $customerId = $request->input('customer_id');

        if (!$customerId) {
            return ApiResponse::badRequest('customer_id is required');
        }

        $customer = Customer::findOrFail($customerId);

        // Get all conversations
        $conversations = Conversation::where('customer_id', $customerId)
            ->with('latestMessage')
            ->orderBy('created_at')
            ->get();

        // Get all messages
        $totalMessages = Message::whereHas('conversation', function ($q) use ($customerId) {
            $q->where('customer_id', $customerId);
        })->count();

        // Get campaign participation
        $campaignParticipation = DB::connection('tenant')->table('campaign_messages')
            ->where('customer_id', $customerId)
            ->selectRaw('COUNT(*) as total_campaigns')
            ->selectRaw('SUM(CASE WHEN status = "read" THEN 1 ELSE 0 END) as read_count')
            ->first();

        return ApiResponse::success(data: [
            'customer' => $customer,
            'total_conversations' => $conversations->count(),
            'conversations_by_platform' => $conversations->groupBy('platform')->map->count(),
            'total_messages' => $totalMessages,
            'first_interaction' => $conversations->first()?->created_at,
            'last_interaction' => $conversations->last()?->last_message_at,
            'campaigns_received' => $campaignParticipation->total_campaigns ?? 0,
            'campaigns_read' => $campaignParticipation->read_count ?? 0,
        ]);
    }
}

