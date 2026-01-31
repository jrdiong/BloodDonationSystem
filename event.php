<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

/* =========================
   Authentication Check
========================= */
if (!isset($_SESSION['userID'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Unauthorized"
    ]);
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
    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed"
    ]);
    exit;
}

/* =========================
   GET: Fetch Hospitals
   ?action=getHospitals
========================= */
if (
    $_SERVER['REQUEST_METHOD'] === 'GET' &&
    isset($_GET['action']) &&
    $_GET['action'] === 'getHospitals'
) {
    $stmt = $pdo->prepare("
        SELECT userID, name
        FROM user
        WHERE role = 'Hospital'
        ORDER BY name
    ");
    $stmt->execute();

    echo json_encode([
        "status" => "success",
        "data" => $stmt->fetchAll(PDO::FETCH_ASSOC)
    ]);
    exit;
}

/* =========================
   GET: Fetch Events
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $stmt = $pdo->prepare("SELECT role FROM user WHERE userID = ?");
    $stmt->execute([$loggedInUserID]);
    $role = $stmt->fetchColumn();

    if ($role === 'Event Organizer') {
        $stmt = $pdo->prepare("
            SELECT e.*, u.name AS hospitalName
            FROM event e
            JOIN user u ON e.hospitalID = u.userID
            WHERE e.organizerID = ?
            ORDER BY e.dateTime DESC
        ");
        $stmt->execute([$loggedInUserID]);
    } elseif ($role === 'Hospital') {
        $stmt = $pdo->prepare("
            SELECT e.*, u.name AS organizerName
            FROM event e
            JOIN user u ON e.organizerID = u.userID
            WHERE e.hospitalID = ?
            ORDER BY e.dateTime DESC
        ");
        $stmt->execute([$loggedInUserID]);
    } else {
        $stmt = $pdo->query("
            SELECT *
            FROM event
            ORDER BY dateTime DESC
        ");
    }

    echo json_encode([
        "status" => "success",
        "data" => $stmt->fetchAll(PDO::FETCH_ASSOC)
    ]);
    exit;
}

/* =========================
   POST: Create Event
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $stmt = $pdo->prepare("SELECT role FROM user WHERE userID = ?");
    $stmt->execute([$loggedInUserID]);
    $role = $stmt->fetchColumn();

    if ($role !== 'Event Organizer') {
        echo json_encode([
            "status" => "error",
            "message" => "Permission denied"
        ]);
        exit;
    }

    $eventName   = $_POST['eventName'] ?? '';
    $location    = $_POST['location'] ?? '';
    $dateTime    = $_POST['dateTime'] ?? '';
    $maxDonors   = $_POST['maxDonors'] ?? 0;
    $description = $_POST['description'] ?? '';
    $hospitalID  = $_POST['hospitalID'] ?? '';

    if (!$eventName || !$location || !$dateTime || !$hospitalID) {
        echo json_encode([
            "status" => "error",
            "message" => "Missing required fields"
        ]);
        exit;
    }

    /* ---------- Image Upload ---------- */
    $imagePath = null;

    if (
        isset($_FILES['imageUpload']) &&
        $_FILES['imageUpload']['error'] === UPLOAD_ERR_OK
    ) {
        $extension = pathinfo($_FILES['imageUpload']['name'], PATHINFO_EXTENSION);
        $fileName = uniqid('event_', true) . '.' . $extension;

        if (!is_dir('uploads')) {
            mkdir('uploads', 0755, true);
        }

        $imagePath = 'uploads/' . $fileName;
        move_uploaded_file($_FILES['imageUpload']['tmp_name'], $imagePath);
    }

    /* ---------- Insert Event ---------- */
    $sql = "
        INSERT INTO event
        (eventName, image_url, location, dateTime, maxDonors, description, organizerID, hospitalID, status)
        VALUES
        (:eventName, :image_url, :location, :dateTime, :maxDonors, :description, :organizerID, :hospitalID, 1)
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':eventName'   => $eventName,
        ':image_url'   => $imagePath,
        ':location'    => $location,
        ':dateTime'    => $dateTime,
        ':maxDonors'   => $maxDonors,
        ':description' => $description,
        ':organizerID' => $loggedInUserID,
        ':hospitalID'  => $hospitalID
    ]);

    echo json_encode([
        "status" => "success",
        "message" => "Event created successfully"
    ]);
    exit;
}
