<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_from_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('user_to_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->enum('type', ['deposit', 'transfer', 'receive', 'reversal']);
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'confirmed', 'reversed'])->default('pending');
            $table->foreignId('reference_id')
                ->nullable()
                ->constrained('transactions')
                ->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
