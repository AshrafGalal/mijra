<?php

namespace App\Services\Tenant;

use App\Enum\AssignmentTypeEnum;
use App\Models\Tenant\Conversation;
use App\Models\Tenant\User;
use App\Models\Tenant\WorkHour;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AutoAssignmentService
{
    public function __construct(
        protected ConversationService $conversationService
    ) {
    }

    /**
     * Auto-assign conversation based on configured strategy.
     */
    public function autoAssign(Conversation $conversation): ?User
    {
        // Get assignment settings
        $settings = $this->getAssignmentSettings();
        $strategy = $settings['strategy'] ?? 'round_robin';

        $assignedUser = match ($strategy) {
            'round_robin' => $this->assignRoundRobin($conversation),
            'load_based' => $this->assignLoadBased($conversation),
            'availability' => $this->assignByAvailability($conversation),
            default => null,
        };

        if ($assignedUser) {
            $this->conversationService->assign(
                conversationId: $conversation->id,
                userId: $assignedUser->id,
                assignedBy: null,
                type: "auto_{$strategy}"
            );

            Log::info('Conversation auto-assigned', [
                'conversation_id' => $conversation->id,
                'user_id' => $assignedUser->id,
                'strategy' => $strategy,
            ]);
        }

        return $assignedUser;
    }

    /**
     * Round-robin assignment.
     */
    protected function assignRoundRobin(Conversation $conversation): ?User
    {
        $users = $this->getAvailableUsers();

        if ($users->isEmpty()) {
            return null;
        }

        // Get the last assigned user ID from cache
        $cacheKey = 'auto_assignment:round_robin:last_user_id';
        $lastUserId = Cache::get($cacheKey);

        // Find next user in rotation
        $lastIndex = $users->search(fn($user) => $user->id == $lastUserId);
        $nextIndex = ($lastIndex === false) ? 0 : ($lastIndex + 1) % $users->count();
        
        $nextUser = $users->get($nextIndex);

        // Store for next rotation
        Cache::put($cacheKey, $nextUser->id, now()->addDay());

        return $nextUser;
    }

    /**
     * Load-based assignment (assign to user with least active conversations).
     */
    protected function assignLoadBased(Conversation $conversation): ?User
    {
        $users = $this->getAvailableUsers();

        if ($users->isEmpty()) {
            return null;
        }

        // Count active conversations per user
        $userLoads = $users->map(function ($user) {
            $activeCount = Conversation::where('assigned_to', $user->id)
                ->whereIn('status', ['new', 'open'])
                ->count();

            return [
                'user' => $user,
                'load' => $activeCount,
            ];
        });

        // Assign to user with lowest load
        $leastLoaded = $userLoads->sortBy('load')->first();

        return $leastLoaded['user'] ?? null;
    }

    /**
     * Availability-based assignment (assign to currently available users).
     */
    protected function assignByAvailability(Conversation $conversation): ?User
    {
        $availableUsers = $this->getUsersByWorkHours();

        if ($availableUsers->isEmpty()) {
            // Fallback to load-based if no one is available
            return $this->assignLoadBased($conversation);
        }

        // Among available users, use load-based selection
        $userLoads = $availableUsers->map(function ($user) {
            $activeCount = Conversation::where('assigned_to', $user->id)
                ->whereIn('status', ['new', 'open'])
                ->count();

            return [
                'user' => $user,
                'load' => $activeCount,
            ];
        });

        $leastLoaded = $userLoads->sortBy('load')->first();

        return $leastLoaded['user'] ?? null;
    }

    /**
     * Get available users (active and have permissions).
     */
    protected function getAvailableUsers(): Collection
    {
        return User::where('is_active', true)
            ->whereHas('roles', function ($query) {
                $query->where('name', '!=', 'admin'); // Exclude admins
            })
            ->get();
    }

    /**
     * Get users based on current work hours.
     */
    protected function getUsersByWorkHours(): Collection
    {
        $now = Carbon::now();
        $dayOfWeek = $now->dayOfWeek; // 0 = Sunday, 6 = Saturday
        $currentTime = $now->format('H:i:00');

        // Get work hours for today
        $workHour = WorkHour::where('day_of_week', $dayOfWeek)
            ->where('is_open', true)
            ->where(function ($query) use ($currentTime) {
                $query->where('is_24_hours', true)
                    ->orWhere(function ($q) use ($currentTime) {
                        $q->where('open_time', '<=', $currentTime)
                            ->where('close_time', '>=', $currentTime);
                    });
            })
            ->first();

        if (!$workHour) {
            // Outside work hours
            return collect();
        }

        // Return all active users if within work hours
        return $this->getAvailableUsers();
    }

    /**
     * Get assignment settings.
     */
    protected function getAssignmentSettings(): array
    {
        // This should be stored in settings table
        // For now, return default
        return [
            'strategy' => 'round_robin', // round_robin, load_based, availability
            'enabled' => true,
        ];
    }

    /**
     * Check if auto-assignment is enabled.
     */
    public function isEnabled(): bool
    {
        $settings = $this->getAssignmentSettings();
        return $settings['enabled'] ?? false;
    }
}

