<?php

namespace App\Http\Resources\Tenant;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkHourResource extends JsonResource
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
            'day' => $this->day,
            'day_text' => $this->day->getLabel(),
            'from' => $this->from,
            'to' => $this->to,
            'is_closed' => $this->is_closed,
        ];
    }
}
