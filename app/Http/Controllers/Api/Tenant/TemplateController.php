<?php

namespace App\Http\Controllers\Api\Tenant;

use App\DTOs\Tenant\CustomerDTO;
use App\DTOs\Tenant\TemplateDTO;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\CustomerRequest;
use App\Http\Requests\Tenant\TemplateRequest;
use App\Http\Resources\Tenant\TemplateResource;
use App\Models\Tenant\Category;
use App\Models\Tenant\Customer;
use App\Models\Tenant\Product;
use App\Services\Tenant\Actions\Template\CreateTemplateService;
use App\Services\Tenant\TemplateService;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    public function __construct(protected readonly CreateTemplateService $createTemplateService, protected readonly TemplateService $templateService) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = array_filter($request->all(), fn ($value) => ! is_null($value) && $value !== '');

        $templates = $this->templateService->paginate(filters: $filters, limit: $request->get('limit', 15));

        return TemplateResource::collection($templates);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TemplateRequest $request)
    {
        $templateDTO = TemplateDTO::fromRequest($request);
        $this->createTemplateService->handle($templateDTO);

        return ApiResponse::success(message: __('app.template_created_successfully'));

    }

    /**
     * Display the specified resource.
     */
    public function show(int $id) {}


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->templateService->delete($id);

        return ApiResponse::success(message: 'Template deleted successfully');
    }

    public function getParameters()
    {
        return ApiResponse::success(
            [
                'contacts' => (new Customer)->getFillable(),
                'categories' => (new Category)->getFillable(),
                'products' => (new Product)->getFillable(),
                'orders' => [],
            ],
        );
    }
}
