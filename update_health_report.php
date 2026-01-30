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

// 使用持久连接
$conn = new mysqli("p:localhost", $username, $password, $dbname);
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
// Check if user is already a donor in the donor table
$stmt = $conn->prepare("SELECT userID FROM donor WHERE userID=?");
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    // User not found in donor table, insert new record
    $stmt->close();

    // Insert new record into donor table if not exists
    $stmt = $conn->prepare("INSERT INTO donor (userID) VALUES (?)");
    $stmt->bind_param("i", $userID);
    if (!$stmt->execute()) {
        echo json_encode(["success" => false, "error" => "Failed to create donor record"]);
        exit;
    }
}

// -------------------
// Get POST data safely
$bloodType = isset($_POST['bloodType']) ? trim($_POST['bloodType']) : '';
$age = isset($_POST['age']) ? trim($_POST['age']) : '';
$dateLastDonate = isset($_POST['dateLastDonate']) ? trim($_POST['dateLastDonate']) : null;
$medicalHistory = isset($_POST['medicalHistory']) ? trim($_POST['medicalHistory']) : '';
$weight = isset($_POST['weight']) ? trim($_POST['weight']) : '';
$height = isset($_POST['height']) ? trim($_POST['height']) : '';

// -------------------
// Debugging: Check the input values
var_dump($bloodType, $age, $dateLastDonate, $medicalHistory, $weight, $height); // Check the data coming from the frontend

// -------------------
// Validation
$validBloodTypes = ['A', 'B', 'AB', 'O'];
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

// Normalize empty fields
$weight = $weight === '' ? null : floatval($weight);
$height = $height === '' ? null : floatval($height);
$dateLastDonate = $dateLastDonate === '' ? null : $dateLastDonate;

// -------------------
// Update donor table with new health data
$stmt = $conn->prepare("
    UPDATE donor
    SET bloodType=?, age=?, dateLastDonate=?, medicalHistory=?, weight=?, height=?
    WHERE userID=?
");

if ($stmt === false) {
    echo json_encode(["success" => false, "error" => "Prepare failed: " . $conn->error]);
    exit;
}

$stmt->bind_param(
    "sissddi",  // Param types: string, integer, string, string, double, double, integer
    $bloodType,
    $age,
    $dateLastDonate,
    $medicalHistory,
    $weight,
    $height,
    $userID
);

// Execute the query and check for success
if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
