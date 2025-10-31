<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Campaign;
use App\Services\Tenant\CampaignExecutionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CampaignController extends Controller
{
    public function __construct(
        protected CampaignExecutionService $campaignExecutionService
    ) {
    }

    /**
     * Get list of campaigns.
     */
    public function index(Request $request)
    {
        $campaigns = Campaign::with('template')
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->channel, fn($q, $channel) => $q->where('channel', $channel))
            ->orderByDesc('created_at')
            ->paginate($request->input('limit', 15));

        return ApiResponse::success(data: $campaigns);
    }

    /**
     * Create a new campaign.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'channel' => 'required|string|in:whatsapp,facebook,instagram,email,sms',
            'type' => 'nullable|string',
            'template_id' => 'nullable|exists:templates,id',
            'target' => 'required|integer',
            'scheduled_at' => 'nullable|date|after:now',
            'customer_ids' => 'nullable|array',
            'customer_ids.*' => 'exists:customers,id',
            'metadata' => 'nullable|array',
        ]);

        $campaign = DB::connection('tenant')->transaction(function () use ($validated) {
            $campaign = Campaign::create([
                'title' => $validated['title'],
                'content' => $validated['content'],
                'channel' => $validated['channel'],
                'type' => $validated['type'] ?? null,
                'template_id' => $validated['template_id'] ?? null,
                'target' => $validated['target'],
                'scheduled_at' => $validated['scheduled_at'] ?? null,
                'status' => \App\Enum\CampaignStatusEnum::DRAFT->value,
            ]);

            // Attach specific customers if provided
            if (!empty($validated['customer_ids'])) {
                $campaign->customers()->attach($validated['customer_ids']);
            }

            return $campaign;
        });

        return ApiResponse::success(
            message: 'Campaign created successfully',
            data: $campaign
        );
    }

    /**
     * Get campaign details.
     */
    public function show($id)
    {
        $campaign = Campaign::with(['template', 'customers'])->findOrFail($id);
        
        return ApiResponse::success(data: $campaign);
    }

    /**
     * Start a campaign.
     */
    public function start($id)
    {
        $campaign = Campaign::findOrFail($id);

        if ($campaign->isRunning() || $campaign->isCompleted()) {
            return ApiResponse::badRequest('Campaign is already running or completed');
        }

        $this->campaignExecutionService->execute($campaign);

        return ApiResponse::success(message: 'Campaign started successfully');
    }

    /**
     * Pause a campaign.
     */
    public function pause($id)
    {
        $campaign = Campaign::findOrFail($id);

        if (!$campaign->isRunning()) {
            return ApiResponse::badRequest('Campaign is not running');
        }

        $campaign->pause();

        return ApiResponse::success(message: 'Campaign paused successfully');
    }

    /**
     * Resume a campaign.
     */
    public function resume($id)
    {
        $campaign = Campaign::findOrFail($id);

        if ($campaign->status !== \App\Enum\CampaignStatusEnum::PAUSED->value) {
            return ApiResponse::badRequest('Campaign is not paused');
        }

        $campaign->resume();
        
        // Continue execution
        dispatch(new \App\Jobs\ExecuteCampaignJob($campaign));

        return ApiResponse::success(message: 'Campaign resumed successfully');
    }

    /**
     * Get campaign analytics.
     */
    public function analytics($id)
    {
        $campaign = Campaign::findOrFail($id);
        $analytics = $this->campaignExecutionService->getAnalytics($campaign);

        return ApiResponse::success(data: $analytics);
    }

    /**
     * Get campaign statistics.
     */
    public function statistics()
    {
        $stats = DB::connection('tenant')->table('campaigns')
            ->selectRaw('
                status,
                COUNT(*) as count,
                channel,
                SUM(CASE WHEN completed_at IS NOT NULL THEN 1 ELSE 0 END) as completed_count
            ')
            ->groupBy('status', 'channel')
            ->get();

        return ApiResponse::success(data: [
            'total' => Campaign::count(),
            'active' => Campaign::active()->count(),
            'scheduled' => Campaign::scheduled()->count(),
            'completed' => Campaign::completed()->count(),
            'by_channel' => $stats->groupBy('channel'),
        ]);
    }

    /**
     * Delete a campaign.
     */
    public function destroy($id)
    {
        $campaign = Campaign::findOrFail($id);

        if ($campaign->isRunning()) {
            return ApiResponse::badRequest('Cannot delete a running campaign. Please pause it first.');
        }

        $campaign->delete();

        return ApiResponse::success(message: 'Campaign deleted successfully');
    }
}

