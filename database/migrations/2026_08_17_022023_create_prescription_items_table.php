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
        Schema::create('prescription_items', function (Blueprint $table) {
            $table->id();
            
            // Foreign key referencing prescriptions table
            $table->foreignId('prescription_id')
                  ->constrained('prescriptions')
                  ->onDelete('cascade');
                  
            // Foreign key referencing medicines table
            $table->foreignId('medicine_id')
                  ->constrained('medicines')
                  ->onDelete('cascade');
                  
            // Quantity must be a positive integer
            $table->unsignedInteger('quantity');
            $table->string('dosage');
            $table->text('usage_instruction')->nullable();
            $table->timestamps();

            // Ensure a medicine is added only once per prescription
            $table->unique(['prescription_id', 'medicine_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescription_items');
    }
};