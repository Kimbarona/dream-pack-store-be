<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CreateCryptoInvoiceRequest extends FormRequest
{
    public function authorize()
    {
        return Auth::check();
    }

    public function rules()
    {
        return [
            'crypto_type' => 'required|string|in:BTC,ETH,LTC,BCH',
        ];
    }

    public function messages()
    {
        return [
            'crypto_type.required' => 'Cryptocurrency type is required.',
            'crypto_type.in' => 'Invalid cryptocurrency selected. Supported: BTC, ETH, LTC, BCH',
        ];
    }

    public function getCryptoType(): string
    {
        return $this->crypto_type;
    }
}