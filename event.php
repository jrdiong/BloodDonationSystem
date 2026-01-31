<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session to retrieve user info from session
session_start();

// Set content type to JSON
header('Content-Type: application/json');

// Database connection settings
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cbdc_system";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// Fetch events based on user role
function getEvents($role, $userID) {
    global $conn;

    // Fetch events based on the user's role
    if ($role === 'Donor') {
        $sql = "SELECT * FROM event WHERE status = 1";  // Donor can only view active events
    } elseif ($role === 'Event Organizer') {
        $sql = "SELECT * FROM event WHERE organizerID = $userID";  // Event Organizer can view their own events
    } elseif ($role === 'Hospital') {
        $sql = "SELECT * FROM event WHERE hospitalID = $userID";  // Hospital can view events related to them
    } elseif ($role === 'Admin') {
        $sql = "SELECT * FROM event";  // Admin can view all events
    } else {
        return [];
    }

    $result = $conn->query($sql);
    $events = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $events[] = $row;
        }
    }

    return $events;
}

// Book an event for Donor
function bookEvent($userID, $eventID) {
    global $conn;

    $sql = "INSERT INTO event_booking (userID, eventID) VALUES ($userID, $eventID)";
    if ($conn->query($sql) === TRUE) {
        return ['success' => 'Event booked successfully'];
    } else {
        return ['error' => 'Error booking event: ' . $conn->error];
    }
}

// Cancel event booking
function cancelEvent($userID, $eventID) {
    global $conn;

    $sql = "DELETE FROM event_booking WHERE userID = $userID AND eventID = $eventID";
    if ($conn->query($sql) === TRUE) {
        return ['success' => 'Event booking cancelled successfully'];
    } else {
        return ['error' => 'Error cancelling booking: ' . $conn->error];
    }
}

// Create a new event by Event Organizer
function createEvent($userID, $eventData) {
    global $conn;

    // Handle file upload for event image
    if (isset($_FILES['eventImage'])) {
        $imagePath = 'uploads/' . $_FILES['eventImage']['name'];
        move_uploaded_file($_FILES['eventImage']['tmp_name'], $imagePath);
    } else {
        $imagePath = '';
    }

    // Insert new event data into the event table
    $sql = "INSERT INTO event (eventName, description, dateTime, location, maxDonors, organizerID, status, image_url)
            VALUES ('{$eventData['eventName']}', '{$eventData['eventDescription']}', '{$eventData['eventDateTime']}', 
                    '{$eventData['eventLocation']}', '{$eventData['eventMaxDonors']}', $userID, 1, '$imagePath')";

    if ($conn->query($sql) === TRUE) {
        return ['success' => 'Event created successfully'];
    } else {
        return ['error' => 'Error creating event: ' . $conn->error];
    }
}

// Delete an event by Admin
function deleteEvent($eventID) {
    global $conn;

    $sql = "DELETE FROM event WHERE eventID = $eventID";
    if ($conn->query($sql) === TRUE) {
        return ['success' => 'Event deleted successfully'];
    } else {
        return ['error' => 'Error deleting event: ' . $conn->error];
    }
}

// Handle GET request to fetch events and user information
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_SESSION['userID']) && isset($_SESSION['role'])) {
        $role = $_SESSION['role']; // Get role from session
        $userID = $_SESSION['userID']; // Get userID from session

        // Output the current logged-in user's ID, role, and if the "Create New Event" button should be shown
        echo json_encode([
            'userID' => $userID,
            'role' => $role,
            'showCreateButton' => $role === 'Event Organizer' // Show button only for Event Organizers
        ]);

        // Fetch and return events for the current user based on their role
        $events = getEvents($role, $userID);
        echo json_encode($events);
    } else {
        echo json_encode(['error' => 'User not logged in']);
    }
}

// Handle POST request for creating a new event
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Only allow Event Organizers to create events
    if (isset($_SESSION['userID']) && isset($_SESSION['role']) && $_SESSION['role'] == 'Event Organizer') {
        $userID = $_SESSION['userID'];

        // Check if all required fields are provided
        if (isset($_POST['eventName'], $_POST['eventDescription'], $_POST['eventDateTime'], $_POST['eventLocation'], $_POST['eventMaxDonors'])) {
            $eventData = [
                'eventName' => $_POST['eventName'],
                'eventDescription' => $_POST['eventDescription'],
                'eventDateTime' => $_POST['eventDateTime'],
                'eventLocation' => $_POST['eventLocation'],
                'eventMaxDonors' => $_POST['eventMaxDonors']
            ];

            // Create the event
            $response = createEvent($userID, $eventData);
            echo json_encode($response);
        } else {
            echo json_encode(['error' => 'All fields are required to create an event']);
        }
    }
}

// Close the database connection
$conn->close();
?>
