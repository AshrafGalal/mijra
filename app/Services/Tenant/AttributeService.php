<?php

namespace App\Services\Tenant;

use App\DTOs\Tenant\AttributeDTO;
use App\Models\Tenant\Attribute;
use App\Models\Tenant\AttributeValue;
use App\Services\BaseService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class AttributeService extends BaseService
{
    protected function getFilterClass(): ?string
    {
        return null;
    }

    protected function baseQuery(): Builder
    {
        return Attribute::query();
    }

    public function create(AttributeDTO $dto): Attribute
    {
        return DB::connection('tenant')->transaction(function () use ($dto) {
            $attribute = $this->baseQuery()->create($dto->toArray());
            // create attribute values
            // if not empty values prepare data and insert data
            if (! empty($dto->values)) {
                $values = collect($dto->values)
                    ->map(fn ($val) => ['value' => $val, 'attribute_id' => $attribute->id])
                    ->toArray();

                AttributeValue::query()->insert($values);
            }

            return $attribute;
        });
    }

    /**
     * @throws \Throwable
     *
     * {
     * "name": "Color",
     * "values": [
     * { "id": 1, "value": "Red" },       // update existing id=1 from "Reed" → "Red"
     * { "id": 2, "value": "Blue" },      // keep Blue as is
     * { "value": "Green" }               // create new (no id yet)
     * ]
     * }
     * ]
     */
    public function update(Attribute|int $attribute, AttributeDTO $dto): Attribute
    {

        return DB::connection('tenant')->transaction(function () use ($attribute, $dto) {
            if (is_int($attribute)) {
                $attribute = parent::findById($attribute);
            }
            // update attribute itself
            $attribute->update($dto->toArray());

            $idsInRequest = collect($dto->values)->pluck('id')->filter()->all();

            // delete removed values
            $attribute->values()
                ->whereNotIn('id', $idsInRequest)
                ->delete();

            // update or create
            foreach ($dto->values as $valueData) {
                if (! empty($valueData['id'])) {
                    // update existing value
                    $attribute->values()
                        ->where('id', $valueData['id'])
                        ->update([
                            'value' => $valueData['value'],
                        ]);
                } else {
                    // create new
                    $attribute->values()->create([
                        'value' => $valueData['value'],
                    ]);
                }
            }

            return $attribute;
        });
    }

    public function delete(Attribute|int $attribute): ?bool
    {
        if (is_int($attribute)) {
            $attribute = parent::findById($attribute);
        }

        return $attribute->delete();
    }

    public function paginate(?array $filters = [], ?array $withRelations = [], int $limit = 15)
    {
        return $this->getQuery(filters: $filters, withRelation: $withRelations)->paginate($limit);
    }

    public function list(?array $filters = []): Collection
    {
        return $this->getQuery($filters)->get();
    }

    public function createAttributeValue($attributeId, $value)
    {
        // check if attribute exists or not
        $attribute = $this->findById($attributeId);

        return AttributeValue::query()->create([
            'attribute_id' => $attribute->id,
            'value' => $value,
        ]);
    }
}
