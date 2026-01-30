<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

session_start();

// -------------------------------
// 1. Check login
if (!isset($_SESSION['userID'])) {
    echo json_encode([
        "success" => false,
        "error" => "Unauthorized. Please login."
    ]);
    exit;
}

$userID = $_SESSION['userID'];

// -------------------------------
// 2. Check file exists
if (!isset($_FILES['avatar'])) {
    echo json_encode([
        "success" => false,
        "error" => "No file uploaded"
    ]);
    exit;
}

// -------------------------------
// 3. Validate upload
$file = $_FILES['avatar'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode([
        "success" => false,
        "error" => "File upload error"
    ]);
    exit;
}

// -------------------------------
// 4. Validate file type
$allowedTypes = ['image/jpeg', 'image/png'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, $allowedTypes)) {
    echo json_encode([
        "success" => false,
        "error" => "Only JPG and PNG images are allowed"
    ]);
    exit;
}

// -------------------------------
// 5. Validate file size (2MB max)
$maxSize = 2 * 1024 * 1024;
if ($file['size'] > $maxSize) {
    echo json_encode([
        "success" => false,
        "error" => "File size exceeds 2MB"
    ]);
    exit;
}

// -------------------------------
// 6. Prepare upload directory
$uploadDir = __DIR__ . "/uploads/profile/";

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// -------------------------------
// 7. Generate safe file name
$extension = $mimeType === 'image/png' ? 'png' : 'jpg';
$newFileName = "avatar_" . $userID . "_" . time() . "." . $extension;
$targetPath = $uploadDir . $newFileName;

// -------------------------------
// 8. Move file
if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    echo json_encode([
        "success" => false,
        "error" => "Failed to save file"
    ]);
    exit;
}

// -------------------------------
// 9. Save image URL to database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cbdc_system";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode([
        "success" => false,
        "error" => "Database connection failed"
    ]);
    exit;
}

$imageUrl = "/uploads/profile/" . $newFileName;

$stmt = $conn->prepare("UPDATE user SET image_url = ? WHERE userID = ?");
$stmt->bind_param("si", $imageUrl, $userID);

if (!$stmt->execute()) {
    echo json_encode([
        "success" => false,
        "error" => "Failed to update profile image"
    ]);
    exit;
}

$stmt->close();
$conn->close();

// -------------------------------
// 10. Success response
echo json_encode([
    "success" => true,
    "image_url" => $imageUrl
]);
