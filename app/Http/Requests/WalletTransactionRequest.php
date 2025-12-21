<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WalletTransactionRequest extends FormRequest
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
        $minAmount = $this->is('*/withdraw*') ? 
            config('wallet.min_withdrawal', 10.00) : 
            config('wallet.min_deposit', 5.00);
            
        $maxAmount = $this->is('*/withdraw*') ? 
            min(auth()->user()->wallet->balance, config('wallet.max_withdrawal', 5000.00)) :
            config('wallet.max_deposit', 1000.00);

        return [
            'amount' => [
                'required',
                'numeric',
                'min:' . $minAmount,
                'max:' . $maxAmount,
            ],
            'payment_method' => [
                'required_if:type,deposit,withdraw',
                'string',
                Rule::in(['credit_card', 'bank_transfer', 'paypal']), // Add more as needed
            ],
            'account_details' => [
                'required_if:type,withdraw',
                'array',
            ],
            'account_details.account_number' => [
                'required_if:type,withdraw',
                'string',
            ],
            'account_details.account_name' => [
                'required_if:type,withdraw',
                'string',
            ],
            'account_details.bank_name' => [
                'required_if:type,withdraw',
                'string',
            ],
            'note' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'amount.min' => 'The minimum amount is :min ' . (config('wallet.currency', 'USD')),
            'amount.max' => 'The maximum amount is :max ' . (config('wallet.currency', 'USD')),
            'payment_method.required_if' => 'Please select a payment method.',
            'account_details.required_if' => 'Account details are required for withdrawal.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        if ($this->has('amount')) {
            $this->merge([
                'amount' => (float) preg_replace('/[^0-9.]/', '', $this->amount),
            ]);
        }
    }
}
