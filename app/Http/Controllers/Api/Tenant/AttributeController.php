<?php

namespace App\Http\Controllers\Api\Tenant;

use App\DTOs\Tenant\AttributeDTO;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\CreateAttributeRequest;
use App\Http\Requests\Tenant\CreateAttributeValueRequest;
use App\Http\Requests\Tenant\UpdateAttributeRequest;
use App\Http\Resources\Tenant\AttributeResource;
use App\Services\Tenant\AttributeService;
use Illuminate\Http\Request;

class AttributeController extends Controller
{
    public function __construct(protected AttributeService $attributeService) {}

    public function index(Request $request)
    {
        $filters = $request->all();

        return AttributeResource::collection($this->attributeService->paginate(filters: $filters, withRelations: ['values']));
    }

    public function show($id)
    {
        $attribute = $this->attributeService->findById(id: $id, withRelation: ['values']);

        return AttributeResource::make($attribute);
    }

    public function store(CreateAttributeRequest $request)
    {
        $dto = AttributeDTO::fromRequest($request);
        $this->attributeService->create($dto);

        return ApiResponse::success(message: 'Attribute created successfully.');
    }

    public function update(UpdateAttributeRequest $request, $attribute)
    {
        $dto = AttributeDTO::fromRequest($request);
        $this->attributeService->update($attribute, $dto);

        return ApiResponse::success(message: 'Attribute updated successfully.');
    }

    public function createAttributeValue($attribute_id, CreateAttributeValueRequest $request)
    {
        $this->attributeService->createAttributeValue(attributeId: $attribute_id, value: $request->value);

        return ApiResponse::success(message: 'Value added successfully.');
    }

    public function destroy($attribute)
    {
        $this->attributeService->delete($attribute);

        return ApiResponse::success(message: 'Attribute deleted successfully.');
    }
}
