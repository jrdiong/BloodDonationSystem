<?php
session_start();

if (!isset($_SESSION['userID'], $_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

$loggedInUserID = $_SESSION['userID'];
$sessionRole = $_SESSION['role']; // already normalized

// map normalized role → navbar / logic role
$roleMap = [
    'admin' => 'admin',
    'event organizer' => 'organizer',
    'hospital' => 'hospital',
    'donor' => 'donor'
];

$role = $roleMap[$sessionRole] ?? 'guest';

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Event History</title>
<link rel="stylesheet" href="style.css" />
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap" rel="stylesheet">

<style>
body {
    font-family: 'Poppins', sans-serif;
    background: #fde7e7;
    margin: 0;
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
.top-bar h2 {
    color: #f44040;
    margin-bottom: 20px;
}
.card-list {
    display: grid;
    grid-template-columns: repeat(3, 1fr); /* 3 cards per row */
    gap: 20px; /* spacing between cards */
}

/* Vertical Card */
.event-card {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    overflow: hidden;
    margin-bottom: 20px;
    cursor: pointer;
    transition: transform 0.2s;
    max-width: 300px;
}
.event-card:hover { transform: translateY(-2px); }

.event-card img {
    width: 100%;
    height: 150px;
    object-fit: cover;
}

.card-content {
    padding: 12px 16px;
}
.card-content h4 { margin: 0 0 6px 0; font-size: 16px; color: #f44040; }
.card-content p { margin: 4px 0; font-size: 14px; color:#555; }
.card-content p i {
    margin-right: 6px;
    color: #d21919;  /* match your theme */
    width: 18px;      /* optional: fixed width for alignment */
    text-align: center;
}
.card-actions {
    padding: 10px 16px;
    text-align: right;
}
.btn {
    padding: 6px 12px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    font-size: 13px;
}
.btn.primary { background:#d21919; color:#fff; }

/* Modal overlay */
.modal-overlay {
    position: fixed; inset:0;
    background: rgba(0,0,0,0.5);
    display: flex; justify-content: center; align-items: center;
    opacity:0; pointer-events:none; transition:0.3s; z-index:1000;
}
.modal-overlay.show { opacity:1; pointer-events:auto; }

.modal-card {
    background: #fff; border-radius:20px; width:90%; max-width:600px;
    padding:25px 30px; max-height:90vh; overflow-y:auto;
    transform: translateY(-30px); transition:0.3s;
}
.modal-overlay.show .modal-card { transform: translateY(0); }

.modal-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
.modal-header h3 { margin:0; color: #f44040; }
.modal-header .close-modal { font-size:20px; cursor:pointer; color:#999; }
.modal-header .close-modal:hover { color:#1976d2; }
.modal-body p i {
    margin-right: 6px;
    color: #d21919;  /* match your theme */
    width: 18px;      /* optional: fixed width for alignment */
    text-align: center;
}

.feedback-section { margin-top:20px; border-top:1px solid #eee; padding-top:10px; max-height: 250px; overflow-y: auto; }
.feedback { margin-bottom:12px; }
.feedback .name { font-weight:600; font-size:14px; margin-bottom:2px; }
.feedback .rating { color:#f5a623; margin-bottom:2px; }
.feedback .comment { font-size:14px; color:#555; margin-bottom:2px; }
.feedback .time { font-size:12px; color:#888; }
@media (max-width: 1024px) {
    .card-list {
        grid-template-columns: repeat(2, 1fr); /* 2 per row on medium screens */
    }
}

@media (max-width: 700px) {
    .card-list {
        grid-template-columns: 1fr; /* 1 per row on mobile */
    }
}
</style>
</head>
<body>

<?php include "navbar_$role.php"; ?>

<div class="main-container">
    <div class="top-bar">
        <h2>History</h2>
    </div>

    <div class="card-list" >

    <?php
    $past_events = [
        [
            "Blood Donation Drive","Hospital ABC","10:00 AM",
            "FEB","10","2026",
            "https://picsum.photos/300/150?random=1",
            [
                ["Alice",5,"Great experience!","1 day ago"],
                ["Bob",4,"Well organized.","2 days ago"],
                ["Charlie",5,"Very informative.","3 days ago"]
            ]
        ],
        [
            "Health Awareness Camp","Community Center XYZ","9:00 AM",
            "MAR","5","2026",
            "https://picsum.photos/300/150?random=2",
            [
                ["Charlie",5,"Very informative.","3 days ago"]
            ]
        ]
        ,
        [
            "Health Awareness Camp","Community Center XYZ","9:00 AM",
            "MAR","5","2026",
            "https://picsum.photos/300/150?random=2",
            [
                ["Charlie",5,"Very informative.","3 days ago"]
            ]
        ]
    ];

    foreach($past_events as $event):
    ?>
    <div class="event-card" 
         data-name="<?= $event[0] ?>" 
         data-location="<?= $event[1] ?>" 
         data-time="<?= $event[2] ?>" 
         data-date="<?= $event[3] ?> <?= $event[4] ?>, <?= $event[5] ?>" 
         data-image="<?= $event[6] ?>"
         data-feedback='<?= json_encode($event[7]) ?>'>
        <img src="<?= $event[6] ?>" alt="Event Image">
        <div class="card-content">
            <h4><?= $event[0] ?></h4>
            <p><strong><i class='fa-regular fa-compass'></i>Location:</strong> <?= $event[1] ?></p>
            <p><strong><i class='fa-regular fa-calendar'></i>Date & Time:</strong> <?= $event[3] ?> <?= $event[4] ?>, <?= $event[5] ?> | <?= $event[2] ?></p>
        </div>
        <div class="card-actions">
            <button class="btn primary view-btn">View Details</button>
        </div>
    </div>
    <?php endforeach; ?>

    </div>
</div>

<!-- Details Modal -->
<div class="modal-overlay" id="details-modal">
    <div class="modal-card">
        <div class="modal-header">
            <h3 id="modal-title">Event Name</h3>
            <i class="fa-solid fa-xmark close-modal"></i>
        </div>
        <div class="modal-body">
            <img id="modal-image" src="" alt="" style="width:100%; height:180px; object-fit:cover; border-radius:12px; margin-bottom:12px;">
            <p><strong><i class='fa-regular fa-compass'></i>Location:</strong> <span id="modal-location"></span></p>
            <p><strong><i class='fa-regular fa-calendar'></i>Date & Time:</strong> <span id="modal-datetime"></span></p>
            <p><strong><i class='fa-regular fa-message'></i>Description:</strong></p>
            <p id="modal-desc">This is a sample description of the event.</p>

            <div class="feedback-section">
                <h4>Feedback</h4>
                <div id="feedback-list"></div>
            </div>
        </div>
    </div>
</div>

<script>
const detailsModal = document.getElementById("details-modal");
const modalTitle = document.getElementById("modal-title");
const modalLocation = document.getElementById("modal-location");
const modalDatetime = document.getElementById("modal-datetime");
const modalDesc = document.getElementById("modal-desc");
const modalImage = document.getElementById("modal-image");
const feedbackList = document.getElementById("feedback-list");

document.querySelectorAll(".view-btn").forEach(btn=>{
    btn.addEventListener("click", e=>{
        const card = e.target.closest(".event-card");
        modalTitle.innerText = card.dataset.name;
        modalLocation.innerText = card.dataset.location;
        modalDatetime.innerText = card.dataset.date + " " + card.dataset.time;
        modalDesc.innerText = "This is a placeholder description for the event.";
        modalImage.src = card.dataset.image;

        // Load feedback
        const feedbacks = JSON.parse(card.dataset.feedback);
        feedbackList.innerHTML = "";
        feedbacks.forEach(f=>{
            const div = document.createElement("div");
            div.className = "feedback";
            div.innerHTML = `
                <div class="name">${f[0]}</div>
                <div class="rating">${"★".repeat(f[1])}${"☆".repeat(5-f[1])}</div>
                <div class="comment">${f[2]}</div>
                <div class="time">${f[3]}</div>
            `;
            feedbackList.appendChild(div);
        });

        detailsModal.classList.add("show");
    });
});

detailsModal.querySelectorAll(".close-modal").forEach(btn=>{
    btn.addEventListener("click", ()=>detailsModal.classList.remove("show"));
});
</script>
<script src="script.js"></script>

</body>
</html>
