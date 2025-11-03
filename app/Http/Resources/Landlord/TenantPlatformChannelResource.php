<?php

namespace App\Http\Resources\Landlord;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantPlatformChannelResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'platform_id' => $this->platform_id,
            'connection_id' => $this->tenant_platform_connection_id,
            'name' => $this->name,
            'category' => $this->category,
            'meta' => $this->meta,
            'status' => $this->status,
        ];
    }
}
