<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

session_start();

// -------------------------------
// For testing, you can set userID manually
// In production, replace with actual logged-in userID
$userID = $_SESSION['userID'] ?? 1;

if (!$userID) {
    echo json_encode(["success" => false, "error" => "Not logged in"]);
    exit;
}

// -------------------------------
// Allowed fields to update
$allowedFields = ['name','email','phoneNumber','location'];
$fieldsToUpdate = [];

// Collect POSTed fields
foreach ($allowedFields as $field) {
    if (isset($_POST[$field]) && trim($_POST[$field]) !== '') {
        $fieldsToUpdate[$field] = trim($_POST[$field]);
    }
}

// Validate email format if email is being updated
if (isset($fieldsToUpdate['email']) && !filter_var($fieldsToUpdate['email'], FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success" => false, "error" => "Invalid email format"]);
    exit;
}

if (empty($fieldsToUpdate)) {
    echo json_encode(["success"=>false, "error"=>"No valid fields to update"]);
    exit;
}

// -------------------------------
// Build SQL dynamically
$setParts = [];
$types = '';
$values = [];

foreach ($fieldsToUpdate as $field => $value) {
    $setParts[] = "$field = ?";
    $types .= 's';
    $values[] = $value;
}

$sql = "UPDATE user SET " . implode(", ", $setParts) . " WHERE userID = ?";
$types .= 'i';
$values[] = $userID;

// -------------------------------
// Database connection
$conn = new mysqli("localhost", "root", "", "cbdc_system");
if ($conn->connect_error) {
    echo json_encode(["success"=>false, "error"=>"Database connection failed"]);
    exit;
}

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(["success"=>false, "error"=>"Prepare failed: ".$conn->error]);
    exit;
}

// Bind parameters dynamically
$stmt->bind_param($types, ...$values);

// Execute
if ($stmt->execute()) {
    echo json_encode(["success"=>true]);
} else {
    echo json_encode(["success"=>false,"error"=>$stmt->error]);
}

$stmt->close();
$conn->close();
?>
