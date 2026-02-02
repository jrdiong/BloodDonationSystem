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
<title>Events</title>
<link rel="stylesheet" href="style.css" />
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap" rel="stylesheet">
<style>
body { font-family: Arial, sans-serif; background: #f5f5f5; padding:20px; }
/* ===== Main Container ===== */
.main-container {
  max-width: 900px;
  margin: 120px 60px 0 320px;
  margin-left: calc(260px + (100% - 260px - 900px)/2);
  padding: 30px;
  background: #fff;
  border-radius: 15px;
  box-shadow: 0 8px 25px rgba(0,0,0,0.08);
}

/* ===== Header ===== */
.main-container h2 {
    margin-bottom: 20px;
    font-weight: 600;
    color: #333;
}

/* ===== Filter Bar ===== */
.filter-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    margin-bottom: 10px;
}

.filter-bar input, .filter-bar select {
    padding: 10px 14px;
    border-radius: 10px;
    border: 1px solid #ddd;
    outline: none;
    font-size: 14px;
    flex:1;
    transition: 0.2s;
}

.filter-bar input:focus, .filter-bar select:focus {
    border-color: #d21919;
}

#createEventBtn {
    padding: 10px 20px;
    background: #d21919;
    color: #fff;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    font-weight: 500;
    transition: 0.3s;
}
#createEventBtn:hover { background:#b21717; }

/* ===== Event Card ===== */
.event-card {
    display: flex;
    gap: 20px;
    background: #fff;
    border-radius: 16px;
    padding: 18px;
    margin-bottom: 18px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    transition: 0.3s;
    position: relative;
    align-items: center;
    justify-content: center;
}
.event-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 20px rgba(0,0,0,0.08);
}

.event-image {
    width: 160px;
    height: 120px;
    border-radius: 14px;
    background: #eee;
    overflow: hidden;
    flex-shrink: 0;
}

.event-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.event-info h3 {
    margin:0 0 6px 0;
    font-size: 18px;
    color:#222;
}

.event-info p {
    margin:4px 0;
    font-size: 14px;
    color:#555;
}

.book-status {
    font-weight: 600;
    color:#444;
    margin-top: 8px;
}


/* ===== Buttons inside cards ===== */
.event-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: center;
  position: relative; /* needed for absolute button placement */
}

.event-info .book {
  background: #d21919; /* red */
  color: #fff;
  border-radius: 8px;
  padding: 8px 16px;
  border: none;
  cursor: pointer;
  font-size: 14px;
  position: absolute;
  top: 16px;
  right: 16px;
}

.event-info .book:disabled {
  background: #e0e0e0;
  color: #999;
  cursor: not-allowed;
}
.event-info .status {
  background: #d21919; /* same red as Book */
  color: #fff;
  border-radius: 8px;
  padding: 8px 16px;
  border: none;
  cursor: pointer;
  font-size: 14px;
  position: absolute;
  top: 16px;
  right: 16px; /* align to right */
  transition: 0.2s;
}

.event-info .status:hover {
  background: #b21717;
}


.delete-btn {
    position: absolute;
    top: 12px;
    right: 12px;
    font-size: 22px;
    color: #e53939;
    cursor: pointer;
    user-select: none;
    transition: 0.2s;
}
.delete-btn:hover { transform: scale(1.2); }

/* ===== Modal ===== */
.modal {
    display: none;
    position: fixed;
    inset:0;
    background: rgba(0,0,0,0.6);
    z-index: 1000;
    justify-content: center;
    align-items: center;
    overflow-y: auto;
    padding: 20px;
}

.modal-content {
    background:#fff;
    width: 600px;
    max-width: 95%;
    padding: 25px 30px;
    border-radius: 18px;
    box-shadow: 0 15px 40px rgba(0,0,0,0.1);
    position: relative;
    animation: fadeIn 0.3s ease;
}

