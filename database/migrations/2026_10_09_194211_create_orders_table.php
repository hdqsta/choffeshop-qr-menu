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
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->string('public_token', 64)->unique();
            $table->string('order_number', 25)->unique();
            $table->foreignId('table_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('total_amount');
            $table->string('payment_status')->default('pending');
            $table->string('order_status')->default('pending_payment');
            $table->boolean('needs_review')->default(false);
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('receipt_printed_at')->nullable();
            $table->timestamps();

            $table->index(['payment_status', 'order_status']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
