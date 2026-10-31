<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\TransactionType;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * The list filters arrive from the query string, so they are validated before
 * they can reach a query builder.
 */
class TransactionFilterRequest extends FormRequest
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

        return [
            'month' => ['nullable', 'date_format:Y-m'],
            'type' => ['nullable', Rule::enum(TransactionType::class)],
            'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')->where('user_id', $user->id)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'month.date_format' => 'Format bulan harus TTTT-BB, misalnya 2026-08.',
            'type.enum' => 'Tipe harus pemasukan atau pengeluaran.',
            'category_id.exists' => 'Kategori yang dipilih tidak ada.',
        ];
    }
}
