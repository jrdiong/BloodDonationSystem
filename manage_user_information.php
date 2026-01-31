<?php
// Database connection (embedded in createUser.php)

// Database configuration
$host = 'localhost';
$db = 'cbdc_system';  // Database name
$username = 'root';   // Database username
$password = '';       // Database password

// Create PDO instance
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit;
}

// Get input data in JSON format
$data = json_decode(file_get_contents("php://input"));

// Extract input fields
$name = $data->name;
$email = $data->email;
$phoneNumber = $data->phoneNumber;
$role = $data->role;
$password = $data->password;
$location = isset($data->location) ? $data->location : null;  // Only for Hospital role

// Data validation
if (empty($name) || empty($email) || empty($phoneNumber) || empty($password) || empty($role)) {
    echo json_encode(['message' => 'Missing required fields']);
    exit;
}

// Check if email already exists
$sql = "SELECT * FROM user WHERE email = :email";
$stmt = $pdo->prepare($sql);
$stmt->execute(['email' => $email]);

if ($stmt->rowCount() > 0) {
    echo json_encode(['message' => 'Email already in use']);
    exit;
}

// Hash password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insert user data into the user table (no image_url)
$sql = "INSERT INTO user (name, email, phoneNumber, role, password) VALUES (:name, :email, :phoneNumber, :role, :password)";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    'name' => $name,
    'email' => $email,
    'phoneNumber' => $phoneNumber,
    'role' => $role,
    'password' => $hashedPassword
]);

// Get inserted userID
$userID = $pdo->lastInsertId();

// If role is Hospital, insert hospital information
if ($role == 'Hospital' && !empty($location)) {
    $sqlHospital = "INSERT INTO hospital (userID, location) VALUES (:userID, :location)";
    $stmtHospital = $pdo->prepare($sqlHospital);
    $stmtHospital->execute([
        'userID' => $userID,
        'location' => $location
    ]);
}

// Return success message
echo json_encode(['status' => 'success', 'message' => 'User created successfully', 'userID' => $userID]);
?>
