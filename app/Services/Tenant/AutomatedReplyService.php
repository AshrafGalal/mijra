<?php

namespace App\Services\Tenant;

use App\Models\Tenant\AutomatedReply;
use App\Models\Tenant\Conversation;
use App\Models\Tenant\Message;
use Illuminate\Support\Facades\Log;

class AutomatedReplyService
{
    public function __construct(
        protected MessageService $messageService
    ) {
    }

    /**
     * Process incoming message and send automated reply if matched.
     */
    public function processMessage(Message $message, Conversation $conversation): ?Message
    {
        // Only process inbound messages
        if (!$message->isInbound()) {
            return null;
        }

        // Check for greeting (first message in conversation)
        if ($conversation->message_count === 1) {
            return $this->sendGreetingReply($conversation);
        }

        // Check for keyword matches
        $keywordReply = $this->findKeywordMatch($message, $conversation);
        if ($keywordReply) {
            return $this->sendAutomatedReply($keywordReply, $conversation);
        }

        // Check if outside work hours
        if ($this->isOutsideWorkHours()) {
            return $this->sendAwayReply($conversation);
        }

        return null;
    }

    /**
     * Send greeting reply for new conversations.
     */
    protected function sendGreetingReply(Conversation $conversation): ?Message
    {
        $greetingRule = AutomatedReply::active()
            ->where('trigger_type', 'greeting')
            ->where(function ($query) use ($conversation) {
                $query->whereNull('conditions')
                    ->orWhereJsonContains('conditions->platforms', $conversation->platform);
            })
            ->first();

        if (!$greetingRule) {
            return null;
        }

        return $this->sendAutomatedReply($greetingRule, $conversation);
    }

    /**
     * Find keyword match.
     */
    protected function findKeywordMatch(Message $message, Conversation $conversation): ?AutomatedReply
    {
        $rules = AutomatedReply::active()
            ->where('trigger_type', 'keyword')
            ->orderByDesc('priority')
            ->get();

        foreach ($rules as $rule) {
            if ($rule->matchesKeyword($message->content) && 
                $rule->conditionsMet(['platform' => $conversation->platform])) {
                return $rule;
            }
        }

        return null;
    }

    /**
     * Send away reply (outside work hours).
     */
    protected function sendAwayReply(Conversation $conversation): ?Message
    {
        // Check if away reply already sent recently
        $recentAwayReply = $conversation->messages()
            ->where('sender_type', 'system')
            ->where('metadata->is_away_reply', true)
            ->where('created_at', '>', now()->subHours(4))
            ->exists();

        if ($recentAwayReply) {
            return null; // Don't spam away messages
        }

        $awayRule = AutomatedReply::active()
            ->where('trigger_type', 'away')
            ->first();

        if (!$awayRule) {
            return null;
        }

        return $this->sendAutomatedReply($awayRule, $conversation, ['is_away_reply' => true]);
    }

    /**
     * Send automated reply.
     */
    protected function sendAutomatedReply(
        AutomatedReply $rule,
        Conversation $conversation,
        array $extraMetadata = []
    ): ?Message {
        try {
            // Prepare content with variables
            $content = $this->replaceVariables($rule->reply_message, $conversation);

            // Create system message
            $message = $this->messageService->createOutboundMessage(
                conversationId: $conversation->id,
                content: $content,
                userId: 1, // System user
                type: $rule->reply_type,
                metadata: array_merge([
                    'automated_reply_id' => $rule->id,
                    'automated_reply_name' => $rule->name,
                    'trigger_type' => $rule->trigger_type,
                ], $extraMetadata)
            );

            // Dispatch to platform
            $platform = $conversation->platform;
            match ($platform) {
                'whatsapp' => dispatch(new \App\Jobs\SendWhatsAppMessageJob($message)),
                'facebook' => dispatch(new \App\Jobs\SendFacebookMessageJob($message)),
                'instagram' => dispatch(new \App\Jobs\SendInstagramMessageJob($message)),
                default => null,
            };

            Log::info('Automated reply sent', [
                'conversation_id' => $conversation->id,
                'rule_id' => $rule->id,
            ]);

            return $message;

        } catch (\Exception $e) {
            Log::error('Error sending automated reply', [
                'conversation_id' => $conversation->id,
                'rule_id' => $rule->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Replace variables in reply message.
     */
    protected function replaceVariables(string $content, Conversation $conversation): string
    {
        $customer = $conversation->customer;

        $variables = [
            '{{customer_name}}' => $customer->name,
            '{{customer_email}}' => $customer->email ?? '',
            '{{customer_phone}}' => $customer->phone ?? '',
        ];

        return str_replace(array_keys($variables), array_values($variables), $content);
    }

    /**
     * Check if current time is outside work hours.
     */
    protected function isOutsideWorkHours(): bool
    {
        $now = Carbon::now();
        $dayOfWeek = $now->dayOfWeek;
        $currentTime = $now->format('H:i:00');

        $workHour = WorkHour::where('day_of_week', $dayOfWeek)
            ->where('is_open', true)
            ->first();

        if (!$workHour) {
            return true; // Day is closed
        }

        if ($workHour->is_24_hours) {
            return false;
        }

        // Check if current time is within work hours
        return !($currentTime >= $workHour->open_time && $currentTime <= $workHour->close_time);
    }

    /**
     * Get assignment settings from database.
     */
    protected function getAssignmentSettings(): array
    {
        // This should fetch from settings table
        // For now, return defaults
        return [
            'strategy' => 'round_robin',
            'enabled' => true,
        ];
    }
}

