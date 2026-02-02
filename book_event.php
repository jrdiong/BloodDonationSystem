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
   Check User Role
========================= */
$stmt = $pdo->prepare("SELECT role FROM user WHERE userID=?");
$stmt->execute([$loggedInUserID]);
$role = $stmt->fetchColumn();

if ($role !== 'Donor') {
    echo json_encode(["status" => "error", "message" => "Permission denied"]);
    exit;
}

/* =========================
   Handle Booking
   GET: Check if donor has filled health report
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['action'] ?? '') === 'checkHealthReport') {
    $stmt = $pdo->prepare("SELECT medicalHistory, weight, height, age FROM donor WHERE userID=?");
    $stmt->execute([$loggedInUserID]);
    $donor = $stmt->fetch(PDO::FETCH_ASSOC);

    $hasHealthReport = false;
    if ($donor) {
        $medicalHistoryFilled = isset($donor['medicalHistory']) && trim($donor['medicalHistory']) !== '';
        $weightFilled = isset($donor['weight']) && is_numeric($donor['weight']) && $donor['weight'] > 0;
        $heightFilled = isset($donor['height']) && is_numeric($donor['height']) && $donor['height'] > 0;
        $ageFilled = isset($donor['age']) && is_numeric($donor['age']) && $donor['age'] > 0;

        $hasHealthReport = $medicalHistoryFilled && $weightFilled && $heightFilled && $ageFilled;
    }

    echo json_encode([
        "status" => "success",
        "hasHealthReport" => $hasHealthReport
    ]);
    exit;
}

/* =========================
   POST: Book Event
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'bookEvent') {

    $eventID = $_POST['eventID'] ?? null;
    if (!$eventID) {
        echo json_encode(["status" => "error", "message" => "Event ID required"]);
        exit;
    }

    //  Ensure donor record exists (auto-create if missing)
    // Ensure donor record exists
    $stmt = $pdo->prepare("SELECT * FROM donor WHERE userID=?");
    $stmt->execute([$loggedInUserID]);
    $donor = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$donor) {
        $stmt = $pdo->prepare("INSERT INTO donor(userID) VALUES (?)");
        $stmt->execute([$loggedInUserID]);

        // Fetch the new donor record
        $stmt = $pdo->prepare("SELECT * FROM donor WHERE userID=?");
        $stmt->execute([$loggedInUserID]);
        $donor = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    //  Check if already booked (pending/approved)
    // Check if already booked (pending/approved)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointment WHERE userID=? AND eventID=? AND status IN ('pending','approved')");
    $stmt->execute([$loggedInUserID, $eventID]);
    if ($stmt->fetchColumn() > 0) {

        exit;
    }

    //  Check if previously cancelled
    // Check previously cancelled
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointment WHERE userID=? AND eventID=? AND status='cancelled'");
    $stmt->execute([$loggedInUserID, $eventID]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(["status" => "error", "message" => "You cannot rebook a cancelled event."]);
        exit;
    }

    //  Check event capacity
    // Check event capacity
    $stmt = $pdo->prepare("
        SELECT maxDonors,
               (SELECT COUNT(*) FROM appointment WHERE eventID=? AND status IN ('pending','approved')) AS currentBookings
        FROM event WHERE eventID=?
    ");
    $stmt->execute([$eventID, $eventID]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        echo json_encode(["status" => "error", "message" => "Event not found."]);
        exit;
    }

    if ($event['currentBookings'] >= $event['maxDonors']) {
        echo json_encode(["status" => "error", "message" => "Event is fully booked."]);
        exit;
    }

    // 5️⃣ Check health report
    // Check health report
    $healthReportData = $_POST['healthReport'] ?? null;
    $needHealthReport = empty($donor['medicalHistory']) || !$donor['weight'] || !$donor['height'];

    $medicalHistoryFilled = isset($donor['medicalHistory']) && trim($donor['medicalHistory']) !== '';
    $weightFilled = isset($donor['weight']) && is_numeric($donor['weight']) && $donor['weight'] > 0;
    $heightFilled = isset($donor['height']) && is_numeric($donor['height']) && $donor['height'] > 0;
    $ageFilled = isset($donor['age']) && is_numeric($donor['age']) && $donor['age'] > 0;

    $needHealthReport = !($medicalHistoryFilled && $weightFilled && $heightFilled && $ageFilled);

    if ($needHealthReport && !$healthReportData) {
        echo json_encode(["status"=>"requireHealthReport","message"=>"Please complete your health report before booking."]);
        exit;
    }

    //  Save health report if provided
    // Save health report if provided
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
                height = ?,
                age = ?
            WHERE userID = ?
        ");
        $stmt->execute([
            $decoded['medicalHistory'] ?? '',
            $decoded['weight'] ?? 0,
            $decoded['height'] ?? 0,
            $decoded['age'] ?? 0,
            $loggedInUserID
        ]);
    }

    //  Create appointment
    // Create appointment
    $stmt = $pdo->prepare("INSERT INTO appointment(userID, eventID, dateTime, status) VALUES (?, ?, NOW(), 'approved')");
    $stmt->execute([$loggedInUserID, $eventID]);

    //  Return success with updated booking count
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
