<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Event Requests</title>
<link rel="stylesheet" href="style.css" />
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<style>
/* ===== BODY & PAGE CONTAINER ===== */
body {
    font-family: 'Poppins', sans-serif;
    margin: 0;
    padding: 0;
    background: #fde7e7;
    color: #1f2937;
}
.page-container {
    max-width: 950px;
    margin: 120px 60px 0 320px;
    margin-left: calc(260px + (100% - 260px - 950px)/2);
    padding: 30px;
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
}

/* ===== PAGE INTRO ===== */
.page-intro-card {
    background: #f44040;
    color: #fff;
    border-radius: 15px;
    padding: 25px 30px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}
.page-intro-card h2 { margin: 0 0 10px 0; font-size: 24px; font-weight: 600; }
.page-intro-card p { margin: 0; font-size: 16px; }

/* ===== SEARCH + REQUEST BUTTON ===== */
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
.request-btn {
    background: #6366f1;
    color: #fff;
    border: none;
    padding: 12px 18px;
    border-radius: 14px;
    font-size: 0.9rem;
    cursor: pointer;
    position: relative;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.25s ease;
}
.request-btn:hover { background: #4f46e5; }
.request-btn .badge {
    background: #ef4444;
    color: #fff;
    font-size: 0.75rem;
    padding: 2px 8px;
    border-radius: 999px;
}

/* ===== TABLE ===== */
.table-wrapper {
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 12px 30px rgba(0,0,0,0.06);
    overflow: hidden;
}
#requestTable {
    width: 100%;
    border-collapse: collapse;
}
#requestTable thead { background: #fbf9f9; }
#requestTable th, #requestTable td {
    padding: 16px;
    font-size: 0.9rem;
    border-bottom: 1px solid #f1f1f1;
    text-align: center;
    vertical-align: middle;
}
#requestTable tbody tr {
    transition: all 0.25s ease;
}
#requestTable tbody tr:hover {
    background: #fff5f5;
    transform: scale(1.003);
    cursor: pointer;
}