.modal-content h3 {
    margin-top:0;
    margin-bottom:20px;
    font-size: 22px;
    font-weight:600;
    color:#222;
}

.modal-content input, 
.modal-content textarea, 
.modal-content select {
    width:100%;
    padding: 10px 12px;
    border-radius: 10px;
    border:1px solid #ddd;
    margin-bottom:12px;
    font-size:14px;
    outline:none;
    transition: 0.2s;
}

.modal-content input:focus, 
.modal-content textarea:focus, 
.modal-content select:focus { border-color:#d21919; }

.modal-buttons {
    display:flex;
    justify-content: flex-end;
    gap:12px;
    margin-top:15px;
}

.modal-buttons button {
    padding: 10px 18px;
    border:none;
    border-radius: 12px;
    font-weight:500;
    cursor:pointer;
    font-size:14px;
    transition:0.3s;
}

.modal-buttons .submit { background:#d21919; color:#fff; }
.modal-buttons .submit:hover { background:#b21717; }

.modal-buttons .cancel { background:#ccc; color:#333; }
.modal-buttons .cancel:hover { background:#b0b0b0; }
#createEventBtn { display:none; margin:20px auto; padding:10px 20px; font-size:16px; cursor:pointer; }

/* Health Report Modal */
#healthModal .modal-content { max-width:500px; }
#healthModal textarea, #healthModal input, #healthModal select { margin-bottom:10px; }

/* Table inside appointment modal */
.table-wrapper {
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.06);
  overflow: hidden;
  margin-top: 15px;
  margin: 0;
}

table {
  width: 100%;
  border-collapse: collapse;
  border: none;
}

thead {
  background: #fbf9f9;
}

th, td {
  padding: 14px 12px;
  text-align: left;
  font-size: 0.95rem;
  border-bottom: 1px solid #f1f1f1;
}

th {
  font-weight: 600;
  color: #555;
}

tbody tr {
  transition: 0.2s;
  border-radius: 12px;
}

tbody tr:hover {
  background: #fff5f5;
  cursor: pointer;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

tbody td {
  color: #333;
}

/* Optional: alternate row color */
tbody tr:nth-child(even) {
  background: #fcfcfc;
}

/* Modal Buttons */
#appointmentModal .modal-buttons {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 15px;
}

#appointmentModal .modal-buttons button {
  padding: 8px 18px;
  border-radius: 8px;
  border: none;
  cursor: pointer;
  font-weight: 500;
  transition: 0.2s;
}

#appointmentModal .modal-buttons button:first-child {
  background: #ccc;
  color: #333;
}

#appointmentModal .modal-buttons button:last-child {
  background: #d21919;
  color: #fff;
}

#appointmentModal .modal-buttons button:hover {
  opacity: 0.9;
}

/* ===== Image Upload Preview ===== */
#previewImage {
    border-radius: 12px;
    width:100%;
    height:200px;
    object-fit:cover;
    margin-bottom:12px;
}

/* ===== Animations ===== */
@keyframes fadeIn {
    0%{ opacity:0; transform: translateY(-10px);}
    100%{ opacity:1; transform: translateY(0);}
}

/* ===== Responsive ===== */
@media(max-width:700px){
    .event-card { flex-direction: column; align-items:center; text-align:center; }
    .event-image { width:100%; height:180px; }
    .event-info button { width:100%; }
    .filter-bar { flex-direction: column; align-items: stretch; }
}
</style>
</head>
<body>
    <?php include "navbar_$role.php"; ?>
    <div class="main-container">

<h2>Event List</h2>
<div class="filter-bar">
            <input id="searchInput" type="text" placeholder="Search events..." />
            <select id="filterSelect">
                <option value="all">All Events</option>
                <option value="upcoming">Upcoming</option>
                <option value="past">Past</option>
            </select>
            <button id="createEventBtn" class="primary-btn">Add New Event</button>
