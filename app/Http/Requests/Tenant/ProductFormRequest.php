<?php

namespace App\Http\Requests\Tenant;

use App\Enum\DiscountTypeEnum;
use App\Enum\ProductStatusEnum;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;

class ProductFormRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $productId = $this->route('product'); // null on create, id on update

        return [
            // 🔹 Basic product fields
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'slug' => [
                'nullable',
                'string',
                Rule::unique('tenant.products')->ignore($productId),
            ],
            'base_price' => ['required', 'numeric', 'min:0'],
            'discount' => [
                'nullable',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) {
                    if ($this->discount_type === 'percentage' && $value > 100) {
                        $fail(__('validation.product.discount_percentage_max'));
                    }
                },
            ],
            'discount_type' => ['nullable', Rule::in(DiscountTypeEnum::values())],
            'vat_percentage' => ['nullable', 'numeric', 'between:0,100'],
            'status' => ['nullable', Rule::in(ProductStatusEnum::values())],
            'category_id' => ['required', 'exists:tenant.categories,id'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50'],

            // 🔹 Media
            'thumbnail_id' => ['nullable', 'string', 'exists:tenant.temp_files,file_id'],
            'media_ids' => ['nullable', 'array'],
            'media_ids.*' => ['string', 'exists:tenant.temp_files,file_id'],

            // 🔹 Variants
            'variants' => ['nullable', 'array'],
            'variants.*.id' => ['nullable', 'integer', 'exists:tenant.product_variants,id'],
            'variants.*.sku' => [
                'required_with:variants.*',
                'string',
                Rule::unique('tenant.product_variants', 'sku')
                    ->where(function (Builder $query) {
                        $variantIds = $this->getVariantIds();
                        if (! empty($variantIds)) {
                            $query->whereNotIn('id', $variantIds);
                        }
                    }),
            ],
            'variants.*.price' => ['required_with:variants.*', 'numeric', 'min:0'],
            'variants.*.stock' => ['required_with:variants.*', 'integer', 'min:0'],
            'variants.*.barcode' => ['nullable', 'string', 'max:255'],
            'variants.*.weight' => ['nullable', 'numeric', 'min:0'],

            // 🔹 Variant attributes
            'variants.*.attributes' => ['required_with:variants.*', 'array', 'min:1'],
            'variants.*.attributes.*.attribute_id' => ['required', 'exists:tenant.attributes,id'],
            'variants.*.attributes.*.attribute_value_id' => ['required', 'exists:tenant.attribute_values,id'],
        ];
    }

    /** * Collect variant IDs from payload to safely ignore during update. */
    protected function getVariantIds(): array
    {
        $variants = $this->input('variants', []);

        return collect($variants)->pluck('id')->filter()->toArray();
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $skus = array_column($this->input('variants', []), 'sku');
            $duplicates = array_unique(array_diff_assoc($skus, array_unique($skus)));

            foreach ($duplicates as $sku) {
                foreach ($this->input('variants', []) as $index => $variant) {
                    if (($variant['sku'] ?? null) === $sku) {
                        $validator->errors()->add("variants.$index.sku", __('Duplicate SKU in request.'));
                    }
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'name.required' => __('validation.product.name_required'),
            'name.string' => __('validation.product.name_string'),
            'slug.unique' => __('validation.product.slug_unique'),
            'base_price.required' => __('validation.product.base_price_required'),
            'base_price.numeric' => __('validation.product.base_price_numeric'),
            'discount.numeric' => __('validation.product.discount_numeric'),
            'discount_type.in' => __('validation.product.discount_type_in'),
            'vat_percentage.numeric' => __('validation.product.vat_numeric'),
            'status.in' => __('validation.product.status_in'),
            'category_id.exists' => __('validation.product.category_exists'),
            'tags.array' => __('validation.product.tags_array'),
            'tags.*.string' => __('validation.product.tag_string'),

            // Media
            'thumbnail_id.exists' => __('validation.product.thumbnail_exists'),
            'media_ids.array' => __('validation.product.media_array'),
            'media_ids.*.exists' => __('validation.product.media_exists'),

            // Variants
            'variants.array' => __('validation.product.variants_array'),
            'variants.*.sku.required_with' => __('validation.product.variant_sku_required'),
            'variants.*.sku.unique' => __('validation.product.variant_sku_unique'),
            'variants.*.price.required_with' => __('validation.product.variant_price_required'),
            'variants.*.price.numeric' => __('validation.product.variant_price_numeric'),
            'variants.*.stock.required_with' => __('validation.product.variant_stock_required'),
            'variants.*.stock.integer' => __('validation.product.variant_stock_integer'),
            'variants.*.barcode.string' => __('validation.product.variant_barcode_string'),
            'variants.*.weight.numeric' => __('validation.product.variant_weight_numeric'),

            // Variant attributes
            'variants.*.attributes.required_with' => __('validation.product.variant_attributes_required'),
            'variants.*.attributes.array' => __('validation.product.variant_attributes_array'),
            'variants.*.attributes.*.attribute_id.required' => __('validation.product.attribute_id_required'),
            'variants.*.attributes.*.attribute_id.exists' => __('validation.product.attribute_id_exists'),
            'variants.*.attributes.*.attribute_value_id.required' => __('validation.product.attribute_value_id_required'),
            'variants.*.attributes.*.attribute_value_id.exists' => __('validation.product.attribute_value_id_exists'),
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'slug' => generateSlug($this->name),
        ]);
    }
}
