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
// Get userID from session or POST (for testing)
session_start();
$userID = isset($_SESSION['userID']) ? (int)$_SESSION['userID'] : 0;

// TEMP: allow POST userID for testing
if ($userID <= 0 && isset($_POST['userID'])) {
    $userID = (int)$_POST['userID'];
}

if ($userID <= 0) {
    echo json_encode(["success" => false, "error" => "Invalid userID"]);
    exit;
}

// -------------------
// Allowed fields to update
$allowedFields = ['name', 'email', 'phoneNumber', 'location'];
$updateFields = [];
$types = '';
$values = [];

foreach ($allowedFields as $field) {
    if (isset($_POST[$field])) {
        $value = trim($_POST[$field]);
        if ($value === '') {
            echo json_encode(["success" => false, "error" => "Field $field cannot be empty"]);
            exit;
        }
        if ($field === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(["success" => false, "error" => "Invalid email format"]);
            exit;
        }
        $updateFields[] = "$field = ?";
        $values[] = $value;
        $types .= ($field === 'email' || $field === 'name' || $field === 'location' || $field === 'phoneNumber') ? 's' : '';
    }
}

if (empty($updateFields)) {
    echo json_encode(["success" => false, "error" => "No fields to update"]);
    exit;
}

// -------------------
// Build query
$sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE userID = ?";
$types .= 'i';
$values[] = $userID;

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(["success" => false, "error" => "Prepare failed: " . $conn->error]);
    exit;
}

// Bind parameters dynamically
$stmt->bind_param($types, ...$values);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
