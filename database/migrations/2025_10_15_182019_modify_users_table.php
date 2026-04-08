<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Fresh base table columns (from 0001_01_01_000000_create_users_table.php):
     *   id, first_name, email, email_verified_at, password, remember_token, created_at, updated_at
     *
     * This migration adds the remaining user profile columns and handles the
     * legacy case where the base table still had 'name' instead of 'first_name'.
     *
     * Every operation is guarded so the migration is safe on any state of the DB.
     */
    public function up(): void
    {
        // Rename 'name' → 'first_name' only on old databases that haven't been updated yet.
        // On a fresh install the base migration already creates 'first_name', so this is skipped.
        if (Schema::hasColumn('users', 'name') && !Schema::hasColumn('users', 'first_name')) {
            Schema::table('users', function (Blueprint $table) {
                $table->renameColumn('name', 'first_name');
            });
        }

        // Define every column we want to add: name → desired 'after' anchor.
        // Anchors may be base-table columns OR columns added earlier in this same list —
        // MySQL processes ADD COLUMN ... AFTER ... sequentially within one ALTER TABLE,
        // so referencing a column that is being added in the same statement is safe.
        $columns = [
            'last_name'      => ['type' => 'string',  'after' => 'first_name'],
            'artist_name'    => ['type' => 'string',  'after' => 'last_name'],
            'phone_number'   => ['type' => 'string',  'after' => 'artist_name'],
            'name_of_shop'   => ['type' => 'string',  'after' => 'phone_number'],
            'street_address' => ['type' => 'string',  'after' => 'password'],
            'city'           => ['type' => 'string',  'after' => 'street_address'],
            'state'          => ['type' => 'string',  'after' => 'city'],
            'zip'            => ['type' => 'integer', 'after' => 'state'],
            'drivers_license'=> ['type' => 'string',  'after' => 'zip'],
            'selfie_photo'   => ['type' => 'string',  'after' => 'drivers_license'],
            'user_type'      => ['type' => 'integer', 'after' => 'selfie_photo'],
        ];

        // Build the set of columns that will exist after this migration:
        // either already in the DB or being added right now.
        $willExist = array_filter(
            array_keys($columns),
            fn($col) => Schema::hasColumn('users', $col)
        );
        // Also include all base-table columns as valid anchors.
        $baseColumns = ['id', 'first_name', 'email', 'email_verified_at', 'password', 'remember_token', 'created_at', 'updated_at'];
        $validAnchors = array_merge($baseColumns, array_values($willExist));

        Schema::table('users', function (Blueprint $table) use ($columns, &$validAnchors) {
            foreach ($columns as $col => $def) {
                if (Schema::hasColumn('users', $col)) {
                    // Column already exists — still a valid anchor for subsequent columns.
                    if (!in_array($col, $validAnchors)) {
                        $validAnchors[] = $col;
                    }
                    continue;
                }

                // Add the column, using 'after' only when the anchor is guaranteed to exist.
                $colDef = $def['type'] === 'integer'
                    ? $table->integer($col)->nullable()
                    : $table->string($col)->nullable();

                if (in_array($def['after'], $validAnchors)) {
                    $colDef->after($def['after']);
                }

                // This column is now being added — treat it as a valid anchor for subsequent ones.
                $validAnchors[] = $col;
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rename 'first_name' back to 'name' only if it was renamed by this migration.
        if (Schema::hasColumn('users', 'first_name') && !Schema::hasColumn('users', 'name')) {
            Schema::table('users', function (Blueprint $table) {
                $table->renameColumn('first_name', 'name');
            });
        }

        $added = [
            'last_name', 'artist_name', 'phone_number', 'name_of_shop',
            'street_address', 'city', 'state', 'zip',
            'drivers_license', 'selfie_photo', 'user_type',
        ];

        Schema::table('users', function (Blueprint $table) use ($added) {
            foreach ($added as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
