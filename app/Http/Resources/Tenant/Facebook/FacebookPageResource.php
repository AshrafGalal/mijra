<?php

namespace App\Http\Resources\Tenant\Facebook;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class FacebookPageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => Arr::get($this->resource, 'id'),
            'name' => Arr::get($this->resource, 'name'),
            'category' => Arr::get($this->resource, 'category'),
            'picture' => Arr::get($this->resource, 'picture.data.url'),
            'access_token' => Arr::get($this->resource, 'access_token'),
            'permissions' => Arr::get($this->resource, 'perms', []),
            'connected_instagram_account' => [
                'id' => Arr::get($this->resource, 'connected_instagram_account.id'),
            ],
        ];
    }
}
