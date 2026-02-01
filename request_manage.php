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
   Helper: Get current user info
==================== */
function getCurrentUserInfo($pdo, $userID) {
    $stmt = $pdo->prepare("SELECT userID, role, name FROM `user` WHERE userID = ?");
    $stmt->execute([$userID]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    return $user ?: ["userID" => $userID, "role" => "Unknown", "name" => "Unknown"];
}

$currentUser = getCurrentUserInfo($pdo, $adminID);

/* ====================
   Verify Admin Role
==================== */
if ($currentUser['role'] !== 'Admin') {
    echo json_encode([
        "status" => "error",
        "message" => "Permission denied",
        "currentUser" => $currentUser
    ]);
    exit;
}

/* ====================
   GET Requests
==================== */
$action = $_GET['action'] ?? 'pending'; // 默认返回 pending

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    switch($action) {

        /* ----- Pending Requests (status=2) ----- */
        case 'pending':
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
                LEFT JOIN event e ON r.eventID = e.eventID
                LEFT JOIN `user` u ON e.organizerID = u.userID
                WHERE r.status = 2
                ORDER BY r.requestID DESC
            ");
            $stmt->execute();
            $pending = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                "status" => "success",
                "data" => $pending,
                "currentUser" => $currentUser
            ]);
            exit;

        /* ----- Recorded Requests (status=1) ----- */
        case 'recorded':
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
                LEFT JOIN event e ON r.eventID = e.eventID
                LEFT JOIN `user` u ON e.organizerID = u.userID
                WHERE r.status = 1
                ORDER BY r.requestID DESC
            ");
            $stmt->execute();
            $recorded = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                "status" => "success",
                "data" => $recorded,
                "currentUser" => $currentUser
            ]);
            exit;

        default:
            echo json_encode([
                "status" => "error",
                "message" => "Invalid GET action",
                "currentUser" => $currentUser
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
            "currentUser" => $currentUser
        ]);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Validate request
        $stmt = $pdo->prepare("SELECT status FROM request WHERE requestID = ?");
        $stmt->execute([$requestID]);
        $request = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$request || intval($request['status']) !== 2) {
            throw new Exception("Invalid or already processed request");
        }

        /* ----- Approve ----- */
        if ($postAction === 'approve') {
            $record = isset($_POST['record']) ? intval($_POST['record']) : 0;
            $record = ($record === 1) ? 1 : 0;

            // Update event status
            $pdo->prepare("UPDATE event SET status = 1 WHERE eventID = ?")->execute([$eventID]);

            // Update request status
            $pdo->prepare("UPDATE request SET status = ? WHERE requestID = ?")->execute([$record, $requestID]);

            $pdo->commit();
            echo json_encode([
                "status" => "success",
                "message" => "Request approved",
                "currentUser" => $currentUser
            ]);
            exit;
        }

        /* ----- Reject ----- */
        if ($postAction === 'reject') {
            // Update event status
            $pdo->prepare("UPDATE event SET status = 0 WHERE eventID = ?")->execute([$eventID]);

            // Update request status
            $pdo->prepare("UPDATE request SET status = 3 WHERE requestID = ?")->execute([$requestID]);

            $pdo->commit();
            echo json_encode([
                "status" => "success",
                "message" => "Request rejected",
                "currentUser" => $currentUser
            ]);
            exit;
        }

        $pdo->rollBack();
        echo json_encode([
            "status" => "error",
            "message" => "Invalid POST action",
            "currentUser" => $currentUser
        ]);
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode([
            "status" => "error",
            "message" => $e->getMessage(),
            "currentUser" => $currentUser
        ]);
        exit;
    }
}

/* ====================
   Fallback
==================== */
echo json_encode([
    "status" => "error",
    "message" => "Invalid request method",
    "currentUser" => $currentUser
]);
exit;
?>
