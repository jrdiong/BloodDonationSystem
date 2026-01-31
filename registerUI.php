<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="register.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
   <title>Regisration Form </title>
</head>
<body>
    <div class="container">
        <header>Donor Registration</header>
        <form action="#">
            <div class="form first">
                <div class="details personal">
                    <div class="fields">
                        <div class="input-field">
                            <label><i class='bx bx-user icon'></i>Full Name</label>
                            <input type="text" placeholder="Enter your name" required>
                            <small class="live-msg"></small>
                        </div>
                        <div class="input-field">
                            <label><i class='bx bx-envelope icon'></i>Email</label>
                            <input type="text" placeholder="Enter your email" required>
                            <small class="live-msg"></small>
                        </div>
                        <div class="input-field">
                            <label><i class='bx bx-lock icon'></i>Password</label>
                            <input type="password" id="password" placeholder="Enter password" required>
                            <small class="live-msg"></small>
                        </div>
                        <div class="input-field">
                            <label><i class='bx bx-lock icon'></i>Confirm Password</label>
                            <input type="password" id="confirmPassword" placeholder="Confirm password" required>
                            <small id="passwordMsg" class="live-msg"></small>
                        </div>
                        <div class="input-field">
                            <label><i class='bx bx-phone icon'></i>Mobile Number</label>
                            <input type="number" placeholder="Enter mobile number" required>
                            <small class="live-msg"></small>
                        </div>
                        <div class="input-field">
                            <label><i class='bx bx-cake icon'></i>Date of Birth</label>
                            <input type="date" id="dob" placeholder="Enter birth date" required>
                            <small id="ageMsg" class="live-msg"></small>
                        </div>
                        <div class="input-field">
                            <label><i class='bx bx-droplet icon'></i>Blood Type</label>
                            <select required>
                                <option disabled selected>Select blood type</option>
                                <option>A+</option>
                                <option>A-</option>
                                <option>B+</option>
                                <option>B-</option>
                                <option>AB+</option>
                                <option>AB-</option>
                                <option>O+</option>
                                <option>O-</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="declaration">
                    <label>
                        <input type="checkbox" id="declare" required>
                        I confirm that the information provided is true.
                    </label>
                </div>
                <button class="sumbit" id="submitBtn" disabled>
                            <span class="btnText">Register</span>
                </button>
            </div>
        </form>
    </div>
    <script src="register.js"></script>
</body>
</html>