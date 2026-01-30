<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

// -------------------
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cbdc_system";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["success" => false, "error" => "Database connection failed"]));
}

// -------------------
// Get userID from session (for real scenario)
session_start();
$userID = isset($_SESSION['userID']) ? (int)$_SESSION['userID'] : 0;
if ($userID <= 0) {
    echo json_encode(["success" => false, "error" => "Invalid user session"]);
    exit;
}

// -------------------
// Get POST data
$requiredFields = ['bloodType','age','dateLastDonate','medicalHistory','weight','height'];
$data = [];
foreach ($requiredFields as $field) {
    if (!isset($_POST[$field]) || $_POST[$field] === '') {
        echo json_encode(["success" => false, "error" => "Missing field: $field"]);
        exit;
    }
    $data[$field] = $_POST[$field];
}

// Optional: validate bloodType
$validBloodTypes = ['A','B','AB','O'];
if (!in_array($data['bloodType'], $validBloodTypes)) {
    echo json_encode(["success" => false, "error" => "Invalid blood type"]);
    exit;
}

// -------------------
// Update donor table
$stmt = $conn->prepare("
    UPDATE donor
    SET bloodType=?, age=?, dateLastDonate=?, medicalHistory=?, weight=?, height=?
    WHERE userID=?
");
$stmt->bind_param(
    "sissddi",
    $data['bloodType'],
    $data['age'],
    $data['dateLastDonate'],
    $data['medicalHistory'],
    $data['weight'],
    $data['height'],
    $userID
);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
