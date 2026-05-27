<?php

use Xditn\Oceanpay\Enums\PaymentMethodStatus;
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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->integer('payment_provider_id')->default(0);
            $table->string('currency_code')->nullable();
            $table->longText('config')->nullable();
            $table->longText('secret_config')->nullable();
            $table->longText('extra')->nullable();
            $table->bigInteger('sort')->default(0);
            $table->json('deposit_options')->nullable();
            $table->string('status')->default(PaymentMethodStatus::active->value);
            $table->timestamps();

            $table->index('type');
            $table->index('payment_provider_id');
            $table->index('currency_code');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
