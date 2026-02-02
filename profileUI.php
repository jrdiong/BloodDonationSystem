<?php
session_start();
if (!isset($_SESSION['userID'], $_SESSION['role'])) {
    header("Location: login.php");
    exit;
}
$loggedInUserID = $_SESSION['userID'];
$sessionRole = $_SESSION['role']; // normalized

$roleMap = [
    'Admin' => 'admin',
    'Event Organizer' => 'organizer',
    'Hospital' => 'hospital',
    'Donor' => 'donor'
];
$role = $roleMap[$sessionRole] ?? 'guest';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>User Profile</title>
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

<?php include "navbar_$role.php"; ?>

<div class="main-container">

  <!-- Upper profile card -->
  <div class="profile-card">
    <img id="avatar" src="default-avatar.png" alt="Avatar">
    <div class="profile-info">
      <h2 id="nameValue">Loading...</h2>
      <p id="emailValue"></p>
      <p id="phoneValue"></p>
    </div>
  </div>

  <!-- Lower editable section -->
  <div class="edit-section">
    <h3 id="roleSectionTitle">Profile</h3>

    <div class="profile-field">
      <span class="field-label">Name</span>
      <span class="field-value" id="editNameValue"></span>
      <span class="edit-icon" data-field="name"><i class='bx bx-pencil'></i></span>
    </div>

    <div class="profile-field">
      <span class="field-label">Email</span>
      <span class="field-value" id="editEmailValue"></span>
      <span class="edit-icon" data-field="email"><i class='bx bx-pencil'></i></span>
    </div>

    <div class="profile-field">
  <span class="field-label">Phone</span>
  <span class="field-value" id="editPhoneNumberValue"></span> <!-- <-- correct ID -->
  <span class="edit-icon" data-field="phoneNumber"><i class='bx bx-pencil'></i></span>
</div>


    <!-- Hospital-specific -->
    <div class="profile-field" id="hospitalField" style="display:none;">
      <span class="field-label">Location</span>
      <span class="field-value" id="editLocationValue"></span>
      <span class="edit-icon" data-field="location"><i class='bx bx-pencil'></i></span>
    </div>

    <!-- Donor Health Report -->
    <button id="editHealthBtn" class="update-profile-btn" style="display:none;">Edit Health Report</button>

  </div>
</div>

<!-- Donor health report modal -->
<div id="healthModal" class="modal">
  <div class="modal-content">
    <h3>Health Report</h3>
    <form id="healthForm">
      <label>Blood Type
        <select name="bloodType" required>
          <option value="A+">A+</option>
          <option value="A-">A-</option>
          <option value="B+">B+</option>
          <option value="B-">B-</option>
          <option value="AB+">AB+</option>
          <option value="AB-">AB-</option>
          <option value="O+">O+</option>
          <option value="O-">O-</option>
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
// ---------------- Avatar ----------------
const avatarImg = document.getElementById('avatar');
const avatarInput = document.createElement('input');
avatarInput.type = 'file';
avatarInput.accept = 'image/*';
avatarInput.style.display = 'none';
document.body.appendChild(avatarInput);

avatarImg.addEventListener('click', () => avatarInput.click());

avatarInput.addEventListener('change', () => {
    const file = avatarInput.files[0];
    if (!file) return;
    const formData = new FormData();
    formData.append('avatar', file);

    fetch('upload_avatar.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) avatarImg.src = data.image_url + '?t=' + new Date().getTime();
            else alert('Upload failed: ' + data.error);
        })
        .catch(err => alert('Upload failed: ' + err.message));
});

