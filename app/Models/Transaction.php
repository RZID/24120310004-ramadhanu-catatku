<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TransactionType;
use Carbon\Carbon;
use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    /** @use HasFactory<TransactionFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = ['wallet_id', 'category_id', 'type', 'amount', 'transacted_on', 'note'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => TransactionType::class,
            'amount' => 'integer',
            'transacted_on' => 'date',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Wallet, $this>
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @param  Builder<$this>  $query
     */
    public function scopeForMonth(Builder $query, string $month): void
    {
        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();

        $query->whereBetween('transacted_on', [
            $start->toDateString(),
            $start->copy()->endOfMonth()->toDateString(),
        ]);
    }

    /**
     * @param  Builder<$this>  $query
     */
    public function scopeOfType(Builder $query, TransactionType $type): void
    {
        $query->where('type', $type->value);
    }
}