</div>
<div id="eventList"></div>
</div>
<!-- Event Modal -->
<div class="modal" id="eventModal">
  <div class="modal-content">
    <h3 id="modalTitle"></h3>
    <div class="event-image" style="margin-bottom:15px;">
        <img id="previewImage" src="placeholder.jpg">
    </div>
    <input type="file" id="imageUpload" accept="image/*">
    <input type="text" id="eventName" placeholder="Event Name" required>
    <input type="datetime-local" id="dateTime" required>
    <input type="number" id="maxDonors" placeholder="Max Donors" required>
    <input type="text" id="eventLocation" placeholder="Location" required>
    <textarea id="description" placeholder="Description" required></textarea>
    <select id="hospitalSelect" style="display:none; margin-bottom:12px;"></select>
    <div class="modal-buttons" id="modalButtons"></div>
  </div>
</div>

<!-- Health Report Modal -->
<div class="modal" id="healthModal">
  <div class="modal-content">
    <h3>Complete Your Health Report</h3>
    <select id="bloodType" required>
      <option value="">Select Blood Type</option>
      <option value="A">A</option>
      <option value="B">B</option>
      <option value="AB">AB</option>
      <option value="0">0</option>
    </select>
    <input type="number" id="age" placeholder="Age" required>
    <input type="date" id="dateLastDonate" placeholder="Date of Last Donation" required>
    <textarea id="medicalHistory" placeholder="Medical History" required></textarea>
    <input type="number" step="0.01" id="weight" placeholder="Weight (kg)" required>
    <input type="number" step="0.01" id="height" placeholder="Height (cm)" required>
    <div class="modal-buttons">
      <button onclick="closeHealthModal()">Cancel</button>
      <button onclick="submitHealthReport()">Save & Book</button>
    </div>
  </div>
</div>

<!-- Appointment Status Modal -->
<div class="modal" id="appointmentModal">
  <div class="modal-content">
    <h3>Appointment Status</h3>
    <div class="table-wrapper">
    <table id="appointmentTable" border="1" width="100%" style="border-collapse: collapse; margin-bottom:15px;">
      <thead>
        <tr>
          <th>Name</th>
          <th>Email</th>
          <th>Phone</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
    </div>
    <div class="modal-buttons">
      <button onclick="closeAppointmentModal()">Close</button>
    </div>
  </div>
</div>

<script>
let currentEventID = null;
let currentRole = null;
let canEdit = false;
let hospitalsLoaded = false;
let allEvents = [];

/* ===== Load Events ===== */
async function loadEvents(){
    try{
        const res = await fetch('event.php');
        const data = await res.json();
        if(data.status!=='success'){ alert(data.message||'Failed to load events'); return; }
        allEvents = data.data;
        currentRole = data.role;
        applyFilters(); 
        if(currentRole==='Event Organizer' && !hospitalsLoaded) loadHospitals();
    } catch(e){ console.error(e); alert('Error loading events'); }
}
function applyFilters(){
    const searchText = document.getElementById('searchInput').value.toLowerCase();
    const filter = document.getElementById('filterSelect').value;

    const filtered = allEvents.filter(event => {
        const matchesSearch = event.eventName.toLowerCase().includes(searchText) ||
                              (event.location && event.location.toLowerCase().includes(searchText));

        let matchesFilter = true;
        if(event.dateTime){
            const eventDate = new Date(event.dateTime.replace(' ', 'T'));
            const now = new Date();
            if(filter === 'upcoming') matchesFilter = eventDate >= now;
            else if(filter === 'past') matchesFilter = eventDate < now;
        }

        return matchesSearch && matchesFilter;
    });

    renderEventCards(filtered);
}
document.getElementById('searchInput').addEventListener('input', applyFilters);
document.getElementById('filterSelect').addEventListener('change', applyFilters);


