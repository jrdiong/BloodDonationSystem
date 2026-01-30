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
    die(json_encode(["error"=>"Connection failed: ".$conn->connect_error]));
}

// Receive POST parameters from AJAX
$bloodType = $_POST['bloodType'];
$addAvailable = isset($_POST['addAvailable']) ? (int)$_POST['addAvailable'] : 0;
$removeAvailable = isset($_POST['removeAvailable']) ? (int)$_POST['removeAvailable'] : 0;
$addUsed = isset($_POST['addUsed']) ? (int)$_POST['addUsed'] : 0;
$addDelivered = isset($_POST['addDelivered']) ? (int)$_POST['addDelivered'] : 0;
$removeDelivered = isset($_POST['removeDelivered']) ? (int)$_POST['removeDelivered'] : 0;

$conn->begin_transaction();

try {
    //  Add Available
    for($i=0; $i<$addAvailable; $i++){
        $stmt = $conn->prepare("INSERT INTO `blood inventory` (userID, bloodType, collectionTime, expiryDate, status)
                                VALUES (?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 35 DAY), 'available')");
        $userID = 1; // example userID
        $stmt->bind_param("is", $userID, $bloodType);
        $stmt->execute();
    }

    //  Remove Available → mark as deleted
    if($removeAvailable>0){
        $stmt = $conn->prepare("UPDATE `blood inventory` 
                                SET status='deleted' 
                                WHERE bloodType=? AND status='available' 
                                LIMIT ?");
        $stmt->bind_param("si", $bloodType, $removeAvailable);
        $stmt->execute();
    }

    //  Add Used → automatically decrease Available
    if($addUsed>0){
        $stmt = $conn->prepare("UPDATE `blood inventory` 
                                SET status='used' 
                                WHERE bloodType=? AND status='available' 
                                LIMIT ?");
        $stmt->bind_param("si", $bloodType, $addUsed);
        $stmt->execute();
    }

    //  Add Delivered → automatically decrease Available
    if($addDelivered>0){
        $stmt = $conn->prepare("UPDATE `blood inventory` 
                                SET status='delivered' 
                                WHERE bloodType=? AND status='available' 
                                LIMIT ?");
        $stmt->bind_param("si", $bloodType, $addDelivered);
        $stmt->execute();
    }

    //  Remove Delivered → automatically increase Available
    if($removeDelivered>0){
        $stmt = $conn->prepare("UPDATE `blood inventory` 
                                SET status='available' 
                                WHERE bloodType=? AND status='delivered' 
                                LIMIT ?");
        $stmt->bind_param("si", $bloodType, $removeDelivered);
        $stmt->execute();
    }

    $conn->commit();
    echo json_encode(["success"=>true]);

} catch(Exception $e){
    $conn->rollback();
    echo json_encode(["error"=>$e->getMessage()]);
}

$conn->close();
?>
