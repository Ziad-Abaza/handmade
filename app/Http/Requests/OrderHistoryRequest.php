<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderHistoryRequest extends FormRequest
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
        return [
            'status_id' => ['required', 'exists:order_statuses,id'],
            'comment' => ['nullable', 'string', 'max:1000'],
            'notify_customer' => ['sometimes', 'boolean'],
        ];
    }
    
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'notify_customer' => $this->boolean('notify_customer'),
        ]);
    }
}
