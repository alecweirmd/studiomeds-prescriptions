<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients_cqi', function (Blueprint $table) {
            $table->integer('lip_cold_sore_active')->nullable()->after('methemoglobinemia');
            $table->integer('eye_infection_active')->nullable()->after('lip_cold_sore_active');
            $table->integer('recent_eye_surgery')->nullable()->after('eye_infection_active');
            $table->integer('contacts_cannot_remove')->nullable()->after('recent_eye_surgery');
            $table->integer('severe_dry_eye')->nullable()->after('contacts_cannot_remove');
        });
    }

    public function down(): void
    {
        Schema::table('patients_cqi', function (Blueprint $table) {
            $table->dropColumn([
                'lip_cold_sore_active',
                'eye_infection_active',
                'recent_eye_surgery',
                'contacts_cannot_remove',
                'severe_dry_eye',
            ]);
        });
    }
};
