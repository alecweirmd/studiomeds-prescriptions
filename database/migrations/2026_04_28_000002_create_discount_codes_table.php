<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discount_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code_string')->unique();
            $table->string('partner_name');
            $table->enum('discount_type', ['free', 'fixed_dollar_off', 'percent_off']);
            $table->decimal('discount_value', 8, 2)->nullable();
            $table->integer('usage_cap');
            $table->integer('usage_count')->default(0);
            $table->date('expiration_date');
            $table->enum('status', ['active', 'exhausted', 'expired', 'paused'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discount_codes');
    }
};
