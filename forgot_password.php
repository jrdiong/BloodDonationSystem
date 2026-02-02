<?php
// Connect to the database
$db_server = "localhost";
$db_user = "root";
$db_password = "";
$dbname = "cbdc_system"; 

$conn = mysqli_connect($db_server, $db_user, $db_password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reset_password'])) {
    $name = trim($_POST['username']); // 对应表列 name
    $email = trim($_POST['email']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if passwords match
    if ($new_password !== $confirm_password) {
        echo "<script>
                alert('Passwords do not match!');
                window.history.back();
              </script>";
        exit;
    }

    // Optional: check password length (min 8 for security)
    if (strlen($new_password) < 8 || strlen($new_password) > 16) {
        echo "<script>
                alert('Password must be 8-16 characters!');
                window.history.back();
              </script>";
        exit;
    }

    // Check if user exists
    $check_sql = "SELECT * FROM user WHERE name = ? AND email = ?";
    $stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($stmt, "ss", $name, $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        // Hash the new password
        $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);

        // Update password
        $update_sql = "UPDATE user SET password = ? WHERE name = ? AND email = ?";
        $update_stmt = mysqli_prepare($conn, $update_sql);
        mysqli_stmt_bind_param($update_stmt, "sss", $hashedPassword, $name, $email);

        if (mysqli_stmt_execute($update_stmt)) {
            echo "<script>
                    alert('Password successfully updated!');
                    window.location.href = 'loginUI.php';
                  </script>";
        } else {
            echo "<script>
                    alert('Error updating password.');
                    window.history.back();
                  </script>";
        }

        mysqli_stmt_close($update_stmt);

    } else {
        echo "<script>
                alert('Name and Email do not match our records.');
                window.history.back();
              </script>";
    }

    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
?>
