<?php

namespace App\DTOs\Tenant\Conversation;

use App\Enum\ExternalPlatformEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class StartConversationDTO
{
    public function __construct(
        public ?int $contact_id,
        public ?int $template_id,
        public ?string $platform_account_id,
        public string $platform = ExternalPlatformEnum::WHATSAPP->value,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            contact_id: Arr::get($data, 'contact_id'),
            template_id: Arr::get($data, 'template_id'),
            platform_account_id: Arr::get($data, 'platform_account_id'),
            platform: Arr::get($data, 'tenant_platform_id', ExternalPlatformEnum::WHATSAPP->value),
        );
    }

    public static function fromRequest(Request $request): static
    {
        return new self(
            contact_id: $request->contact_id,
            template_id: $request->template_id,
            platform_account_id: $request->platform_account_id,
            platform: $request->platform ?? ExternalPlatformEnum::WHATSAPP->value,

        );
    }

    public function toArray(): array
    {
        return [
            'contact_id' => $this->contact_id,
            'template_id' => $this->template_id,
            'platform_account_id' => $this->platform_account_id,
            'platform' => $this->platform,

        ];
    }
}
