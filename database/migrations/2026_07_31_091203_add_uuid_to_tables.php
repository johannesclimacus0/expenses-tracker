<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['categories', 'transactions', 'budgets'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->uuid('uuid')->unique()->after('id');
            });
        }
    }

    public function down(): void
    {
        foreach (['budgets', 'transactions', 'categories'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropUnique(['uuid']);
                $table->dropColumn('uuid');
            });
        }
    }
};
