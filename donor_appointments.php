<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

/* =========================
   Authentication Check
========================= */
if (!isset($_SESSION['userID'])) {
    die("Unauthorized");
}

$donorID = $_SESSION['userID'];

/* =========================
   Database Connection
========================= */
$host = 'localhost';
$dbname = 'cbdc_system';
$username = 'root';
$password = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

/* =========================
   Handle Cancel Appointment
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'cancelAppointment') {
    $appointmentID = $_POST['appointmentID'] ?? null;
    if (!$appointmentID) { echo json_encode(["status"=>"error","message"=>"Appointment ID required"]); exit; }

    // Check appointment belongs to donor and is in future
    $stmt = $pdo->prepare("SELECT a.*, e.dateTime AS eventDateTime FROM appointment a JOIN event e ON a.eventID=e.eventID WHERE a.appointmentID=? AND a.userID=?");
    $stmt->execute([$appointmentID, $donorID]);
    $app = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$app) { echo json_encode(["status"=>"error","message"=>"Appointment not found"]); exit; }

    $now = new DateTime();
    $eventTime = new DateTime($app['eventDateTime']);
    if ($eventTime < $now || $app['status'] !== 'pending') {
        echo json_encode(["status"=>"error","message"=>"Cannot cancel past or non-pending appointment"]);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE appointment SET status='cancelled' WHERE appointmentID=?");
    $stmt->execute([$appointmentID]);
    echo json_encode(["status"=>"success","message"=>"Appointment cancelled"]);
    exit;
}

/* =========================
   Fetch Donor Appointments
========================= */
$stmt = $pdo->prepare("
    SELECT a.appointmentID, a.status AS appointmentStatus, a.dateTime AS appointmentDateTime,
           e.eventID, e.eventName, e.location, e.dateTime AS eventDateTime, e.image_url,
           e.maxDonors,
           (SELECT COUNT(*) FROM appointment WHERE eventID=e.eventID AND status IN ('pending','approved')) AS currentBookings
    FROM appointment a
    JOIN event e ON a.eventID = e.eventID
    WHERE a.userID = ?
    ORDER BY e.dateTime DESC
");
$stmt->execute([$donorID]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   Update status based on current time
========================= */
$now = new DateTime();
foreach ($appointments as &$app) {
    $eventTime = new DateTime($app['eventDateTime']);
    if ($app['appointmentStatus'] === 'cancelled') {
        $app['status'] = 'cancelled';
    } elseif ($eventTime < $now) {
        $app['status'] = 'completed';
    } else {
        $app['status'] = 'pending';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Appointments</title>
<style>
body { font-family: Arial, sans-serif; background:#f5f5f5; margin:0; padding:20px; }
h2 { text-align:center; }
.appointment-card { display:flex; background:#fff; border-radius:15px; padding:15px; margin:15px auto; width:80%; cursor:pointer; transition:0.2s; position:relative; }
.appointment-card:hover { box-shadow:0 4px 12px rgba(0,0,0,0.15); }
.appointment-card img { width:120px; height:90px; border-radius:12px; object-fit:cover; flex-shrink:0; }
.appointment-info { margin-left:20px; flex:1; }
.appointment-info h3 { margin:0; font-size:20px; }
.appointment-info p { margin:4px 0; }
.cancel-btn { position:absolute; bottom:10px; right:10px; padding:6px 12px; background:red; color:#fff; border:none; border-radius:6px; cursor:pointer; }
.greyed { filter: grayscale(100%); opacity:0.6; }
.modal { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; }
.modal-content { background:#fff; width:600px; max-width:90%; margin:60px auto; padding:25px; border-radius:15px; position:relative; }
.close-modal { position:absolute; top:10px; right:15px; font-size:22px; cursor:pointer; }
.modal-content img { width:100%; height:200px; object-fit:cover; border-radius:12px; margin-bottom:15px; }
</style>
</head>
<body>
<h2>My Appointments</h2>
<div id="appointmentList"></div>

<!-- Modal -->
<div class="modal" id="appointmentModal">
  <div class="modal-content">
    <span class="close-modal" onclick="closeModal()">×</span>
    <img id="modalImage" src="">
    <h3 id="modalTitle"></h3>
    <p><strong>Date & Time:</strong> <span id="modalDate"></span></p>
    <p><strong>Location:</strong> <span id="modalLocation"></span></p>
    <p><strong>Description:</strong></p>
    <p id="modalDescription"></p>
  </div>
</div>

<script>
let appointments = <?php echo json_encode($appointments); ?>;

/* ===== Render Appointment Cards ===== */
function renderAppointments(){
    const container = document.getElementById('appointmentList');
    container.innerHTML = '';
    appointments.forEach(app=>{
        const card = document.createElement('div');
        card.className='appointment-card';
        if(app.status==='completed' || app.status==='cancelled' || app.status==='rejected') card.classList.add('greyed');

        card.innerHTML=`
            <img src="${app.image_url||'placeholder.jpg'}">
            <div class="appointment-info">
                <h3>${app.eventName}</h3>
                <p>Date: ${app.eventDateTime}</p>
                <p>Location: ${app.location||'TBD'}</p>
                <p>Bookings: ${app.currentBookings}/${app.maxDonors}</p>
                <p>Status: ${app.status}</p>
            </div>
        `;
        card.onclick=()=>openModal(app);
        if(app.status==='pending'){
            const btn=document.createElement('button');
            btn.className='cancel-btn';
            btn.innerText='Cancel Appointment';
            btn.onclick=(e)=>{ e.stopPropagation(); cancelAppointment(app.appointmentID); };
            card.appendChild(btn);
        }
        container.appendChild(card);
    });
}

/* ===== Modal Functions ===== */
function openModal(app){
    document.getElementById('modalImage').src=app.image_url||'placeholder.jpg';
    document.getElementById('modalTitle').innerText=app.eventName;
    document.getElementById('modalDate').innerText=app.eventDateTime;
    document.getElementById('modalLocation').innerText=app.location||'TBD';
    document.getElementById('modalDescription').innerText=app.description||'No description';
    document.getElementById('appointmentModal').style.display='block';
}
function closeModal(){ document.getElementById('appointmentModal').style.display='none'; }

/* ===== Cancel Appointment ===== */
async function cancelAppointment(appointmentID){
    if(!confirm('Cancel this appointment?')) return;
    const form = new FormData();
    form.append('action','cancelAppointment');
    form.append('appointmentID',appointmentID);
    const res = await fetch('donor_appointments.php',{ method:'POST', body:form });
    const data = await res.json();
    if(data.status==='success'){
        alert('Appointment cancelled');
        location.reload();
    } else alert(data.message||'Failed');
}

/* ===== Init ===== */
renderAppointments();
</script>
</body>
</html>
