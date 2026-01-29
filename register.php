<?php
// register.php
// Handles user registration with default role = donor

$host = "localhost";
$dbname = "cbdc_system";   // Change to your database name
$username = "root";               // Database username
$password = "";                   // Database password

try {
    // Create PDO connection
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8",
        $username,
        $password
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed");
}

// Execute only when the form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Retrieve form data
    $name = $_POST['name'];
    $email = $_POST['email'];
    $plainPassword = $_POST['password'];

    // Default role
    $role = "donor";

    // Hash the password
    $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

    // Check if email already exists
    $checkSql = "SELECT userID FROM users WHERE email = ?";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([$email]);

    if ($checkStmt->rowCount() > 0) {
        echo "Email already exists";
        exit;
    }

    // Insert new user (phoneNumber excluded)
    $sql = "INSERT INTO users (name, email, password, role)
            VALUES (?, ?, ?, ?)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $name,
        $email,
        $hashedPassword,
        $role
    ]);

    echo "Registration successful";
}
?>
