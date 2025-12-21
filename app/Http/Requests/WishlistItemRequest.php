<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WishlistItemRequest extends FormRequest
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
            'product_id' => [
                'required',
                'exists:products,id',
                function ($attribute, $value, $fail) {
                    // Prevent duplicate products in the same wishlist
                    $exists = \App\Models\WishlistItem::where('wishlist_id', $this->route('wishlist')->id)
                        ->where('product_id', $value)
                        ->exists();

                    if ($exists) {
                        $fail('This product is already in your wishlist');
                    }
                },
            ],
            'options' => 'nullable|array',
            'note' => 'nullable|string|max:500',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        if ($this->has('options') && is_string($this->options)) {
            $this->merge([
                'options' => json_decode($this->options, true) ?? []
            ]);
        }
    }
}
