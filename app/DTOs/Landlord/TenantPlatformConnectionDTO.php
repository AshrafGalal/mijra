<?php

namespace App\DTOs\Landlord;

use App\DTOs\Abstract\BaseDTO;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TenantPlatformConnectionDTO extends BaseDTO
{
    public function __construct(
        public string $tenant_id,
        public int $platform_id,
        public string $user_access_token,
        public ?string $refresh_token = null,
        public ?string $token_expires_at = null,
        public ?string $external_user_id = null,
        public ?string $external_account_id = null,
        public ?string $webhook_id = null,
        public ?string $webhook_secret = null,
        public ?array $credentials = [],
        public ?array $meta = [],
        public ?array $settings = [],
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            tenant_id: $data['tenant_id'],
            platform_id: $data['platform_id'],
            user_access_token: $data['user_access_token'],
            refresh_token: $data['refresh_token'] ?? null,
            token_expires_at: isset($data['token_expires_at'])
                ? Carbon::parse($data['token_expires_at'])
                : null,
            external_user_id: $data['external_user_id'] ?? null,
            external_account_id: $data['external_account_id'] ?? null,
            webhook_id: $data['webhook_id'] ?? null,
            webhook_secret: $data['webhook_secret'] ?? null,
            credentials: $data['credentials'] ?? [],
            meta: $data['meta'] ?? [],
            settings: $data['settings'] ?? [],
        );
    }

    public static function fromRequest(Request $request): static
    {
        return new static(
            tenant_id: $request->input('tenant_id'),
            platform_id: $request->input('platform_id'),
            user_access_token: $request->input('user_access_token'),
            refresh_token: $request->input('refresh_token'),
            token_expires_at: $request->filled('token_expires_at')
                ? Carbon::parse($request->input('token_expires_at'))
                : null,
            external_user_id: $request->input('external_user_id'),
            external_account_id: $request->input('external_account_id'),
            webhook_id: $request->input('webhook_id'),
            webhook_secret: $request->input('webhook_secret'),
            credentials: $request->input('credentials', []),
            meta: $request->input('meta', []),
            settings: $request->input('settings', []),
        );
    }

    public function toArray(): array
    {
        return [];
    }
}
