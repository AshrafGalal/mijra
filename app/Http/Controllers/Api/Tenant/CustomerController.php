<?php

namespace App\Http\Controllers\Api\Tenant;

use App\DTOs\Tenant\CustomerDTO;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\CustomerRequest;
use App\Http\Resources\Tenant\CustomerResource;
use App\Services\Tenant\CustomerService;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function __construct(protected readonly CustomerService $customerService) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = array_filter([
            'name' => $request->name ?? null,
            'status' => $request->status ?? null,
        ], fn ($value) => ! is_null($value) && $value !== '');

        $customers = $this->customerService->paginate(filters: $filters, limit: $request->get('limit', 15));

        return CustomerResource::collection($customers);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CustomerRequest $request)
    {
        $customerDTO = CustomerDTO::fromRequest($request);
        $this->customerService->create($customerDTO);

        return ApiResponse::success(message: __('app.customer_created_successfully'));

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $customer = $this->customerService->findById($id);

        return ApiResponse::success(data: CustomerResource::make($customer));
    }

    public function statics()
    {
        $statics = $this->customerService->statics();

        return ApiResponse::success(data: $statics);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CustomerRequest $request, string $customer)
    {
        $customerDTO = CustomerDTO::fromRequest($request);
        $this->customerService->update(id: $customer, customerDTO: $customerDTO);

        return ApiResponse::success(message: __('app.customer_updated_successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->customerService->delete($id);

        return ApiResponse::success(message: 'Customer deleted successfully');

    }
}
