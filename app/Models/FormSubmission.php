<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FormSubmission extends Model
{
    use HasFactory;

    /**
     * Use UUID instead of auto-incrementing integer IDs
     * 
     * - keyType = string --> tells Laravel the primary key is a string
     * - incrementing = false --> disables auto-increment
     */
    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * Mass assignable attributes
     * 
     * These are the fields we allow to be filled using:
     * FormSubmission::create($data);
     * 
     * Keeps things secure by preventing unexpected fields from being written
     */
    protected $fillable = [
        'id',

        // Requester info
        'requester_name',
        'requester_email',

        // Recipient info (if not for self)
        'is_for_self',
        'recipient_name',
        'recipient_email',

        // Core request details
        'request_type',
        'budget_range',

        // Multi-select fields stored as JSON
        'usage_type',
        'brands',
        'accessories',

        // "Other" text inputs
        'usage_other',
        'brand_other',
        'accessories_other',

        // System preferences
        'operating_system',
        'portability',
        'os_other',

        // Delivery date + notes
        'delivery_date',
        'additional_info',

        // AI response + workflow status
        'ai_response',
        'status'
    ];

    /**
     * Attribute casting
     * 
     * Automatically conver values when reading/writing:
     * 
     * - JSON --> PHP arrays (no need to json_decode manually)
     * - date --> Carbon instance (easy date handling)
     */
    protected $casts = [
        'brands' => 'array',
        'accessories' => 'array',
        'ai_response' => 'array',
        'delivery_date' => 'date',
    ];
}