/* ===== Load Hospitals ===== */
async function loadHospitals(){
    try{
        const res = await fetch('event.php?action=getHospitals');
        const data = await res.json();
        if(data.status==='success'){
            const select=document.getElementById('hospitalSelect');
            select.innerHTML='<option value="">Select Hospital</option>';
            data.data.forEach(h=>{
                const opt=document.createElement('option'); opt.value=h.userID; opt.textContent=h.name;
                select.appendChild(opt);
            });
            hospitalsLoaded = true;
            document.getElementById('createEventBtn').style.display='block';
        }
    } catch(e){ console.error(e); }
}

/* ===== Render Event Cards ===== */
function renderEventCards(events){
    const container=document.getElementById('eventList');
    container.innerHTML='';
    events.forEach(event=>{
        const card=document.createElement('div'); card.className='event-card';
        const imgSrc=event.image_url||'placeholder.jpg';
        const current = event.currentBookings||0;
        const max = event.maxDonors||0;
        const statusText = `${current}/${max} booked`;

        let canBook = true;
        if(currentRole==='Donor'){ if(current >= max) canBook=false; }

        card.innerHTML=`
            <div class="event-image"><img src="${imgSrc}"></div>
            <div class="event-info">
                <h3>${event.eventName}</h3>
                <p>Time: ${event.dateTime}</p>
                <p>Location: ${event.location||'TBD'}</p>
                <p class="book-status">${statusText}</p>
            </div>
        `;

        // ===== new button for ER / HP /AM =====
        const infoDiv = card.querySelector('.event-info');
        if(currentRole==='Donor'){
            const btn = document.createElement('button');
            btn.textContent = 'Book';
            btn.className = 'book';
            if(!canBook) btn.disabled = true;
            btn.onclick = (e) => {
                e.stopPropagation();
                if(confirm(`Are you sure you want to book "${event.eventName}"?`)) {
                    currentEventID = event.eventID;
                    bookEvent();
                }
            };
            infoDiv.appendChild(btn);
        }else{
            // Event Organizer / Hospital / Admin 显示 Appointment Status
            const statusBtn = document.createElement('button');
            statusBtn.textContent = 'Appointment Status';
            statusBtn.className = 'status'; // apply the new CSS class
            statusBtn.onclick = (e)=> { e.stopPropagation(); viewAppointmentStatus(event.eventID); };
            infoDiv.appendChild(statusBtn);
        }

        if(currentRole==='Admin'){
            const del=document.createElement('div'); del.className='delete-btn'; del.innerText='×';
            del.onclick=(e)=>{ e.stopPropagation(); deleteEvent(event.eventID); }
            card.appendChild(del);
        }

        card.onclick=()=>openEvent(event.eventID);
        container.appendChild(card);
    });
}

/* ===== Book Event ===== */
function bookEventHandler(e, eventID){ e.stopPropagation(); currentEventID=eventID; bookEvent(); }

async function bookEvent(){
    if(!currentEventID){ alert('No event selected'); return; }
    try{
        const res = await fetch('book_event.php',{
            method:'POST',
            headers:{'Content-Type':'application/x-www-form-urlencoded'},
            body:`action=bookEvent&eventID=${currentEventID}`
        });
        const data = await res.json();
        if(data.status==='success'){ alert('Event booked successfully!'); closeModal(); loadEvents(); }
        else if(data.status==='requireHealthReport'){ showHealthModal(data.existingReport||{}); }
        else{ alert(data.message||'Booking failed'); }
    } catch(e){ console.error(e); alert('Booking failed'); }
}

