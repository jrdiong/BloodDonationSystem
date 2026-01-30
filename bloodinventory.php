<?php
session_start();
error_reporting(E_ALL); ini_set('display_errors',1);

if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'Hospital') {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

$userID = $_SESSION['userID'];
$conn = mysqli_connect("localhost","root","","cbdc_system");
if (!$conn) die(json_encode(["error"=>"DB connection failed"]));

// 自动更新过期
$expireSQL = "UPDATE blood_inventory SET status='expired' WHERE status='available' AND expiryDate < CURDATE() AND userID=?";
$stmt = mysqli_prepare($conn,$expireSQL);
mysqli_stmt_bind_param($stmt,"i",$userID);
mysqli_stmt_execute($stmt);

$statuses = ['available','expired','delivered','used'];

// Fetch inventory summary
$sql = "SELECT bloodType,status,COUNT(*) AS quantity, MIN(expiryDate) AS nearestExpiry 
        FROM blood_inventory WHERE userID=? GROUP BY bloodType,status";
$stmt = mysqli_prepare($conn,$sql);
mysqli_stmt_bind_param($stmt,"i",$userID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$inventory = [];
while ($row = mysqli_fetch_assoc($result)) {
    $inventory[$row['bloodType']][$row['status']] = [
        "quantity"=>$row['quantity'],
        "nearestExpiry"=>$row['nearestExpiry']
    ];
}

echo json_encode([
    "statuses"=>$statuses,
    "summary"=>$inventory
]);
