<?php
ob_start();
header('Content-Type: application/json');

// Database connection
$host = 'localhost';
$db = 'cbdc_system';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Connection failed: ' . $e->getMessage()]);
    exit;
}

// ==================== POST: Create User ====================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? null;
    $email = $_POST['email'] ?? null;
    $phoneNumber = $_POST['phoneNumber'] ?? null;
    $role = $_POST['role'] ?? null;
    $password = $_POST['password'] ?? null;

    if (empty($name) || empty($email) || empty($phoneNumber) || empty($password) || empty($role)) {
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
        exit;
    }

    // Check email exists
    $stmt = $pdo->prepare("SELECT * FROM user WHERE email = :email");
    $stmt->execute(['email' => $email]);
    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Email already in use']);
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    try {
        // Insert into user table
        $stmt = $pdo->prepare("INSERT INTO user (name, email, phoneNumber, role, password) VALUES (:name, :email, :phoneNumber, :role, :password)");
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'phoneNumber' => $phoneNumber,
            'role' => $role,
            'password' => $hashedPassword
        ]);

        $userID = $pdo->lastInsertId();

        // Insert into hospital table if role is Hospital
        if ($role === 'Hospital') {
            $location = $_POST['location'] ?? null;
            $stmtHospital = $pdo->prepare("INSERT INTO hospital (userID, location) VALUES (:userID, :location)");
            $stmtHospital->execute([
                'userID' => $userID,
                'location' => $location
            ]);
        }

        echo json_encode(['status' => 'success', 'message' => 'User created successfully', 'userID' => $userID]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// ==================== GET: Query Users ====================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Hospital users with location
        $stmtHospital = $pdo->prepare("
            SELECT u.userID, u.name, u.email, u.phoneNumber, h.location
            FROM user u
            JOIN hospital h ON u.userID = h.userID
            WHERE u.role = 'Hospital'
        ");
        $stmtHospital->execute();
        $hospitals = $stmtHospital->fetchAll(PDO::FETCH_ASSOC);

        // Event Organizer users
        $stmtEvent = $pdo->prepare("
            SELECT userID, name, email, phoneNumber
            FROM user
            WHERE role = 'Event Organizer'
        ");
        $stmtEvent->execute();
        $eventOrganizers = $stmtEvent->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'status' => 'success',
            'hospitals' => $hospitals,
            'event_organizers' => $eventOrganizers
        ]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}
