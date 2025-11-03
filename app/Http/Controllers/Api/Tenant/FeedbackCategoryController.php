<?php

namespace App\Http\Controllers\Api\Tenant;

use App\DTOs\Tenant\FeedbackCategoryDTO;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\FeedbackCategoryRequest;
use App\Http\Resources\Tenant\FeedbackCategoryResource;
use App\Services\Tenant\FeedbackCategoryService;
use Illuminate\Http\Request;

class FeedbackCategoryController extends Controller
{
    public function __construct(protected FeedbackCategoryService $feedbackCategoryService) {}

    public function index(Request $request)
    {
        $filters = $request->all();

        return FeedbackCategoryResource::collection($this->feedbackCategoryService->paginate(filters: $filters));
    }

    public function show($id)
    {
        $feedbackCategory = $this->feedbackCategoryService->findById($id);

        return ApiResponse::success(data: FeedbackCategoryResource::make($feedbackCategory));
    }

    public function store(FeedbackCategoryRequest $request)
    {
        $dto = FeedbackCategoryDTO::fromRequest($request);
        $this->feedbackCategoryService->create($dto);

        return ApiResponse::success(message: 'Feedback category created successfully.');
    }

    public function update(FeedbackCategoryRequest $request, $feedbackCategory)
    {
        $dto = FeedbackCategoryDTO::fromRequest($request);
        $this->feedbackCategoryService->update($feedbackCategory, $dto);

        return ApiResponse::success(message: 'Feedback category updated successfully.');
    }

    public function destroy($feedback_category)
    {
        $this->feedbackCategoryService->delete($feedback_category);

        return ApiResponse::success(message: 'Feedback category deleted successfully.');
    }
}
