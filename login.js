const form = document.querySelector('.login-form');
const emailInput = document.querySelector('.email-input');
const passwordInput = document.querySelector('.password-input');
const rememberMe = document.querySelector('.remember-me');
const loginBtn = document.querySelector('.login-btn');

const emailMsg = emailInput.nextElementSibling;
const passwordMsg = passwordInput.nextElementSibling;

let emailValid = false;
let passwordValid = false;

// Enable submit if valid
function updateButton() {
    loginBtn.disabled = !(emailValid && passwordValid);
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

// Live password validation
passwordInput.addEventListener('input', () => {
    if (passwordInput.value.length >= 6) {
        passwordMsg.textContent = '';
        passwordMsg.className = 'live-msg success';
        passwordValid = true;
    } else {
        passwordMsg.textContent = 'Password must be at least 6 characters';
        passwordMsg.className = 'live-msg error';
        passwordValid = false;
    }
    updateButton();
});
