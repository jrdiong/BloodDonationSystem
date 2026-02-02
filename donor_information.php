<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Donor Health Reports</title>
<link rel="stylesheet" href="style.css" />
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<style>
    body {
        font-family: 'Poppins', sans-serif;
        margin: 0;
        padding: 0;
        background: #fde7e7;
        color: #1f2937;
    }
    .page-container {
    max-width: 900px;
    margin: 120px 60px 0 320px;
    margin-left: calc(260px + (100% - 260px - 900px) / 2);
    padding: 30px;
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    }

    /* Page intro card like Blood Inventory */
    .page-intro-card {
        background: #f44040;          /* red gradient or solid color */
        color: #fff;
        border-radius: 15px;
        padding: 25px 30px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        margin-bottom: 30px;
    }

    .page-intro-card h2 {
        margin: 0 0 10px 0;
        font-size: 24px;
        font-weight: 600;
    }

    .page-intro-card p {
        margin: 0;
        font-size: 16px;
    }

    .table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 18px;
    }

    .table-header input {
    padding: 12px 16px;
    border-radius: 14px;
    border: 1px solid #e5e7eb;
    width: 100%;
    max-width: 380px;
    font-size: 0.95rem;
    background: #fff;
    transition: all 0.25s ease;
    }

    .table-header input:focus {
    outline: none;
    border-color: #f16363;
    box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
    }
    .table-wrapper {
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 12px 30px rgba(0,0,0,0.06);
    overflow: hidden;
    }

    #donorTable {
    width: 100%;
    border-collapse: collapse;
    }

    #donorTable thead {
    background: #fbf9f9;
    }

    #donorTable th {
    padding: 16px;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #6b7280;
    }

    #donorTable td {
    padding: 16px;
    font-size: 0.9rem;
    border-bottom: 1px solid #f1f1f1;
    align-items: center;
    }

    #donorTable tbody tr {
    transition: all 0.25s ease;
    }

    #donorTable tbody tr:hover {
    background: #fff5f5;
    transform: scale(1.003);
    cursor: pointer;
    }

    #donorTable td, #donorTable th {
        text-align: center;       /* horizontal alignment */
        vertical-align: middle;   /* vertical alignment */
    }
    .status {
    padding: 6px 14px;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 500;
    display: inline-block;
    }

    .status.pending {
    background: #fff7ed;
    color: #c2410c;
    }

    .status.approved {
    background: #ecfdf5;
    color: #047857;
    }

    .status.rejected {
    background: #fef2f2;
    color: #b91c1c;
    }
    .modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(17, 24, 39, 0.55);
    backdrop-filter: blur(4px);
    z-index: 999;
    }
    .modal-content {
    background: #fff;
    margin: auto;
    padding: 20px;
    border-radius: 22px;
    width: 92%;
    max-width: 520px;
    max-height: 85vh; 
    overflow: hidden;
    box-shadow: 0 25px 60px rgba(0,0,0,0.18);
    animation: slideUp 0.35s ease;
    position: relative;
    }

    @keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px) scale(0.96);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
    }
    .close {
    position: absolute;
    top: 18px;
    right: 18px;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    cursor: pointer;
    transition: all 0.2s ease;
    }

    .close:hover {
    background: #e5e7eb;
    }

    .modal-content form {
    display: flex;
    flex-direction: column;
    gap: 14px;
    }

    .modal-content label {
    font-size: 0.75rem;
    font-weight: 500;
    color: #6b7280;
    }

    .modal-content input,
    .modal-content select,
    .modal-content textarea {
    padding: 12px 14px;
    border-radius: 14px;
    border: 1px solid #e5e7eb;
    font-size: 0.9rem;
    transition: all 0.25s ease;
    }

    .modal-content input:focus,
    .modal-content select:focus,
    .modal-content textarea:focus {
    outline: none;
    border-color: #6366f1;
    box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
    }
    .modal-actions {
    display: flex;
    gap: 12px;
    margin-top: 22px;
    }

    .modal-actions button {
    flex: 1;
    padding: 12px;
    border-radius: 14px;
    font-size: 0.85rem;
    font-weight: 500;
    border: none;
    cursor: pointer;
    transition: all 0.25s ease;
    }

    .approve-btn {
    background: #22c55e;
    color: #fff;
    }

    .reject-btn {
    background: #ef4444;
    color: #fff;
    }

    .modal-actions button:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.12);
    }
    /* ===== PROFILE CARD (MODAL HEADER) ===== */
    .profile-card {
    display: flex;
    gap: 16px;
    align-items: center;
    padding-bottom: 18px;
    border-bottom: 1px solid #eee;
    margin-bottom: 20px;
    }

    .profile-card img {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    border: 3px solid #6366f1;
    object-fit: cover;
    }

    .profile-info h2 {
    margin: 0;
    font-size: 1.15rem;
    font-weight: 600;
    }

    .profile-info p {
    margin: 2px 0;
    font-size: 0.85rem;
    color: #6b7280;
    }

    /* ===== HEALTH REPORT SECTION ===== */
    .edit-section h3 {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 14px;
    color: #111827;
    }

    /* ===== FIELD ROWS ===== */
    .profile-field {
    display: grid;
    grid-template-columns: 150px 1fr auto;
    align-items: center;
    padding: 14px 0;
    border-bottom: 1px dashed #e5e7eb;
    }

    .field-label {
    font-size: 0.8rem;
    color: #6b7280;
    }

    .field-value {
    font-size: 0.9rem;
    font-weight: 500;
    color: #111827;
    }

    .edit-icon {
    font-size: 18px;
    color: #9ca3af;
    cursor: pointer;
    transition: color 0.2s ease;
    }

    .edit-icon:hover {
    color: #6366f1;
    }

    /* ===== APPROVE / REJECT BUTTONS ===== */
    .modal-buttons {
    display: flex;
    gap: 12px;
    margin-top: 24px;
    }

    .save-btn {
    flex: 1;
    padding: 12px;
    border-radius: 14px;
    border: none;
    font-size: 0.85rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.25s ease;
    color: #ffffff;
    background-color: #ff4646;
    }

    #approveBtn {
    background: linear-gradient(135deg, #22c55e, #16a34a);
    }

    #rejectBtn {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    }

    .save-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.12);
    }

    /* ===== MOBILE ===== */
    @media (max-width: 600px) {
    .profile-card {
        flex-direction: column;
        text-align: center;
    }

    .profile-field {
        grid-template-columns: 1fr;
        gap: 6px;
    }

    .modal-buttons {
        flex-direction: column;
    }
    }
    /* ===== Status Pills ===== */
    .status-pill {
    display: inline-block;
    padding: 6px 14px;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 500;
    text-transform: capitalize;
    white-space: nowrap;
    }

    /* Pending */
    .status-pill.pending {
    background: #fff7ed;
    color: #c2410c;
    }

    /* Approved */
    .status-pill.approved {
    background: #ecfdf5;
    color: #047857;
    }

    /* Rejected */
    .status-pill.rejected {
    background: #fef2f2;
    color: #b91c1c;
    }

