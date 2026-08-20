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
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            
            // Foreign key referencing examinations table (1-to-1 relationship)
            $table->foreignId('examination_id')
                  ->unique()
                  ->constrained('examinations')
                  ->onDelete('cascade');
                  
            // Foreign key referencing doctors table
            $table->foreignId('doctor_id')
                  ->constrained('doctors')
                  ->onDelete('cascade');
                  
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};