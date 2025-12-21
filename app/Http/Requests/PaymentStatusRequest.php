<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentStatusRequest extends FormRequest
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
        $paymentStatusId = $this->route('payment_status') ? $this->route('payment_status')->id : null;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('payment_statuses', 'name')->ignore($paymentStatusId)
            ],
            'color' => 'required|string|max:50',
            'is_default' => 'sometimes|boolean',
        ];
    }
}
