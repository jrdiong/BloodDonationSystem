<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection settings
$host = 'localhost';      
$dbname = 'cbdc_system';   // Your database name
$username = 'root';        // Database username
$password = '';            // Database password (change as needed)

try {
    // Establishing a PDO connection to the MySQL database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Enable exception mode
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
    exit;
}

// Handle GET request to fetch events
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sql = "SELECT * FROM event"; // Fetch all events from the 'event' table
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return events as JSON
    echo json_encode($events);
}

// Handle POST request to create a new event (including file upload)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get POST data
    $eventName = $_POST['eventName'];
    $location = $_POST['location'];
    $dateTime = $_POST['dateTime'];
    $maxDonors = $_POST['maxDonors'];
    $description = $_POST['description'];
    $organizerID = $_POST['organizerID'];  // Ensure these fields are correctly handled
    $hospitalID = $_POST['hospitalID'];

    // Handle image upload
    if (isset($_FILES['imageUpload'])) {
        $imageTmp = $_FILES['imageUpload']['tmp_name'];
        $imageName = $_FILES['imageUpload']['name'];
        $imagePath = 'uploads/' . basename($imageName);

        // Ensure the 'uploads' directory exists
        if (!is_dir('uploads')) {
            mkdir('uploads', 0755, true);
        }

        // Move the uploaded file to the 'uploads' directory
        if (move_uploaded_file($imageTmp, $imagePath)) {
            // Image uploaded successfully
        } else {
            // Error uploading the image
            echo json_encode(["status" => "error", "message" => "Failed to upload image."]);
            exit;
        }
    } else {
        $imagePath = null; // Set to null if no image is uploaded
    }

    // Insert event data into the 'event' table (updated table name)
    try {
        $sql = "INSERT INTO event (eventName, image_url, location, dateTime, maxDonors, description, organizerID, hospitalID, status)
                VALUES (:eventName, :image_url, :location, :dateTime, :maxDonors, :description, :organizerID, :hospitalID, 1)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':eventName', $eventName);
        $stmt->bindParam(':image_url', $imagePath);
        $stmt->bindParam(':location', $location);
        $stmt->bindParam(':dateTime', $dateTime);
        $stmt->bindParam(':maxDonors', $maxDonors);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':organizerID', $organizerID);
        $stmt->bindParam(':hospitalID', $hospitalID);
        
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Event created successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to create event"]);
        }
    } catch (PDOException $e) {
        // If there is a database error, catch and display it
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
}
?>
