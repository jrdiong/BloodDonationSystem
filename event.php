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
   Helper: Get User Role
========================= */
function getUserRole($pdo, $userID){
    $stmt = $pdo->prepare("SELECT role FROM user WHERE userID=?");
    $stmt->execute([$userID]);
    return $stmt->fetchColumn();
}

$role = getUserRole($pdo, $loggedInUserID);

/* =========================
   Helper: Handle Image Upload
========================= */
function handleImageUpload($fileInputName){
    if(!isset($_FILES[$fileInputName]) || $_FILES[$fileInputName]['error'] !== UPLOAD_ERR_OK){
        return null; // No file uploaded
    }

    $fileTmp = $_FILES[$fileInputName]['tmp_name'];
    $fileName = $_FILES[$fileInputName]['name'];

    // Check if file is an image
    $fileInfo = getimagesize($fileTmp);
    if($fileInfo === false){
        throw new Exception("Uploaded file is not a valid image");
    }

    // Allow only certain extensions
    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','gif'];
    if(!in_array($ext, $allowed)){
        throw new Exception("Invalid file type. Allowed: jpg, jpeg, png, gif");
    }

    // Ensure uploads folder exists
    $uploadDir = __DIR__ . '/uploads/';
    if(!is_dir($uploadDir)){
        mkdir($uploadDir, 0755, true);
    }

    // Unique filename
    $newName = 'event_' . time() . '_' . uniqid() . '.' . $ext;
    $destination = $uploadDir . $newName;

    if(!move_uploaded_file($fileTmp, $destination)){
        throw new Exception("Failed to save uploaded file");
    }

    // Return relative path for DB
    return 'uploads/' . $newName;
}

/* =========================
   GET: Fetch Hospitals
========================= */
if($_SERVER['REQUEST_METHOD']==='GET' && ($_GET['action']??'')==='getHospitals'){
    $stmt = $pdo->prepare("SELECT userID,name FROM user WHERE role='Hospital' ORDER BY name");
    $stmt->execute();
    echo json_encode([
        "status"=>"success",
        "data"=>$stmt->fetchAll(PDO::FETCH_ASSOC),
        "role"=>$role
    ]);
    exit;
}

