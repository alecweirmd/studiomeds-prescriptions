<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_starts', function (Blueprint $table) {
            $table->timestamp('dismissed_at')->nullable()->after('contacted_at');
        });
    }

    public function down(): void
    {
        Schema::table('form_starts', function (Blueprint $table) {
            $table->dropColumn('dismissed_at');
        });
    }
};
