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
        if (Schema::hasColumn('users', 'name') && !Schema::hasColumn('users', 'first_name')) {
            Schema::table('users', function (Blueprint $table) {
                $table->renameColumn('name', 'first_name');
            });
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('artist_name')->nullable()->after('last_name');
            $table->string('phone_number')->nullable()->after('artist_name');
            $table->string('name_of_shop')->nullable()->after('phone_number');
            $table->string('street_address')->nullable()->after('password');
            $table->string('city')->nullable()->after('street_address');
            $table->string('state')->nullable()->after('city');
            $table->integer('zip')->nullable()->after('state');
            $table->string('drivers_license')->nullable()->after('zip');
            $table->string('selfie_photo')->nullable()->after('drivers_license');
            $table->integer('user_type')->nullable()->after('patient_photo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('first_name', 'name');
            $table->dropColumn('last_name');
            $table->dropColumn('artist_name');
            $table->dropColumn('phone_number');
            $table->dropColumn('name_of_shop');
            $table->dropColumn('street_address');
            $table->dropColumn('city');
            $table->dropColumn('state');
            $table->dropColumn('zip');
            $table->dropColumn('drivers_license');
            $table->dropColumn('selfie_photo');
            $table->dropColumn('user_type');
        });
    }
};
