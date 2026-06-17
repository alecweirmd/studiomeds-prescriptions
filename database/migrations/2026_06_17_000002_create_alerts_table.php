<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Generalized polymorphic alerts index for the unified clinician Alerts tab.
 *
 * Thin by design: this table only records that something needs admin attention,
 * its type, and a polymorphic pointer to the typed payload that carries the
 * type-specific data (charge_no_record → pending_submissions). Future alert
 * types (e.g. email_delivery_failed in Fix 2) point alertable at their own
 * source model with no restructuring here.
 *
 * resolved_at is the authoritative resolution flag the tab queries on.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();

            $table->string('type'); // charge_no_record (later: email_delivery_failed, ...)
            $table->string('alertable_type');
            $table->unsignedBigInteger('alertable_id');

            $table->json('metadata')->nullable(); // type-specific display extras

            $table->timestamp('resolved_at')->nullable();
            $table->unsignedBigInteger('resolved_by_admin_user_id')->nullable();

            $table->timestamps();

            $table->index('type');
            $table->index('resolved_at');
            $table->index(['alertable_type', 'alertable_id']);

            $table->foreign('resolved_by_admin_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
