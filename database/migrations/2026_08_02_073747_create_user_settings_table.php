<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('currency', 3)->default('RUB');
            $table->string('dashboard_period')->default('month');
            $table->unsignedTinyInteger('transactions_per_page')->default(10);
            $table->unsignedTinyInteger('budget_warning_percent')->default(80);
            $table->boolean('show_cents')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_settings');
    }
};
