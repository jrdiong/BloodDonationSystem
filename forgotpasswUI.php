<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="forgot-password.css">
</head>
<body>

<div class="forgot-container">
    <header>Forgot Password</header>

    <form class="forgot-form" method="POST" action="forgot_password.php">
        <input type="hidden" name="reset_password" value="1">
        <div class="input-field">
            <label>
                <i class='bx bx-user icon'></i>
                Username
            </label>
            <input type="text" class="username-input" name="username" placeholder="Enter your username" required>
            <small class="live-msg"></small>
        </div>

        <div class="input-field">
            <label>
                <i class='bx bx-envelope icon'></i>
                Email
            </label>
            <input type="email" class="email-input" name="email" placeholder="Enter your registered email" required>
            <small class="live-msg"></small>
        </div>

        <div class="input-field">
            <label>
                <i class='bx bx-lock-alt icon'></i>
                New Password
            </label>
            <input type="password" class="newpass-input" name="new_password" placeholder="8-16 characters" required>
            <small class="live-msg"></small>
        </div>

        <div class="input-field">
            <label>
                <i class='bx bx-lock icon'></i>
                Confirm Password
            </label>
            <input type="password" class="confpass-input" name="confirm_password" placeholder="Re-enter new password" required>
            <small class="live-msg"></small>
        </div>

        <button type="submit" class="submit-btn" disabled>
            <span class="btnText">Submit</span>
        </button>

        <div class="forgot-links">
            <a href="loginUI.php">Back to Login</a>
        </div>

        <div class="confirmation-msg"></div>
    </form>
</div>

<script src="forgot-password.js"></script>
</body>
</html>
