<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('eftkads', function (Blueprint $table) {
            $table->id();
            $table->string('membership_code');
            $table->timestamp('date');
            $table->text('correspondence_address')->nullable();
            $table->integer('mass_attendence');
            $table->json('needs')->nullable();
            $table->json('communication_means')->nullable();
            $table->integer('attend_meetings')->nullable();
            $table->string('need_eftkad_from_meeting')->nullable();
            $table->string('father_confession')->nullable();
            $table->string('mother_confession')->nullable();
            $table->string('children_confession')->nullable();
            $table->string('need_eftkad_by_father');
            $table->text('location')->nullable();
            $table->text('general_notes')->nullable();
            $table->string('father_membership_code');
            $table->string('servant_membership_code');
            $table->intger('type');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eftkads');
    }
};
