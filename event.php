<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

/* =========================
   Authentication Check
========================= */
if (!isset($_SESSION['userID'])) {
    echo json_encode(["status"=>"error","message"=>"Unauthorized"]);
    exit;
}
$loggedInUserID = $_SESSION['userID'];

/* =========================
   Database Connection
========================= */
$host = 'localhost';
$dbname = 'cbdc_system';
$username = 'root';
$password = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]
    );
} catch(PDOException $e) {
    echo json_encode(["status"=>"error","message"=>"Database connection failed"]);
    exit;
}

/* =========================
   Helper Functions
========================= */
function getUserRole($pdo, $userID){
    $stmt = $pdo->prepare("SELECT role FROM user WHERE userID=?");
    $stmt->execute([$userID]);
    return $stmt->fetchColumn();
}
$role = getUserRole($pdo, $loggedInUserID);

function checkPermission($requiredRoles){
    global $role;
    if(!in_array($role, $requiredRoles)){
        echo json_encode(["status"=>"error","message"=>"Permission denied"]);
        exit;
    }
}

function countBookings($pdo, $eventID){
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointment WHERE eventID=? AND status IN ('Pending','Approved')");
    $stmt->execute([$eventID]);
    return $stmt->fetchColumn();
}

function handleImageUpload($fileInputName){
    if(!isset($_FILES[$fileInputName]) || $_FILES[$fileInputName]['error'] !== UPLOAD_ERR_OK){
        return null;
    }
    $fileTmp = $_FILES[$fileInputName]['tmp_name'];
    $fileName = $_FILES[$fileInputName]['name'];
    $fileInfo = getimagesize($fileTmp);
    if($fileInfo === false){ throw new Exception("Uploaded file is not a valid image"); }
    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    if(!in_array($ext,['jpg','jpeg','png','gif'])){ throw new Exception("Invalid file type"); }
    $uploadDir = __DIR__.'/uploads/';
    if(!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    $newName = 'event_'.time().'_'.uniqid().'.'.$ext;
    if(!move_uploaded_file($fileTmp, $uploadDir.$newName)){ throw new Exception("Failed to save uploaded file"); }
    return 'uploads/'.$newName;
}

/* =========================
   GET: Fetch Hospitals
========================= */
if($_SERVER['REQUEST_METHOD']==='GET' && ($_GET['action']??'')==='getHospitals'){
    checkPermission(['Event Organizer']);
    $stmt = $pdo->prepare("SELECT userID,name FROM user WHERE role='Hospital' ORDER BY name");
    $stmt->execute();
    echo json_encode(["status"=>"success","data"=>$stmt->fetchAll(PDO::FETCH_ASSOC),"role"=>$role]);
    exit;
}

/* =========================
   GET: Fetch Donors for Event
========================= */
if($_SERVER['REQUEST_METHOD']==='GET' && ($_GET['action']??'')==='getDonors'){
    checkPermission(['Hospital','Event Organizer','Admin']);
    $eventID = $_GET['eventID'] ?? null;
    if(!$eventID){ echo json_encode(["status"=>"error","message"=>"Event ID required"]); exit; }
    $stmt = $pdo->prepare("SELECT hospitalID FROM event WHERE eventID=?");
    $stmt->execute([$eventID]);
    $eventRow = $stmt->fetch(PDO::FETCH_ASSOC);
    if(!$eventRow){ echo json_encode(["status"=>"error","message"=>"Event not found"]); exit; }
    $hospitalID = $eventRow['hospitalID'];
    if($role==='Hospital' && $hospitalID!=$loggedInUserID){ echo json_encode(["status"=>"error","message"=>"Permission denied"]); exit; }
    $stmt = $pdo->prepare("SELECT a.appointmentID,a.status,u.name,u.email,u.phone FROM appointment a JOIN user u ON a.donorID=u.userID WHERE a.eventID=?");
    $stmt->execute([$eventID]);
    $donors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["status"=>"success","data"=>$donors,"canEdit"=>($role==='Hospital')]);
    exit;
}

