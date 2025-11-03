<?php

namespace App\Http\Resources\Tenant;

use App\Http\Resources\Role\RoleResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'locale' => $this->locale,
            'is_verified' => isset($this->email_verified_at),
            'role' => RoleResource::collection($this->whenLoaded('roles')),
            'is_active' => $this->is_active->value,
            'is_active_text' => $this->is_active->getLabel(),
            'created_at' => $this->created_at,
            'department' => DepartmentResource::make($this->whenLoaded('department')),
        ];
    }
}
