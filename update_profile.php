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
// Get userID from session (temporary via GET for testing)
$userID = isset($_GET['userID']) ? (int)$_GET['userID'] : 0;
if ($userID <= 0) {
    echo json_encode(["success" => false, "error" => "Invalid userID"]);
    exit;
}

// -------------------------------
// Fetch user role
$stmt = $conn->prepare("SELECT role FROM user WHERE userID = ?");
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "error" => "User not found"]);
    exit;
}
$user = $result->fetch_assoc();
$role = $user['role'];
$stmt->close();

// -------------------------------
// Fields to update
$allowedUserFields = ['name', 'email', 'phoneNumber', 'location'];
$allowedDonorFields = ['medicalHistory'];

$updateUserFields = [];
$updateUserValues = [];
$updateUserTypes = '';

foreach ($allowedUserFields as $field) {
    if (isset($_POST[$field])) {
        $value = trim($_POST[$field]);
        if ($field !== 'location' && $value === '') {
            echo json_encode(["success" => false, "error" => "Field '$field' cannot be empty"]);
            exit;
        }
        if ($field === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(["success" => false, "error" => "Invalid email format"]);
            exit;
        }
        $updateUserFields[] = "$field = ?";
        $updateUserValues[] = $value;
        $updateUserTypes .= 's';
    }
}

// -------------------------------
// Update user table
if (!empty($updateUserFields)) {
    $sql = "UPDATE user SET " . implode(',', $updateUserFields) . " WHERE userID = ?";
    $stmt = $conn->prepare($sql);
    $updateUserTypes .= 'i';
    $updateUserValues[] = $userID;
    $stmt->bind_param($updateUserTypes, ...$updateUserValues);
    if (!$stmt->execute()) {
        echo json_encode(["success" => false, "error" => "Failed to update user: " . $stmt->error]);
        exit;
    }
    $stmt->close();
}

// -------------------------------
// Update donor table (only for Donor role)
if ($role === 'Donor') {
    $updateDonorFields = [];
    $updateDonorValues = [];
    $updateDonorTypes = '';

    foreach ($allowedDonorFields as $field) {
        if (isset($_POST[$field])) {
            $value = trim($_POST[$field]);
            if ($value === '') {
                echo json_encode(["success" => false, "error" => "Field '$field' cannot be empty"]);
                exit;
            }
            $updateDonorFields[] = "$field = ?";
            $updateDonorValues[] = $value;
            $updateDonorTypes .= 's';
        }
    }

    if (!empty($updateDonorFields)) {
        $sql = "UPDATE donor SET " . implode(',', $updateDonorFields) . " WHERE userID = ?";
        $stmt = $conn->prepare($sql);
        $updateDonorTypes .= 'i';
        $updateDonorValues[] = $userID;
        $stmt->bind_param($updateDonorTypes, ...$updateDonorValues);
        if (!$stmt->execute()) {
            echo json_encode(["success" => false, "error" => "Failed to update donor: " . $stmt->error]);
            exit;
        }
        $stmt->close();
    }
}

$conn->close();

echo json_encode(["success" => true]);
?>
