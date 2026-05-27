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
        Schema::create('payment_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code');
            $table->longText('deposit_config')->nullable();
            $table->longText('deposit_secret_config')->nullable();
            $table->longText('withdrawal_config')->nullable();
            $table->longText('withdrawal_secret_config')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->unique('code');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_providers');
    }
};
