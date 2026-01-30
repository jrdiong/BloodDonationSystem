<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

// ---------------------------
// 1. Check if user is logged in
$userID = $_SESSION['userID'] ?? 0;
if ($userID <= 0) {
    echo json_encode(["success" => false, "error" => "Invalid userID"]);
    exit;
}

// ---------------------------
// 2. Check if file is uploaded
if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(["success" => false, "error" => "No file uploaded or upload error"]);
    exit;
}

// ---------------------------
// 3. Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cbdc_system";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "error" => "Database connection failed"]);
    exit;
}

// ---------------------------
// 4. Save uploaded file
$targetDir = "uploads/";

// create folder if not exists
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0755, true);
}

// generate unique filename
$ext = pathinfo($_FILES["avatar"]["name"], PATHINFO_EXTENSION);
$filename = "avatar_user{$userID}_" . time() . "." . $ext;
$targetFile = $targetDir . $filename;

if (!move_uploaded_file($_FILES["avatar"]["tmp_name"], $targetFile)) {
    echo json_encode(["success" => false, "error" => "Failed to save uploaded file"]);
    $conn->close();
    exit;
}

// ---------------------------
// 5. Update user table with relative path
$stmt = $conn->prepare("UPDATE user SET image_url = ? WHERE userID = ?");
$stmt->bind_param("si", $targetFile, $userID);
$stmt->execute();
$stmt->close();

$conn->close();

// ---------------------------
// 6. Return success with new image URL
echo json_encode(["success" => true, "image_url" => $targetFile]);
?>
