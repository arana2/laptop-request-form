/*
Created by: Andrew A.
Description: Javascript used with the web form.
Organized into small functions so it stays clean and easier to maintain as the form grows.
*/
document.addEventListener('DOMContentLoaded', function () {

    // Main form + submit button references
    const form = document.getElementById('requestForm');
    const submitBtn = document.getElementById('submitBtn');

    // Initialize all form behaviours
    setupRecipientToggle();
    setupUsageToggle();
    setupSystemPreferences();
    setupAccessoriesToggle();
    setupDeliveryDate();
    setupValidation(form, submitBtn);
    setupFormSubmit(form, submitBtn);

});

/*
Handles showing/hiding the recipient section.
If user selects "someone else", we show the fields and make them required.
If they switch back to "myself", we hide + clear the fields to avoid accidental submission.
*/
function setupRecipientToggle() {
    const radios = document.querySelectorAll('input[name="request_for"]');
    const recipientSection = document.getElementById('recipientSection');
    const recipientName = document.getElementById('recipient_name');
    const recipientEmail = document.getElementById('recipient_email');

    // Safety check in case elements aren't found
    if (!radios.length || !recipientSection) return;

    radios.forEach(radio => {
        radio.addEventListener('change', function () {

            if (this.value === 'other') {
                recipientSection.classList.remove('hidden');

                recipientName.required = true;
                recipientEmail.required = true;
            } else {
                recipientSection.classList.add('hidden');

                recipientName.required = false;
                recipientEmail.required = false;

                // Clear values so nothing gets submitted by accident
                recipientName.value = '';
                recipientEmail.value = '';
            }
        });
    });
}


/*
Handles the "Other" checkbox in the usage section.
Shows a textbox when selected and makes it required.
If unchecked, hides it and clears the value.
*/
function setupUsageToggle() {
    const otherCheckbox = document.getElementById('otherUsageCheckbox');
    const otherContainer = document.getElementById('otherUsageContainer');
    const otherInput = document.getElementById('otherUsageInput');

    // Safety check
    if (!otherCheckbox) return;

    otherCheckbox.addEventListener('change', () => {
        if (otherCheckbox.checked) {
            otherContainer.classList.remove('hidden');
            otherInput.required = true;
        } else {
            otherContainer.classList.add('hidden');

            // Clear value to prevent stale data
            otherInput.value = '';
            otherInput.required = false;
        }
    });
}


/*
Handles overall form validation.
- Uses built-in HTML validation (required fields, email format, etc.)
- Also checks that at least one usage checkbox is selected
- Enables/disables the submit button accordingly
*/
function setupValidation(form, submitBtn) {

    // Custom validation: ensure at least one usage checkbox is selected
    function validateUsage() {
        const checkboxes = document.querySelectorAll('input[name="usage[]"]');
        return Array.from(checkboxes).some(cb => cb.checked);
    }

    // Main validation function
    function validateForm() {
        const isValid = form.checkValidity() && validateUsage();

        if (isValid) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        } else {
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
        }
    }

    // Run validation whenever user interacts with the form
    form.addEventListener('input', validateForm);
    form.addEventListener('change', validateForm);

    // Run once on load to set the correct initial state
    validateForm();
}

/*
Handles the system preferences section.
Brand Preferences:
- Allows selecting one or more brands
- If "Other" is selected, a textbox is displayed and becomes required
- If "No preference" is selected, all other brand selections are cleared
  and any "Other" input is hidden and reset

Operating System:
- Single selection (radio buttons)
- If "Other" is selected, a textbox is displayed and becomes required
- If another option is selected, the "Other" textbox is hidden and cleared
*/
function setupSystemPreferences() {

    // --- Brand logic ---
    const brandOptions =document.querySelectorAll('.brand-option');
    const noPref = document.getElementById('noPreferenceCheckbox');

    const brandOtherCheckbox = document.getElementById('brandOtherCheckbox');
    const brandOtherContainer = document.getElementById('brandOtherContainer');
    const brandOtherInput = document.getElementById('brandOtherInput');

    //If "no preference" is checked, clear all other selections
    if (noPref) {
        noPref.addEventListener('change', () => {
            if (noPref.checked) {
                brandOptions.forEach (cb => {
                    cb.checked = false;
                });

                // Hide and clear "Other"
                brandOtherContainer.classList.add('hidden');
                brandOtherInput.value = '';
                brandOtherInput.required = false;
            }
        });
    }

    // If any brand is selected, uncheck "No preference"
    brandOptions.forEach(cb => {
        cb.addEventListener('change', () => {
            if (cb.checked && noPref) {
                noPref.checked = false;
            }
        });
    });

    // Toggle "Other" brand textbox
    if (brandOtherCheckbox) {
        brandOtherCheckbox.addEventListener('change', () =>{
            if (brandOtherCheckbox.checked) {
                brandOtherContainer.classList.remove('hidden');
                brandOtherInput.required = true;
            } else {
                brandOtherContainer.classList.add('hidden');
                brandOtherInput.value ='';
                brandOtherInput.required = false;
            }
        });
    }

    // --- OS logic ---
    const osOtherRadio = document.getElementById('osOtherRadio');
    const osOtherContainer = document.getElementById('osOtherContainer');
    const osOtherInput = document.getElementById('osOtherInput');

    const osRadios = document.querySelectorAll('input[name="os"]');

    osRadios.forEach(radio => {
        radio.addEventListener('change', () => {
            if (osOtherRadio.checked) {
                osOtherContainer.classList.remove('hidden');
                osOtherInput.required = true;
            } else {
                osOtherContainer.classList.add('hidden');
                osOtherInput.value ='';
                osOtherInput.required = false;
            }
        });
    });
}

