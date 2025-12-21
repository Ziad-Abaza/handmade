<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'cart_id' => ['nullable', 'exists:carts,id'],
            'status_id' => ['required', 'exists:order_statuses,id'],
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'payment_status_id' => ['required', 'exists:payment_statuses,id'],
            
            // Billing info
            'billing_name' => ['required', 'string', 'max:255'],
            'billing_email' => ['required', 'email', 'max:255'],
            'billing_phone' => ['required', 'string', 'max:20'],
            'billing_address' => ['required', 'string', 'max:500'],
            'billing_city' => ['required', 'string', 'max:100'],
            'billing_state' => ['nullable', 'string', 'max:100'],
            'billing_country' => ['required', 'string', 'max:100'],
            'billing_postcode' => ['required', 'string', 'max:20'],
            
            // Shipping info (same as billing by default)
            'shipping_name' => ['required_without:same_as_billing', 'string', 'max:255'],
            'shipping_email' => ['required_without:same_as_billing', 'email', 'max:255'],
            'shipping_phone' => ['required_without:same_as_billing', 'string', 'max:20'],
            'shipping_address' => ['required_without:same_as_billing', 'string', 'max:500'],
            'shipping_city' => ['required_without:same_as_billing', 'string', 'max:100'],
            'shipping_state' => ['nullable', 'string', 'max:100'],
            'shipping_country' => ['required_without:same_as_billing', 'string', 'max:100'],
            'shipping_postcode' => ['required_without:same_as_billing', 'string', 'max:20'],
            'same_as_billing' => ['sometimes', 'boolean'],
            
            // Order items
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.vendor_id' => ['required', 'exists:users,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.tax_amount' => ['sometimes', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['sometimes', 'numeric', 'min:0'],
            'items.*.options' => ['sometimes', 'array'],
            
            // Order totals
            'subtotal' => ['required', 'numeric', 'min:0'],
            'shipping_cost' => ['required', 'numeric', 'min:0'],
            'tax_amount' => ['required', 'numeric', 'min:0'],
            'discount_amount' => ['required', 'numeric', 'min:0'],
            'total' => ['required', 'numeric', 'min:0'],
            
            // Payment info
            'transaction_id' => ['nullable', 'string', 'max:255'],
            'payment_details' => ['nullable', 'array'],
            
            // Shipping info
            'shipping_method' => ['nullable', 'string', 'max:255'],
            'tracking_number' => ['nullable', 'string', 'max:255'],
            
            // Notes
            'notes' => ['nullable', 'string'],
        ];
    }
    
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        if ($this->same_as_billing) {
            $this->merge([
                'shipping_name' => $this->billing_name,
                'shipping_email' => $this->billing_email,
                'shipping_phone' => $this->billing_phone,
                'shipping_address' => $this->billing_address,
                'shipping_city' => $this->billing_city,
                'shipping_state' => $this->billing_state,
                'shipping_country' => $this->billing_country,
                'shipping_postcode' => $this->billing_postcode,
            ]);
        }
    }
}
