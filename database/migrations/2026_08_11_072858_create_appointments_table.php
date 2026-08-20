<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('doctors')->onDelete('cascade');
            
            $table->timestamp('scheduled_at');
            
            $table->enum('status', [
                'scheduled', 
                'confirmed', 
                'cancelled', 
                'completed'
            ])->default('scheduled');
            
            $table->string('reason')->nullable();
            $table->timestamps();
            $table->index(['doctor_id', 'scheduled_at']); 
            $table->index('status');
            
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};