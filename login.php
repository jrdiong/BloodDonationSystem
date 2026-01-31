<?php
session_start();

// Enable error reporting for debugging 
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database connection
$db_server = "localhost";
$db_user = "root";
$db_password = "";
$dbname = "cbdc_system";

$conn = mysqli_connect($db_server, $db_user, $db_password, $dbname);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Only process login if POST method
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['login'])) {
    $username = $_POST['name'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        echo "<script>
                alert('Please enter both name and password.');
                window.history.back();
              </script>";
        exit();
    }

    // Check if the user exists
    $sql = "SELECT * FROM user WHERE name = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        // Compare password using password_verify
        if (password_verify($password, $row['password'])) {
            // Login successful
            $_SESSION['userID'] = $row['userID'];
            $_SESSION['name'] = $row['name'];
            $_SESSION['role'] = $row['role'];

            header("Location: event.html"); 
            exit();
        } else {
            echo "<script>
                    alert('Incorrect password!');
                    window.history.back();
                  </script>";
        }
    } else {
        echo "<script>
                alert('Account does not exist!');
                window.history.back();
              </script>";
    }

    mysqli_stmt_close($stmt);
} else {
    header("Location: login.html");
    exit();
}

mysqli_close($conn);
?>
