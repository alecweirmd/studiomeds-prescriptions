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
            if (!Schema::hasColumn('users', 'last_name')) {
                $table->string('last_name')->nullable()->after('first_name');
            }
            if (!Schema::hasColumn('users', 'artist_name')) {
                $table->string('artist_name')->nullable()->after('last_name');
            }
            if (!Schema::hasColumn('users', 'phone_number')) {
                $table->string('phone_number')->nullable()->after('artist_name');
            }
            if (!Schema::hasColumn('users', 'name_of_shop')) {
                $table->string('name_of_shop')->nullable()->after('phone_number');
            }
            if (!Schema::hasColumn('users', 'street_address')) {
                $table->string('street_address')->nullable()->after('password');
            }
            if (!Schema::hasColumn('users', 'city')) {
                $table->string('city')->nullable()->after('street_address');
            }
            if (!Schema::hasColumn('users', 'state')) {
                $table->string('state')->nullable()->after('city');
            }
            if (!Schema::hasColumn('users', 'zip')) {
                $table->integer('zip')->nullable()->after('state');
            }
            if (!Schema::hasColumn('users', 'drivers_license')) {
                $table->string('drivers_license')->nullable()->after('zip');
            }
            if (!Schema::hasColumn('users', 'selfie_photo')) {
                $table->string('selfie_photo')->nullable()->after('drivers_license');
            }
            if (!Schema::hasColumn('users', 'user_type')) {
                $table->integer('user_type')->nullable()->after('patient_photo');
            }
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
