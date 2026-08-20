<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('examinations', function (Blueprint $table) {
            $table->id();
            
            // 1-to-1 relationship: Each appointment has exactly one examination record
            $table->foreignId('appointment_id')->unique()->constrained('appointments')->cascadeOnDelete();
            
            // Foreign keys linking to doctors and patients
            $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            
            // Examination details
            $table->text('diagnosis');
            $table->text('notes')->nullable();
            $table->timestamp('examined_at');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('examinations');
    }
};