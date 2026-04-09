<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_acknowledgements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id')->nullable();
            $table->string('session_id');
            $table->string('ip_address');
            $table->json('triggered_questions');
            $table->timestamp('acknowledged_at');
            $table->string('pdf_path')->nullable();
            $table->timestamps();

            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_acknowledgements');
    }
};
