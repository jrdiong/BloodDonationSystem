<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

/* =========================
   Authentication Check
========================= */
if (!isset($_SESSION['userID'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

$loggedInUserID = $_SESSION['userID'];

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
   Get User Role
========================= */
$stmt = $pdo->prepare("SELECT role FROM user WHERE userID=?");
$stmt->execute([$loggedInUserID]);
$role = $stmt->fetchColumn();

if ($role !== 'Donor') {
    echo json_encode(["status" => "error", "message" => "Permission denied"]);
    exit;
}

/* =========================
   POST: Book Event with Health Report Check
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'bookEvent') {

    $eventID = $_POST['eventID'] ?? null;
    if (!$eventID) {
        echo json_encode(["status" => "error", "message" => "Event ID required"]);
        exit;
    }

    // 1️⃣ Get donor info
    $stmt = $pdo->prepare("SELECT * FROM donor WHERE userID=?");
    $stmt->execute([$loggedInUserID]);
    $donor = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$donor) {
        echo json_encode(["status" => "error", "message" => "Donor record not found"]);
        exit;
    }

    // 2️⃣ Check if health report submitted
    $healthReportData = $_POST['healthReport'] ?? null;
    $needHealthReport = empty($donor['medicalHistory']) || !$donor['weight'] || !$donor['height'];

    if ($needHealthReport && !$healthReportData) {
        // Health report required, front-end should show modal
        echo json_encode([
            "status" => "requireHealthReport",
            "message" => "Please complete your health report before booking."
        ]);
        exit;
    }

    // 3️⃣ Save health report if submitted
    if ($healthReportData) {
        $decoded = json_decode($healthReportData, true);
        if (!$decoded) {
            echo json_encode(["status" => "error", "message" => "Invalid health report data"]);
            exit;
        }

        $stmt = $pdo->prepare("
            UPDATE donor SET
                medicalHistory = ?,
                weight = ?,
                height = ?
            WHERE userID = ?
        ");
        $stmt->execute([
            $decoded['medicalHistory'] ?? '',
            $decoded['weight'] ?? 0,
            $decoded['height'] ?? 0,
            $loggedInUserID
        ]);
    }

    // 4️⃣ Check if already booked this event
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointment WHERE eventID=? AND userID=?");
    $stmt->execute([$eventID, $loggedInUserID]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(["status" => "error", "message" => "You have already booked this event."]);
        exit;
    }

    // 5️⃣ Check if event exists and is not full
    $stmt = $pdo->prepare("
        SELECT maxDonors,
               (SELECT COUNT(*) FROM appointment WHERE eventID=? AND status IN ('pending','approved')) AS currentBookings
        FROM event WHERE eventID=? AND status=1
    ");
    $stmt->execute([$eventID, $eventID]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        echo json_encode(["status" => "error", "message" => "Event not found or not active."]);
        exit;
    }

    if ($event['currentBookings'] >= $event['maxDonors']) {
        echo json_encode(["status" => "error", "message" => "Event is fully booked."]);
        exit;
    }

    // 6️⃣ Create appointment
    $stmt = $pdo->prepare("INSERT INTO appointment(eventID, userID, dateTime, status) VALUES (?, ?, NOW(), 'approved')");
    $stmt->execute([$eventID, $loggedInUserID]);

    $current = $event['currentBookings'] + 1;
    $max = $event['maxDonors'];

    echo json_encode([
        "status" => "success",
        "message" => "Event booked successfully",
        "bookingCount" => "$current/$max"
    ]);
    exit;
}

?>
