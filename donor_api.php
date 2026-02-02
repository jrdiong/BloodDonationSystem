<?php
session_start();
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

error_log("POST DATA: " . print_r($_POST, true));


// ===== ROLE CHECK =====
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['Hospital', 'Admin', 'Event Organizer'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// ===== DATABASE CONNECTION =====
$host = 'localhost';
$db   = 'cbdc_system';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}

// ===== GET ACTION =====
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    // ===== GET ALL DONORS =====
    case 'getDonors':
    $eventID = $_GET['eventID'] ?? 0;  // 获取 eventID
    if (!$eventID) {
        echo json_encode(['status'=>'error','message'=>'Event ID missing']);
        exit;
    }

   $stmt = $pdo->prepare("
    SELECT u.userID, u.name, u.email, u.phoneNumber, u.image_url,
           d.bloodType, d.age, d.weight, d.height, d.medicalHistory, d.dateLastDonate,
           a.status AS appointmentStatus
    FROM donor d
    JOIN user u ON d.userID = u.userID
    INNER JOIN appointment a ON a.userID = u.userID AND a.eventID = ?
    ORDER BY u.name ASC
    ");

    $stmt->execute([$eventID]);
    $donors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['status' => 'success', 'donors' => $donors]);
    break;


    // ===== GET SINGLE DONOR HEALTH REPORT =====
    case 'getDonorHealth':
    $donorID = $_POST['donorID'] ?? $_GET['donorID'] ?? 0;  
    if (!$donorID) {
        echo json_encode(['status'=>'error','message'=>'Donor ID missing']);
        exit;
    }

    $eventID = $_POST['eventID'] ?? $_GET['eventID'] ?? 0;
    if (!$eventID) {
    echo json_encode(['status'=>'error','message'=>'Event ID missing']);
    exit;
    }

    try {
        $stmt = $pdo->prepare("
    SELECT u.userID, u.name, u.email, u.phoneNumber, u.image_url,
           d.bloodType, d.age, d.weight, d.height, d.medicalHistory, d.dateLastDonate,
           a.status AS healthStatus
            FROM donor d
            JOIN user u ON d.userID = u.userID
            LEFT JOIN appointment a 
                ON a.userID = u.userID AND a.eventID = ?
            WHERE u.userID = ?
            LIMIT 1
        ");
        $stmt->execute([$eventID, $donorID]);
        $donor = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($donor) {
            echo json_encode(['status'=>'success','donor'=>$donor]);
        } else {
            echo json_encode(['status'=>'error','message'=>'Donor not found']);
        }
    } catch(PDOException $e) {
        echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
    }
    break;


  
       // ===== APPROVE / REJECT =====
        case 'updateStatus':
        $donorID = $_POST['donorID'] ?? 0;
        $eventID = $_POST['eventID'] ?? 0; 
        $status = strtolower($_POST['status'] ?? '');
        $allowedStatus = ['pending', 'approved', 'rejected'];

        if (!$eventID) {
            echo json_encode(['status' => 'error', 'message' => 'Event ID missing']); 
        }

        if (!in_array($status, $allowedStatus)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid status']);
            exit;
        }

        // ✅ based on userID + eventID update appointment
        $stmt = $pdo->prepare("
            UPDATE appointment
            SET status = ?
            WHERE userID = ? AND eventID = ?
        ");
        $stmt->execute([$status, $donorID, $eventID]);

        echo json_encode(['status' => 'success']);
        break;


    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        break;
}
