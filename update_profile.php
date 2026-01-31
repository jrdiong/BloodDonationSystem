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
    echo json_encode(["success" => false, "error" => "Database connection failed"]);
    exit;
}

// -------------------
// Start session and get userID
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
$values = [];
$types = '';

$updateUserFields = []; // Store fields for the `user` table update
$updateHospitalFields = []; // Store fields for the `hospital` table update

foreach ($allowedFields as $field) {
    if (isset($_POST[$field])) {
        $value = trim($_POST[$field]);
        if ($value === '') {
            echo json_encode(["success" => false, "error" => "Field '$field' cannot be empty"]);
            exit;
        }
        if ($field === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(["success" => false, "error" => "Invalid email format"]);
            exit;
        }

        if ($field === 'location') {
            // Handle location field differently for hospital table
            $updateHospitalFields[] = "$field = ?";
        } else {
            $updateUserFields[] = "$field = ?";
        }

        $values[] = $value;
        $types .= 's'; // all fields are strings
    }
}

if (empty($updateUserFields) && empty($updateHospitalFields)) {
    echo json_encode(["success" => false, "error" => "No fields to update"]);
    exit;
}

// -------------------
// Prepare query
// Update user table first
if (!empty($updateUserFields)) {
    $sqlUser = "UPDATE user SET " . implode(', ', $updateUserFields) . " WHERE userID = ?";
    $values[] = $userID;
    $types .= 'i';

    $stmtUser = $conn->prepare($sqlUser);
    if (!$stmtUser) {
        echo json_encode(["success" => false, "error" => "Prepare failed for user update: " . $conn->error]);
        exit;
    }

    // Bind parameters dynamically for user table
    $stmtUser->bind_param($types, ...$values);

    // Execute user update
    if (!$stmtUser->execute()) {
        echo json_encode(["success" => false, "error" => $stmtUser->error]);
        exit;
    }

    $stmtUser->close();
}

// Now handle the hospital table update
if (!empty($updateHospitalFields)) {
    $sqlHospital = "UPDATE hospital SET " . implode(', ', $updateHospitalFields) . " WHERE userID = ?";
    $stmtHospital = $conn->prepare($sqlHospital);
    if (!$stmtHospital) {
        echo json_encode(["success" => false, "error" => "Prepare failed for hospital update: " . $conn->error]);
        exit;
    }

    // Bind parameters dynamically for hospital table
    $stmtHospital->bind_param('si', $_POST['location'], $userID);

    // Execute hospital update
    if (!$stmtHospital->execute()) {
        echo json_encode(["success" => false, "error" => $stmtHospital->error]);
        exit;
    }

    $stmtHospital->close();
}

// -------------------
// Success response
$conn->close();
echo json_encode(["success" => true]);
?>
