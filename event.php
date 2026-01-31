<?php
// Database connection settings
$host = 'localhost';      // Database host
$dbname = 'cbdc_system';   // Your database name
$username = 'root';        // Database username
$password = '';            // Database password (change as needed)

try {
    // Establishing a PDO connection to the MySQL database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Enable exception mode
} catch (PDOException $e) {
    // If connection fails, output error message
    echo 'Connection failed: ' . $e->getMessage();
    exit;
}

// Handle GET request to fetch events
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // SQL to fetch all events from the database
    $sql = "SELECT * FROM events"; // Fetch all events
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return events as JSON
    echo json_encode($events);
}

// Handle POST request to create a new event
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get POST data from the form
    $eventName = $_POST['eventName'];
    $imageUrl = $_POST['imageUrl'];
    $location = $_POST['location'];
    $dateTime = $_POST['dateTime'];
    $maxDonors = $_POST['maxDonors'];
    $description = $_POST['description'];
    $organizerID = $_POST['organizerID'];
    $hospitalID = $_POST['hospitalID'];

    // Validate required fields
    if (empty($eventName) || empty($location) || empty($dateTime) || empty($maxDonors) || empty($organizerID) || empty($hospitalID)) {
        echo json_encode(["status" => "error", "message" => "All fields are required."]);
        exit;
    }

    // SQL query to insert the new event into the database
    $sql = "INSERT INTO events (eventName, image_url, location, dateTime, maxDonors, description, organizerID, hospitalID, status)
            VALUES (:eventName, :imageUrl, :location, :dateTime, :maxDonors, :description, :organizerID, :hospitalID, 1)";
    
    // Prepare the SQL statement
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':eventName', $eventName);
    $stmt->bindParam(':imageUrl', $imageUrl);
    $stmt->bindParam(':location', $location);
    $stmt->bindParam(':dateTime', $dateTime);
    $stmt->bindParam(':maxDonors', $maxDonors);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':organizerID', $organizerID);
    $stmt->bindParam(':hospitalID', $hospitalID);
    
    // Execute the query and check for success
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Event created successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to create event"]);
    }
}
?>
