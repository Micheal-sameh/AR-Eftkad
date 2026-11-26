<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Mass attendance is optional when recording a visit.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE eftkads MODIFY mass_attendence INT NULL');
    }

    public function down(): void
    {
        DB::statement('UPDATE eftkads SET mass_attendence = 1 WHERE mass_attendence IS NULL');
        DB::statement('ALTER TABLE eftkads MODIFY mass_attendence INT NOT NULL');
    }
};
