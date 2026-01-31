<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
function handleUpload($file){
    if(!$file || $file['error']!==UPLOAD_ERR_OK) return null;
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = uniqid('event_', true).'.'.$ext;
    if(!is_dir('uploads')) mkdir('uploads',0755,true);
    $path = 'uploads/'.$fileName;
    move_uploaded_file($file['tmp_name'],$path);
    return $path;
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
            SELECT e.*, u.name AS hospitalName
            FROM event e
            JOIN user u ON e.hospitalID=u.userID
            WHERE e.organizerID=? AND e.status IN (1,2)
            ORDER BY e.dateTime DESC
        ");
        $stmt->execute([$loggedInUserID]);
    } elseif($role==='Hospital'){
        $stmt = $pdo->prepare("
            SELECT e.*, u.name AS organizerName
            FROM event e
            JOIN user u ON e.organizerID=u.userID
            WHERE e.hospitalID=? AND e.status=1
            ORDER BY e.dateTime DESC
        ");
        $stmt->execute([$loggedInUserID]);
    } else { // Admin
        $stmt = $pdo->query("SELECT * FROM event WHERE status=1 ORDER BY dateTime DESC");
    }

    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Ensure image URL is valid
    foreach($events as &$ev){
        $ev['image_url'] = $ev['image_url'] ? $ev['image_url'] : 'placeholder.jpg';
    }

    echo json_encode([
        "status"=>"success",
        "data"=>$events,
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
        $stmt = $pdo->prepare("SELECT * FROM event WHERE eventID=? AND organizerID=? AND status IN (1,2)");
        $stmt->execute([$eventID,$loggedInUserID]);
    } elseif($role==='Hospital'){
        $stmt = $pdo->prepare("SELECT * FROM event WHERE eventID=? AND hospitalID=? AND status=1");
        $stmt->execute([$eventID,$loggedInUserID]);
    } else { // Admin
        $stmt = $pdo->prepare("SELECT * FROM event WHERE eventID=?");
        $stmt->execute([$eventID]);
    }

    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    if(!$event){
        echo json_encode(["status"=>"error","message"=>"Event not found"]);
        exit;
    }

    // Determine if user can edit
    $canEdit = false;
    if($role==='Event Organizer' && $event['organizerID']==$loggedInUserID){
        $canEdit = true;
    }
    if($role==='Hospital' && $event['hospitalID']==$loggedInUserID){
        $canEdit = true;
    }

    $event['image_url'] = $event['image_url'] ? $event['image_url'] : 'placeholder.jpg';

    echo json_encode([
        "status"=>"success",
        "event"=>$event,
        "role"=>$role,
        "canEdit"=>$canEdit
    ]);
    exit;
}

/* =========================
   POST: Save Event (Organizer/Hospital)
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

    // Permission check
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

    $imagePath = handleUpload($_FILES['imageUpload']) ?? $event['image_url'];

    $stmt = $pdo->prepare("
        UPDATE event
        SET eventName=?, dateTime=?, maxDonors=?, description=?, image_url=?
        WHERE eventID=?
    ");
    $stmt->execute([$eventName,$dateTime,$maxDonors,$description,$imagePath,$eventID]);

    echo json_encode(["status"=>"success","message"=>"Event saved successfully","image_url"=>$imagePath]);
    exit;
}

/* =========================
   POST: Send Request to Admin (Create Pending Event)
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

    $imagePath = handleUpload($_FILES['imageUpload']);

    $stmt = $pdo->prepare("
        INSERT INTO event(eventName,image_url,location,dateTime,maxDonors,description,organizerID,hospitalID,status)
        VALUES(?,?,?,?,?,?,?,? ,2)
    ");
    $stmt->execute([$eventName,$imagePath,$location,$dateTime,$maxDonors,$description,$loggedInUserID,$hospitalID]);

    echo json_encode(["status"=>"success","message"=>"Request sent to admin","image_url"=>$imagePath]);
    exit;
}

/* =========================
   POST: Donor Book Event
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
   POST: Admin Delete Event
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