</style>
<body>
    <?php include "navbar_hospital.php"; ?>

<div class="page-container">

  <!-- Page Heading -->
  <div class="page-intro-card">
    <h2>Manage Donor Information</h2>
    <p>Here you can review, approve, reject, or edit donors’ submitted health reports.</p>
  </div>


  <!-- Search -->
  <div class="table-header">
    <input type="text" id="searchInput" placeholder="Search donors by name, email or phone..." />
  </div>

  <!-- Donor Table -->
  <table id="donorTable">
    <thead>
      <tr>
        <th>Name</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Appointment Status</th>
      </tr>
    </thead>
    <tbody id="donorTableBody">
      <!-- Donor rows will be populated here -->
    </tbody>
  </table>
<!-- Donor Info Modal -->
<div id="donorModal" class="modal">
  <div class="modal-content">
    <span class="close">&times;</span>

    <div class="profile-card">
      <img id="donorImg" src="default_avatar.png" alt="Donor Photo" />
      <div class="profile-info">
        <h2 id="donorName">Donor Name</h2>
        <p id="donorEmail">email@example.com</p>
        <p id="donorPhone">+6012-3456789</p>
      </div>
    </div>

    <div class="edit-section">
      <h3>Donor Details</h3>
      <div class="profile-field">
        <span class="field-label">Blood Type</span>
        <span class="field-value" id="donorBloodType"></span>
      </div>
      <div class="profile-field">
        <span class="field-label">Age</span>
        <span class="field-value" id="donorAge"></span>
      </div>
      <div class="profile-field">
        <span class="field-label">Weight</span>
        <span class="field-value" id="donorWeight"></span>
      </div>
      <div class="profile-field">
        <span class="field-label">Height</span>
        <span class="field-value" id="donorHeight"></span>
      </div>
      <div class="profile-field">
        <span class="field-label">Medical History</span>
        <span class="field-value" id="donorMedHistory"></span>
      </div>
      <div class="profile-field">
        <span class="field-label">Last Donation</span>
        <span class="field-value" id="donorLastDonate"></span>
      </div>
      <div class="profile-field">
        <span class="field-label">Appointment Status</span>
        <span class="status-pill" id="donorStatus"></span>
      </div>

      <!-- APPROVE / REJECT BUTTONS -->
      <div class="modal-buttons">
          <button id="approveBtn" class="approve-btn">Approve</button>
          <button id="rejectBtn" class="reject-btn">Reject</button>
      </div>
    </div>
  </div>
