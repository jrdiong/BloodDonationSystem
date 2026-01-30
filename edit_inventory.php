<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username = "root";
$password = ""; 
$dbname = "cbdc_system";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// ---------------------------
// 1. GET blood inventory details for a blood type
if(isset($_GET['getDetails']) && $_GET['getDetails']==1 && isset($_GET['bloodType'])){
    $bloodType = $conn->real_escape_string($_GET['bloodType']);
    $result = $conn->query("SELECT inventoryID, collectionTime, expiryDate, status 
                            FROM `blood inventory` 
                            WHERE bloodType='$bloodType' 
                            ORDER BY expiryDate ASC");
    $details = [];
    while($row = $result->fetch_assoc()){
        $details[] = $row;
    }
    echo json_encode($details);
    $conn->close();
    exit;
}

// ---------------------------
// 2. POST: update inventory
$bloodType = $_POST['bloodType'] ?? '';
$addAvailable = intval($_POST['addAvailable'] ?? 0);
$removeAvailable = intval($_POST['removeAvailable'] ?? 0);
$addUsed = intval($_POST['addUsed'] ?? 0);
$addDelivered = intval($_POST['addDelivered'] ?? 0);
$removeDelivered = intval($_POST['removeDelivered'] ?? 0);

if(!$bloodType){
    echo json_encode(["error"=>"Blood type is required"]);
    exit;
}

$conn->begin_transaction();

try {
    // ---------------------------
    // Remove Available (mark as deleted), oldest expiry first
    if($removeAvailable > 0){
        $stmt = $conn->prepare("UPDATE `blood inventory` 
                                SET status='deleted' 
                                WHERE bloodType=? AND status='available' 
                                ORDER BY expiryDate ASC 
                                LIMIT ?");
        $stmt->bind_param("si", $bloodType, $removeAvailable);
        $stmt->execute();
        $stmt->close();
    }

    // ---------------------------
    // Add Available: create new blood packages
    if($addAvailable > 0){
        $stmt = $conn->prepare("INSERT INTO `blood inventory` (bloodType, collectionTime, expiryDate, status) 
                                VALUES (?, NOW(), DATE_ADD(NOW(), INTERVAL 35 DAY), 'available')");
        for($i=0;$i<$addAvailable;$i++){
            $stmt->bind_param("s", $bloodType);
            $stmt->execute();
        }
        $stmt->close();
    }

    // ---------------------------
    // Add Used: mark oldest Available as used
    if($addUsed > 0){
        $stmt = $conn->prepare("UPDATE `blood inventory` 
                                SET status='used' 
                                WHERE bloodType=? AND status='available' 
                                ORDER BY expiryDate ASC 
                                LIMIT ?");
        $stmt->bind_param("si", $bloodType, $addUsed);
        $stmt->execute();
        $stmt->close();
    }

    // ---------------------------
    // Add Delivered: mark oldest Available as delivered
    if($addDelivered > 0){
        $stmt = $conn->prepare("UPDATE `blood inventory` 
                                SET status='delivered' 
                                WHERE bloodType=? AND status='available' 
                                ORDER BY expiryDate ASC 
                                LIMIT ?");
        $stmt->bind_param("si", $bloodType, $addDelivered);
        $stmt->execute();
        $stmt->close();
    }

    // ---------------------------
    // Remove Delivered: mark delivered back to available (reverse)
    if($removeDelivered > 0){
        $stmt = $conn->prepare("UPDATE `blood inventory` 
                                SET status='available' 
                                WHERE bloodType=? AND status='delivered' 
                                ORDER BY expiryDate DESC 
                                LIMIT ?");
        $stmt->bind_param("si", $bloodType, $removeDelivered);
        $stmt->execute();
        $stmt->close();
    }

    $conn->commit();
    echo json_encode(["success"=>true]);
} catch(Exception $e){
    $conn->rollback();
    echo json_encode(["error"=>$e->getMessage()]);
}

$conn->close();
?>
