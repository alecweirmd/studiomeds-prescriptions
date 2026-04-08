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
        //
        Schema::create('patients_cqi', function (Blueprint $table) {
            $table->id();
            $table->integer('artist_id');
            $table->integer('status');
            $table->integer('patient_id');
            $table->integer('lidocaine');
            $table->integer('bactine');
            $table->integer('broken_skin');
            $table->integer('eczema');
            $table->integer('heart_rhythm');
            $table->integer('liver_disease');
            $table->integer('seizures');
            $table->integer('pregnant');
            $table->integer('antiarrhythmic');
            $table->integer('seizure_meds');
            $table->integer('fainted');
            $table->integer('methemoglobinemia');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('patients_cqi');
    }
};
