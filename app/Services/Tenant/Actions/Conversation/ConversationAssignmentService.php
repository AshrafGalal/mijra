<?php

namespace App\Services\Tenant\Actions\Conversation;

use App\Enum\ConversationStatusEnum;
use App\Enum\RotationTypeEnum;
use App\Exceptions\NoAssignableUsersException;
use App\Models\Settings\Tenant\AssignmentSetting;
use App\Models\Tenant\Conversation;
use App\Models\Tenant\User;

class ConversationAssignmentService
{
    /**
     * @throws NoAssignableUsersException
     */
    public function handle(Conversation $conversation): ?Conversation
    {
        //check if conversation is already assigned
        if ($conversation->assigned_to) {
            return $conversation;
        }
        $settings = app(AssignmentSetting::class);
        $settings->rotation_type = RotationTypeEnum::SEQUENTIAL->value;
        $assignToUser = null;
        $users = User::query()
            ->withCount(['conversations' => fn($query) => $query->where('status', ConversationStatusEnum::OPEN->value)])
            ->select('id')
            ->get();

        // 🔀 Random balanced mode
        if ($settings->rotation_type == RotationTypeEnum::RANDOM->value) {
            $assignToUser = $this->assignBalancedRandom(users: $users);
        }

        // 🔁 Sequential mode
        if ($settings->rotation_type == RotationTypeEnum::SEQUENTIAL->value) {
            $assignToUser = $this->assignSequentially($users);
        }
        if (!$assignToUser) {
            throw new NoAssignableUsersException('No assignable users found.');
        }

        $conversation->update(['assigned_to' => $assignToUser->id]);

        return $conversation;
    }

    /**
     * Choose random user but prefer those with fewest open conversations.
     */
    protected function assignBalancedRandom($users): ?User
    {
        if ($users->isEmpty()) {
            throw new NoAssignableUsersException;
        }
        // Find the minimum number of open conversations
        $minLoad = $users->min('conversations_count');

        // Get all users who have that minimum load
        $leastLoaded = $users->where('conversations_count', $minLoad);

        // Randomly pick one among the least loaded users
        return $leastLoaded->random();
    }

    /**
     * Classic sequential round-robin
     */
    protected function assignSequentially($users): ?User
    {
        if ($users->isEmpty()) {
            throw new NoAssignableUsersException;
        }

        $users = $users->sortBy('id')->values();
        $count = $users->count();

        $cacheKey = 'conversation:last_assigned_index';

        // Increment atomically
        $index = cache()->increment($cacheKey, 1);

        // Handle cache initialization (increment starts at 1)
        if ($index === 1) {
            $index = 0;
        }

        // Wrap around
        $nextIndex = ($index - 1) % $count;

        // ✅ Safety check
        $user = $users->get($nextIndex);

        if (!$user) {
            // If somehow index is invalid (user deleted / list changed)
            cache()->put($cacheKey, 0);
            $user = $users->first();
        }

        return $user;
    }
}
