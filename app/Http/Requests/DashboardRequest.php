<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DashboardRequest extends FormRequest
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
        return [
            'month' => ['nullable', 'date_format:Y-m'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'month.date_format' => 'Format bulan harus TTTT-BB, misalnya 2026-08.',
        ];
    }

    public function month(): string
    {
        $validated = $this->validated();

        return isset($validated['month']) ? (string) $validated['month'] : now()->format('Y-m');
    }
}
