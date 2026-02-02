<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Users</title>
<link rel="stylesheet" href="style.css" />
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

<style>
/* ===== BASE ===== */
body {
  font-family: 'Poppins', sans-serif;
  margin: 0;
  background: #fde7e7;
  color: #1f2937;
}
.page-container {
  max-width: 1100px;
  margin: 120px auto 60px 320px;
  padding: 30px;
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.08);
}

/* ===== INTRO ===== */
.page-intro-card {
  background: #f44040;
  color: #fff;
  border-radius: 16px;
  padding: 25px 30px;
  margin-bottom: 30px;
}
.page-intro-card h2 { margin: 0; }
.page-intro-card p { margin-top: 8px; }

/* ===== HEADER BAR ===== */
.table-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 25px;
}
.table-header input {
  padding: 12px 16px;
  border-radius: 14px;
  border: 1px solid #e5e7eb;
  width: 360px;
}
.create-btn {
  background: #46e54e;
  color: #fff;
  border: none;
  padding: 12px 18px;
  border-radius: 14px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
}
.create-btn i{
    font-size: 18px;
}
.create-btn:hover { background: #49d650; }

/* ===== SECTION ===== */
.section-title {
  margin: 30px 0 12px;
  font-weight: 600;
  font-size: 1.1rem;
}

/* ===== TABLE ===== */
.table-wrapper {
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.06);
  overflow: hidden;
}
table {
  width: 100%;
  border-collapse: collapse;
}
th, td {
  padding: 14px;
  text-align: center;
  font-size: 0.9rem;
  border-bottom: 1px solid #f1f1f1;
}
thead { background: #fbf9f9; }
tbody tr:hover {
  background: #fff5f5;
  cursor: pointer;
}

/* ===== MODAL ===== */
.modal {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(17,24,39,0.55);
  backdrop-filter: blur(4px);
  z-index: 999;
  align-items: center;
  justify-content: center;
}
.modal.active {
  display: flex;
}
.modal-content {
  background: #fff;
  border-radius: 20px;
  width: 100%;
  max-width: 480px;
  padding: 25px;
  position: relative;
  animation: fadeUp .3s ease;
}
@keyframes fadeUp {
  from { opacity:0; transform: translateY(20px); }
  to { opacity:1; transform: translateY(0); }
}
.close {
  position: absolute;
  top: 16px;
  right: 18px;
  font-size: 20px;
  cursor: pointer;
}

/* ===== FORM ===== */
.form-group {
  margin-bottom: 14px;
}
.form-group label {
  font-size: 0.85rem;
  display: block;
  margin-bottom: 6px;
}
.form-group input, select {
  width: 100%;
  padding: 10px 12px;
  border-radius: 12px;
  border: 1px solid #e5e7eb;
}
.modal-buttons {
  display: flex;
  gap: 12px;
  margin-top: 20px;
}
.modal-buttons button {
  flex: 1;
  padding: 12px;
  border-radius: 14px;
  border: none;
  color: #fff;
  cursor: pointer;
}
.btn-primary { background: #22c55e; }
.btn-danger { background: #ef4444; }
.btn-secondary { background: #9ca3af; }
</style>
</head>

<body>
<?php include "navbar/navbar_admin.php"; ?>

<div class="page-container">

  <!-- Intro -->
  <div class="page-intro-card">
    <h2>Manage Users</h2>
    <p>Create and manage hospital and event organizer accounts.</p>
  </div>

  <!-- Search + Create -->
  <div class="table-header">
    <input type="text" id="searchInput" placeholder="Search users...">
    <button class="create-btn" id="createUserBtn">
      <i class='bx bx-user-plus'></i> Create User
    </button>
  </div>

  <!-- Hospital -->
  <div class="section-title">Hospital Users</div>
  <div class="table-wrapper">
    <table id="hospitalTable">
      <thead>
        <tr>
          <th>User ID</th><th>Name</th><th>Email</th><th>Phone</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>

  <!-- Organizer -->
  <div class="section-title">Event Organizer Users</div>
  <div class="table-wrapper">
    <table id="organizerTable">
      <thead>
        <tr>
          <th>User ID</th><th>Name</th><th>Email</th><th>Phone</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>

</div>

<!-- CREATE USER MODAL -->
<div class="modal" id="createModal">
  <div class="modal-content">
    <span class="close" onclick="closeModal('createModal')">&times;</span>
    <h3>Create User</h3>

    <div class="form-group"><label>Name</label><input id="cName"></div>
    <div class="form-group"><label>Email</label><input id="cEmail"></div>
    <div class="form-group"><label>Phone</label><input id="cPhone"></div>
    <div class="form-group">
      <label>Role</label>
      <select id="cRole">
        <option value="hospital">Hospital</option>
        <option value="organizer">Event Organizer</option>
      </select>
    </div>
    <div class="form-group"><label>Password</label><input type="password" id="cPass"></div>

    <div class="modal-buttons">
      <button class="btn-primary" onclick="createUser()">Create</button>
      <button class="btn-secondary" onclick="closeModal('createModal')">Cancel</button>
    </div>
  </div>
</div>

<!-- USER DETAIL MODAL -->
<div class="modal" id="detailModal">
  <div class="modal-content">
    <span class="close" onclick="closeModal('detailModal')">&times;</span>
    <h3>User Details</h3>

    <div class="form-group"><label>Name</label><input id="dName"></div>
    <div class="form-group"><label>Email</label><input id="dEmail"></div>
    <div class="form-group"><label>Phone</label><input id="dPhone"></div>

    <div class="modal-buttons">
      <button class="btn-primary" onclick="saveEdit()">Save</button>
      <button class="btn-danger" onclick="deleteUser()">Delete</button>
    </div>
  </div>
</div>

<script>
let users = [
  {id:1,name:"City Hospital",email:"city@hospital.com",phone:"0123456789",role:"hospital"},
  {id:2,name:"Red Cross Org",email:"rc@org.com",phone:"0198887777",role:"organizer"}
];
let selectedUser = null;

function renderTables(){
  hospitalTable.querySelector('tbody').innerHTML='';
  organizerTable.querySelector('tbody').innerHTML='';
  users.forEach(u=>{
    const tr=document.createElement('tr');
    tr.innerHTML=`<td>${u.id}</td><td>${u.name}</td><td>${u.email}</td><td>${u.phone}</td>`;
    tr.onclick=()=>openDetail(u);
    (u.role==='hospital'?hospitalTable:organizerTable).querySelector('tbody').appendChild(tr);
  });
}
renderTables();

createUserBtn.onclick=()=>openModal('createModal');

function openModal(id) {
  document.getElementById(id).classList.add('active');
}

function closeModal(id) {
  document.getElementById(id).classList.remove('active');
}


function createUser(){
  users.push({
    id: users.length+1,
    name:cName.value,
    email:cEmail.value,
    phone:cPhone.value,
    role:cRole.value
  });
  renderTables();
  closeModal('createModal');
}

function openDetail(user){
  selectedUser=user;
  dName.value=user.name;
  dEmail.value=user.email;
  dPhone.value=user.phone;
  openModal('detailModal');
}

function saveEdit(){
  selectedUser.name=dName.value;
  selectedUser.email=dEmail.value;
  selectedUser.phone=dPhone.value;
  renderTables();
  closeModal('detailModal');
}

function deleteUser(){
  users=users.filter(u=>u!==selectedUser);
  renderTables();
  closeModal('detailModal');
}
</script>
</body>
</html>