/*
Handles the "Other" checkbox in the usage section.
Shows a textbox when selected and makes it required.
If unchecked, hides it and clears the value.
*/
function setupAccessoriesToggle() {
    const checkbox = document.getElementById('accessoryOtherCheckbox');
    const container = document.getElementById('accessoryOtherContainer');
    const input = document.getElementById('accessoryOtherInput');

    if (!checkbox) return;

    checkbox.addEventListener('change', () => {
        if(checkbox.checked) {
            container.classList.remove('hidden');
            input.required = true;
        } else {
            container.classList.add('hidden');
            input.value ='';
            input.required = false;
        }
    });
}

/*
* Sets up the delivery date input to enforce a minimum date of 7 days from today.
* Also warns the user if they select a weekend date, and clears the input to prevent submission.
*/
function setupDeliveryDate() {
    const dateInput = document.getElementById('deliveryDate');
    if (!dateInput) return;

    const minDate = new Date();
    minDate.setDate(minDate.getDate() + 7);

    const yyyy = minDate.getFullYear();
    const mm = String(minDate.getMonth() + 1).padStart(2, '0');
    const dd = String(minDate.getDate()).padStart(2, '0');

    dateInput.min = `${yyyy}-${mm}-${dd}`;

    // Show a warning if the user picks a weekend date
    dateInput.addEventListener('change', function () {
        const picked = new Date(this.value + 'T00:00:00');
        const day = picked.getDay();
        const existingWarning = document.getElementById('dateWeekendWarning');

        if (day === 0 || day === 6) {
            if (!existingWarning) {
                const warning = document.createElement('p');
                warning.id = 'dateWeekendWarning';
                warning.className = 'text-red-500 text-sm mt-1';
                warning.textContent = 'Please select a weekday (Monday to Friday).';
                dateInput.insertAdjacentElement('afterend', warning);
            }
            // Clear the value so the submit button stays disabled
            this.value = '';
        } else {
            if (existingWarning) existingWarning.remove();
        }
    });
}

/*
Handles form submission via AJAX.
- Prevents default form submission to avoid page reload
- Shows a loading state on the submit button
- Collects form data into a structured object
- Sends data to the server using Fetch API
- Handles server response:
  - On success: shows confirmation and resets form
  - On error: shows error message and re-enables submit button
- Also catches network errors and informs the user
*/  
function setupFormSubmit(form, submitBtn) {
    form.addEventListener('submit', async function (e) {
        // Prevent the browser from doing a standard page-reload form submission
        e.preventDefault();

        // Show loading state so the user knows something is happening
        submitBtn.disabled = true;
        submitBtn.textContent = 'Submitting...';
        submitBtn.classList.add('opacity-50', 'cursor-not-allowed');

        // Collect all form fields into a structured object
        const formData = new FormData(form);

        const payload = {
            requester_name:  formData.get('requester_name'),
            requester_email: formData.get('requester_email'),
            request_for:     formData.get('request_for'),
            recipient_name:  formData.get('recipient_name'),
            recipient_email: formData.get('recipient_email'),
            request_type:    formData.get('request_type'),
            budget_range:    formData.get('budget_range'),
            // getAll() captures multi-select checkboxes as an array
            usage:           formData.getAll('usage[]'),
            other_usage:     formData.get('other_usage'),
            brands:          formData.getAll('brand[]'),
            brand_other:     formData.get('brand_other'),
            operating_system: formData.get('os'),
            os_other:        formData.get('os_other'),
            accessories:     formData.getAll('accessories[]'),
            accessories_other: formData.get('accessories_other'),
            delivery_date:   formData.get('delivery_date'),
            additional_info: formData.get('additional_info'),
        };

        try {
            const response = await fetch('/api/submissions', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    // Laravel requires this header for API routes
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload),
            });

            const result = await response.json();

            if (!response.ok || result.error) {
                // Default to the generic message
                let errorMessage = result.message ?? 'Something went wrong. Please try again.';

                // If Laravel sent field-specific validation errors, show the first one instead —
                if (result.errors) {
                    const firstField = Object.keys(result.errors)[0];
                    errorMessage = result.errors[firstField][0];
                }

                showStatus(errorMessage, true);

                submitBtn.disabled = false;
                submitBtn.textContent = 'Submit Request';
                submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                return;
            }

            // Success — redirect or show confirmation
            showStatus('Your request has been submitted successfully! EngIT will review your request and follow up with next steps.');
            form.reset();

        } catch (err) {
            // Network failure (no connection, server down, etc.)
            console.error('Submission error:', err);
            showStatus('A network error occurred. Please check your connection and try again.', true);

            submitBtn.disabled = false;
            submitBtn.textContent = 'Submit Request';
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    });
}

/*
Utility function to show status messages to the user.
- message: the text to display
- isError: if true, styles the message as an error (red); otherwise, success (green)
*/
function showStatus(message, isError = false) {
    const status = document.getElementById('formStatus');
    status.textContent = message;
    status.className = isError
        ? 'mt-4 p-4 rounded text-sm bg-red-100 text-red-700'
        : 'mt-4 p-4 rounded text-sm bg-green-100 text-green-700';
    status.classList.remove('hidden');
}