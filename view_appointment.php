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
} catch(PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit;
}

/* =========================
   Helper Functions
========================= */
function getUserRole($pdo, $userID){
    $stmt = $pdo->prepare("SELECT role FROM user WHERE userID=?");
    $stmt->execute([$userID]);
    return $stmt->fetchColumn();
}

function checkPermission($requiredRoles, $role){
    if(!in_array($role, $requiredRoles)){
        echo json_encode(["status"=>"error","message"=>"Permission denied"]);
        exit;
    }
}

/* =========================
   Get User Role
========================= */
$role = getUserRole($pdo, $loggedInUserID);
if(!$role){
    echo json_encode(["status"=>"error","message"=>"User role not found"]);
    exit;
}

/* =========================
   Get Event ID
========================= */
$eventID = isset($_GET['eventID']) ? intval($_GET['eventID']) : 0;
if($eventID <= 0){
    echo json_encode(["status"=>"error","message"=>"Event ID required"]);
    exit;
}

/* =========================
   Permission Check
========================= */
checkPermission(['Admin','Event Organizer','Hospital'], $role);

/* =========================
   Fetch Event Info
========================= */
$stmt = $pdo->prepare("
    SELECT eventID, hospitalID, organizerID
    FROM event
    WHERE eventID = ? AND status = 1
");
$stmt->execute([$eventID]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$event){
    echo json_encode(["status"=>"error","message"=>"Event not found or inactive"]);
    exit;
}

/* =========================
   Hospital Ownership Check 
========================= */
$hospitalID = null;

if ($role === 'Hospital') {
    $stmt = $pdo->prepare("
        SELECT hospitalID
        FROM hospital
        WHERE userID = ?
    ");
    $stmt->execute([$loggedInUserID]);
    $hospitalID = $stmt->fetchColumn();

    if (!$hospitalID || $hospitalID != $event['hospitalID']) {
        echo json_encode(["status"=>"error","message"=>"Permission denied"]);
        exit;
    }
}

/* =========================
   Fetch Donors
========================= */
$stmt = $pdo->prepare("
    SELECT 
        a.appointmentID,
        a.status,
        u.userID,
        u.name,
        u.email,
        u.phoneNumber
    FROM appointment a
    JOIN user u ON a.userID = u.userID
    WHERE a.eventID = ?
      AND a.status IN ('pending','approved')
    ORDER BY u.name ASC
");
$stmt->execute([$eventID]);
$donors = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   Can Edit Flag
========================= */
$canEdit = ($role === 'Hospital');

/* =========================
   Return JSON
========================= */
echo json_encode([
    "status" => "success",
    "totalBookings" => count($donors),
    "donors" => $donors,
    "canEdit" => $canEdit
]);
exit;
?>
