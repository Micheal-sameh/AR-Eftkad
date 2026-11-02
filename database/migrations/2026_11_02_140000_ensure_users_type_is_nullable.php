<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Users created from Avarewase SSO have no Father/Servant type until an admin assigns one, so
 * `users.type` must be nullable. 2026_08_19_130000_make_users_type_nullable already does this, but
 * with MySQL-only syntax; this migration makes sure it holds on every supported driver without
 * editing that (already applied) migration.
 *
 * Safe to re-run: on MySQL the column is already nullable, so the statement changes nothing.
 * SQLite cannot alter a column in place, so it is left alone there.
 */
return new class extends Migration
{
    public function up(): void
    {
        match (DB::getDriverName()) {
            'mysql', 'mariadb' => DB::statement('ALTER TABLE users MODIFY type INT NULL'),
            'pgsql' => DB::statement('ALTER TABLE users ALTER COLUMN type DROP NOT NULL'),
            default => null,
        };
    }

    public function down(): void
    {
        // Intentionally empty: making the column NOT NULL again would break SSO-created users.
        // The earlier migration owns the original column definition and its own down().
    }
};
