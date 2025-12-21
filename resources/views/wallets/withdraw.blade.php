<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Withdraw Funds</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
<div class="min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-4 bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Withdraw Funds</h2>
            
            @if (session('status'))
                <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                <div class="flex justify-between items-center">
                    <span class="text-gray-700">Available Balance:</span>
                    <span class="text-xl font-semibold">${{ number_format($wallet->balance, 2) }}</span>
                </div>
            </div>

            <form action="{{ route('wallet.withdraw') }}" method="POST">
                @csrf
                
                <div class="mb-6">
                    <label for="amount" class="block text-gray-700 text-sm font-bold mb-2">
                        Amount to Withdraw ($)
                    </label>
                    <div class="relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">$</span>
                        </div>
                        <input 
                            type="number" 
                            name="amount" 
                            id="amount" 
                            value="{{ old('amount') }}"
                            class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-7 pr-12 sm:text-sm border-gray-300 rounded-md py-2 px-3 border @error('amount') border-red-500 @enderror" 
                            placeholder="0.00" 
                            min="{{ number_format($minWithdrawal, 2) }}" 
                            max="{{ number_format($maxWithdrawal, 2) }}"
                            step="0.01"
                            required
                        >
                        @error('amount')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <p class="mt-1 text-xs text-gray-500">
                        Minimum: ${{ number_format($minWithdrawal, 2) }} - Maximum: ${{ number_format($maxWithdrawal, 2) }}
                    </p>
                </div>

                <div class="mb-6">
                    <label for="payment_method" class="block text-gray-700 text-sm font-bold mb-2">
                        Payment Method
                    </label>
                    <select 
                        name="payment_method" 
                        id="payment_method" 
                        class="block w-full mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('payment_method') border-red-500 @enderror"
                        required
                    >
                        <option value="">Select payment method</option>
                        <option value="bank">Bank Transfer</option>
                        <option value="paypal">PayPal</option>
                        <option value="credit_card">Credit Card</option>
                    </select>
                    @error('payment_method')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between">
                    <a href="{{ route('wallet.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">
                        &larr; Back to Wallet
                    </a>
                    <button 
                        type="submit" 
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                    >
                        Withdraw Funds
                    </button>
                </div>
            </form>
            </form>
        </div>
    </div>
</div>
</body>
</html>