/* ===== Health Modal ===== */
function showHealthModal(report){
    document.getElementById('bloodType').value = report.bloodType||'';
    document.getElementById('age').value = report.age||'';
    document.getElementById('dateLastDonate').value = report.dateLastDonate||'';
    document.getElementById('medicalHistory').value = report.medicalHistory||'';
    document.getElementById('weight').value = report.weight||'';
    document.getElementById('height').value = report.height||'';
    document.getElementById('healthModal').style.display='flex';
}
function submitHealthReport(){
    const report = {
        bloodType: document.getElementById('bloodType').value,
        age: document.getElementById('age').value,
        dateLastDonate: document.getElementById('dateLastDonate').value,
        medicalHistory: document.getElementById('medicalHistory').value.trim(),
        weight: document.getElementById('weight').value,
        height: document.getElementById('height').value
    };
    for(let k in report){ if(!report[k]) { alert('Please fill all fields'); return; } }
    fetch('book_event.php',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:`action=bookEvent&eventID=${currentEventID}&healthReport=${encodeURIComponent(JSON.stringify(report))}`
    }).then(r=>r.json()).then(data=>{
        if(data.status==='success'){ alert('Health report saved & event booked!'); closeHealthModal(); closeModal(); loadEvents(); }
        else alert(data.message||'Booking failed');
    }).catch(e=>{ console.error(e); alert('Booking failed'); });
}

function closeModal(){ document.getElementById('eventModal').style.display='none'; }
function closeHealthModal(){ document.getElementById('healthModal').style.display='none'; }

