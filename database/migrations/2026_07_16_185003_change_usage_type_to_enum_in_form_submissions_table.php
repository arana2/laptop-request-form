<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Wipe existing rows since old usage_type JSON data isn't compatible
        // (skip this line if you'd rather keep other columns' data and only null out usage_type)
        DB::table('form_submissions')->truncate();

        Schema::table('form_submissions', function (Blueprint $table) {
            $table->dropColumn('usage_type');
        });

        Schema::table('form_submissions', function (Blueprint $table) {
            $table->enum('usage_type', ['standard', 'advanced'])
                ->nullable()
                ->after('budget_range');
        });
    }

    public function down(): void
    {
        Schema::table('form_submissions', function (Blueprint $table) {
            $table->dropColumn('usage_type');
        });

        Schema::table('form_submissions', function (Blueprint $table) {
            $table->longText('usage_type')->nullable()->after('budget_range');
        });

        DB::statement('ALTER TABLE form_submissions ADD CONSTRAINT `form_submissions.usage` CHECK (json_valid(`usage_type`))');
    }
};