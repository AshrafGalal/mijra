<?php

namespace App\Http\Controllers\Api\Tenant;

use App\DTOs\Tenant\ProductDTO;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\ProductFormRequest;
use App\Http\Resources\Tenant\Product\ProductDetailsResource;
use App\Http\Resources\Tenant\Product\ProductResource;
use App\Services\Tenant\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(protected readonly ProductService $productService) {}

    public function index(Request $request)
    {
        $filters = array_filter($request->all(), fn ($value) => filled($value));
        $limit = $request->get('limit', 15);

        return ProductResource::collection($this->productService->paginate(filters: $filters, perPage: $limit));
    }

    public function show($id)
    {
        $product = $this->productService->details($id);

        return ApiResponse::success(data: ProductDetailsResource::make($product));
    }

    /**
     * @throws \Throwable
     */
    public function store(ProductFormRequest $request)
    {
        $productDTO = ProductDTO::fromRequest($request);
        $this->productService->create(productDTO: $productDTO);

        return ApiResponse::success(message: 'product created successfully.');
    }

    public function update(ProductFormRequest $request, int $product)
    {
        $productDTO = ProductDTO::fromRequest($request);
        $this->productService->update(product: $product, productDTO: $productDTO);

        return ApiResponse::success(message: 'product updated successfully.');
    }

    public function destroy($product)
    {
        $this->productService->delete($product);

        return ApiResponse::success(message: 'product deleted successfully.');
    }
}
