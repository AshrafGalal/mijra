<?php

namespace App\Http\Controllers\Api\Tenant;

use App\DTOs\Tenant\CustomerFeedbackDTO;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\CustomerFeedbackRequest;
use App\Http\Resources\Tenant\CustomerFeedbackResource;
use App\Services\Tenant\CustomerFeedbackService;
use Illuminate\Http\Request;

class CustomerFeedbackController extends Controller
{
    public function __construct(protected CustomerFeedbackService $customerFeedbackService) {}

    public function index(Request $request)
    {
        $filters = $request->all();
        $perPage = $request->input('limit', 15);
        $filters = array_filter($filters, fn ($value) => ! is_null($value) && $value !== '');
        $withRelations = ['feedbackCategory'];

        return CustomerFeedbackResource::collection(
            $this->customerFeedbackService->paginate(filters: $filters, limit: (int) $perPage, withRelations: $withRelations)
        );
    }

    public function show($id)
    {
        $feedback = $this->customerFeedbackService->findById($id, ['feedbackCategory']);

        return ApiResponse::success(data: CustomerFeedbackResource::make($feedback));
    }

    public function store(CustomerFeedbackRequest $request)
    {
        $dto = CustomerFeedbackDTO::fromRequest($request);
        $this->customerFeedbackService->create($dto);

        return ApiResponse::success(
            message: 'Customer feedback submitted successfully.',
            code: 201
        );
    }

    public function update(CustomerFeedbackRequest $request, $feedback)
    {
        $dto = CustomerFeedbackDTO::fromRequest($request);
        $this->customerFeedbackService->update($feedback, $dto);

        return ApiResponse::success(message: 'Customer feedback updated successfully.');
    }

    public function destroy($feedback)
    {
        $this->customerFeedbackService->delete($feedback);

        return ApiResponse::success(message: 'Customer feedback deleted successfully.');
    }

    public function statics()
    {
        $statics = $this->customerFeedbackService->statics();

        return ApiResponse::success(data: $statics);
    }
}
