<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'categories',
        'transactions',
        'budgets',
        'recurring_transactions',
        'goals',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropForeign(['user_id']);
                $table->dropForeign(['account_id']);
            });

            Schema::table($tableName, function (Blueprint $table): void {
                $table->unsignedBigInteger('user_id')->nullable()->change();
                $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
                $table->foreign('account_id')->references('id')->on('accounts')->cascadeOnDelete();
            });
        }

        Schema::table('categories', function (Blueprint $table): void {
            $table->index('user_id');
            $table->dropUnique(['user_id', 'name', 'type']);
            $table->unique(['account_id', 'type', 'name']);
        });

        $this->dropBudgetIndex('budgets_user_category_scope_month_unique');

        DB::statement(
            'CREATE UNIQUE INDEX budgets_account_category_scope_month_unique '
            . 'ON budgets (account_id, (COALESCE(category_id, 0)), month)',
        );
    }

    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropForeign(['user_id']);
                $table->dropForeign(['account_id']);
            });
        }

        $this->dropBudgetIndex('budgets_account_category_scope_month_unique');

        DB::statement(
            'CREATE UNIQUE INDEX budgets_user_category_scope_month_unique '
            . 'ON budgets (user_id, (COALESCE(category_id, 0)), month)',
        );

        Schema::table('categories', function (Blueprint $table): void {
            $table->dropUnique(['account_id', 'type', 'name']);
            $table->unique(['user_id', 'name', 'type']);
            $table->dropIndex(['user_id']);
        });

        foreach (array_reverse($this->tables) as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->unsignedBigInteger('user_id')->nullable(false)->change();
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                $table->foreign('account_id')->references('id')->on('accounts')->restrictOnDelete();
            });
        }
    }

    private function dropBudgetIndex(string $name): void
    {
        DB::statement("DROP INDEX {$name} ON budgets");
    }
};
