<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WalletRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var User $user */
        $user = $this->user();

        $wallet = $this->route('wallet');

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('wallets', 'name')
                    ->where('user_id', $user->id)
                    ->ignore($wallet instanceof Wallet ? $wallet->id : null),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.unique' => 'Kamu sudah punya dompet dengan nama ini.',
        ];
    }
}
