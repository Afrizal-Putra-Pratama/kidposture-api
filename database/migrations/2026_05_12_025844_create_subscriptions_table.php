<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('plan')->default('premium'); // premium, basic, dll
            $table->string('status')->default('pending'); // pending, active, expired, failed
            $table->string('midtrans_order_id')->unique();
            $table->string('midtrans_transaction_id')->nullable();
            $table->string('midtrans_payment_type')->nullable();
            $table->decimal('amount', 10, 2)->default(50000);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->json('midtrans_payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};