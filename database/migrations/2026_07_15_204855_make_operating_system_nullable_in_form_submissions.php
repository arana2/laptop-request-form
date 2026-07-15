<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_submissions', function (Blueprint $table) {
            // OS is now implied by brand selection — no longer a standalone required field
            $table->string('operating_system')->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('form_submissions', function (Blueprint $table) {
            $table->string('operating_system')->nullable(false)->change();
        });
    }
};