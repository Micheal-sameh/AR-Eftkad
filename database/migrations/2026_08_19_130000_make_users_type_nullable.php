<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Users auto-created from Avarewase SSO have no Father/Servant equivalent
 * in the SSO identity, so `type` must stay unset until an admin assigns it.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE users MODIFY type INT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE users MODIFY type INT NOT NULL');
    }
};
