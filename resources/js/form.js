/*
Created by: Andrew A.
Description: Various Javascript code used with the web form
*/

// Hide/Display the recipient name and email fields based on who the request is for
document.addEventListener('DOMContentLoaded', function () {

    const radios = document.querySelectorAll('input[name="request_for"]');
    const recipientSection = document.getElementById('recipientSection');
    const recipientName = document.getElementById('recipient_name');
    const recipientEmail = document.getElementById('recipient_email');

    radios.forEach(radio => {
        radio.addEventListener('change', function () {

            if(this.value === 'other') {
                recipientSection.classList.remove('hidden');

                recipientName.setAttribute('required', true);
                recipientEmail.setAttribute('required', true);
            } else {
                recipientSection.classList.add('hidden');

                recipientName.removeAttribute('required');
                recipientEmail.removeAttribute('required');

                recipientName.value ='';
                recipientEmail.value='';
            }
        });
    });
});

// Enable button only when form is valid
const form = document.getElementById('requestForm');
const submitBtn = document.getElementById('submitBtn');

function validateForm() {
    if (form.checkValidity()) {
        submitBtn.disabled = false;
        submitBtn.classList.remove('opacity-50', 'cursor-notallowed');
    } else {
        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
    }
}

// Execute validation on input changes
form.addEventListener('input', validateForm);
form.addEventListener('change', validateForm);