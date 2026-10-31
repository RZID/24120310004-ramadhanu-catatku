<?php

declare(strict_types=1);

namespace App\Enums;

enum TransactionType: string
{
    case Income = 'income';
    case Expense = 'expense';

    public function label(): string
    {
        return match ($this) {
            self::Income => 'Pemasukan',
            self::Expense => 'Pengeluaran',
        };
    }

    /**
     * Money is never distinguished by colour alone, so every place that shows a
     * type also shows this arrow.
     */
    public function icon(): string
    {
        return match ($this) {
            self::Income => 'arrow-up',
            self::Expense => 'arrow-down',
        };
    }
}
