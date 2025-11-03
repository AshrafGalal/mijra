<?php

namespace App\DTOs\Landlord;

use App\DTOs\Abstract\BaseDTO;
use App\Enum\ChannelStatusEnum;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TenantPlatformChannelDTO extends BaseDTO
{
    public function __construct(
        public string  $tenant_id,
        public string  $platform_id,
        public string  $tenant_platform_connection_id,
        public string  $external_id,
        public string  $name,
        public ?string $access_token = null,
        public ?string $category = null,
        public ?array  $category_list = null,
        public ?array  $capabilities = null,
        public ?array  $meta = null,
        public ?array  $settings = null,
        public ?string $status = ChannelStatusEnum::ACTIVE->value,
    )
    {
    }

    public static function fromArray(array $data): static
    {
        return new self(
            tenant_id: $data['tenant_id'],
            platform_id: $data['platform_id'],
            tenant_platform_connection_id: $data['tenant_platform_connection_id'],
            external_id: $data['external_id'],
            name: $data['name'],
            access_token: $data['access_token'] ?? null,
            category: $data['category'] ?? null,
            category_list: $data['category_list'] ?? null,
            capabilities: $data['capabilities'] ?? null,
            meta: $data['meta'] ?? null,
            settings: $data['settings'] ?? null,
            status: $data['status'] ?? ChannelStatusEnum::ACTIVE->value,
        );
    }

    public static function fromRequest(Request $request): static
    {
        return new static(
            tenant_id: $request->tenant_id,
            platform_id: $request->platform_id,
            tenant_platform_connection_id: $request->tenant_platform_connection_id,
            external_id: $request->external_id,
            name: $request->name,
            access_token: $request->access_token,
            category: $request->category,
            category_list: $request->category_list,
            capabilities: $request->capabilities,
            meta: $request->meta,
            settings: $request->settings,
            status: $request->status ?? ChannelStatusEnum::ACTIVE->value,
        );
    }

    public function toArray(): array
    {
        return [
            'tenant_id' => $this->tenant_id,
            'platform_id' => $this->platform_id,
            'tenant_platform_connection_id' => $this->tenant_platform_connection_id,
            'external_id' => $this->external_id,
            'name' => $this->name,
            'access_token' => $this->access_token,
            'category' => $this->category,
            'category_list' => $this->category_list,
            'capabilities' => $this->capabilities,
            'meta' => $this->meta,
            'settings' => $this->settings,
            'status' => $this->status,
        ];
    }
}
