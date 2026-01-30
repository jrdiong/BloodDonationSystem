<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

session_start();

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
// Get userID from session
$userID = $_SESSION['userID'] ?? 0;
if ($userID <= 0) {
    echo json_encode(["success" => false, "error" => "Invalid userID"]);
    exit;
}

// -------------------------------
// Step 1: Get basic user info
$stmt = $conn->prepare("
    SELECT userID, name, email, phoneNumber, image_url, role
    FROM users
    WHERE userID = ?
");
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "error" => "User not found"]);
    exit;
}

$profile = $result->fetch_assoc();
$stmt->close();

// -------------------------------
// Step 2: Role-specific data
$role = $profile['role'];

// Donor info
if ($role === 'Donor') {
    $stmt = $conn->prepare("
        SELECT bloodType, age, dateLastDonate, medicalHistory, weight, height
        FROM donor
        WHERE userID = ?
    ");
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $donorResult = $stmt->get_result();

    $profile['donorInfo'] = $donorResult->num_rows > 0
        ? $donorResult->fetch_assoc()
        : [
            'bloodType' => '',
            'age' => '',
            'dateLastDonate' => '',
            'medicalHistory' => '',
            'weight' => '',
            'height' => ''
        ];
    $stmt->close();
}

// Hospital info
if ($role === 'Hospital') {
    $stmt = $conn->prepare("
        SELECT location
        FROM hospital
        WHERE userID = ?
    ");
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $hospitalResult = $stmt->get_result();

    $profile['hospitalInfo'] = $hospitalResult->num_rows > 0
        ? $hospitalResult->fetch_assoc()
        : ['location' => ''];
    $stmt->close();
}

// -------------------------------
// Return JSON
$conn->close();
echo json_encode([
    "success" => true,
    "profile" => $profile
]);
?>
