<?php
session_start();
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

if(!isset($_SESSION['userID'])){
    echo json_encode(["status"=>"error","message"=>"Unauthorized"]);
    exit;
}

$userID = $_SESSION['userID'];
$appointmentID = $_GET['appointmentID'] ?? null;

if(!$appointmentID){
    echo json_encode(["status"=>"error","message"=>"Missing appointmentID"]);
    exit;
}

$host = 'localhost';
$db = 'cbdc_system';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("SELECT rating, comment FROM feedback WHERE appointmentID=? AND userID=? LIMIT 1");
    $stmt->execute([$appointmentID, $userID]);
    $feedback = $stmt->fetch(PDO::FETCH_ASSOC);

    if($feedback){
        echo json_encode(["status"=>"exists","feedback"=>$feedback]);
    } else {
        echo json_encode(["status"=>"not_exists"]);
    }

} catch(PDOException $e){
    echo json_encode(["status"=>"error","message"=>$e->getMessage()]);
}
?>
