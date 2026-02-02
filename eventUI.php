<?php
session_start();

if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

$dbRole = $_SESSION['role'];

// Map DB roles to lowercase file-friendly roles
$roleMap = [
    "Admin" => "admin",
    "Event Organizer" => "organizer",
    "Hospital" => "hospital",
    "Donor" => "donor"
];

// Default to guest if DB value is unexpected
$role = $roleMap[$dbRole] ?? "guest";
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Events</title>
<link rel="stylesheet" href="style.css" />
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap" rel="stylesheet">

<style>
body { font-family:'Poppins',sans-serif; background:#fde7e7; margin:0; }

.main-container {
  max-width: 900px;
  margin: 120px 60px 0 320px;
  margin-left: calc(260px + (100% - 260px - 900px)/2);
  padding: 30px;
  background:#fff;
  border-radius:15px;
  box-shadow:0 8px 25px rgba(0,0,0,0.08);
}

.top-bar { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
.top-bar h2 { margin:0; }

.btn { padding:8px 16px; border-radius:8px; border:none; cursor:pointer; }
.btn.primary { background:#d21919; color:#fff; }
.btn.secondary { border:1px solid #1976d2; color:#1976d2; background:#fff; }
.btn.danger { border:1px solid #d32f2f; color:#d32f2f; background:#fff; }

.event-card { display:flex; align-items:center; background:#fff; border-radius:14px; padding:16px; margin-bottom:16px; box-shadow:0 2px 6px rgba(0,0,0,.1); }
.date-badge { width:90px; height:110px; background:#d21919; color:#fff; text-align:center; border-radius:14px; padding:15px; }
.date-badge .month { font-size:16px; }
.date-badge .day { font-size:36px; line-height:1; }
.date-badge .year { font-size:16px; }

.card-info { flex:1; padding:0 20px; }
.card-info h4 { margin:0 0 6px; font-size:16px; }
.card-info p { margin:2px 0; color:#555; font-size:14px; }

.card-actions { display:flex; gap:10px; }

/* Modal */
.modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,0.5); display:flex; justify-content:center; align-items:center; opacity:0; pointer-events:none; transition:opacity 0.3s; }
.modal-overlay.show { opacity:1; pointer-events:auto; }
.modal-card { background:#fff; border-radius:20px; width:90%; max-width:420px; padding:25px 30px; box-shadow:0 15px 40px rgba(0,0,0,0.2); position:relative; }
.modal-card h3 { margin-top:0; }
.close-modal { position:absolute; top:15px; right:20px; font-size:30px; cursor:pointer; color:#999; }
.close-modal:hover { color:#1976d2; }
.modal-card input { width:100%; padding:10px; margin:8px 0; border-radius:6px; border:1px solid #ccc; }
.modal-footer { display:flex; justify-content:flex-end; gap:10px; margin-top:10px; }
</style>
</head>

<body>
<?php include "navbar_$role.php"; ?>

<div class="main-container">

<div class="top-bar">
    <h2>Manage Events</h2>
    <?php if($role==='Event Organizer'): ?>
        <button class="btn primary" id="create-event-btn"><i class="fa fa-plus"></i> Create Event</button>
    <?php endif; ?>
</div>

<div class="card-list">

<?php
$events = [
    ["Blood Donation Drive","Hospital ABC","2026-02-10","10:00"],
    ["Health Awareness Camp","Community Center XYZ","2026-03-05","09:00"]
];

foreach($events as $event):
    $d = new DateTime($event[2]);
    $month = strtoupper($d->format('M'));
    $day = $d->format('d');
    $year = $d->format('Y');
?>
<div class="event-card">
    <div class="date-badge">
        <div class="month"><?= $month ?></div>
        <div class="day"><?= $day ?></div>
        <div class="year"><?= $year ?></div>
    </div>

    <div class="card-info">
        <h4><?= $event[0] ?></h4>
        <p><?= $event[1] ?></p>
        <p><?= date("h:i A", strtotime($event[3])) ?></p>
    </div>

    <div class="card-actions">
        <?php if($role==='organizer' || $role==='hospital'): ?>
            <button class="btn secondary edit-btn">Edit</button>
            <button class="btn danger delete-btn">Delete</button>
        <?php elseif($role==='admin'): ?>
            <button class="btn danger delete-btn">Delete</button>
        <?php endif; ?>
    </div>
</div>
<?php endforeach; ?>

</div>
</div>

<!-- Create/Edit Modal -->
<div class="modal-overlay" id="event-modal">
    <div class="modal-card">
        <h3>Create / Edit Event</h3>
        <span class="close-modal">&times;</span>

        <input type="text" id="event-name" placeholder="Event name">
        <input type="text" id="event-location" placeholder="Location">
        <input type="date" id="event-date">
        <input type="time" id="event-time">

        <div class="modal-footer">
            <button class="btn primary" id="save-event-btn">Save</button>
            <button class="btn" id="cancel-event-btn">Cancel</button>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal-overlay" id="delete-modal">
    <div class="modal-card">
        <h3>Delete Event?</h3>
        <span class="close-modal">&times;</span>
        <p>Are you sure you want to delete this event?</p>
        <div class="modal-footer">
            <button class="btn danger" id="confirm-delete-btn">Delete</button>
            <button class="btn" id="cancel-delete-btn">Cancel</button>
        </div>
    </div>
</div>

<script>
const USER_ROLE = "<?= $role ?>";

const eventModal = document.getElementById("event-modal");
const deleteModal = document.getElementById("delete-modal");
let editCard = null;
let deleteCard = null;

// Helper to convert 12h to 24h
function to24Hour(time12) {
    const [time, modifier] = time12.split(" ");
    let [hours, minutes] = time.split(":");
    if(modifier==="PM" && hours!=="12") hours = +hours+12;
    if(modifier==="AM" && hours==="12") hours="00";
    return `${hours.toString().padStart(2,'0')}:${minutes}`;
}

// Open Create Modal
const createBtn = document.getElementById("create-event-btn");
if(createBtn){   // make sure it exists
    createBtn.addEventListener("click", ()=>{
        editCard = null;
        // Update modal title
        eventModal.querySelector("h3").innerText = "Create Event";

        // Clear inputs
        eventModal.querySelector("#event-name").value="";
        eventModal.querySelector("#event-location").value="";
        eventModal.querySelector("#event-date").value="";
        eventModal.querySelector("#event-time").value="";

        // Show modal
        eventModal.classList.add("show");
    });
}


// Close modals via X or Cancel
eventModal.querySelector(".close-modal").addEventListener("click", ()=>{ eventModal.classList.remove("show"); });
document.getElementById("cancel-event-btn").addEventListener("click", ()=>{ eventModal.classList.remove("show"); });
deleteModal.querySelector(".close-modal").addEventListener("click", ()=>{ deleteModal.classList.remove("show"); });
document.getElementById("cancel-delete-btn").addEventListener("click", ()=>{ deleteModal.classList.remove("show"); });

// Edit event
document.addEventListener("click", e=>{
    if(e.target.classList.contains("edit-btn") && USER_ROLE==="Event Organizer"){
        editCard = e.target.closest(".event-card");
        const info = editCard.querySelector(".card-info");

        eventModal.querySelector("#event-name").value = info.querySelector("h4").innerText;
        eventModal.querySelector("#event-location").value = info.querySelector("p:nth-of-type(1)").innerText;

        // Time
        const timeText = info.querySelector("p:nth-of-type(2)").innerText;
        eventModal.querySelector("#event-time").value = to24Hour(timeText);

        // Date
        const dateBadge = editCard.querySelector(".date-badge");
        const day = dateBadge.querySelector(".day").innerText;
        const month = dateBadge.querySelector(".month").innerText;
        const year = dateBadge.querySelector(".year").innerText;
        const monthIndex = new Date(`${month} 1, 2000`).getMonth();
        const dateStr = `${year}-${String(monthIndex+1).padStart(2,'0')}-${String(day).padStart(2,'0')}`;
        eventModal.querySelector("#event-date").value = dateStr;

        eventModal.classList.add("show");
    }

    if(e.target.classList.contains("delete-btn") && USER_ROLE!=="hospital"){
        deleteCard = e.target.closest(".event-card");
        deleteModal.classList.add("show");
    }
});

// Save event
document.getElementById("save-event-btn").addEventListener("click", ()=>{
    const name = eventModal.querySelector("#event-name").value;
    const location = eventModal.querySelector("#event-location").value;
    const date = eventModal.querySelector("#event-date").value;
    const time = eventModal.querySelector("#event-time").value;

    if(!name||!location||!date||!time){ alert("Please fill all fields."); return; }

    if(editCard){
        // Update existing card
        const info = editCard.querySelector(".card-info");
        info.querySelector("h4").innerText = name;
        info.querySelector("p:nth-of-type(1)").innerText = location;
        info.querySelector("p:nth-of-type(2)").innerText = new Date(`1970-01-01T${time}`).toLocaleTimeString("en-US",{hour:"numeric",minute:"2-digit",hour12:true});

        // Update date badge
        const d = new Date(date);
        editCard.querySelector(".date-badge .month").innerText = d.toLocaleString('en-US',{month:'short'}).toUpperCase();
        editCard.querySelector(".date-badge .day").innerText = d.getDate();
        editCard.querySelector(".date-badge .year").innerText = d.getFullYear();
    } else {
        // Create new card
        const cardList = document.querySelector(".card-list");
        const d = new Date(date);
        const month = d.toLocaleString('en-US',{month:'short'}).toUpperCase();
        const day = d.getDate();
        const year = d.getFullYear();

        const newCard = document.createElement("div");
        newCard.className = "event-card";
        newCard.innerHTML = `
            <div class="date-badge">
                <div class="month">${month}</div>
                <div class="day" style="font-size:32px">${day}</div>
                <div class="year">${year}</div>
            </div>
            <div class="card-info">
                <h4>${name}</h4>
                <p>${location}</p>
                <p>${new Date(`1970-01-01T${time}`).toLocaleTimeString("en-US",{hour:"numeric",minute:"2-digit",hour12:true})}</p>
            </div>
            <div class="card-actions">
                <button class="btn secondary edit-btn">Edit</button>
                <button class="btn danger delete-btn">Delete</button>
            </div>
        `;
        cardList.appendChild(newCard);
    }

    eventModal.classList.remove("show");
});

// Confirm delete
document.getElementById("confirm-delete-btn").addEventListener("click", ()=>{
    if(deleteCard){ deleteCard.remove(); deleteCard=null; }
    deleteModal.classList.remove("show");
});
</script>
<script src="script.js"></script>

</body>
</html>
