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
// 3. Check file type (only allow images)
$fileInfo = getimagesize($_FILES['avatar']['tmp_name']);
if ($fileInfo === false) {
    echo json_encode(["success" => false, "error" => "Uploaded file is not an image"]);
    exit;
}

// ---------------------------
// 4. Database connection
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
// 5. Save uploaded file
$targetDir = "uploads/";

// create folder if not exists
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0755, true);
}

// generate unique filename
$ext = pathinfo($_FILES["avatar"]["name"], PATHINFO_EXTENSION);
$filename = "avatar_user{$userID}_" . time() . "." . $ext;
$targetFile = $targetDir . $filename;

// move the uploaded file to target directory
if (!move_uploaded_file($_FILES["avatar"]["tmp_name"], $targetFile)) {
    echo json_encode(["success" => false, "error" => "Failed to save uploaded file"]);
    $conn->close();
    exit;
}

// ---------------------------
// 6. Update user table with relative path
$imagePath = "/{$targetFile}";  // return relative path to the root
$stmt = $conn->prepare("UPDATE users SET image_url = ? WHERE userID = ?");
$stmt->bind_param("si", $imagePath, $userID);
$stmt->execute();
$stmt->close();

// Close database connection
$conn->close();

// ---------------------------
// 7. Return success with new image URL
echo json_encode(["success" => true, "image_url" => $imagePath]);
?>
