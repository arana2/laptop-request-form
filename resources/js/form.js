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
Handles the "Other" checkbox in the usage section.
Shows a textbox when selected and makes it required.
If unchecked, hides it and clears the value.
*/
function setupDeliveryDate() {
    const dateInput = document.getElementById('deliveryDate');
    if (!dateInput) return;

    const today = new Date();

    // Add 7 days
    const minDate = new Date();
    minDate.setDate(today.getDate() + 7);

    // Format as YYYY-MM-DD
    const yyyy = minDate.getFullYear();
    const mm = String(minDate.getMonth() + 1).padStart(2, '0');
    const dd = String(minDate.getDate()).padStart(2, '0');

    dateInput.min = `${yyyy}-${mm}-${dd}`;
}