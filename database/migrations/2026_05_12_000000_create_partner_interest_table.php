<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partner_interest', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('shop_name');
            $table->string('shop_location');
            $table->string('procedure_focus');
            $table->string('source_page');
            $table->string('social_handle')->nullable();
            $table->text('how_did_you_hear')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_interest');
    }
};
