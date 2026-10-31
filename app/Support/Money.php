<?php

declare(strict_types=1);

namespace App\Support;

final class Money
{
    /**
     * Amounts are whole rupiah, so the only formatting needed is the thousands
     * separator: 1250000 becomes "Rp 1.250.000".
     */
    public static function rupiah(int $amount): string
    {
        return 'Rp '.number_format($amount, 0, ',', '.');
    }
}
