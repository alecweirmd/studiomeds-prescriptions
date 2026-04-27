<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_starts', function (Blueprint $table) {
            $table->timestamp('contacted_at')->nullable()->after('abandoned_at');
        });
    }

    public function down(): void
    {
        Schema::table('form_starts', function (Blueprint $table) {
            $table->dropColumn('contacted_at');
        });
    }
};
