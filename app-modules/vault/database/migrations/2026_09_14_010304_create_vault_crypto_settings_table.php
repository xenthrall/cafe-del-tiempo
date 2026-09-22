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
        Schema::create('vault_crypto_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->restrictOnDelete();
            $table->text('key_salt');
            $table->unsignedInteger('kdf_memory_cost');
            $table->unsignedInteger('kdf_iterations');
            $table->unsignedInteger('kdf_parallelism');
            $table->unsignedSmallInteger('protocol_version')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vault_crypto_settings');
    }
};
