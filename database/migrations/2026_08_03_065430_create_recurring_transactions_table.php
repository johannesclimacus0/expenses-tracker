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
        Schema::create('recurring_transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['income', 'expense']);
            $table->decimal('amount', 12);
            $table->string('description')->nullable();
            $table->string('period');
            $table->dateTime('starts_at');
            $table->dateTime('next_run_at');
            $table->dateTime('last_run_at')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['user_id', 'is_active', 'next_run_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_transactions');
    }
};
