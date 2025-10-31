<?php

namespace App\Services\Tenant;

use App\Models\Tenant\Conversation;
use App\Models\Tenant\SlaPolicy;
use Illuminate\Support\Facades\Log;

class SlaTrackingService
{
    /**
     * Apply SLA policy to a conversation.
     */
    public function applySla(Conversation $conversation): void
    {
        // Find applicable SLA policy
        $slaPolicy = $this->findApplicablePolicy($conversation);

        if (!$slaPolicy) {
            return;
        }

        // Calculate deadlines
        $deadlines = $slaPolicy->calculateDeadlines($conversation->created_at);

        // Update conversation with SLA info
        $conversation->update([
            'sla_policy_id' => $slaPolicy->id,
            'sla_first_response_due_at' => $deadlines['first_response_due_at'],
            'sla_resolution_due_at' => $deadlines['resolution_due_at'],
        ]);

        Log::info('SLA policy applied to conversation', [
            'conversation_id' => $conversation->id,
            'sla_policy_id' => $slaPolicy->id,
            'first_response_due' => $deadlines['first_response_due_at'],
            'resolution_due' => $deadlines['resolution_due_at'],
        ]);
    }

    /**
     * Find applicable SLA policy for conversation.
     */
    protected function findApplicablePolicy(Conversation $conversation): ?SlaPolicy
    {
        $policies = SlaPolicy::where('is_active', true)
            ->orderByDesc('is_default')
            ->get();

        foreach ($policies as $policy) {
            if ($policy->appliesTo($conversation)) {
                return $policy;
            }
        }

        return null;
    }

    /**
     * Check and mark SLA breaches.
     */
    public function checkBreaches(Conversation $conversation): void
    {
        $now = now();

        // Check first response SLA
        if ($conversation->sla_first_response_due_at && 
            !$conversation->first_response_at &&
            $now->isAfter($conversation->sla_first_response_due_at)) {
            
            $conversation->update(['sla_first_response_breached' => true]);
            
            Log::warning('First response SLA breached', [
                'conversation_id' => $conversation->id,
                'due_at' => $conversation->sla_first_response_due_at,
            ]);
        }

        // Check resolution SLA
        if ($conversation->sla_resolution_due_at &&
            !$conversation->resolved_at &&
            $now->isAfter($conversation->sla_resolution_due_at)) {
            
            $conversation->update(['sla_resolution_breached' => true]);
            
            Log::warning('Resolution SLA breached', [
                'conversation_id' => $conversation->id,
                'due_at' => $conversation->sla_resolution_due_at,
            ]);
        }
    }

    /**
     * Get SLA compliance report.
     */
    public function getComplianceReport($dateFrom, $dateTo): array
    {
        $conversations = Conversation::whereBetween('created_at', [$dateFrom, $dateTo])
            ->whereNotNull('sla_policy_id')
            ->get();

        $total = $conversations->count();
        $firstResponseBreached = $conversations->where('sla_first_response_breached', true)->count();
        $resolutionBreached = $conversations->where('sla_resolution_breached', true)->count();

        return [
            'total_with_sla' => $total,
            'first_response_met' => $total - $firstResponseBreached,
            'first_response_breached' => $firstResponseBreached,
            'first_response_compliance_rate' => $total > 0 ? round((($total - $firstResponseBreached) / $total) * 100, 2) : 0,
            'resolution_met' => $total - $resolutionBreached,
            'resolution_breached' => $resolutionBreached,
            'resolution_compliance_rate' => $total > 0 ? round((($total - $resolutionBreached) / $total) * 100, 2) : 0,
        ];
    }
}

