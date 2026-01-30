<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

// -------------------------------
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cbdc_system";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["success" => false, "error" => "Database connection failed"]));
}

// -------------------------------
// Get userID
// TODO: Replace with $_SESSION['userID'] after login system
$userID = isset($_POST['userID']) ? (int)$_POST['userID'] : 0;

if ($userID <= 0) {
    echo json_encode(["success" => false, "error" => "Invalid userID"]);
    exit;
}

// -------------------------------
// Get POST data
$bloodType = $_POST['bloodType'] ?? '';
$age = $_POST['age'] ?? '';
$dateLastDonate = $_POST['dateLastDonate'] ?? '';
$medicalHistory = $_POST['medicalHistory'] ?? '';
$weight = $_POST['weight'] ?? '';
$height = $_POST['height'] ?? '';

// Validation
if (!$bloodType || !$age || !$dateLastDonate || !$medicalHistory || !$weight || !$height) {
    echo json_encode(["success" => false, "error" => "Missing required fields"]);
    exit;
}

// Optional: further validation
if (!in_array($bloodType, ['A','B','AB','O'])) {
    echo json_encode(["success" => false, "error" => "Invalid blood type"]);
    exit;
}

// -------------------------------
// Update donor table
$stmt = $conn->prepare("
    UPDATE donor SET
        bloodType = ?,
        age = ?,
        dateLastDonate = ?,
        medicalHistory = ?,
        weight = ?,
        height = ?
    WHERE userID = ?
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
