<?php
session_start();
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors',1);

/* =========================
   Authentication Check
========================= */
if(!isset($_SESSION['userID'])){
    echo json_encode([
        "status"=>"error",
        "message"=>"Unauthorized"
    ]);
    exit;
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
    echo json_encode([
        "status"=>"error",
        "message"=>"Database connection failed: ".$e->getMessage()
    ]);
    exit;
}

/* =========================
   Get Appointment ID
========================= */
$appointmentID = $_POST['appointmentID'] ?? null;
if(!$appointmentID){
    echo json_encode([
        "status"=>"error",
        "message"=>"Appointment ID required"
    ]);
    exit;
}

/* =========================
   Verify appointment belongs to donor
========================= */
$stmt = $pdo->prepare("SELECT a.*, e.dateTime AS eventDateTime FROM appointment a JOIN event e ON a.eventID=e.eventID WHERE a.appointmentID=? AND a.userID=?");
$stmt->execute([$appointmentID, $donorID]);
$appointment = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$appointment){
    echo json_encode([
        "status"=>"error",
        "message"=>"Appointment not found"
    ]);
    exit;
}

/* =========================
   Only pending or approved appointments can be cancelled
========================= */
if(!in_array($appointment['status'], ['pending', 'approved'])){
    echo json_encode([
        "status"=>"error",
        "message"=>"Cannot cancel non-pending/approved appointment"
    ]);
    exit;
}

/* =========================
   Optionally check if event is in the past
========================= */
$now = new DateTime();
$eventTime = new DateTime($appointment['eventDateTime']);
if($eventTime < $now){
    echo json_encode([
        "status"=>"error",
        "message"=>"Cannot cancel past appointment"
    ]);
    exit;
}

/* =========================
   Update appointment status to cancelled
========================= */
$stmt = $pdo->prepare("UPDATE appointment SET status='cancelled' WHERE appointmentID=?");
$stmt->execute([$appointmentID]);

echo json_encode([
    "status"=>"success",
    "message"=>"Appointment cancelled successfully"
]);
exit;
?>
