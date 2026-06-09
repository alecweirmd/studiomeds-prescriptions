<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients_cqi', function (Blueprint $table) {
            // Issuance record of the prescription PDFs persisted at approval time.
            // Stores an array of {key, name, path} entries (1 doc for lip/eyeliner, 2 for default).
            $table->json('prescription_paths')->nullable()->after('status');
            $table->timestamp('prescription_issued_at')->nullable()->after('prescription_paths');
        });
    }

    public function down(): void
    {
        Schema::table('patients_cqi', function (Blueprint $table) {
            $table->dropColumn(['prescription_paths', 'prescription_issued_at']);
        });
    }
};
