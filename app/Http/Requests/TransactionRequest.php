<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class TransactionRequest extends FormRequest
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
            // Foreign keys are never trusted: the id must belong to this user.
            'wallet_id' => ['required', Rule::exists('wallets', 'id')->where('user_id', $user->id)],
            'category_id' => ['required', Rule::exists('categories', 'id')->where('user_id', $user->id)],
            'type' => ['required', Rule::enum(TransactionType::class)],
            'amount' => ['required', 'integer', 'min:1', 'max:1000000000000'],
            'transacted_on' => ['required', 'date', 'before_or_equal:today'],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * `transactions.type` is denormalized from the category, so this hook is what
     * keeps the copy equal to the source.
     *
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->hasAny(['category_id', 'type'])) {
                    return;
                }

                $category = Category::query()->find($this->integer('category_id'));

                if ($category !== null && $category->type !== $this->enum('type', TransactionType::class)) {
                    $validator->errors()->add(
                        'type',
                        'Tipe transaksi harus sama dengan tipe kategori yang dipilih.',
                    );
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'wallet_id.exists' => 'Dompet yang dipilih tidak ada.',
            'category_id.exists' => 'Kategori yang dipilih tidak ada.',
            'type.required' => 'Pilih tipe transaksi.',
            'type.enum' => 'Tipe transaksi harus pemasukan atau pengeluaran.',
            'amount.integer' => 'Jumlah harus angka bulat tanpa titik atau koma.',
            'amount.min' => 'Jumlah minimal Rp 1.',
            'amount.max' => 'Jumlah terlalu besar.',
            'transacted_on.before_or_equal' => 'Tanggal transaksi tidak boleh melewati hari ini.',
        ];
    }
}
