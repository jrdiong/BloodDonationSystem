<?php
session_start();
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

/* =========================
   Authentication Check
========================= */
if (!isset($_SESSION['userID'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
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
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit;
}

/* =========================
   Handle Cancel Appointment
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'cancelAppointment') {
    $appointmentID = $_POST['appointmentID'] ?? null;
    if (!$appointmentID) {
        echo json_encode(["status" => "error", "message" => "Appointment ID required"]);
        exit;
    }

    // Check appointment belongs to donor and is pending/future
    $stmt = $pdo->prepare("
        SELECT a.*, e.dateTime AS eventDateTime 
        FROM appointment a 
        JOIN event e ON a.eventID = e.eventID 
        WHERE a.appointmentID = ? AND a.userID = ?
    ");
    $stmt->execute([$appointmentID, $donorID]);
    $app = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$app) {
        echo json_encode(["status" => "error", "message" => "Appointment not found"]);
        exit;
    }

    $now = new DateTime();
    $eventTime = new DateTime($app['eventDateTime']);
    if ($eventTime < $now || $app['status'] !== 'pending') {
        echo json_encode(["status" => "error", "message" => "Cannot cancel past or non-pending appointment"]);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE appointment SET status = 'cancelled' WHERE appointmentID = ?");
    $stmt->execute([$appointmentID]);
    echo json_encode(["status" => "success", "message" => "Appointment cancelled"]);
    exit;
}

/* =========================
   Fetch Donor Appointments with Event Details
========================= */
$stmt = $pdo->prepare("
    SELECT 
        a.appointmentID, 
        a.status AS appointmentStatus,
        e.eventID, 
        e.eventName, 
        e.location, 
        e.dateTime AS eventDateTime, 
        e.image_url, 
        e.description,
        e.maxDonors,
        (SELECT COUNT(*) 
         FROM appointment 
         WHERE eventID = e.eventID AND status IN ('pending','approved')
        ) AS currentBookings
    FROM appointment a
    JOIN event e ON a.eventID = e.eventID
    WHERE a.userID = ?
    ORDER BY e.dateTime DESC
");
$stmt->execute([$donorID]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   Update appointment status based on current time
========================= */
$now = new DateTime();
foreach ($appointments as &$app) {
    $eventTime = new DateTime($app['eventDateTime']);

    $app['displayStatus'] = $app['appointmentStatus'];

    if ($app['appointmentStatus'] === 'approved' && $eventTime < $now) {
        $app['displayStatus'] = 'completed';
    }
}

/* =========================
   Return JSON
========================= */
echo json_encode([
    "status" => "success",
    "appointments" => $appointments
]);
exit;
