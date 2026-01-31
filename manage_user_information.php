<?php
// Start output buffering to avoid any unwanted HTML output
ob_start();

// Set headers to ensure the response is in JSON format
header('Content-Type: application/json');

// Database connection details
$host = 'localhost';
$db = 'cbdc_system';  // Database name
$username = 'root';   // Database username
$password = '';       // Database password

// Try to establish the database connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Connection failed: ' . $e->getMessage()]);
    exit;
}

// ==================== Create New User Function ====================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get data from $_POST (since the form data is sent as multipart/form-data)
    $name = $_POST['name'] ?? null;
    $email = $_POST['email'] ?? null;
    $phoneNumber = $_POST['phoneNumber'] ?? null;
    $role = $_POST['role'] ?? null;
    $password = $_POST['password'] ?? null;

    // Validate input data
    if (empty($name) || empty($email) || empty($phoneNumber) || empty($password) || empty($role)) {
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
        exit;
    }

    // Check if the email already exists in the database
    $sql = "SELECT * FROM user WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['email' => $email]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Email already in use']);
        exit;
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert user data into the user table
    try {
        $sql = "INSERT INTO user (name, email, phoneNumber, role, password) VALUES (:name, :email, :phoneNumber, :role, :password)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'phoneNumber' => $phoneNumber,
            'role' => $role,
            'password' => $hashedPassword
        ]);

        // Get the user ID of the newly inserted user
        $userID = $pdo->lastInsertId();

        // Return success response
        echo json_encode(['status' => 'success', 'message' => 'User created successfully', 'userID' => $userID]);

    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        exit;
    }

    // Ensure no further output is sent
    exit;
}

// ==================== Query Existing Users Function ====================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Query for Hospital users
        $sqlHospital = "
            SELECT u.userID, u.name, u.email, u.phoneNumber, u.role
            FROM user u
            WHERE u.role = 'Hospital';
        ";
        $stmtHospital = $pdo->prepare($sqlHospital);
        $stmtHospital->execute();
        $hospitals = $stmtHospital->fetchAll(PDO::FETCH_ASSOC);

        // Query for Event Organizer users
        $sqlEventOrganizer = "
            SELECT userID, name, email, phoneNumber, role
            FROM user
            WHERE role = 'Event Organizer';
        ";
        $stmtEventOrganizer = $pdo->prepare($sqlEventOrganizer);
        $stmtEventOrganizer->execute();
        $eventOrganizers = $stmtEventOrganizer->fetchAll(PDO::FETCH_ASSOC);

        // Prepare the final data
        $data = [
            'status' => 'success',
            'hospitals' => $hospitals,
            'event_organizers' => $eventOrganizers
        ];

        // Return the JSON response with the data
        echo json_encode($data);

    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        exit;
    }

    // Ensure no further output is sent
    exit;
}
?>