/* =========================
   POST: Update Appointment Status
========================= */
if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='updateAppointmentStatus'){
    checkPermission(['Hospital']);
    $appointmentID = $_POST['appointmentID'] ?? null;
    $newStatus = $_POST['newStatus'] ?? null;
    if(!$appointmentID || !in_array($newStatus,['Approved','Rejected'])){
        echo json_encode(["status"=>"error","message"=>"Invalid input"]); exit;
    }
    $stmt = $pdo->prepare("SELECT e.hospitalID FROM appointment a JOIN event e ON a.eventID=e.eventID WHERE a.appointmentID=?");
    $stmt->execute([$appointmentID]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if(!$row || $row['hospitalID'] != $loggedInUserID){ echo json_encode(["status"=>"error","message"=>"Permission denied"]); exit; }
    $stmt = $pdo->prepare("UPDATE appointment SET status=? WHERE appointmentID=?");
    $stmt->execute([$newStatus,$appointmentID]);
    echo json_encode(["status"=>"success","message"=>"Status updated"]); exit;
}

/* =========================
   GET: Fetch Event List
========================= */
if($_SERVER['REQUEST_METHOD']==='GET' && !isset($_GET['action'])){
    $events=[];
    if($role==='Event Organizer'){
        $stmt = $pdo->prepare("SELECT e.*, u.name AS hospitalName FROM event e JOIN user u ON e.hospitalID=u.userID WHERE e.organizerID=? AND e.status IN (1,2) ORDER BY e.dateTime DESC");
        $stmt->execute([$loggedInUserID]);
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } elseif($role==='Hospital'){
        $stmt = $pdo->prepare("SELECT e.*, u.name AS organizerName FROM event e JOIN user u ON e.organizerID=u.userID WHERE e.hospitalID=? AND e.status=1 ORDER BY e.dateTime DESC");
        $stmt->execute([$loggedInUserID]);
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else { // Admin
        $stmt = $pdo->prepare("SELECT * FROM event WHERE status=1 ORDER BY dateTime DESC");
        $stmt->execute();
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    foreach($events as &$e){ $e['currentBookings']=countBookings($pdo,$e['eventID']); }
    echo json_encode(["status"=>"success","data"=>$events,"role"=>$role]); exit;
}

/* =========================
   GET: Fetch Single Event
========================= */
if($_SERVER['REQUEST_METHOD']==='GET' && ($_GET['action']??'')==='getEventByID'){
    $eventID = $_GET['eventID'] ?? null;
    if(!$eventID){ echo json_encode(["status"=>"error","message"=>"Event ID required"]); exit; }
    $stmt = $pdo->prepare("SELECT * FROM event WHERE eventID=?");
    $stmt->execute([$eventID]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    if(!$event){ echo json_encode(["status"=>"error","message"=>"Event not found"]); exit; }
    $event['currentBookings']=countBookings($pdo,$eventID);
    $canEdit = ($role==='Event Organizer' && $event['organizerID']==$loggedInUserID) || ($role==='Hospital' && $event['hospitalID']==$loggedInUserID);
    echo json_encode(["status"=>"success","event"=>$event,"role"=>$role,"canEdit"=>$canEdit]); exit;
}

/* =========================
   POST: Save Event
========================= */
if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='saveEvent'){
    $eventID = $_POST['eventID'] ?? null;
    if(!$eventID){ echo json_encode(["status"=>"error","message"=>"Event ID required"]); exit; }
    $stmt = $pdo->prepare("SELECT * FROM event WHERE eventID=?");
    $stmt->execute([$eventID]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    if(!$event){ echo json_encode(["status"=>"error","message"=>"Event not found"]); exit; }
    if(!(($role==='Event Organizer' && $event['organizerID']==$loggedInUserID) || ($role==='Hospital' && $event['hospitalID']==$loggedInUserID))){
        echo json_encode(["status"=>"error","message"=>"Permission denied"]); exit;
    }
    $eventName = $_POST['eventName'] ?? $event['eventName'];
    $dateTime  = $_POST['dateTime'] ?? $event['dateTime'];
    $maxDonors = $_POST['maxDonors'] ?? $event['maxDonors'];
    $description = $_POST['description'] ?? $event['description'];
    $location = $_POST['location'] ?? $event['location'];
    $imagePath = $event['image_url'];
    try{
        $uploadedImage = handleImageUpload('imageUpload');
        if($uploadedImage) $imagePath=$uploadedImage;
    } catch(Exception $e){ echo json_encode(["status"=>"error","message"=>$e->getMessage()]); exit; }
    $stmt = $pdo->prepare("UPDATE event SET eventName=?, dateTime=?, maxDonors=?, description=?, location=?, image_url=? WHERE eventID=?");
    $stmt->execute([$eventName,$dateTime,$maxDonors,$description,$location,$imagePath,$eventID]);
    echo json_encode(["status"=>"success","message"=>"Event updated successfully"]); exit;
}

/* =========================
   POST: Send New Event Request (Event Organizer)
========================= */
if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='sendRequest'){
    checkPermission(['Event Organizer']);
    $eventName = $_POST['eventName'] ?? '';
    $location = $_POST['location'] ?? 'TBD';
    $dateTime = $_POST['dateTime'] ?? '';
    $maxDonors = $_POST['maxDonors'] ?? 0;
    $description = $_POST['description'] ?? '';
    $hospitalID = $_POST['hospitalID'] ?? '';
    if(!$eventName || !$dateTime || !$hospitalID){ echo json_encode(["status"=>"error","message"=>"Missing required fields"]); exit; }
    $imagePath = null;
    try{ $uploadedImage = handleImageUpload('imageUpload'); if($uploadedImage) $imagePath=$uploadedImage; } 
    catch(Exception $e){ echo json_encode(["status"=>"error","message"=>$e->getMessage()]); exit; }
    try{
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("INSERT INTO event(eventName,image_url,location,dateTime,maxDonors,description,organizerID,hospitalID,status) VALUES(?,?,?,?,?,?,?,?,2)");
        $stmt->execute([$eventName,$imagePath,$location,$dateTime,$maxDonors,$description,$loggedInUserID,$hospitalID]);
        $eventID = $pdo->lastInsertId();
        $stmt = $pdo->prepare("INSERT INTO request(eventID,status) VALUES(?,2)");
        $stmt->execute([$eventID]);
        $pdo->commit();
        echo json_encode(["status"=>"success","message"=>"Request sent to admin"]);
    } catch(Exception $e){
        $pdo->rollBack();
        echo json_encode(["status"=>"error","message"=>$e->getMessage()]);
    }
    exit;
}

/* =========================
   POST: Book Event (Donor)
========================= */
if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='bookEvent'){
    checkPermission(['Donor']);
    $eventID = $_POST['eventID'] ?? null;
    if(!$eventID){ echo json_encode(["status"=>"error","message"=>"Event ID required"]); exit; }
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointment WHERE eventID=? AND donorID=?");
    $stmt->execute([$eventID,$loggedInUserID]);
    if($stmt->fetchColumn()>0){ echo json_encode(["status"=>"error","message"=>"You already booked this event"]); exit; }
    $stmt = $pdo->prepare("INSERT INTO appointment(eventID,donorID,status) VALUES(?,?, 'Pending')");
    $stmt->execute([$eventID,$loggedInUserID]);
    echo json_encode(["status"=>"success","message"=>"Event booked successfully"]);
    exit;
}

/* =========================
   POST: Delete Event (Admin)
========================= */
if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='deleteEvent'){
    checkPermission(['Admin']);
    $eventID = $_POST['eventID'] ?? null;
    if(!$eventID){ echo json_encode(["status"=>"error","message"=>"Event ID required"]); exit; }
    $stmt = $pdo->prepare("UPDATE event SET status=0 WHERE eventID=?");
    $stmt->execute([$eventID]);
    echo json_encode(["status"=>"success","message"=>"Event deleted successfully"]);
    exit;
}
?>
