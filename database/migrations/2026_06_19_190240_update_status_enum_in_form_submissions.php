<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE form_submissions
            MODIFY COLUMN status ENUM(
                'pending',
                'processing',
                'completed',
                'failed',
                'reviewed',
                'sent'
            )
            NOT NULL DEFAULT 'pending'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE form_submissions
            MODIFY COLUMN status ENUM(
                'pending',
                'reviewed',
                'sent'
            )
            NOT NULL DEFAULT 'pending'
        ");
    }
};