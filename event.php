<?php
// Set the content type to JSON
header('Content-Type: application/json');

// Enable error reporting for debugging (you can remove this in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cbdc_system";

// Create a new MySQLi connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check if the connection was successful
if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Connection failed: ' . $conn->connect_error]));
}

// Get the token from the request header
$token = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : null;

// If no token is provided, return 401 Unauthorized error
if (!$token) {
    echo json_encode(['status' => 'error', 'message' => 'No token provided']);
    exit;
}

// Function to verify the JWT token (you can replace this with your own JWT verification logic)
function verify_token($token) {
    // Sample JWT verification (replace this with actual logic)
    // This function should return a decoded token or false if invalid
    // Example:
    // return ['userID' => 1, 'role' => 'Event Organize']; // Mocked for demo purposes
    return true;  // Mocked success for demo purposes
}

// Verify the token
$decoded = verify_token($token);

// If token verification fails, return 403 Forbidden error
if (!$decoded) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid token']);
    exit;
}

// Get user ID and role from decoded token
$userID = $decoded['userID'];
$role = $decoded['role'];

// If the user is not an event organizer, return a 403 Forbidden error
if ($role !== 'Event Organize') {
    echo json_encode(['status' => 'error', 'message' => 'You are not authorized to create an event.']);
    exit;
}

// Get the event data from the POST request
$data = json_decode(file_get_contents('php://input'), true);

// Assign variables for event data
$eventName = $data['eventName'];
$image_url = $data['image_url'];
$location = $data['location'];
$dateTime = $data['dateTime'];
$maxDonors = $data['maxDonors'];
$description = $data['description'];

// Check if required fields are missing
if (!$eventName || !$image_url || !$location || !$dateTime || !$maxDonors || !$description) {
    echo json_encode(['status' => 'error', 'message' => 'Required fields are missing.']);
    exit;
}

// Insert the event data into the database
$query = "INSERT INTO event (eventName, image_url, location, dateTime, maxDonors, description, organizerID) 
          VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($query);  // Use $conn to prepare the query
$stmt->bind_param("ssssisi", $eventName, $image_url, $location, $dateTime, $maxDonors, $description, $userID);

// Execute the query and check if the event was created successfully
if ($stmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'eventID' => $stmt->insert_id,
        'eventName' => $eventName,
        'location' => $location,
        'dateTime' => $dateTime,
        'maxDonors' => $maxDonors,
        'description' => $description,
        'organizerID' => $userID
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to create event.']);
}
?>
