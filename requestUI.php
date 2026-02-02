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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Event Requests</title>
<link rel="stylesheet" href="style.css" />
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
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
#recordedTable {
    width: 100%;
    border-collapse: collapse;
}
#recordedTable thead { background: #fbf9f9; }
#recordedTable th, #recordedTable td {
    padding: 16px;
    font-size: 0.9rem;
    border-bottom: 1px solid #f1f1f1;
    text-align: center;
    vertical-align: middle;
}
#recordedTable img {
    width: 100px;
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
}
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
    overflow-y: auto;
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
</head>
<body>

<?php include "navbar_admin.php"; ?>

<div class="page-container">

  <!-- Page Intro -->
  <div class="page-intro-card">
    <h2>Manage Event Requests</h2>
    <p>Review and approve or reject incoming event requests from organizers.</p>
  </div>

  <!-- Search + Request Button -->
  <div class="table-header">
    <input type="text" id="searchInput" placeholder="Search events..." />
    <button class="request-btn" id="requestBtn">
        Requests <span class="badge" id="requestBadge">0</span>
    </button>
  </div>

  <!-- Recorded Requests Table -->
  <div class="table-wrapper" style="margin-top:30px;">
    <table id="recordedTable">
      <thead>
        <tr>
          <th>Event Image</th>
          <th>Event Name</th>
          <th>Date / Time</th>
          <th>Location</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody id="recordedBody">
        <!-- Recorded requests dynamically populated -->
      </tbody>
    </table>
  </div>
</div>

<!-- Event Request Modal -->
<div class="modal" id="requestModal">
  <div class="modal-content">
    <span class="close" id="closeModal">&times;</span>

    <div class="event-card">
      <img id="eventImage" src="placeholder.jpg" alt="Event Image">
      <div class="event-info">
        <h2 id="eventName">Sample Event</h2>
        <p id="eventDateTime">01/02/2026, 10:00 AM</p>
        <p id="eventLocation">Sample Location</p>
        <p>Organizer: <span id="eventOrganizer">Organizer Name</span></p>
        <p id="eventDescription">Event description goes here.</p>
      </div>
    </div>

    <div class="modal-buttons">
      <button id="approveBtn">Approve</button>
      <button id="rejectBtn">Reject</button>
    </div>
  </div>
</div>

<script src="request.js"></script>
<script>
let pendingRequests = [];
let currentIndex = 0;

const requestBadge = document.getElementById('requestBadge');
const requestBtn = document.getElementById('requestBtn');
const requestModal = document.getElementById('requestModal');
const closeModal = document.getElementById('closeModal');

const eventImage = document.getElementById('eventImage');
const eventName = document.getElementById('eventName');
const eventDateTime = document.getElementById('eventDateTime');
const eventLocation = document.getElementById('eventLocation');
const eventOrganizer = document.getElementById('eventOrganizer');
const eventDescription = document.getElementById('eventDescription');

const approveBtn = document.getElementById('approveBtn');
const rejectBtn = document.getElementById('rejectBtn');
const recordedBody = document.getElementById('recordedBody');
const loading = document.getElementById('loading');

/* ================= Pending ================= */
async function loadPending() {
    try {
        const res = await fetch('request_manage.php?action=pending');
        const data = await res.json();
        console.log("Pending requests raw data:", data);

        if (data.status === 'success') {
            pendingRequests = data.data || [];
            requestBadge.textContent = pendingRequests.length;
            console.log("Pending requests count:", pendingRequests.length);
        } else {
            console.error('Failed to load pending:', data.message);
            pendingRequests = [];
            requestBadge.textContent = 0;
        }
    } catch (e) {
        console.error('Failed to load pending requests', e);
        pendingRequests = [];
        requestBadge.textContent = 0;
    }
}

/* ================= Recorded ================= */
async function loadRecorded() {
    try {
        const res = await fetch('request_manage.php?action=recorded');
        const data = await res.json();
        console.log("Recorded requests raw data:", data);

        if (data.status === 'success') {
            recordedBody.innerHTML = '';
            (data.data || []).forEach(r => {  // <-- 修正这里
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td><img src="${r.image_url || 'placeholder.jpg'}"></td>
                    <td>${r.eventName}</td>
                    <td>${r.dateTime}</td>
                    <td>${r.location || ''}</td>
                    <td>${r.requestStatus == 1 ? 'Recorded' : r.requestStatus == 0 ? 'Not Recorded' : 'Rejected'}</td>
                `;
                recordedBody.appendChild(tr);
            });
        }
    } catch (e) {
        console.error('Failed to load recorded requests', e);
    }
}

/* ================= Modal ================= */
function showCurrent() {
    if (currentIndex >= pendingRequests.length) {
        requestModal.style.display = 'none';
        currentIndex = 0;
        loadPending();
        loadRecorded();
        return;
    }

    const r = pendingRequests[currentIndex];
    if (!r) return;

    eventImage.src = r.image_url || 'placeholder.jpg';
    eventName.textContent = r.eventName;
    eventDateTime.textContent = r.dateTime;
    eventLocation.textContent = r.location || '';
    eventOrganizer.textContent = r.organizerName;
    eventDescription.textContent = r.description || '';

    requestModal.style.display = 'flex';
}

/* ================= Buttons ================= */
requestBtn.onclick = () => {
    if (pendingRequests.length === 0) {
        loadPending().then(() => {
            if (pendingRequests.length === 0) {
                alert('No pending requests');
                return;
            }
            currentIndex = 0;
            showCurrent();
        });
    } else {
        currentIndex = 0;
        showCurrent();
    }
};

closeModal.onclick = () => requestModal.style.display = 'none';

async function handleAction(action) {
    if (!pendingRequests[currentIndex]) {
        alert('No pending request selected');
        return;
    }

    const r = pendingRequests[currentIndex];
    const fd = new FormData();
    fd.append('action', action);
    fd.append('requestID', r.requestID);
    fd.append('eventID', r.eventID);

    if (action === 'approve') {
        const record = confirm('Record this event?');
        fd.append('record', record ? 1 : 0);
    }

    try {
        const res = await fetch('request_manage.php', { method:'POST', body: fd });
        const data = await res.json();
        console.log("Action result:", data);

        if (data.status === 'success') {
            currentIndex++;
            showCurrent();
            loadPending();
            loadRecorded();
        } else {
            alert(data.message || 'Action failed');
            loadPending();
            loadRecorded();
        }
    } catch(e) {
        console.error(e);
        alert('Action failed');
    }
}

approveBtn.onclick = () => handleAction('approve');
rejectBtn.onclick = () => handleAction('reject');

/* ================= Init ================= */
window.onload = () => {
    loadPending();
    loadRecorded();
    setInterval(loadPending, 10000);
};
</script>
</body>
</html>
