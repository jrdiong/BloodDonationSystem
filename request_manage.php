<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

/* ====================
   Authentication
==================== */
if (!isset($_SESSION['userID'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

$adminID = $_SESSION['userID'];

/* ====================
   Database Connection
==================== */
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

/* Verify Admin Role */
$stmt = $pdo->prepare("SELECT role FROM user WHERE userID=?");
$stmt->execute([$adminID]);
$role = $stmt->fetchColumn();

if($role !== 'Admin'){
    echo json_encode(["status" => "error", "message" => "Permission denied"]);
    exit;
}

/* ====================
   GET Pending Requests
==================== */
if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['action'] ?? '') === 'pending') {
    $stmt = $pdo->prepare("
        SELECT e.eventID, e.eventName, e.dateTime, e.location, e.image_url, e.description,
               u.name AS organizerName
        FROM event e
        JOIN user u ON e.organizerID = u.userID
        WHERE e.status = 2
        ORDER BY e.dateTime ASC
    ");
    $stmt->execute();
    $pending = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["status" => "success", "pending" => $pending]);
    exit;
}

/* ====================
   GET Recorded Requests
==================== */
if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['action'] ?? '') === 'recorded') {
    $stmt = $pdo->prepare("
        SELECT r.requestID, r.eventID, r.status AS recordStatus,
               e.eventName, e.dateTime, e.location, e.image_url, e.description
        FROM request r
        JOIN event e ON r.eventID = e.eventID
        WHERE r.status = 1
        ORDER BY r.requestID DESC
    ");
    $stmt->execute();
    $recorded = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["status" => "success", "recorded" => $recorded]);
    exit;
}

/* ====================
   POST Approve Request
==================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'approve') {
    $eventID = $_POST['eventID'] ?? null;
    $record = isset($_POST['record']) ? intval($_POST['record']) : 0;

    if (!$eventID || ($record !== 0 && $record !== 1)) {
        echo json_encode(["status" => "error", "message" => "Missing parameters"]);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Approve event
        $stmt = $pdo->prepare("UPDATE event SET status = 1 WHERE eventID = ?");
        $stmt->execute([$eventID]);

        // Insert into request table with record choice
        $stmt = $pdo->prepare("INSERT INTO request(eventID, status) VALUES(?, ?)");
        $stmt->execute([$eventID, $record]);

        $pdo->commit();
        echo json_encode(["status" => "success", "message" => "Request approved"]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
    exit;
}

/* ====================
   POST Reject Request
==================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'reject') {
    $eventID = $_POST['eventID'] ?? null;

    if (!$eventID) {
        echo json_encode(["status" => "error", "message" => "Event ID required"]);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Reject event
        $stmt = $pdo->prepare("UPDATE event SET status = 0 WHERE eventID = ?");
        $stmt->execute([$eventID]);

        $pdo->commit();
        echo json_encode(["status" => "success", "message" => "Request rejected"]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
    exit;
}

/* ====================
   Invalid Request
==================== */
echo json_encode(["status" => "error", "message" => "Invalid request"]);
?>
