<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A visit can be shared with other fathers/servants: each row gives one person access to one visit.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eftkad_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eftkad_id')->constrained('eftkads')->cascadeOnDelete();
            $table->string('membership_code');
            $table->string('shared_by');
            $table->timestamps();

            $table->unique(['eftkad_id', 'membership_code']);
            $table->index('membership_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eftkad_shares');
    }
};
