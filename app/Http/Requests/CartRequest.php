<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Product;

class CartRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Using policies/middleware for authorization
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.options' => 'nullable|array',
            'items.*.note' => 'nullable|string|max:500',
            'coupon_code' => 'nullable|string',
            'shipping_method' => 'nullable|string|in:standard,express,overnight',
            'shipping_address' => 'nullable|string|max:1000',
            'billing_address' => 'nullable|string|max:1000',
            'same_as_billing' => 'boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'items.required' => 'At least one item is required',
            'items.*.product_id.required' => 'Product ID is required',
            'items.*.product_id.exists' => 'The selected product does not exist',
            'items.*.quantity.required' => 'Quantity is required',
            'items.*.quantity.integer' => 'Quantity must be a whole number',
            'items.*.quantity.min' => 'Quantity must be at least 1',
            'coupon_code.exists' => 'The provided coupon code is invalid',
            'quantity.min' => 'Quantity must be at least 1',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Ensure options is an array if it's a string
        if ($this->has('options') && is_string($this->options)) {
            $this->merge([
                'options' => json_decode($this->options, true) ?? []
            ]);
        }

        // Handle old format: {"product_id": 6, "product_detail_id": 1, "quantity": 1}
        // Convert to new format: {"items": [{"product_id": 6, "quantity": 1, "options": {"product_detail_id": 1}}]}
        if ($this->has('product_id')) {
            $this->merge([
                'items' => [[
                    'product_id' => $this->product_id,
                    'quantity' => $this->quantity,
                    'options' => $this->product_detail_id ? ['product_detail_id' => $this->product_detail_id] : []
                ]]
            ]);
        }
    }
}