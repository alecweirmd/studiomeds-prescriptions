<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pattern A data store for the charge-but-no-record recovery flow (Audit Finding #5).
 *
 * A row is written ONLY after Authorize.net confirms the charge (verified
 * transaction_id in hand), strictly outside the main DB transaction so it
 * survives a post-charge rollback. The transaction_id NOT NULL column is the
 * data-layer enforcement of the hard constraint: a submission without a verified
 * charge can never be preserved here, and therefore can never surface as an alert.
 *
 * recovery_status lifecycle:
 *   open      → post-charge work failed; awaiting admin recovery via Alerts tab
 *   resolved  → either auto-resolved on a clean commit, or recovered by an admin
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pending_submissions', function (Blueprint $table) {
            $table->id();

            // The existing form-start stub patients row (created by ajaxStartUser).
            // Recovery refills this same row rather than inserting a duplicate.
            $table->unsignedBigInteger('patient_id')->nullable();

            // Data-layer enforcement of the hard constraint — never nullable.
            $table->string('transaction_id');
            $table->decimal('charged_amount', 8, 2)->nullable();
            $table->timestamp('charged_at')->nullable();

            // Preserved intake fields (everything needed to recreate the patient).
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('date_of_birth')->nullable();
            $table->string('street_address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip')->nullable();
            $table->string('procedure_type')->nullable();
            $table->unsignedBigInteger('artist_id')->nullable();
            $table->string('artist_name')->nullable();
            $table->string('name_of_shop')->nullable();

            // All medical + procedure-specific CQI answers.
            $table->json('cqi_answers')->nullable();

            // Preserved ID-image paths (stored to disk before the transaction so
            // they survive rollback).
            $table->string('drivers_license_path')->nullable();
            $table->string('selfie_path')->nullable();

            $table->string('verification_method')->nullable();
            $table->string('didit_session_id')->nullable();

            $table->string('recovery_status')->default('open');
            $table->timestamp('resolved_at')->nullable();

            $table->timestamps();

            $table->index('recovery_status');
            $table->index('transaction_id');

            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_submissions');
    }
};