/* ===== STATUS PILL ===== */
.status-pill {
    display: inline-block;
    padding: 6px 14px;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 500;
    text-transform: capitalize;
}
.status-pill.pending { background: #fff7ed; color: #c2410c; }
.status-pill.approved { background: #ecfdf5; color: #047857; }
.status-pill.rejected { background: #fef2f2; color: #b91c1c; }

/* ===== MODAL ===== */
.modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(17, 24, 39, 0.55);
    backdrop-filter: blur(4px);
    z-index: 999;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: #fff;
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
    from { opacity: 0; transform: translateY(30px) scale(0.96); }
    to { opacity: 1; transform: translateY(0) scale(1); }
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
.close:hover { background: #e5e7eb; }

/* ===== EVENT MODAL CONTENT ===== */
.event-card img {
    width: 100%;
    border-radius: 15px;
    max-height: 200px;
    object-fit: cover;
    margin-bottom: 16px;
}
.event-info h2 { margin: 0; font-size: 1.2rem; font-weight: 600; }
.event-info p { margin: 4px 0; font-size: 0.9rem; color: #6b7280; }
.modal-buttons {
    display: flex;
    gap: 12px;
    margin-top: 22px;
}
.modal-buttons button {
    flex: 1;
    padding: 12px;
    border-radius: 14px;
    border: none;
    font-size: 0.85rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.25s ease;
    color: #fff;
}
#approveBtn { background: linear-gradient(135deg,#22c55e,#16a34a); }
#rejectBtn { background: linear-gradient(135deg,#ef4444,#dc2626); }
.modal-buttons button:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.12);
}

/* ===== MOBILE ===== */
@media(max-width:600px){
    .table-header { flex-direction: column; gap: 10px; }
    .modal-buttons { flex-direction: column; }
}
</style>
<body>
<?php include "navbar_admin.php"; ?>

<div class="page-container">

  <!-- Page Heading -->
  <div class="page-intro-card">
    <h2>Manage Event Requests</h2>
    <p>Review and approve or reject incoming event requests from organizers.</p>
  </div>

  <!-- Search + Request Button -->
  <div class="table-header">
    <input type="text" id="searchInput" placeholder="Search events by name or location..." />
    <button class="request-btn" id="requestBtn">
        Requests <span class="badge" id="requestBadge">0</span>
    </button>
  </div>

  <!-- Event Request Table -->
  <div class="table-wrapper">
    <table id="requestTable">
      <thead>
        <tr>
          <th>Event Image</th>
          <th>Event Name</th>
          <th>Date / Time</th>
          <th>Location</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody id="requestTableBody">
        <!-- Requests dynamically populated -->
      </tbody>
    </table>
  </div>
</div>

<!-- Event Request Modal -->
<div class="modal" id="eventModal">
  <div class="modal-content">
    <span class="close" id="modalClose">&times;</span>

    <div class="event-card">
      <img id="eventImage" src="default-event.jpg" alt="Event Image">
      <div class="event-info">
        <h2 id="eventName">Sample Event</h2>
        <p id="eventDate">01/02/2026, 10:00 AM</p>
        <p id="eventLocation">Sample Location</p>
        <p>Status: <span id="eventStatus" class="status-pill pending">Pending</span></p>
      </div>
    </div>

    <div class="modal-buttons">
      <button id="approveBtn">Approve</button>
      <button id="rejectBtn">Reject</button>
    </div>
  </div>
</div>

<!-- Confirmation Modal -->
<div class="modal" id="confirmModal">
  <div class="modal-content">
    <span class="close" id="confirmClose">&times;</span>
    <p style="font-weight:500; font-size:1rem;">Do you want to record this request?</p>
    <div class="modal-buttons">
      <button id="confirmYes" style="background:#22c55e;">Yes</button>
      <button id="confirmNo" style="background:#ef4444;">No</button>
    </div>
  </div>
</div>

<script>
// ===== DUMMY DATA =====
let requests = [
    {
        image: "event1.jpg",
        name: "Blood Donation Camp",
        date: "05/02/2026, 9:00 AM",
        location: "City Hall",
        status: "pending"
    },
    {
        image: "event2.jpg",
        name: "Health Awareness Seminar",
        date: "10/02/2026, 2:00 PM",
        location: "Community Center",
        status: "pending"
    }
];

const tableBody = document.getElementById('requestTableBody');
const requestBadge = document.getElementById('requestBadge');

// Populate Table
function populateTable() {
    tableBody.innerHTML = "";
    requests.forEach((req, index) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><img src="${req.image}" style="width:60px;height:40px;border-radius:6px;object-fit:cover;"></td>
            <td>${req.name}</td>
            <td>${req.date}</td>
            <td>${req.location}</td>
            <td><span class="status-pill ${req.status}">${req.status}</span></td>
        `;
        tr.addEventListener('click', () => openModal(index));
        tableBody.appendChild(tr);
    });
    requestBadge.textContent = requests.filter(r => r.status==='pending').length;
}
populateTable();

document.getElementById('requestBtn').addEventListener('click', function () {
    // find first pending request
    const pendingIndex = requests.findIndex(r => r.status === 'pending');

    if (pendingIndex === -1) {
        alert("No pending requests 🎉");
        return;
    }

    openModal(pendingIndex);
});


// ===== MODAL LOGIC =====
const eventModal = document.getElementById('eventModal');
const confirmModal = document.getElementById('confirmModal');

function openModal(index){
    const req = requests[index];
    document.getElementById('eventImage').src = req.image;
    document.getElementById('eventName').textContent = req.name;
    document.getElementById('eventDate').textContent = req.date;
    document.getElementById('eventLocation').textContent = req.location;
    const statusSpan = document.getElementById('eventStatus');
    statusSpan.textContent = req.status;
    statusSpan.className = 'status-pill ' + req.status;

    eventModal.style.display = 'flex';

    // Approve Button
    document.getElementById('approveBtn').onclick = function(){
        eventModal.style.display = 'flex';
        confirmModal.style.display = 'flex';
        document.getElementById('confirmYes').onclick = function(){
            req.status = 'approved';
            populateTable();
            confirmModal.style.display='none';
        }
        document.getElementById('confirmNo').onclick = function(){
            confirmModal.style.display='none';
        }
    }

    // Reject Button
    document.getElementById('rejectBtn').onclick = function(){
        req.status = 'rejected';
        populateTable();
        eventModal.style.display='none';
    }
}

// Close Modals
document.getElementById('modalClose').onclick = ()=> eventModal.style.display='none';
document.getElementById('confirmClose').onclick = ()=> confirmModal.style.display='none';
window.onclick = function(e){
    if(e.target==eventModal) eventModal.style.display='none';
    if(e.target==confirmModal) confirmModal.style.display='none';
}

// ===== SEARCH =====
document.getElementById('searchInput').addEventListener('input', function(){
    const filter = this.value.toLowerCase();
    const rows = tableBody.querySelectorAll('tr');
    rows.forEach(row=>{
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(filter)? '' : 'none';
    });
});
</script>

</body>
</html>
