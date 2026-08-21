<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id(); // PK
            
            // Foreign key to invoices table
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            
            // Amount with decimal precision for currency
            $table->decimal('amount', 12, 2);
            
            // Payment method (paypal or visa)
            $table->enum('method', ['paypal', 'visa']);
            
            // Payment status (pending, completed, failed, cancelled)
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])->default('pending');
            
            // Payment gateway provider (default paypal)
            $table->string('provider')->default('paypal');
            
            // Provider IDs (Nullable, recorded after interaction with Third-Party API)
            $table->string('provider_order_id')->nullable()->comment('PayPal Order ID');
            $table->string('provider_capture_id')->nullable()->comment('PayPal Capture ID upon success');
            
            // Timestamps for recording success time
            $table->timestamp('paid_at')->nullable()->comment('Time of successful capture');
            
            // Optional notes
            $table->string('note')->nullable();
            
            $table->timestamps();

            // Create Indexes for faster querying
            $table->index('invoice_id');
            $table->index('provider_order_id');
        });

        // Add a database-level CHECK constraint to ensure amount is always greater than 0
        DB::statement('ALTER TABLE payments ADD CONSTRAINT check_amount_positive CHECK (amount > 0)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};