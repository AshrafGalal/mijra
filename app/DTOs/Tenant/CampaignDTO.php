<?php

namespace App\DTOs\Tenant;

use App\DTOS\Abstract\BaseDTO;
use App\Enum\CampaignTargetEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class CampaignDTO extends BaseDTO
{
    public function __construct(
        public string $channel,
        public string $target,
        public ?int $template_id,
        public ?string $title,
        public ?string $body,
        public string $campaign_type,
        public ?string $scheduled_at = null,
        public ?array $media_ids = null,
        public mixed $contacts_file = null
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            title: Arr::get($data, 'title'),
            body: Arr::get($data, 'body'),
            campaign_type: Arr::get($data, 'campaign_type'),
            target: Arr::get($data, 'target'),
            channel: Arr::get($data, 'channel'),
            scheduled_at: Arr::get($data, 'scheduled_at'),
            media_ids: Arr::get($data, 'media_ids', []),
            contacts_file: Arr::get($data, 'contacts_file')
        );
    }

    public static function fromRequest(Request $request): static
    {
        return new self(
            title: $request->title,
            body: $request->body,
            campaign_type: $request->campaign_type,
            target: $request->target,
            channel: $request->channel,
            scheduled_at: $request->scheduled_at,
            media_ids: $request->media_ids,
            contacts_file: $request->contacts_file,
        );
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'content' => $this->body,
            'campaign_type' => $this->campaign_type,
            'channel' => $this->channel,
            'scheduled_at' => $this->scheduled_at,
            'target' => $this->target ?? CampaignTargetEnum::ALL_CUSTOMERS->value,
        ];
    }
}
