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
    setupValidation(form, submitBtn);

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
}