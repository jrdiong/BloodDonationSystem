<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['userID'], $_SESSION['role'])) {
    echo json_encode(["status"=>"error","message"=>"Unauthorized"]);
    exit;
}

$loggedInUserID = $_SESSION['userID'];
$role = $_SESSION['role']; // 'Admin', 'Event Organizer', 'Hospital'

// DB Connection
$host = 'localhost';
$db = 'cbdc_system';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    
    $params = [];
    // Build base query
    $eventQuery = "
        SELECT e.eventID, e.eventName, e.location, e.dateTime, e.image_url, e.description
        FROM event e
        INNER JOIN appointment a ON e.eventID = a.eventID
    ";

    // init WHERE
    $whereClauses = ["a.status = 'completed'"]; // appointment 表的 status

    if ($role === 'Event Organizer') {
        $whereClauses[] = "e.organizerID = ?";
        $params[] = $loggedInUserID;
    } elseif ($role === 'Hospital') {
        $whereClauses[] = "e.hospitalID = ?";
        $params[] = $loggedInUserID;
    }

    //  WHERE
    if (count($whereClauses) > 0) {
        $eventQuery .= " WHERE " . implode(" AND ", $whereClauses);
    }

    $eventQuery .= " GROUP BY e.eventID ORDER BY e.dateTime DESC";
    
    $stmt = $pdo->prepare($eventQuery);
    $stmt->execute($params);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $result = [];

    foreach ($events as $event) {
        // Fetch feedbacks for this event
        $feedbackStmt = $pdo->prepare("
            SELECT f.rating, f.comment, f.dateTime, u.name 
            FROM feedback f
            INNER JOIN user u ON f.userID = u.userID
            WHERE f.eventID = ?
            ORDER BY f.dateTime DESC
        ");
        $feedbackStmt->execute([$event['eventID']]);
        $feedbacks = $feedbackStmt->fetchAll(PDO::FETCH_ASSOC);

        // Format feedbacks
        $formattedFeedbacks = [];
        foreach ($feedbacks as $f) {
            $formattedFeedbacks[] = [
                $f['name'],
                intval($f['rating']),
                $f['comment'],
                $f['dateTime']
            ];
        }

        $eventDateTime = new DateTime($event['dateTime']);
        $result[] = [
        "eventName" => $event['eventName'],
        "location" => $event['location'],
        "date" => $eventDateTime->format('M d, Y'),
        "time" => $eventDateTime->format('h:i A'),
        "image" => $event['image_url'] ?? 'placeholder.jpg',
        "description" => $event['description'] ?? "No description available.",
        "feedback" => $formattedFeedbacks
    ];

    }

    echo json_encode(["status"=>"success","events"=>$result]);

} catch (PDOException $e) {
    echo json_encode(["status"=>"error","message"=>$e->getMessage()]);
}
