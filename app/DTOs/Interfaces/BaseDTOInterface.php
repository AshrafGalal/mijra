<?php

namespace App\DTOs\Interfaces;

use Illuminate\Http\Request;

interface BaseDTOInterface
{
    public static function fromArray(array $data): static;

    public static function fromRequest(Request $request): static;

    public function toArray(): array;
}
