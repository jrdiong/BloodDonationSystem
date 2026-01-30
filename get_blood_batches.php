<?php
session_start();
error_reporting(E_ALL); ini_set('display_errors',1);

if(!isset($_SESSION['userID'])||$_SESSION['role']!=='Hospital'){http_response_code(401);echo json_encode(["error"=>"Unauthorized"]);exit();}
$userID=$_SESSION['userID'];
$conn=mysqli_connect("localhost","root","","cbdc_system");
if(!$conn){http_response_code(500);echo json_encode(["error"=>"DB connection failed"]);exit();}

$bloodType=$_GET['bloodType']??null;
if(!$bloodType){http_response_code(400);echo json_encode(["error"=>"Missing bloodType"]);exit();}

$sql="SELECT inventoryID,status,collectionTime,expiryDate 
      FROM blood_inventory 
      WHERE userID=? AND bloodType=? AND status NOT IN('expired','deleted') 
      ORDER BY expiryDate ASC";

$stmt=mysqli_prepare($conn,$sql);
mysqli_stmt_bind_param($stmt,"is",$userID,$bloodType);
mysqli_stmt_execute($stmt);
$res=mysqli_stmt_get_result($stmt);

$batches=[];
while($row=mysqli_fetch_assoc($res)){
    $batches[]= [
        "inventoryID"=>$row['inventoryID'],
        "status"=>$row['status'],
        "collectionTime"=>$row['collectionTime'],
        "expiryDate"=>$row['expiryDate']
    ];
}

echo json_encode(["status"=>"success","bloodType"=>$bloodType,"batches"=>$batches]);
