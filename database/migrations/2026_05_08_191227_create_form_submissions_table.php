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
        /**
         * Create the form_submissions table
         * 
         * This is the main table that stores:
         * - user input from the form
         * - AI-generated recommendations
         * - basic workflow status (pending → reviewed → sent)
         */
        Schema::create('form_submissions', function (Blueprint $table) {

            /**
             * Primary key (UUID instead of auto-increment)
             * 
             * Useful for:
             * - API usage
             * - better uniqueness across systems
             * - avoiding predictable IDs
             */    
            $table->uuid('id')->primary();

            /**
             * Requester Information
             * 
             * The person submitting the form
             */
            $table->string('requester_name');
            $table->string('requester_email');


            /**
             * Recipient Information
             * 
             * If the request is for someone else:
             * - is_for_self = false
             * - recipient fields will be populated
             */
            $table->boolean('is_for_self')->default(true);
            $table->string('recipient_name')->nullable();
            $table->string('recipient_email')->nullable();

            /**
             * Core Request Details
             * 
             * These drive the AI recommendation logic
             */
            $table->enum('request_type', ['laptop', 'desktop']);
            $table->enum('budget_range', [
                'under_1000',
                '1000_1499',
                '1500_1999',
                '2000_plus'
            ]);

            /**
             * Usage + Preferences (stored as JSON for flexibility)
             * 
             * Example:
             * usage = ["standard", "advanced"]
             * brands = ["lenovo", "dell"]
             * accessories = ["mouse", "dock"]
             * 
             * Using JSON avoids needing extra tables for v1
             */
            $table->json('usage')->nullable();
            $table->json('brands')->nullable();
            $table->json('accessories')->nullable();
            $table->json('ai_response')->nullable();

            /**
             * "Other" text inputs
             * 
             * Captures any custom input when user selects "Other"
             */
            $table->text('usage_other')->nullable();
            $table->string('brand_other')->nullable();
            $table->text('accessories_other')->nullable();

            /**
             * System Preferences
             * 
             * OS is required, but "other" allows flexibility
             */
            $table->string('operating_system');
            $table->string('os_other')->nullable();

            /**
             * Delivery Information
             * 
             * Optional but used for planning and prioritization
             */
            $table->date('delivery_date')->nullable();

            /**
             * Additional Notes
             * 
             * Free-text field for anything not captured elsewhere
             */
            $table->text('additional_info')->nullable();

            /**
             * AI Response (stored as JSON)
             * 
             * Contains:
             * - 3 recommended computers
             * - reasons
             * - purchase URLs
             * - accessories
             * - summary
             * 
             * Stored as JSON so structure can evolve without schema changes
             */
            $table->json('ai_response')->nullable();

            /**
             * Workflow Status
             * 
             * pending  = just submitted, AI generated
             * reviewed = you have reviewed internally
             * sent     = shared with requester
             */
            $table->enum('status', ['pending', 'reviewed', 'sent'])->default('pending');

            /**
             * Laravel timestamps
             * 
             * created_at → when submission was created
             * updated_at → when submission was last updated
             */
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_submissions');
    }
};
