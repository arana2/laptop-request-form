<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

/**
 * Validates incoming hardware request submissions before they ever
 * reach the controller. If validation fails, Laravel automatically
 * returns a 422 response with a JSON "errors" object — no manual
 * try/catch needed in the controller.
 */
class StoreSubmissionRequest extends FormRequest
{
    /**
     * Strip HTML from all free text fields before validation runs.
     * Prevents stored XSS if content is ever rendered in a browser
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'requester_name'    => strip_tags($this->requester_name ?? ''),
            'recipient_name'    => strip_tags($this->recipient_name ?? ''),
            'other_usage'       => strip_tags($this->other_usage ?? ''),
            'brand_other'       => strip_tags($this->brand_other ?? ''),
            'accessories_other' => strip_tags($this->accessories_other ?? ''),
            'additional_info'   => strip_tags($this->additional_info ?? ''),
            'os_other'          => strip_tags($this->os_other ?? ''),
        ]);
    }

    /**
     * Anyone can submit this form — no auth/permission check needed.
     * Return true to allow the request to proceed to validation.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * The actual validation rules.
     * Field names here must match what the JS payload sends.
     */
    public function rules(): array
    {
        return [
            // --- Requester Info ---
            // Restricted to McMaster emails only — this is an internal tool
            'requester_name'  => ['required', 'string', 'max:255'],
            'requester_email' => ['required', 'email', 'max:255', 'regex:/@mcmaster\.ca$/i'],

            // --- Who is this request for ---
            'request_for' => ['required', Rule::in(['self', 'other'])],

            // Only required when requesting on behalf of someone else
            // Also restricted to McMaster emails
            'recipient_name'  => ['nullable', 'required_if:request_for,other', 'string', 'max:255'],
            'recipient_email' => ['nullable', 'required_if:request_for,other', 'email', 'max:255', 'regex:/@mcmaster\.ca$/i'],

            // --- Core request details ---
            // Must exactly match the DB enum values
            'request_type' => ['required', Rule::in(['laptop', 'desktop'])],
            'budget_range'  => ['required', Rule::in(['under_1000', '1000_1499', '1500_1999', '2000_plus'])],

            // --- Usage (multi-select checkboxes) ---
            // At least one option must be selected
            'usage'       => ['required', 'array', 'min:1'],
            'usage.*'     => [Rule::in(['standard', 'advanced', 'other'])],
            'other_usage' => ['nullable', 'string', 'max:500'],

            // --- Brands (now required, implies OS) ---
            // 'no_preference' is a valid single-item array when user has no brand preference
            'brands'      => ['required', 'array', 'min:1'],
            'brands.*' => ['string', 'max:100', Rule::in([
                'dell', 'lenovo', 'hp', 'apple', 'no_preference'
            ])],
            // brand_other only matters when a real brand is selected, not no_preference
            'brand_other' => ['nullable', 'string', 'max:255'],

            // --- Portability (optional) ---
            // Only valid values are the three Option A choices, or null if skipped
            'portability' => ['nullable', Rule::in([
                'lightweight',
                'performance',
                'no_preference'
            ])],

            // --- Accessories ---
            'accessories'       => ['nullable', 'array'],
            'accessories.*'     => [Rule::in([
                'docking_station', 'wired_keyboard', 'wireless_keyboard',
                'web_camera', 'wired_mouse', 'wireless_mouse',
            ])],
            'accessories_other' => ['nullable', 'string', 'max:255'],

            // --- Delivery & notes ---
            // Must be at least 7 days from today
            'delivery_date'   => ['required', 'date', 'after_or_equal:' . now()->addDays(7)->format('Y-m-d')],
            'additional_info' => ['nullable', 'string', 'max:250'],
        ];
    }

    /**
     * Custom error messages for specific rules.
     * Makes validation errors more user-friendly than Laravel's defaults.
     */
    public function messages(): array
    {
        return [
            'requester_email.regex' => 'Please use your McMaster email address (@mcmaster.ca).',
            'recipient_email.regex' => 'Recipient must have a McMaster email address (@mcmaster.ca).',
            'usage.required'        => 'Please select at least one usage option.',
            'usage.min'             => 'Please select at least one usage option.',
            'delivery_date.after_or_equal' => 'Delivery date must be at least 7 days from today.',
        ];
    }

    /**
     * Cross-field validation checks that basic rules() can't handle alone.
     * Runs after all rules() pass.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {

            // If "other" was checked in usage, the text field must be filled
            if (in_array('other', $this->input('usage', []), true)
                && empty($this->input('other_usage'))) {
                $validator->errors()->add('other_usage', 'Please describe your other usage needs.');
            }

            // Can't select "no preference" alongside specific brands
            if (in_array('no_preference', $this->input('brands', []), true)
                && count($this->input('brands', [])) > 1) {
                $validator->errors()->add('brands', 'Please select specific brands or "No preference", not both.');
            }

            // Delivery date must be a weekday — no Saturday or Sunday
            if ($this->input('delivery_date')) {
                $dayOfWeek = Carbon::parse($this->input('delivery_date'))->dayOfWeek;
                if ($dayOfWeek === Carbon::SATURDAY || $dayOfWeek === Carbon::SUNDAY) {
                    $validator->errors()->add('delivery_date', 'Delivery date must be a weekday (Monday to Friday).');
                }
            }
        });
    }
}