/* =========================
   GET: Fetch Event List
========================= */
if($_SERVER['REQUEST_METHOD']==='GET' && !isset($_GET['action'])){
    if($role==='Event Organizer'){
        $stmt = $pdo->prepare("
            SELECT e.*, u.name AS hospitalName,
            (SELECT COUNT(*) FROM appointment WHERE eventID=e.eventID AND status IN ('Pending','Approved')) AS currentBookings
            FROM event e
            JOIN user u ON e.hospitalID=u.userID
            WHERE e.organizerID=? AND e.status IN (1,2)
            ORDER BY e.dateTime DESC
        ");
        $stmt->execute([$loggedInUserID]);
    } elseif($role==='Hospital'){
        $stmt = $pdo->prepare("
            SELECT e.*, u.name AS organizerName,
            (SELECT COUNT(*) FROM appointment WHERE eventID=e.eventID AND status IN ('Pending','Approved')) AS currentBookings
            FROM event e
            JOIN user u ON e.organizerID=u.userID
            WHERE e.hospitalID=? AND e.status=1
            ORDER BY e.dateTime DESC
        ");
        $stmt->execute([$loggedInUserID]);
    } else {
        $stmt = $pdo->prepare("
            SELECT e.*, 
            (SELECT COUNT(*) FROM appointment WHERE eventID=e.eventID AND status IN ('Pending','Approved')) AS currentBookings
            FROM event e
            WHERE e.status=1
            ORDER BY e.dateTime DESC
        ");
        $stmt->execute();
    }

    echo json_encode([
        "status"=>"success",
        "data"=>$stmt->fetchAll(PDO::FETCH_ASSOC),
        "role"=>$role
    ]);
    exit;
}

/* =========================
   GET: Fetch Single Event
========================= */
if($_SERVER['REQUEST_METHOD']==='GET' && ($_GET['action']??'')==='getEventByID'){
    $eventID = $_GET['eventID'] ?? null;
    if(!$eventID){
        echo json_encode(["status"=>"error","message"=>"Event ID required"]);
        exit;
    }

    if($role==='Event Organizer'){
        $stmt = $pdo->prepare("
            SELECT e.*, 
            (SELECT COUNT(*) FROM appointment WHERE eventID=e.eventID AND status IN ('Pending','Approved')) AS currentBookings
            FROM event e
            WHERE e.eventID=? AND organizerID=? AND e.status IN (1,2)
        ");
        $stmt->execute([$eventID,$loggedInUserID]);
    } elseif($role==='Hospital'){
        $stmt = $pdo->prepare("
            SELECT e.*, 
            (SELECT COUNT(*) FROM appointment WHERE eventID=e.eventID AND status IN ('Pending','Approved')) AS currentBookings
            FROM event e
            WHERE e.eventID=? AND hospitalID=? AND e.status=1
        ");
        $stmt->execute([$eventID,$loggedInUserID]);
    } else {
        $stmt = $pdo->prepare("
            SELECT e.*, 
            (SELECT COUNT(*) FROM appointment WHERE eventID=e.eventID AND status IN ('Pending','Approved')) AS currentBookings
            FROM event e
            WHERE e.eventID=?
        ");
        $stmt->execute([$eventID]);
    }

    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    if(!$event){
        echo json_encode(["status"=>"error","message"=>"Event not found"]);
        exit;
    }

    $canEdit = false;
    if(($role==='Event Organizer' && $event['organizerID']==$loggedInUserID) || ($role==='Hospital' && $event['hospitalID']==$loggedInUserID)){
        $canEdit = true;
    }

    echo json_encode([
        "status"=>"success",
        "event"=>$event,
        "role"=>$role,
        "canEdit"=>$canEdit
    ]);
    exit;
}

/* =========================
   POST: Save Event
========================= */
if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='saveEvent'){
    $eventID = $_POST['eventID'] ?? null;
    if(!$eventID){
        echo json_encode(["status"=>"error","message"=>"Event ID required"]);
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM event WHERE eventID=?");
    $stmt->execute([$eventID]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    if(!$event){
        echo json_encode(["status"=>"error","message"=>"Event not found"]);
        exit;
    }

    if(!(
        ($role==='Event Organizer' && $event['organizerID']==$loggedInUserID) ||
        ($role==='Hospital' && $event['hospitalID']==$loggedInUserID)
    )){
        echo json_encode(["status"=>"error","message"=>"Permission denied"]);
        exit;
    }

    $eventName = $_POST['eventName'] ?? $event['eventName'];
    $dateTime  = $_POST['dateTime'] ?? $event['dateTime'];
    $maxDonors = $_POST['maxDonors'] ?? $event['maxDonors'];
    $description = $_POST['description'] ?? $event['description'];

    $imagePath = $event['image_url'];

    try {
        $uploadedImage = handleImageUpload('imageUpload');
        if($uploadedImage) $imagePath = $uploadedImage;
    } catch(Exception $e){
        echo json_encode(["status"=>"error","message"=>$e->getMessage()]);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE event SET eventName=?, dateTime=?, maxDonors=?, description=?, image_url=? WHERE eventID=?");
    $stmt->execute([$eventName,$dateTime,$maxDonors,$description,$imagePath,$eventID]);

    echo json_encode(["status"=>"success","message"=>"Event updated successfully"]);
    exit;
}

/* =========================
   POST: Send Request (Create Pending Event + Request)
========================= */
if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='sendRequest'){
    if($role!=='Event Organizer'){
        echo json_encode(["status"=>"error","message"=>"Permission denied"]);
        exit;
    }

    $eventName   = $_POST['eventName'] ?? '';
    $location    = $_POST['location'] ?? 'TBD';
    $dateTime    = $_POST['dateTime'] ?? '';
    $maxDonors   = $_POST['maxDonors'] ?? 0;
    $description = $_POST['description'] ?? '';
    $hospitalID  = $_POST['hospitalID'] ?? '';

    if(!$eventName || !$dateTime || !$hospitalID){
        echo json_encode(["status"=>"error","message"=>"Missing required fields"]);
        exit;
    }

    $imagePath = null;
    try {
        $uploadedImage = handleImageUpload('imageUpload');
        if($uploadedImage) $imagePath = $uploadedImage;
    } catch(Exception $e){
        echo json_encode(["status"=>"error","message"=>$e->getMessage()]);
        exit;
    }

    try {
        $pdo->beginTransaction();

        /* Insert event (pending) */
        $stmt = $pdo->prepare("
            INSERT INTO event(eventName,image_url,location,dateTime,maxDonors,description,organizerID,hospitalID,status)
            VALUES(?,?,?,?,?,?,?,?,2)
        ");
        $stmt->execute([$eventName,$imagePath,$location,$dateTime,$maxDonors,$description,$loggedInUserID,$hospitalID]);
        $eventID = $pdo->lastInsertId();

        /* Insert request (pending) */
        $stmt = $pdo->prepare("INSERT INTO request(eventID,status) VALUES(?,2)");
        $stmt->execute([$eventID]);

        $pdo->commit();
        echo json_encode(["status"=>"success","message"=>"Request sent to admin"]);
        exit;

    } catch(Exception $e){
        $pdo->rollBack();
        echo json_encode(["status"=>"error","message"=>$e->getMessage()]);
        exit;
    }
}

/* =========================
   POST: Book Event (Donor)
========================= */
if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='bookEvent'){
    if($role!=='Donor'){
        echo json_encode(["status"=>"error","message"=>"Permission denied"]);
        exit;
    }

    $eventID = $_POST['eventID'] ?? null;
    if(!$eventID){
        echo json_encode(["status"=>"error","message"=>"Event ID required"]);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO appointment(eventID,donorID,status) VALUES(?,?, 'Pending')");
    $stmt->execute([$eventID,$loggedInUserID]);

    echo json_encode(["status"=>"success","message"=>"Event booked successfully"]);
    exit;
}

/* =========================
   POST: Delete Event (Admin)
========================= */
if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='deleteEvent'){
    if($role!=='Admin'){
        echo json_encode(["status"=>"error","message"=>"Permission denied"]);
        exit;
    }

    $eventID = $_POST['eventID'] ?? null;
    if(!$eventID){
        echo json_encode(["status"=>"error","message"=>"Event ID required"]);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE event SET status=0 WHERE eventID=?");
    $stmt->execute([$eventID]);

    echo json_encode(["status"=>"success","message"=>"Event deleted successfully"]);
    exit;
}

?>