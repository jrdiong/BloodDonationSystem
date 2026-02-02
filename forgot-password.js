const form = document.querySelector('.forgot-form');

const usernameInput = document.querySelector('.username-input');
const emailInput = document.querySelector('.email-input');
const newPassInput = document.querySelector('.newpass-input');
const confPassInput = document.querySelector('.confpass-input');

const submitBtn = document.querySelector('.submit-btn');
const emailMsg = emailInput.nextElementSibling;
const confirmationMsg = document.querySelector('.confirmation-msg');

function getMsgEl(input) {
    return input.parentElement.querySelector('.live-msg');
}

function updateButton(ok) {
    submitBtn.disabled = !ok;
}

function setError(input, message) {
    const el = getMsgEl(input);
    el.textContent = message;
    el.className = 'live-msg error';
}

function setSuccess(input) {
    const el = getMsgEl(input);
    el.textContent = '';
    el.className = 'live-msg success';
}

function validateForm() {
    let ok = true;

    if (usernameInput.value.trim() === '') {
        setError(usernameInput, 'Username required');
        ok = false;
    } else {
        setSuccess(usernameInput);
    }

    if (!emailInput.value.includes('@')) {
        setError(emailInput, 'Enter a valid email');
        ok = false;
    } else {
        setSuccess(emailInput);
    }

    const p = newPassInput.value;
    if (p.length < 8 || p.length > 16) {
        setError(newPassInput, 'Password must be 8-16 characters');
        ok = false;
    } else {
        setSuccess(newPassInput);
    }

    if (confPassInput.value !== newPassInput.value) {
        setError(confPassInput, 'Passwords do not match');
        ok = false;
    } else {
        setSuccess(confPassInput);
    }
    
    updateButton(ok);
    return ok;
}
    
[usernameInput, emailInput, newPassInput, confPassInput].forEach(el => {
    el.addEventListener('input', () => {
        validateForm();
        confirmationMsg.textContent = '';
    });
});

// Simulate submission
form.addEventListener('submit', (e) => {
    if (!validateForm()) {
        e.preventDefault();
        confirmationMsg.textContent = "Please fix the errors above.";
        return;
    }
    confirmationMsg.textContent = "Password reset successfully!";
});
