<?php
session_start();
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
// Get userID from session
$userID = isset($_SESSION['userID']) ? (int)$_SESSION['userID'] : 0;
if ($userID <= 0) {
    echo json_encode(["success" => false, "error" => "Invalid user session"]);
    exit;
}

// -------------------
// Check if user is a donor
$stmt = $conn->prepare("SELECT role FROM user WHERE userID=?");
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "error" => "User not found"]);
    exit;
}
$user = $result->fetch_assoc();
if ($user['role'] !== 'Donor') {
    echo json_encode(["success" => false, "error" => "Only donors can update health report"]);
    exit;
}
$stmt->close();

// -------------------
// Get POST data
$bloodType = $_POST['bloodType'] ?? '';
$age = $_POST['age'] ?? '';
$dateLastDonate = $_POST['dateLastDonate'] ?? null;
$medicalHistory = $_POST['medicalHistory'] ?? '';
$weight = $_POST['weight'] ?? '';
$height = $_POST['height'] ?? '';

// -------------------
// Validation
$validBloodTypes = ['A','B','AB','O'];
if (!in_array($bloodType, $validBloodTypes)) {
    echo json_encode(["success" => false, "error" => "Invalid blood type"]);
    exit;
}

if (!is_numeric($age) || $age <= 0) {
    echo json_encode(["success" => false, "error" => "Invalid age"]);
    exit;
}

if ($weight !== '' && (!is_numeric($weight) || $weight <= 0)) {
    echo json_encode(["success" => false, "error" => "Invalid weight"]);
    exit;
}

if ($height !== '' && (!is_numeric($height) || $height <= 0)) {
    echo json_encode(["success" => false, "error" => "Invalid height"]);
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
    $bloodType,
    $age,
    $dateLastDonate,
    $medicalHistory,
    $weight,
    $height,
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
