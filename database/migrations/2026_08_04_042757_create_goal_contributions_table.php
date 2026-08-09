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
        Schema::create('goal_contributions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('goal_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['deposit', 'withdrawal']);
            $table->decimal('amount', 12, 2)->unsigned();
            $table->dateTime('contributed_at');
            $table->string('note')->nullable();
            $table->timestamps();

            $table->index(['goal_id', 'contributed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goal_contributions');
    }
};
