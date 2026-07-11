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
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('document_type', 5)->default('CC');
            $table->unsignedBigInteger('dni')->unique();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('nationality')->nullable()->default('Colombiana');
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->foreignId('insurer_id')->nullable()->constrained('insurers')->onDelete('restrict');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
