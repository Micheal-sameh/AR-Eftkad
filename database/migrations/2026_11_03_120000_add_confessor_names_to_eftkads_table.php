<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Name of the priest each family member confesses to (father, mother, children).
 * Only filled when the matching *_confession toggle is on.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eftkads', function (Blueprint $table) {
            $table->string('father_confessor')->nullable()->after('father_confession');
            $table->string('mother_confessor')->nullable()->after('mother_confession');
            $table->string('children_confessor')->nullable()->after('children_confession');
        });
    }

    public function down(): void
    {
        Schema::table('eftkads', function (Blueprint $table) {
            $table->dropColumn(['father_confessor', 'mother_confessor', 'children_confessor']);
        });
    }
};
