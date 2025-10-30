<?php

namespace App\DTOs\Tenant;

use App\DTOs\Abstract\BaseDTO;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class WorkHourDTO extends BaseDTO
{
    public function __construct(
        public ?array $workHours,
        //        public string $from,
        //        public string $to,
        //        public ?bool $is_closed = false
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            workHours: Arr::get($data, 'work_days'),
            //            from: Arr::get($data, 'from'),
            //            to: Arr::get($data, 'to', true),
            //            is_closed: Arr::get($data, 'is_closed', false),
        );
    }

    public static function fromRequest(Request $request): static
    {
        return new self(
            workHours: $request->work_hours,
            //            from: $request->from,
            //            to: $request->to,
            //            is_closed: $request->is_closed,
        );
    }

    public function toArray(): array
    {
        return [
            'workHours' => $this->workHours,
            //            'from' => $this->from,
            //            'to' => $this->to,
            //            'is_closed' => $this->is_closed,
        ];
    }
}
