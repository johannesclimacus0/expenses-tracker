<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasIndex('budgets', 'budgets_user_id_index')) {
            Schema::table('budgets', function (Blueprint $table) {
                $table->index('user_id');
            });
        }

        if (! Schema::hasIndex('budgets', 'budgets_category_id_index')) {
            Schema::table('budgets', function (Blueprint $table) {
                $table->index('category_id');
            });
        }

        if (Schema::hasIndex('budgets', 'budgets_user_id_category_id_month_unique')) {
            Schema::table('budgets', function (Blueprint $table) {
                $table->dropUnique(['user_id', 'category_id', 'month']);
            });
        }

        if (! Schema::hasIndex('budgets', 'budgets_user_category_scope_month_unique')) {
            DB::statement(<<<'SQL'
                CREATE UNIQUE INDEX budgets_user_category_scope_month_unique
                ON budgets (user_id, (COALESCE(category_id, 0)), month)
            SQL);
        }
    }

    public function down(): void
    {
        DB::statement('DROP INDEX budgets_user_category_scope_month_unique ON budgets');

        Schema::table('budgets', function (Blueprint $table) {
            $table->unique(['user_id', 'category_id', 'month']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['category_id']);
        });
    }
};
