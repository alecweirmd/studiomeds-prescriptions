<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('utm_visits', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->index();
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->timestamp('first_touch_at')->nullable();
            $table->timestamp('last_touch_at')->nullable();
            $table->boolean('completed')->default(false);
            $table->foreignId('patient_id')->nullable()->constrained('patients')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('utm_visits');
    }
};
