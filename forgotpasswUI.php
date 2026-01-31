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

    <form class="forgot-form">
        <div class="input-field">
            <label>
                <i class='bx bx-envelope icon'></i>
                Email
            </label>
            <input type="email" class="email-input" placeholder="Enter your registered email" required>
            <small class="live-msg"></small>
        </div>

        <button type="submit" class="submit-btn" disabled>
            <span class="btnText">Submit</span>
            <i class="uil uil-navigator"></i>
        </button>

        <div class="forgot-links">
            <a href="login.html">Back to Login</a>
        </div>

        <div class="confirmation-msg"></div>
    </form>
</div>

<script src="forgot-password.js"></script>
</body>
</html>
