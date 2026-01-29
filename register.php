<?php
// register.php
// Handles user registration with default role = Donor

// Database connection info
$host = "localhost";
$dbname = "cbdc_system";
$username = "root";
$password = "";

// Enable error reporting for debugging (development only)
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8",
        $username,
        $password
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Execute only on POST request
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Retrieve and sanitize form data
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $plainPassword = $_POST['password'];

    // Default role
    $role = "Donor";

    // Optional: check if fields are empty
    if (empty($name) || empty($email) || empty($plainPassword)) {
        die("Please fill all required fields.");
    }

    // Hash the password
    $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

    try {
        // Check if email already exists
        $checkSql = "SELECT userID FROM user WHERE email = ?";
        $stmt = $pdo->prepare($checkSql);
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            echo "<script>
                    alert('Email already exists! Please use another email.');
                    window.history.back();
                  </script>";
            exit;
        }

        // Insert new user (phoneNumber left blank)
        $insertSql = "INSERT INTO user (name, email, password, role, phoneNumber)
                      VALUES (?, ?, ?, ?, ?)";
        $insertStmt = $pdo->prepare($insertSql);
        $insertStmt->execute([
            $name,
            $email,
            $hashedPassword,
            $role,
            "" // phoneNumber empty
        ]);

        // Registration successful
        echo "<script>
                alert('Registration successful!');
                window.location.href='login.html';
              </script>";

    } catch (PDOException $e) {
        // Catch all DB errors
        echo "<script>
                alert('Database error: " . $e->getMessage() . "');
                window.history.back();
              </script>";
        exit;
    }
}
?>
