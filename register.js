const password = document.getElementById("password");
const confirmPassword = document.getElementById("confirmPassword");
const dob = document.getElementById("dob");
const declareCheckbox = document.getElementById("declare");
const submitBtn = document.getElementById("submitBtn");

const passwordMsg = document.getElementById("passwordMsg");
const ageMsg = document.getElementById("ageMsg");

let passwordValid = false;
let ageValid = false;

// Live password match check
function checkPasswordMatch() {
    if (!confirmPassword.value) {
        passwordMsg.textContent = "";
        passwordMsg.className = "live-msg";
        passwordValid = false;
        return;
    }

    if (password.value === confirmPassword.value) {
        passwordMsg.textContent = "Passwords match ✔";
        passwordMsg.className = "live-msg success";
        passwordValid = true;
    } else {
        passwordMsg.textContent = "Passwords do not match";
        passwordMsg.className = "live-msg error";
        passwordValid = false;
    }

    updateSubmitState();
}

password.addEventListener("input", checkPasswordMatch);
confirmPassword.addEventListener("input", checkPasswordMatch);

// Live age check
dob.addEventListener("change", () => {
    const birthDate = new Date(dob.value);
    const today = new Date();

    let age = today.getFullYear() - birthDate.getFullYear();
    const m = today.getMonth() - birthDate.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }

    if (age >= 18) {
        ageMsg.textContent = `Age ${age} ✔ Eligible`;
        ageMsg.className = "live-msg success";
        ageValid = true;
    } else {
        ageMsg.textContent = "You must be at least 18 years old";
        ageMsg.className = "live-msg error";
        ageValid = false;
    }

    updateSubmitState();
});

// Declaration checkbox
declareCheckbox.addEventListener("change", updateSubmitState);

// Enable submit only if all valid
function updateSubmitState() {
    submitBtn.disabled = !(passwordValid && ageValid && declareCheckbox.checked);
}
