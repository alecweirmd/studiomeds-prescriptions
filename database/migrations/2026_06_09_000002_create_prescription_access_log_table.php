<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescription_access_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_user_id')->nullable(); // acting admin (users.id)
            $table->unsignedBigInteger('patient_id');
            $table->string('action');                 // view | download | resend
            $table->string('document')->nullable();   // zensa | bactine | lidocaine (null for resend)
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index('patient_id');
            $table->index('admin_user_id');

            $table->foreign('admin_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescription_access_log');
    }
};