// ---------------- Load Profile ----------------
function loadProfile() {
    fetch('profile.php')
    .then(res => res.json())
    .then(data => {
        if(!data.success){ alert(data.error); return; }
        const profile = data.profile;

        document.getElementById('nameValue').textContent = profile.name;
        document.getElementById('emailValue').textContent = profile.email;
        document.getElementById('phoneValue').textContent = profile.phoneNumber;

        document.getElementById('editNameValue').textContent = profile.name;
        document.getElementById('editEmailValue').textContent = profile.email;
        document.getElementById('editPhoneNumberValue').textContent = profile.phoneNumber;

        if(profile.image_url) avatarImg.src = profile.image_url + '?t=' + new Date().getTime();

        // Role-specific fields
        if(profile.role === 'Hospital'){
            document.getElementById('hospitalField').style.display = 'flex';
            document.getElementById('editLocationValue').textContent = profile.hospitalInfo?.location || '';
            document.getElementById('roleSectionTitle').textContent = 'Hospital Profile';
        } else if(profile.role === 'Donor'){
            document.getElementById('editHealthBtn').style.display = 'block';
            const form = document.getElementById('healthForm');
            form.bloodType.value = profile.donorInfo?.bloodType || '';
            form.age.value = profile.donorInfo?.age || '';
            form.dateLastDonate.value = profile.donorInfo?.dateLastDonate || '';
            form.medicalHistory.value = profile.donorInfo?.medicalHistory || '';
            form.weight.value = profile.donorInfo?.weight || '';
            form.height.value = profile.donorInfo?.height || '';
            document.getElementById('roleSectionTitle').textContent = 'Donor Profile';
        } else if(profile.role === 'Admin'){
            document.getElementById('roleSectionTitle').textContent = 'Admin Profile';
        } else if(profile.role === 'Organizer'){
            document.getElementById('roleSectionTitle').textContent = 'Organizer Profile';
        }

        bindEditIcons();
    });
}

// ---------------- Edit Fields ----------------
function bindEditIcons() {
    document.querySelectorAll('.edit-icon').forEach(icon => {
        if(!icon.dataset.bound){
            icon.addEventListener('click', () => startEdit(icon.dataset.field));
            icon.dataset.bound = 'true';
        }
    });
}

function startEdit(field){
    const valueSpan = document.getElementById('edit' + field.charAt(0).toUpperCase() + field.slice(1) + 'Value');
    const currentValue = valueSpan.textContent;
    const parentDiv = valueSpan.parentNode;
    const icon = parentDiv.querySelector('.edit-icon');

    const input = document.createElement('input');
    input.value = currentValue;
    input.className = 'edit-input';
    if(field === 'email') input.type = 'email';
    if(field === 'phoneNumber') input.type='text';

    const saveBtn = document.createElement('button');
    saveBtn.textContent = 'Save';
    saveBtn.className = 'save-btn';
    const cancelBtn = document.createElement('button');
    cancelBtn.textContent = 'Cancel';
    cancelBtn.className = 'cancel-btn';

    valueSpan.replaceWith(input);
    icon.style.display = 'none';
    parentDiv.appendChild(saveBtn);
    parentDiv.appendChild(cancelBtn);

    cancelBtn.addEventListener('click', ()=>{
        input.replaceWith(valueSpan);
        saveBtn.remove();
        cancelBtn.remove();
        icon.style.display = 'inline';
    });

    saveBtn.addEventListener('click', ()=>{
        const newValue = input.value.trim();
        if(!newValue){ alert('Cannot be empty'); return; }
        const formData = new FormData();
        formData.append(field,newValue);

        fetch('update_profile.php',{method:'POST',body:formData})
        .then(res=>res.json())
        .then(data=>{
            if(data.success){
                input.replaceWith(valueSpan);
                valueSpan.textContent = newValue;
                saveBtn.remove(); cancelBtn.remove();
                icon.style.display = 'inline';
                loadProfile();
            } else alert('Update failed: '+data.error);
        });
    });
}

// ---------------- Donor Health Modal ----------------
const editHealthBtn = document.getElementById('editHealthBtn');
const healthModal = document.getElementById('healthModal');
const cancelHealthBtn = document.getElementById('cancelHealthBtn');
const healthForm = document.getElementById('healthForm');

editHealthBtn.addEventListener('click', ()=> healthModal.style.display='flex');
cancelHealthBtn.addEventListener('click', ()=> healthModal.style.display='none');
window.addEventListener('click',(e)=>{if(e.target===healthModal) healthModal.style.display='none';});

healthForm.addEventListener('submit',(e)=>{
    e.preventDefault();
    const formData = new FormData(healthForm);

    fetch('update_health_report.php',{method:'POST',body:formData})
    .then(res=>res.json())
    .then(data=>{
        if(data.success){
            alert('Health report updated!');
            healthModal.style.display='none';
            loadProfile();
        } else alert('Update failed: '+data.error);
    });
});

// ---------------- Initialize ----------------
window.addEventListener('DOMContentLoaded', ()=> loadProfile());
</script>

</body>
</html>
