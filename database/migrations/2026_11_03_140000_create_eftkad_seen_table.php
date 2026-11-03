<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Remembers which visits a person has already opened, so the home page can flag the ones that are
 * new to them (assigned to them or shared with them).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eftkad_seen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eftkad_id')->constrained('eftkads')->cascadeOnDelete();
            $table->string('membership_code');
            $table->timestamps();

            $table->unique(['eftkad_id', 'membership_code']);
            $table->index('membership_code');
        });

        // Visits that exist today are not "new" to the people already on them.
        foreach (['father_membership_code', 'servant_membership_code'] as $column) {
            DB::statement("INSERT IGNORE INTO eftkad_seen (eftkad_id, membership_code, created_at, updated_at)
                SELECT id, {$column}, NOW(), NOW() FROM eftkads WHERE {$column} IS NOT NULL");
        }
        DB::statement('INSERT IGNORE INTO eftkad_seen (eftkad_id, membership_code, created_at, updated_at)
            SELECT eftkad_id, membership_code, NOW(), NOW() FROM eftkad_shares');
    }

    public function down(): void
    {
        Schema::dropIfExists('eftkad_seen');
    }
};
