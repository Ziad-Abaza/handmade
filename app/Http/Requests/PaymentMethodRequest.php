<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentMethodRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled by the controller
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        $methodId = $this->route('method') ? $this->route('method')->id : null;
        
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('payment_methods', 'name')->ignore($methodId),
            ],
            'code' => [
                'required',
                'string',
                'max:50',
                'alpha_dash',
                Rule::unique('payment_methods', 'code')->ignore($methodId),
            ],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
            'is_online' => ['sometimes', 'boolean'],
            'config' => ['nullable', 'array'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }
    
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'is_online' => $this->boolean('is_online'),
            'sort_order' => $this->input('sort_order', 0),
            'name' => trim($this->name),
            'code' => strtoupper(trim($this->code)),
        ]);
    }
}
