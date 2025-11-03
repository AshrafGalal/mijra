<?php

namespace App\Http\Controllers\Api\Tenant;

use App\DTOs\Tenant\CategoryDTO;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\CategoryRequest;
use App\Http\Resources\Tenant\CategoryResource;
use App\Services\Tenant\CategoryService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(protected CategoryService $categoryService) {}

    public function index(Request $request)
    {
        $filters = $request->all();

        return ApiResponse::success(data: CategoryResource::collection($this->categoryService->list($filters)));
    }

    public function show($id)
    {
        $category = $this->categoryService->findById($id);

        return ApiResponse::success(data: CategoryResource::make($category));
    }

    /**
     * @throws \Throwable
     */
    public function store(CategoryRequest $request)
    {
        $dto = CategoryDTO::fromRequest($request);
        $this->categoryService->create($dto);

        return ApiResponse::success(message: 'Category created successfully.');
    }

    public function update(CategoryRequest $request, int $category)
    {
        $dto = CategoryDTO::fromRequest($request);

        $this->categoryService->update($category, $dto);

        return ApiResponse::success(message: 'Category updated successfully.');
    }

    public function destroy($category)
    {
        $this->categoryService->delete($category);

        return ApiResponse::success(message: 'Category deleted successfully.');
    }
}
