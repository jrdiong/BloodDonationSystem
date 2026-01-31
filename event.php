<?php
// Include the database connection file
include('db_connection.php');

// Start the session to retrieve user information
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['userID'])) {
    die('Please log in first');
}
$userID = $_SESSION['userID'];
$role = $_SESSION['role'];

// Handle actions based on user roles
if ($_SERVER['REQUEST_METHOD'] == 'GET') {

    // Fetch all events that are not deleted
    if ($role == 'Donor') {
        // Donors can see all events, regardless of hospital or organizer
        $query = "SELECT * FROM event WHERE status = 1";
    } elseif ($role == 'Event Organizer' || $role == 'Hospital') {
        // Event Organizers and Hospitals only see events they are involved in
        $query = "SELECT * FROM event WHERE status = 1 AND (organizerID = ? OR hospitalID = ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $userID, $userID);
        $stmt->execute();
        $result = $stmt->get_result();
        $events = $result->fetch_all(MYSQLI_ASSOC);
    } elseif ($role == 'Admin') {
        // Admin can see all events
        $query = "SELECT * FROM event WHERE status != 0"; // Including deleted events for admin
    }

    // Fetch events and return to frontend
    if ($role == 'Event Organizer' || $role == 'Hospital') {
        echo json_encode($events); // Return the events for Event Organizer or Hospital
        exit;
    }

    // Handle form submission (POST request)
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Handle Event Organizer submitting a new event
    if ($role == 'Event Organizer') {
        $eventName = trim($_POST['eventName']);
        $description = trim($_POST['description']);
        $dateTime = $_POST['dateTime'];
        $location = trim($_POST['location']);
        $maxDonors = (int) $_POST['maxDonors'];
        $hospitalID = (int) $_POST['hospitalID'];  // Hospital selected by Event Organizer

        // Validate the form data
        if (empty($eventName) || empty($description) || empty($dateTime) || empty($location) || $maxDonors <= 0 || $hospitalID <= 0) {
            die('Please ensure all fields are filled correctly.');
        }

        // Insert the new event request into the database (pending approval)
        $query = "INSERT INTO event (eventName, description, dateTime, location, maxDonors, organizerID, hospitalID, status) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, 1)";  // Status is set to 1 (active)
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssis", $eventName, $description, $dateTime, $location, $maxDonors, $userID, $hospitalID);
        if ($stmt->execute()) {
            echo 'Event request has been successfully created and sent for admin approval.';
        } else {
            echo 'Failed to create the event. Please try again later.';
        }
        $stmt->close();
        exit;
    }

    // Handle Admin deleting an event
    if ($role == 'Admin') {
        $eventID = (int) $_POST['eventID'];

        // Validate the event ID
        if ($eventID <= 0) {
            die('Invalid event ID.');
        }

        // Update event status to 0 (deleted) instead of actually deleting the record
        $query = "UPDATE event SET status = 0 WHERE eventID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $eventID);
        if ($stmt->execute()) {
            echo 'Event has been deleted successfully.';
        } else {
            echo 'Failed to delete the event. Please try again later.';
        }
        $stmt->close();
        exit;
    }

    // Handle Donor booking an event
    if ($role == 'Donor') {
        $eventID = (int) $_POST['eventID'];

        // Validate the event ID
        if ($eventID <= 0) {
            die('Invalid event ID.');
        }

        // Add event to Donor's event page (assuming a `donor_events` table exists to track bookings)
        $query = "INSERT INTO donor_events (donorID, eventID) VALUES (?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $userID, $eventID);
        if ($stmt->execute()) {
            echo 'Event has been booked successfully.';
        } else {
            echo 'Failed to book the event. Please try again later.';
        }
        $stmt->close();
        exit;
    }
}

// Closing database connection
$conn->close();
?>