/* ===== Appointment Modal ===== */
async function viewAppointmentStatus(eventID){
    try {
        const res = await fetch(`view_appointment.php?eventID=${eventID}`);
        const data = await res.json();
        if(data.status !== 'success'){ alert(data.message||'Failed to load appointments'); return; }

        const tbody = document.querySelector('#appointmentTable tbody');
        tbody.innerHTML = '';
        data.donors.forEach(donor => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${donor.name}</td>
                <td>${donor.email}</td>
                <td>${donor.phone}</td>
            `;
            tbody.appendChild(tr);
        });

         const modalButtons = document.querySelector('#appointmentModal .modal-buttons');
        modalButtons.innerHTML = `<button onclick="closeAppointmentModal()">Close</button>`;

        if(currentRole === 'Hospital'){
            const infoBtn = document.createElement('button');
            infoBtn.textContent = 'Donor Information';
            infoBtn.onclick = () => {
            window.location.href = `donor_information.php?eventID=${eventID}`;
        };
            modalButtons.appendChild(infoBtn);
        }

        document.getElementById('appointmentModal').style.display = 'flex';
    } catch(e){ console.error(e); alert('Failed to load appointments'); }
}

function closeAppointmentModal(){
    document.getElementById('appointmentModal').style.display='none';
}

/* ===== Event Modal ===== */
async function openEvent(eventID){
    const res = await fetch(`event.php?action=getEventByID&eventID=${eventID}`);
    const data = await res.json();
    if(data.status!=='success'){ alert(data.message||'Failed'); return; }
    const e=data.event;
    currentEventID=e.eventID; canEdit=data.canEdit; currentRole=data.role;

    document.getElementById('modalTitle').innerText=e.eventName;
    document.getElementById('eventName').value=e.eventName;
    document.getElementById('dateTime').value=e.dateTime.replace(' ','T').slice(0,16);
    document.getElementById('maxDonors').value=e.maxDonors;
    document.getElementById('eventLocation').value=e.location||'';
    document.getElementById('description').value=e.description||'';
    document.getElementById('previewImage').src=e.image_url||'placeholder.jpg';

    const inputs=document.querySelectorAll('#eventModal input,#eventModal textarea');
    inputs.forEach(i=>i.readOnly=!canEdit && currentRole!=='Donor');

    const select=document.getElementById('hospitalSelect');
    if(currentRole==='Event Organizer'){ select.style.display='block'; select.value=e.hospitalID||''; }
    else select.style.display='none';

    const buttons=document.getElementById('modalButtons');
    buttons.innerHTML='';
    if(currentRole==='Donor') buttons.innerHTML=`<button onclick="closeModal()">Cancel</button><button onclick="bookEvent()">Book</button>`;
    else if(currentRole==='Admin') buttons.innerHTML=`<button onclick="closeModal()">Close</button>`;
    else if(currentRole==='Event Organizer' || currentRole==='Hospital' ){
        buttons.innerHTML=`<button onclick="closeModal()">Cancel</button>`;
        if(canEdit) buttons.innerHTML+=`<button onclick="saveEvent()">Save</button>`;
    }

    const imageInput = document.getElementById('imageUpload');
    if(currentRole === 'Admin'){
        imageInput.style.display = 'none';
    } else {
        imageInput.style.display = 'block';
    }
    document.getElementById('eventModal').style.display='flex';
}

/* ===== Image Preview ===== */
document.getElementById('imageUpload').addEventListener('change',function(){
    const file=this.files[0];
    if(file){ const reader=new FileReader(); reader.onload=e=>document.getElementById('previewImage').src=e.target.result; reader.readAsDataURL(file); }
});

/* ===== Delete Event ===== */
async function deleteEvent(id){
    if(!confirm('Delete this event?')) return;
    const res=await fetch('event.php',{ method:'POST', body:`action=deleteEvent&eventID=${id}`, headers:{'Content-Type':'application/x-www-form-urlencoded'}});
    const data=await res.json();
    if(data.status==='success'){ alert('Deleted'); loadEvents(); } else alert(data.message||'Delete failed');
}

/* ===== Add New Event ===== */
document.getElementById('createEventBtn').onclick=()=>{
    currentEventID=null; canEdit=true;
    document.getElementById('modalTitle').innerText='New Event';
    document.getElementById('eventName').value='';
    document.getElementById('dateTime').value='';
    document.getElementById('maxDonors').value='';
    document.getElementById('eventLocation').value='';
    document.getElementById('description').value='';
    document.getElementById('previewImage').src='placeholder.jpg';
    const select=document.getElementById('hospitalSelect');
    select.style.display='block'; select.value='';
    document.querySelectorAll('#eventModal input,#eventModal textarea').forEach(i=>i.readOnly=false);
    document.getElementById('modalButtons').innerHTML=`<button onclick="closeModal()">Cancel</button><button onclick="sendRequest()">Send Request</button>`;
    document.getElementById('eventModal').style.display='flex';
}

/* ===== Save / Send Request ===== */
async function sendRequest(){ submitEvent('sendRequest'); }
async function saveEvent(){ submitEvent('saveEvent'); }
async function submitEvent(actionType){
    if(!validateForm()) return;
    const formData = new FormData();
    formData.append('action', actionType);
    if(actionType==='saveEvent') formData.append('eventID', currentEventID);
    formData.append('eventName', document.getElementById('eventName').value);
    formData.append('dateTime', document.getElementById('dateTime').value);
    formData.append('maxDonors', document.getElementById('maxDonors').value);
    formData.append('description', document.getElementById('description').value);
    formData.append('location', document.getElementById('eventLocation').value);
    const file=document.getElementById('imageUpload').files[0];
    if(file) formData.append('imageUpload',file);
    // Always send hospitalID if current role is Event Organizer
    if(currentRole === 'Event Organizer'){
    formData.append('hospitalID', document.getElementById('hospitalSelect').value);
}

    try{
        const res=await fetch('event.php',{ method:'POST', body:formData });
        const data=await res.json();
        if(data.status==='success'){ 
            alert(actionType==='saveEvent'?'Event saved':'Request sent'); 
            closeModal(); loadEvents(); 
        } else alert(data.message||'Failed');
    } catch(e){ console.error(e); alert('Failed'); }
}

function validateForm(){
    if(!document.getElementById('eventName').value.trim() || 
       !document.getElementById('dateTime').value || 
       !document.getElementById('maxDonors').value ||
       !document.getElementById('eventLocation').value.trim()) { 
        alert('Please fill all required fields'); return false; 
    }
    return true;
}

/* ===== Init ===== */
loadEvents();
</script>
<script src="script.js"></script>
</body>
</html>
