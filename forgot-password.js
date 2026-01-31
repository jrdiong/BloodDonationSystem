const form = document.querySelector('.forgot-form');
const emailInput = document.querySelector('.email-input');
const submitBtn = document.querySelector('.submit-btn');
const emailMsg = emailInput.nextElementSibling;
const confirmationMsg = document.querySelector('.confirmation-msg');

let emailValid = false;

function updateButton() {
    submitBtn.disabled = !emailValid;
}

// Live email validation
emailInput.addEventListener('input', () => {
    if (emailInput.value.includes('@')) {
        emailMsg.textContent = '';
        emailMsg.className = 'live-msg success';
        emailValid = true;
    } else {
        emailMsg.textContent = 'Enter a valid email';
        emailMsg.className = 'live-msg error';
        emailValid = false;
    }
    updateButton();
});

// Simulate submission
form.addEventListener('submit', (e) => {
    e.preventDefault();
    confirmationMsg.textContent = "If this email is registered, a reset link has been sent.";
    form.reset();
    submitBtn.disabled = true;
});
