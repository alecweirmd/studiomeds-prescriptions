<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discount_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discount_code_id')->constrained('discount_codes')->cascadeOnDelete();
            $table->foreignId('patient_id')->nullable()->constrained('patients')->nullOnDelete();
            $table->string('session_id')->nullable();
            $table->enum('attempt_outcome', ['success', 'failed_expired', 'failed_exhausted', 'failed_invalid']);
            $table->decimal('discount_amount_applied', 8, 2)->nullable();
            $table->timestamp('redeemed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discount_redemptions');
    }
};
