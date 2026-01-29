<?php
// Start the session
session_start();

// Enable error reporting for debugging 
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Set up the database connection
$db_server = "localhost";
$db_user = "root";
$db_password = "";
$dbname = "main_system";

$conn = mysqli_connect($db_server, $db_user, $db_password, $dbname);

// Check connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Only process login if POST method and login button was clicked
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['login'])) {
    $username = $_POST['name'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        echo "<script>
                alert('Please enter both username and password.');
                window.history.back();
              </script>";
        exit();
    }

    // Check if the user exists
    $sql = "SELECT * FROM user WHERE Username = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        // Compare the provided password with the stored plain text
        if ($password === $row['password']) {
            // Login successful, set session variables
            $_SESSION['user'] = $row['user']; 
            $_SESSION['name'] = $row['name'];
            $_SESSION['role'] = $row['role'];

            header("Location: index.php");
            exit();
        } else {
            // Password incorrect
            echo "<script>
                    alert('Incorrect password!');
                    window.history.back();
                  </script>";
        }
    } else {
        // Username not found
        echo "<script>
                alert('Account does not exist!');
                window.history.back();
              </script>";
    }

    mysqli_stmt_close($stmt);
} else {
    // If user accessed login.php without POST, redirect to login.html
    header("Location: login.html");
    exit();
}

mysqli_close($conn);
?>
