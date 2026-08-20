<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('unit'); // e.g., pill, box, bottle
            $table->decimal('price', 12, 2); 
            $table->unsignedInteger('stock')->default(0); // Ensures value is always >= 0 at the database level
            $table->boolean('is_active')->default(true);
            $table->softDeletes(); // Implements soft delete functionality
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};