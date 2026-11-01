<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eftkads', function (Blueprint $table) {
            $table->index('membership_code');
            $table->index('father_membership_code');
            $table->index('servant_membership_code');
            $table->index('date');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::table('eftkads', function (Blueprint $table) {
            $table->dropIndex(['membership_code']);
            $table->dropIndex(['father_membership_code']);
            $table->dropIndex(['servant_membership_code']);
            $table->dropIndex(['date']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['type']);
        });
    }
};
