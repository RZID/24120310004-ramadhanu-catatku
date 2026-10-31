<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
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

        $category = $this->route('category');

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categories', 'name')
                    ->where('user_id', $user->id)
                    // The same name may exist once per type, matching the schema's
                    // unique (user_id, name, type) index.
                    ->where('type', $this->enum('type', TransactionType::class)?->value)
                    ->ignore($category instanceof Category ? $category->id : null),
            ],
            'type' => ['required', Rule::enum(TransactionType::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.unique' => 'Kamu sudah punya kategori dengan nama dan tipe ini.',
            'type.enum' => 'Tipe kategori harus pemasukan atau pengeluaran.',
        ];
    }
}
