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
        // Rename 'name' to 'first_name' only if 'name' exists and 'first_name' does not
        if (Schema::hasColumn('users', 'name') && !Schema::hasColumn('users', 'first_name')) {
            Schema::table('users', function (Blueprint $table) {
                $table->renameColumn('name', 'first_name');
            });
        }

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'last_name')) {
                $after = Schema::hasColumn('users', 'first_name') ? 'first_name' : null;
                $col = $table->string('last_name')->nullable();
                if ($after) $col->after($after);
            }
            if (!Schema::hasColumn('users', 'artist_name')) {
                $after = Schema::hasColumn('users', 'last_name') ? 'last_name' : null;
                $col = $table->string('artist_name')->nullable();
                if ($after) $col->after($after);
            }
            if (!Schema::hasColumn('users', 'phone_number')) {
                $after = Schema::hasColumn('users', 'artist_name') ? 'artist_name' : null;
                $col = $table->string('phone_number')->nullable();
                if ($after) $col->after($after);
            }
            if (!Schema::hasColumn('users', 'name_of_shop')) {
                $after = Schema::hasColumn('users', 'phone_number') ? 'phone_number' : null;
                $col = $table->string('name_of_shop')->nullable();
                if ($after) $col->after($after);
            }
            if (!Schema::hasColumn('users', 'street_address')) {
                $after = Schema::hasColumn('users', 'password') ? 'password' : null;
                $col = $table->string('street_address')->nullable();
                if ($after) $col->after($after);
            }
            if (!Schema::hasColumn('users', 'city')) {
                $after = Schema::hasColumn('users', 'street_address') ? 'street_address' : null;
                $col = $table->string('city')->nullable();
                if ($after) $col->after($after);
            }
            if (!Schema::hasColumn('users', 'state')) {
                $after = Schema::hasColumn('users', 'city') ? 'city' : null;
                $col = $table->string('state')->nullable();
                if ($after) $col->after($after);
            }
            if (!Schema::hasColumn('users', 'zip')) {
                $after = Schema::hasColumn('users', 'state') ? 'state' : null;
                $col = $table->integer('zip')->nullable();
                if ($after) $col->after($after);
            }
            if (!Schema::hasColumn('users', 'drivers_license')) {
                $after = Schema::hasColumn('users', 'zip') ? 'zip' : null;
                $col = $table->string('drivers_license')->nullable();
                if ($after) $col->after($after);
            }
            if (!Schema::hasColumn('users', 'selfie_photo')) {
                $after = Schema::hasColumn('users', 'drivers_license') ? 'drivers_license' : null;
                $col = $table->string('selfie_photo')->nullable();
                if ($after) $col->after($after);
            }
            if (!Schema::hasColumn('users', 'user_type')) {
                $after = Schema::hasColumn('users', 'selfie_photo') ? 'selfie_photo' : null;
                $col = $table->integer('user_type')->nullable();
                if ($after) $col->after($after);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rename 'first_name' back to 'name' only if 'first_name' exists and 'name' does not
        if (Schema::hasColumn('users', 'first_name') && !Schema::hasColumn('users', 'name')) {
            Schema::table('users', function (Blueprint $table) {
                $table->renameColumn('first_name', 'name');
            });
        }

        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'last_name', 'artist_name', 'phone_number', 'name_of_shop',
                'street_address', 'city', 'state', 'zip',
                'drivers_license', 'selfie_photo', 'user_type',
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
