<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderStatusRequest extends FormRequest
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
        $statusId = $this->route('status') ? $this->route('status')->id : null;
        
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('order_statuses', 'name')->ignore($statusId),
            ],
            'color' => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'is_default' => ['sometimes', 'boolean'],
        ];
    }
    
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'is_default' => $this->boolean('is_default'),
            'name' => trim($this->name),
        ]);
    }
}
