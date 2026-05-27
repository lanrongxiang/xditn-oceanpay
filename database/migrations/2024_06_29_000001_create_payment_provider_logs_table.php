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
        Schema::create('payment_provider_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payment_provider_id')->default(0);
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index('payment_provider_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_provider_logs');
    }
};
