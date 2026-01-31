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
if ($role !== 'Admin') {
    echo json_encode(["status" => "error", "message" => "Permission denied"]);
    exit;
}

/* ====================
   GET Requests
==================== */
$action = $_GET['action'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($action === 'pending') {
        // Pending = event.status = 2
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

    if ($action === 'recorded') {
        // Recorded = request.status = 0 or 1
        $stmt = $pdo->prepare("
            SELECT r.requestID, r.eventID, r.status AS recordStatus,
                   e.eventName, e.dateTime, e.location, e.image_url, e.description
            FROM request r
            JOIN event e ON r.eventID = e.eventID
            ORDER BY r.requestID DESC
        ");
        $stmt->execute();
        $recorded = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["status" => "success", "recorded" => $recorded]);
        exit;
    }
}

/* ====================
   POST Approve / Reject
==================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['action'] ?? null;
    $eventID = $_POST['eventID'] ?? null;

    if (!$postAction || !$eventID) {
        echo json_encode(["status" => "error", "message" => "Missing parameters"]);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Check if event exists
        $stmt = $pdo->prepare("SELECT status FROM event WHERE eventID = ?");
        $stmt->execute([$eventID]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$event) {
            throw new Exception("Event not found");
        }

        if ($postAction === 'approve') {
            $record = isset($_POST['record']) ? intval($_POST['record']) : 0;
            if ($record !== 0 && $record !== 1) $record = 0;

            // Update event.status based on record
            $newEventStatus = $record === 1 ? 1 : 0;
            $stmt = $pdo->prepare("UPDATE event SET status = ? WHERE eventID = ?");
            $stmt->execute([$newEventStatus, $eventID]);

            // Insert into request table
            $stmt = $pdo->prepare("INSERT INTO request(eventID, status) VALUES (?, ?)");
            $stmt->execute([$eventID, $record]);

            $pdo->commit();
            echo json_encode(["status" => "success", "message" => "Request approved"]);
            exit;
        }

        if ($postAction === 'reject') {
            // Reject → event.status stays 2, no need to update
            $pdo->commit();
            echo json_encode(["status" => "success", "message" => "Request rejected"]);
            exit;
        }

        $pdo->rollBack();
        echo json_encode(["status" => "error", "message" => "Invalid action"]);
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        exit;
    }
}

echo json_encode(["status" => "error", "message" => "Invalid request"]);
exit;
?>
