<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

$userID = $_SESSION['userID'] ?? 0;
if ($userID <= 0) {
    echo json_encode(["success" => false, "error" => "Invalid userID"]);
    exit;
}

// Check POST data
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phoneNumber'] ?? '';
$location = $_POST['location'] ?? ''; // optional, only for hospital

if (!$name || !$email || !$phone) {
    echo json_encode(["success" => false, "error" => "Missing required fields"]);
    exit;
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cbdc_system";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "error" => "Database connection failed"]);
    exit;
}

// Update basic user info
$stmt = $conn->prepare("UPDATE user SET name=?, email=?, phoneNumber=? WHERE userID=?");
$stmt->bind_param("sssi", $name, $email, $phone, $userID);
$stmt->execute();
$stmt->close();

// Get user role
$result = $conn->query("SELECT role FROM user WHERE userID=$userID");
$role = $result->fetch_assoc()['role'] ?? '';
$result->free();

// Update hospital location if role is Hospital
if ($role === 'Hospital') {
    $stmt = $conn->prepare("UPDATE hospital SET location=? WHERE userID=?");
    $stmt->bind_param("si", $location, $userID);
    $stmt->execute();
    $stmt->close();
}

$conn->close();

echo json_encode(["success" => true, "message" => "Profile updated successfully"]);
?>