</div>


  <script>
  let donors = [];
  const donorTableBody = document.getElementById('donorTableBody');

  let currentDonor = null;

  // ---------- FETCH DONORS ----------

  const urlParams = new URLSearchParams(window.location.search);
  const eventID = urlParams.get('eventID');  
  async function loadDonors() {
    const urlParams = new URLSearchParams(window.location.search);
    const eventID = urlParams.get('eventID');  
    if (!eventID) {
        alert("Event ID missing");
        return;
    }

    try {
        const res = await fetch(`donor_api.php?action=getDonors&eventID=${eventID}`);
        const data = await res.json();
        if(data.status === "success") {
            donors = data.donors;
            renderTable(donors);
        } else {
            alert(data.message || "Failed to load donors");
        }
    } catch(err) {
        console.error(err);
        alert("Error fetching donors");
    }
}

// ---------- RENDER TABLE ----------
function renderTable(donorsList) {
    donorTableBody.innerHTML = '';
    donorsList.forEach(donor => {
        const status = (donor.appointmentStatus || 'pending').toLowerCase();
        const displayStatus = status.charAt(0).toUpperCase() + status.slice(1);

        const tr = document.createElement('tr');
        tr.dataset.id = donor.userID;
        tr.innerHTML = `
            <td>${donor.name}</td>
            <td>${donor.email}</td>
            <td>${donor.phoneNumber}</td>
            <td>
                <span class="status-pill ${status}">
                    ${displayStatus}
                </span>
            </td>
        `;
        tr.style.cursor = "default";
        donorTableBody.appendChild(tr);
    });
}

const donorModal = document.getElementById('donorModal');
const closeBtn = donorModal.querySelector('.close');

const donorImg = document.getElementById('donorImg');
const donorName = document.getElementById('donorName');
const donorEmail = document.getElementById('donorEmail');
const donorPhone = document.getElementById('donorPhone');
const donorBloodType = document.getElementById('donorBloodType');
const donorAge = document.getElementById('donorAge');
const donorWeight = document.getElementById('donorWeight');
const donorHeight = document.getElementById('donorHeight');
const donorMedHistory = document.getElementById('donorMedHistory');
const donorLastDonate = document.getElementById('donorLastDonate');
const donorStatus = document.getElementById('donorStatus');

document.getElementById('donorTableBody').addEventListener('click', async (e) => {
  const tr = e.target.closest('tr');
  if (!tr) return;

  const donor = donors.find(d => d.userID == tr.dataset.id);
  if (!donor) return;
  currentDonor = donor;

  try {
    const res = await fetch(`donor_api.php?action=getDonorHealth&donorID=${donor.userID}&eventID=${eventID}`);
    const data = await res.json();
    if (data.status !== 'success') {
      alert(data.message || "Failed to fetch donor info");
      return;
    }
    const d = data.donor;

    donorImg.src = d.image_url || 'default_avatar.png';
    donorName.textContent = d.name;
    donorEmail.textContent = d.email;
    donorPhone.textContent = d.phoneNumber;
    donorBloodType.textContent = d.bloodType || '-';
    donorAge.textContent = d.age || '-';
    donorWeight.textContent = d.weight || '-';
    donorHeight.textContent = d.height || '-';
    donorMedHistory.textContent = d.medicalHistory || '-';
    donorLastDonate.textContent = d.dateLastDonate || '-';

    const statusText = (d.healthStatus || 'pending').toLowerCase();
    donorStatus.textContent = statusText.charAt(0).toUpperCase() + statusText.slice(1);
    donorStatus.className = 'status-pill ' + statusText;

    donorModal.style.display = 'block';
  } catch(err) {
    console.error(err);
    alert("Error fetching donor info");
  }
});

    // close modal
    closeBtn.onclick = () => donorModal.style.display = 'none';
    window.onclick = (e) => { if (e.target === donorModal) donorModal.style.display = 'none'; };

    // ---------- APPROVE / REJECT ----------
    const approveBtn = document.getElementById('approveBtn');
    const rejectBtn = document.getElementById('rejectBtn');

    approveBtn.onclick = () => updateStatus('Approved');
    rejectBtn.onclick = () => updateStatus('Rejected');

    async function updateStatus(status) {
    if (!currentDonor) return;

    try {
        const params = new URLSearchParams();
        params.append('action', 'updateStatus');
        params.append('donorID', currentDonor.userID);
        params.append('status', status);
        params.append('eventID', eventID);

        const res = await fetch('donor_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: params.toString()   // 
        });

        const data = await res.json();

        if (data.status === 'success') {
            // update modal
            currentDonor.appointmentStatus = status.toLowerCase();
            donorStatus.textContent = status;
            donorStatus.className = 'status-pill ' + status.toLowerCase();

            // update table
            const tr = donorTableBody.querySelector(`tr[data-id='${currentDonor.userID}']`);
            if(tr){
                const statusSpan = tr.querySelector('.status-pill');
                statusSpan.textContent = status;
                statusSpan.className = 'status-pill ' + status.toLowerCase();
            }

            // close modal
            donorModal.style.display = 'none';
        } else {
            alert(data.message || "Failed to update status");
        }
    } catch (err) {
        console.error(err);
        alert("Error updating status");
    }
}


