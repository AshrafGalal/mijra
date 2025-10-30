<?php

namespace App\DTOs\Tenant\Settings;

use App\Enum\ActivationStatusEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class AssignmentSettingDTO
{
    public function __construct(
        public int $rotation_type,
        public bool $consider_workload = ActivationStatusEnum::ACTIVE->value,
        public bool $enable_department_based_assignment = ActivationStatusEnum::INACTIVE->value,
        public bool $enable_ai_suggestions = false
    ) {}

    /**
     * Create a new DTO from request data
     *
     * @param  array  $data
     *
     * @throws ValidationException
     */
    public static function fromRequest(Request $request): self
    {

        return new self(
            rotation_type: $request->rotation_type,
            consider_workload: $request->consider_workload,
            enable_department_based_assignment: $request->enable_department_based_assignment,
            enable_ai_suggestions: $request->enable_ai_suggestions,
        );
    }

    public static function fromArray(array $data): static
    {
        return new self(
            rotation_type: Arr::get($data, 'rotation_type'),
            consider_workload: Arr::get($data, 'consider_workload', true),
            enable_department_based_assignment: Arr::get($data, 'enable_department_based_assignment', false),
            enable_ai_suggestions: Arr::get($data, 'enable_ai_suggestions', false),
        );
    }

    /**
     * Convert DTO to array for database operations
     */
    public function toArray(): array
    {
        return [
            'rotation_type' => $this->rotation_type,
            'consider_workload' => $this->consider_workload,
            'enable_department_based_assignment' => $this->enable_department_based_assignment,
            'enable_ai_suggestions' => $this->enable_ai_suggestions,
        ];
    }
}
