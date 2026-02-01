<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="login-container">
        <header>Donor Login</header>
        <form class="login-form" action="login.php" method="POST">
            <div class="input-field">
                <label>
                    <i class='bx bx-envelope icon'></i>
                    Email
                </label>
                <input name="email" type="email" class="email-input" placeholder="Enter your email" required>
                <small class="live-msg"></small>
            </div>

            <div class="input-field">
                <label>
                    <i class='bx bx-lock icon'></i>
                    Password
                </label>
                <input name="password" type="password" class="password-input" placeholder="Enter your password" required>
                <small class="live-msg"></small>
            </div>

            <div class="checkbox-field">
                <label>
                    <input type="checkbox" class="remember-me"> Remember Me
                </label>
            </div>
            <input type="hidden" name="login" value="1">
            <button type="submit" class="login-btn">
                <span class="btnText">Login</span>
            </button>

            <div class="login-links">
                <a href="forgotpasswUI.php">Forgot Password?</a>
                <span>|</span>
                Don’t have an account? <a href="registerUI.php">Register</a>
            </div>
        </form>
    </div>
    <script src="login.js"></script>
</body>
</html>
