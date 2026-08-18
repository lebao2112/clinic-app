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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            
            // FK to examinations, ensuring 1-1 relationship with unique()
            $table->foreignId('examination_id')
                  ->unique()
                  ->constrained('examinations')
                  ->onDelete('restrict'); 
            
            $table->string('invoice_code')->unique();
            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            
            $table->enum('status', ['unpaid', 'paid', 'cancelled'])
                  ->default('unpaid')
                  ->index();
            
            $table->timestamp('issued_at')->nullable(); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};