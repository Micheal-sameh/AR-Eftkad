<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * A visit needs a father or a servant, not necessarily both.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE eftkads MODIFY father_membership_code VARCHAR(255) NULL');
        DB::statement('ALTER TABLE eftkads MODIFY servant_membership_code VARCHAR(255) NULL');
    }

    public function down(): void
    {
        DB::statement("UPDATE eftkads SET father_membership_code = '' WHERE father_membership_code IS NULL");
        DB::statement("UPDATE eftkads SET servant_membership_code = '' WHERE servant_membership_code IS NULL");
        DB::statement('ALTER TABLE eftkads MODIFY father_membership_code VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE eftkads MODIFY servant_membership_code VARCHAR(255) NOT NULL');
    }
};