// ---------- INLINE EDIT ----------
// function initEditIcons() {
//     document.querySelectorAll('.edit-icon').forEach(icon => {
//         icon.onclick = async () => {
//             const field = icon.dataset.field;
//             const spanEl = document.getElementById(field + "Value");
//             const oldValue = spanEl.textContent.replace(/[^0-9.\/a-zA-Z ]/g,"");

//             const input = document.createElement('input');
//             input.value = oldValue;
//             input.style.width = '100%';
//             spanEl.replaceWith(input);
//             icon.style.display = 'none';
//             input.focus();

//             const saveBtn = document.createElement('button');
//             saveBtn.textContent = 'Save';
//             saveBtn.className = 'save-btn';
//             input.after(saveBtn);

//             saveBtn.onclick = async () => {
//                 const value = input.value.trim();
//                 const fieldMap = {bp:'bloodPressure', hb:'hemoglobin', weight:'weight', notes:'notes'};
//                 try {
//                     const formData = new FormData();
//                     formData.append('action', 'updateHealth');
//                     formData.append('donorID', currentDonor.userID);
//                     formData.append('field', fieldMap[field]);
//                     formData.append('value', value);
//                     const res = await fetch('donor_api.php', {method:'POST', body:formData});
//                     const data = await res.json();
//                     if(data.status === "success"){
//                         spanEl.textContent = (field==='hb' ? value + " g/dL" : field==='weight' ? value + " kg" : value);
//                         currentDonor[fieldMap[field]] = value;
//                         input.replaceWith(spanEl);
//                         saveBtn.remove();
//                         icon.style.display = 'inline';
//                     } else {
//                         alert(data.message || "Failed to update");
//                     }
//                 } catch(err) {
//                     console.error(err);
//                     alert("Error updating field");
//                 }
//             };
//         };
//     });
// }

// // ---------- APPROVE / REJECT ----------
// document.getElementById('approveBtn').onclick = async () => updateStatus('Approved');
// document.getElementById('rejectBtn').onclick = async () => updateStatus('Rejected');

// async function updateStatus(status){
//     if(!currentDonor) return;
//     try {
//         const formData = new FormData();
//         formData.append('action','updateStatus');
//         formData.append('donorID', currentDonor.userID);
//         formData.append('status', status);
//         const res = await fetch('donor_api.php', {method:'POST', body:formData});
//         const data = await res.json();
//         if(data.status==='success'){
//             currentDonor.appointmentStatus = status.toLowerCase();
//             statusEl.textContent = status;
//             statusEl.className = 'status-pill ' + status.toLowerCase();
//             renderTable(donors);
//             healthModal.style.display = 'none';
//         } else {
//             alert(data.message || "Failed to update status");
//         }
//     } catch(err){
//         console.error(err);
//         alert("Error updating status");
//     }
// }

// ---------- SEARCH ----------
document.getElementById('searchInput').addEventListener('input', (e)=>{
    const query = e.target.value.toLowerCase();
    const filtered = donors.filter(d => 
        d.name.toLowerCase().includes(query) || 
        d.email.toLowerCase().includes(query) || 
        d.phoneNumber.toLowerCase().includes(query)
    );
    renderTable(filtered);
});

// // ---------- CLOSE MODAL ----------
// document.querySelectorAll('.close').forEach(btn=>{
//     btn.onclick = () => healthModal.style.display = 'none';
// });
// window.onclick = e => { if(e.target === healthModal) healthModal.style.display = 'none'; }

// ---------- INIT ----------
loadDonors();
</script>

<script src="script.js"></script>
</body>
</html>
