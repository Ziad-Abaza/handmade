<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WishlistRequest extends FormRequest
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
        $rules = [
            'name' => 'required|string|max:255',
            'is_default' => 'sometimes|boolean',
        ];

        // On update, ignore the current wishlist for unique name check
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $wishlist = $this->route('wishlist');
            $rules['name'] = [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('wishlists', 'name')
                    ->where('user_id', $this->user()->id)
                    ->ignore($wishlist->id)
            ];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Wishlist name is required',
            'name.unique' => 'You already have a wishlist with this name',
        ];
    }
}
