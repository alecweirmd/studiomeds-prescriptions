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
        Schema::table('patients', function (Blueprint $table) {
            $table->string('artist_name')->nullable()->after('artist_id');
            $table->string('email')->nullable()->after('last_name');
            $table->string('name_of_shop')->nullable()->after('artist_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn('artist_name');
            $table->dropColumn('name_of_shop');
            $table->dropColumn('email');
        });
    }
};
