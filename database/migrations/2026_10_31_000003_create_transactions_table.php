<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wallet_id')->constrained()->restrictOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('type', 10);
            $table->unsignedBigInteger('amount');
            $table->date('transacted_on');
            $table->string('note', 255)->nullable();
            $table->timestamps();

            // The dashboard and the transaction list always filter by owner and
            // then by date range; wallet_id and category_id already have an
            // index from their foreign keys.
            $table->index(['user_id', 'transacted_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
