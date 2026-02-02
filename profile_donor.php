<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Donor Profile</title>
<link rel="stylesheet" href="style.css" />
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap" rel="stylesheet">

<style>
body {
  font-family: 'Poppins', sans-serif;
  background: #fde7e7;
  margin: 0;
  padding: 0;
}

.main-container {
  max-width: 900px;
  margin: 120px 60px 0 320px;
  margin-left: calc(260px + (100% - 260px - 900px) / 2);
  padding: 30px;
  background: #fff;
  border-radius: 15px;
  box-shadow: 0 8px 25px rgba(0,0,0,0.08);
}

/* Upper profile card */
.profile-card {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 30px;
  padding-bottom: 25px;
  border-bottom: 1px solid #eee;
}

.profile-card img {
  width: 130px;
  height: 130px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid #f44040;
  cursor: pointer;
}

.profile-info {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.profile-info h2 {
  margin: 0;
  color: #f44040;
}

.profile-info p {
  margin: 0;
  color: #555;
  font-weight: 500;
}

/* Lower editable section */
.edit-section {
  margin-top: 30px;
}

.edit-section h3 {
  color: #f44040;
  margin-bottom: 15px;
}

.profile-field {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 0;
  border-bottom: 1px solid #eee;
}

.profile-field .field-label {
  font-weight: 500;
  color: #555;
  min-width: 130px;
}

.profile-field .field-value {
  flex: 1;
  text-align: right;
  color: #333;
}

.profile-field .edit-icon {
  margin-left: 10px;
  cursor: pointer;
  color: #f44040;
  font-size: 18px;
  transition: color 0.2s;
}

.profile-field .edit-icon:hover {
  color: #ff0000;
}
/* Editable input field */
.profile-field input {
  flex: 1;
  padding: 8px 12px;
  border: 2px solid #f0f0f0;
  border-radius: 12px;
  outline: none;
  font-size: 15px;
  transition: all 0.3s ease;
  background-color: #f9f9f9;
}

.profile-field input:focus {
  border-color: #f44040;
  box-shadow: 0 2px 8px rgba(244, 64, 64, 0.2);
  background-color: #fff;
}
.inline-edit {
  display: flex;
  gap: 10px;
  width: 100%;
}

.inline-edit input {
  flex: 1;
}

/* Save button next to input */
.profile-field .save-btn {
  padding: 5px 12px;
  border: none;
  background-color: #f44040;
  color: #fff;
  border-radius: 20px;
  font-size: 13px;
  margin-left: 10px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.profile-field .save-btn:hover {
  background-color: #d23333;
}

/* Buttons */
.update-profile-btn {
  display: block;
  margin: 25px auto 0 auto;
  padding: 10px 25px;
  border: none;
  background-color: #f44040;
  color: #fff;
  font-size: 16px;
  border-radius: 25px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.update-profile-btn:hover {
  background-color: #d23333;
}

/* Health report modal */
.modal {
  display: none;
  position: fixed;
  top:0; left:0;
  width:100%; height:100%;
  background: rgba(0,0,0,0.5);
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background: #fff;
  padding: 25px 30px;
  border-radius: 12px;
  width: 400px;
  max-width: 90%;
}

.modal-content h3 {
  margin-bottom: 15px;
  color: #f44040;
}

.modal-content label {
  display: block;
  margin-bottom: 10px;
}

.modal-content input,
.modal-content select,
.modal-content textarea {
  width: 100%;
  padding: 6px 10px;
  margin-top: 4px;
  border-radius: 6px;
  border: 1px solid #ccc;
}

.modal-buttons {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 15px;
}

.save-btn {
  background-color: #f44040;
  color: #fff;
  border: none;
  padding: 6px 15px;
  border-radius: 6px;
  cursor: pointer;
}

.cancel-btn {
  background-color: #ccc;
  border: none;
  padding: 6px 15px;
  border-radius: 6px;
  cursor: pointer;
}

@media screen and (max-width: 768px) {
  .profile-card {
    flex-direction: column;
    align-items: center;
    gap: 15px;
  }
  .profile-info {
    text-align: center;
  }
  .profile-field {
    flex-direction: column;
    align-items: flex-start;
  }
  .profile-field .field-value {
    text-align: left;
    margin-top: 5px;
  }
}
</style>
</head>
<body>
    <?php include "navbar_donor.php"; ?>

<div class="main-container">

  <!-- Upper profile card -->
  <div class="profile-card">
    <img id="avatar" src="default-avatar.png" alt="Avatar">
    <div class="profile-info">
      <h2 id="nameValue">John Doe</h2>
      <p id="emailValue">john@example.com</p>
      <p id="phoneValue">0123456789</p>
    </div>
  </div>

  <!-- Lower editable section -->
  <div class="edit-section">
    <h3>Edit Profile</h3>
    <div class="profile-field">
      <span class="field-label">Name</span>
      <span class="field-value" id="editNameValue">John Doe</span>
      <span class="edit-icon" data-field="name"><i class='bx bx-pencil'></i></span>
    </div>
    <div class="profile-field">
      <span class="field-label">Email</span>
      <span class="field-value" id="editEmailValue">john@example.com</span>
      <span class="edit-icon" data-field="email"><i class='bx bx-pencil'></i></span>
    </div>
    <div class="profile-field">
      <span class="field-label">Phone</span>
      <span class="field-value" id="editPhoneValue">0123456789</span>
      <span class="edit-icon" data-field="phone"><i class='bx bx-pencil'></i></span>
    </div>

    <button id="editHealthBtn" class="update-profile-btn">Edit Health Report</button>

  </div>

</div>

<!-- Donor health report modal -->
<div id="healthModal" class="modal">
  <div class="modal-content">
    <h3>Health Report</h3>
    <form id="healthForm">
      <label>Blood Type
        <select name="bloodType" required>
          <option value="A">A</option>
          <option value="B">B</option>
          <option value="AB">AB</option>
          <option value="O">O</option>
        </select>
      </label>
      <label>Age <input type="number" name="age" required></label>
      <label>Last Donation Date <input type="date" name="dateLastDonate" required></label>
      <label>Medical History <textarea name="medicalHistory" required></textarea></label>
      <label>Weight (kg) <input type="number" step="0.01" name="weight" required></label>
      <label>Height (cm) <input type="number" step="0.01" name="height" required></label>
      <div class="modal-buttons">
        <button type="submit" class="save-btn">Save</button>
        <button type="button" id="cancelHealthBtn" class="cancel-btn">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
// ---------- MOCK DATA FETCH ----------
const profileData = {
  name: "John Doe",
  email: "john@example.com",
  phoneNumber: "0123456789",
  donorInfo: {
    bloodType: "O",
    age: 30,
    dateLastDonate: "2025-01-15",
    medicalHistory: "No major illnesses",
    weight: 65,
    height: 172
  }
};

function loadProfile() {
  // upper card
  document.getElementById("nameValue").textContent = profileData.name;
  document.getElementById("emailValue").textContent = profileData.email;
  document.getElementById("phoneValue").textContent = profileData.phoneNumber;

  // lower editable section
  document.getElementById("editNameValue").textContent = profileData.name;
  document.getElementById("editEmailValue").textContent = profileData.email;
  document.getElementById("editPhoneValue").textContent = profileData.phoneNumber;

  // Health form
  const form = document.getElementById("healthForm");
  form.bloodType.value = profileData.donorInfo.bloodType;
  form.age.value = profileData.donorInfo.age;
  form.dateLastDonate.value = profileData.donorInfo.dateLastDonate;
  form.medicalHistory.value = profileData.donorInfo.medicalHistory;
  form.weight.value = profileData.donorInfo.weight;
  form.height.value = profileData.donorInfo.height;
}

loadProfile();

// ---------- EDIT FIELD LOGIC ----------
document.querySelectorAll('.edit-icon').forEach(icon => {
  icon.addEventListener('click', () => {
    const field = icon.dataset.field;
    const span = document.getElementById("edit" + field.charAt(0).toUpperCase() + field.slice(1) + "Value");
    const currentValue = span.textContent;

    // create input
    const input = document.createElement('input');
    input.type = (field === "email") ? "email" : "text";
    input.value = currentValue;
    input.classList.add('modern-input');

    span.replaceWith(input);
    icon.style.display = "none";

    // create save button
    const saveBtn = document.createElement('button');
    saveBtn.textContent = "Save";
    saveBtn.className = "save-btn";
    input.after(saveBtn);

    saveBtn.addEventListener('click', () => {
      span.textContent = input.value;
      input.replaceWith(span);
      saveBtn.remove();
      icon.style.display = "inline";
    });
  });
});


// ---------- HEALTH REPORT MODAL ----------
const editHealthBtn = document.getElementById('editHealthBtn');
const healthModal = document.getElementById('healthModal');
const cancelHealthBtn = document.getElementById('cancelHealthBtn');

editHealthBtn.addEventListener('click', () => healthModal.style.display = 'flex');
cancelHealthBtn.addEventListener('click', () => healthModal.style.display = 'none');
window.addEventListener('click', (e) => {
  if(e.target === healthModal) healthModal.style.display = 'none';
});
</script>
<script src="script.js"></script>

</body>
</html>
