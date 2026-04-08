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
            $table->datetime('agree_time')->nullable()->after('last_name');
            $table->string('user_ip')->nullable()->after('agree_time');
            $table->integer('terms_agree_check')->nullable()->after('user_ip');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn('agree_time');
            $table->dropColumn('user_ip');
            $table->dropColumn('terms_agree_check');
        });
    }
};
