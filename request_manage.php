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

/* ====================
   Verify Admin Role
==================== */
$stmt = $pdo->prepare("SELECT role FROM `user` WHERE userID = ?");
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

    /* --------------------
       Get Pending Requests
       request.status = 2
    -------------------- */
    if ($action === 'pending') {

        $stmt = $pdo->prepare("
            SELECT 
                r.requestID,
                r.status AS requestStatus,
                e.eventID,
                e.eventName,
                e.dateTime,
                e.location,
                e.image_url,
                e.description,
                u.name AS organizerName
            FROM request r
            JOIN event e ON r.eventID = e.eventID
            JOIN `user` u ON e.organizerID = u.userID
            WHERE r.status = 2
            ORDER BY r.requestID DESC
        ");
        $stmt->execute();
        $pending = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            "status" => "success",
            "pending" => $pending
        ]);
        exit;
    }

    /* --------------------
       Get All Recorded Requests
       request.status = 0,1,3
    -------------------- */
    if ($action === 'recorded') {

        $stmt = $pdo->prepare("
            SELECT 
                r.requestID,
                r.eventID,
                r.status AS requestStatus,
                e.eventName,
                e.dateTime,
                e.location,
                e.image_url,
                e.description,
                u.name AS organizerName
            FROM request r
            JOIN event e ON r.eventID = e.eventID
            JOIN `user` u ON e.organizerID = u.userID
            WHERE r.status IN (0, 1, 3)
            ORDER BY r.requestID DESC
        ");
        $stmt->execute();
        $recorded = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            "status" => "success",
            "recorded" => $recorded
        ]);
        exit;
    }
}

/* ====================
   POST Approve / Reject
==================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $postAction = $_POST['action'] ?? null;
    $requestID  = $_POST['requestID'] ?? null;
    $eventID    = $_POST['eventID'] ?? null;

    if (!$postAction || !$requestID || !$eventID) {
        echo json_encode([
            "status" => "error",
            "message" => "Missing parameters",
            "debug" => [
                "action" => $postAction,
                "requestID" => $requestID,
                "eventID" => $eventID
            ]
        ]);
        exit;
    }

    try {
        $pdo->beginTransaction();

        /* Validate request */
        $stmt = $pdo->prepare("SELECT status FROM request WHERE requestID = ?");
        $stmt->execute([$requestID]);
        $request = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$request || intval($request['status']) !== 2) {
            throw new Exception("Invalid or already processed request");
        }

        /* ---------- Approve ---------- */
        if ($postAction === 'approve') {

            $record = isset($_POST['record']) ? intval($_POST['record']) : 0;
            if ($record !== 0 && $record !== 1) {
                $record = 0;
            }

            // event.status -> approved
            $stmt = $pdo->prepare("UPDATE event SET status = 1 WHERE eventID = ?");
            $stmt->execute([$eventID]);

            // request.status -> 1 (recorded) OR 0 (not recorded)
            $stmt = $pdo->prepare("UPDATE request SET status = ? WHERE requestID = ?");
            $stmt->execute([$record, $requestID]);

            $pdo->commit();
            echo json_encode(["status" => "success", "message" => "Request approved"]);
            exit;
        }

        /* ---------- Reject ---------- */
        if ($postAction === 'reject') {

            // event.status -> rejected / deleted
            $stmt = $pdo->prepare("UPDATE event SET status = 0 WHERE eventID = ?");
            $stmt->execute([$eventID]);

            // request.status -> rejected
            $stmt = $pdo->prepare("UPDATE request SET status = 3 WHERE requestID = ?");
            $stmt->execute([$requestID]);

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

/* ====================
   Invalid Request Fallback
==================== */
echo json_encode(["status" => "error", "message" => "Invalid request"]);
exit;
?>
