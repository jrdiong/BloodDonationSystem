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

$userID = $_SESSION['userID'];

/* =========================
   Get POST Data
========================= */
$appointmentID = $_POST['appointmentID'] ?? null;
$eventID = $_POST['eventID'] ?? null;
$rating = $_POST['rating'] ?? null;
$comment = $_POST['comment'] ?? null;

/* =========================
   Basic Validation
========================= */
if (!$appointmentID || !$eventID || !$rating || !$comment) {
    echo json_encode(["status" => "error", "message" => "Missing required fields"]);
    exit;
}

$rating = intval($rating);
if($rating < 1 || $rating > 5){
    echo json_encode(["status" => "error", "message" => "Invalid rating value"]);
    exit;
}

/* =========================
   Database Connection
========================= */
$host = 'localhost';
$db = 'cbdc_system';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    /* =========================
       Insert or Update Feedback
       (Assumes feedback table has unique key on appointmentID)
    ========================== */
    $stmt = $pdo->prepare("
        INSERT INTO feedback (appointmentID, eventID, userID, rating, comment, dateTime)
        VALUES (?, ?, ?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE 
            rating = VALUES(rating), 
            comment = VALUES(comment), 
            dateTime = NOW()
    ");

    $stmt->execute([$appointmentID, $eventID, $userID, $rating, $comment]);

    echo json_encode(["status" => "success", "message" => "Feedback submitted successfully"]);

} catch(PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>