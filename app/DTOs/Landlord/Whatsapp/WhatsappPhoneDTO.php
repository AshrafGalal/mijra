<?php

namespace App\DTOs\Landlord\Whatsapp;

use App\DTOs\Abstract\BaseDTO;
use App\Enum\WhatsappPhoneStatusEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class WhatsappPhoneDTO extends BaseDTO
{
    public function __construct(
        public ?string $tenant_id = null,
        public ?string $phone_number = null,
        public ?string $phone_label = null,
        public ?int $status = WhatsappPhoneStatusEnum::INITIALIZING->value,
        public ?string $qr_code = null,
        public ?string $last_update = null,
        public ?string $connected_at = null,
        public ?string $error_message = null
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            tenant_id: (string) Arr::get($data, 'tenant_id'),
            phone_number: (string) Arr::get($data, 'phone_number'),
            phone_label: Arr::get($data, 'phone_label'),
            status: Arr::get($data, 'status', WhatsappPhoneStatusEnum::INITIALIZING->value),
            qr_code: Arr::get($data, 'qr_code'),
            last_update: Arr::get($data, 'last_update'),
            connected_at: Arr::get($data, 'connected_at'),
            error_message: Arr::get($data, 'error_message')
        );
    }

    public static function fromRequest(Request $request): static
    {
        return new self(
            tenant_id: (string) $request->input('tenant_id'),
            phone_number: (string) $request->input('phone_number'),
            phone_label: $request->input('phone_label'),
            status: $request->input('status', WhatsappPhoneStatusEnum::INITIALIZING->value),
            qr_code: $request->input('qr_code'),
            last_update: $request->input('last_update'),
            connected_at: $request->input('connected_at'),
            error_message: $request->input('error_message')
        );
    }

    public function toArray(): array
    {
        return [
            'tenant_id' => $this->tenant_id,
            'phone_number' => $this->phone_number,
            'phone_label' => $this->phone_label,
            'status' => $this->status,
            'qr_code' => $this->qr_code,
            'last_update' => $this->last_update,
            'connected_at' => $this->connected_at,
            'error_message' => $this->error_message,
        ];
    }
}
