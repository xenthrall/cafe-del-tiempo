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
        Schema::create('finance_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('type');
            $table->foreignId('account_id')->nullable()->constrained('finance_accounts')->restrictOnDelete();
            $table->foreignId('from_account_id')->nullable()->constrained('finance_accounts')->restrictOnDelete();
            $table->foreignId('to_account_id')->nullable()->constrained('finance_accounts')->restrictOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('finance_categories')->restrictOnDelete();
            $table->foreignId('financial_context_id')->nullable()->constrained('finance_financial_contexts')->restrictOnDelete();
            $table->decimal('amount', 14, 2);
            $table->date('date');
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance_movements');
    }
};
