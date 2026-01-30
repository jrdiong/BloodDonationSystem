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
    var_dump($_FILES);  // Debug the uploaded file array
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
$targetDir = $_SERVER['DOCUMENT_ROOT'] . "/uploads/";  // Absolute path

// Create folder if it does not exist
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0755, true);
}

// ---------------------------
// Check the file extension and allow only specific types
$ext = strtolower(pathinfo($_FILES["avatar"]["name"], PATHINFO_EXTENSION));
$validExts = ['jpg', 'jpeg', 'png', 'gif'];  // Allow only these file extensions

if (!in_array($ext, $validExts)) {
    echo json_encode(["success" => false, "error" => "Invalid file type. Allowed types: jpg, jpeg, png, gif."]);
    exit;
}

// Generate a unique filename to avoid overwriting
$filename = "avatar_user{$userID}_" . time() . "." . $ext;
$targetFile = $targetDir . $filename;

// Move the uploaded file to the target directory
if (!move_uploaded_file($_FILES["avatar"]["tmp_name"], $targetFile)) {
    echo json_encode(["success" => false, "error" => "Failed to save uploaded file"]);
    $conn->close();
    exit;
}

// ---------------------------
// 6. Update user table with the relative path to the uploaded image
$imagePath = "/uploads/{$filename}";  // relative path for use in the URL
$stmt = $conn->prepare("UPDATE user SET image_url = ? WHERE userID = ?");
$stmt->bind_param("si", $imagePath, $userID);
$stmt->execute();
$stmt->close();

// Close the database connection
$conn->close();

// ---------------------------
// 7. Return success with the new image URL
echo json_encode(["success" => true, "image_url" => $imagePath]);
?>
