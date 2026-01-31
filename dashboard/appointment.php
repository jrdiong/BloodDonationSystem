<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Donor Profile</title>
<link rel="stylesheet" href="style.css" />
<link rel="stylesheet" href="appointment.css" />
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" />
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap" rel="stylesheet">

<style>
body {
  font-family: 'Poppins', sans-serif;
  background: #fde7e7;
  margin: 0;
  padding: 0;
}

.main-container {
  max-width: 900px;
  margin: 120px 60px 0 320px;
  margin-left: calc(260px + (100% - 260px - 900px) / 2);
  padding: 30px;
  background: #fff;
  border-radius: 15px;
  box-shadow: 0 8px 25px rgba(0,0,0,0.08);
}

</style>
</head>
<body>
    <?php include "navbar/navbar_donor.php"; ?>

<div class="main-container">
    <!-- Filter Bar -->
    <div class="filter-bar">
        <input type="text" placeholder="Search appointments..." />

        <select>
        <option>Date Range</option>
        <option>Upcoming</option>
        <option>Past</option>
        </select>

        <select>
        <option>Filter by Status</option>
        <option>Upcoming</option>
        <option>Completed</option>
        <option>Cancelled</option>
        </select>
    </div>

    <!-- Appointment List -->
    <div class="card-list">

        <!-- Upcoming Appointment -->
        <div class="appointment-card">
            <div class="date-badge upcoming">
                <span class="month">DEC</span>
                <span class="day">15</span>
                <span class="year">2024</span>
            </div>

            <div class="card-info">
                <h4>Blood Donation Appointment</h4>
                <p>Hospital ABC</p>
                <p>10:00 AM – 12:00 PM</p>
                <span class="status upcoming">Upcoming</span>
            </div>

            <div class="card-actions">
                <button class="btn disabled" disabled>Feedback</button>
                <button class="btn danger">Cancel</button>
            </div>
        </div>

        <!-- Past Appointment (Feedback Active) -->
        <div class="appointment-card">
            <div class="date-badge past">
                <span class="month">NOV</span>
                <span class="day">28</span>
                <span class="year">2024</span>
            </div>

            <div class="card-info">
                <h4>Blood Donation Appointment</h4>
                <p>Hospital XYZ</p>
                <p>2:00 PM – 4:00 PM</p>
                <span class="status completed">Completed</span>
            </div>

            <div class="card-actions">
                <button class="btn secondary feedback-btn">Feedback</button>
                <button class="btn disabled" disabled>Cancel</button>
            </div>
        </div>

    </div>

    <!-- Feedback Modal -->
    <div class="modal-overlay hidden">
        <div class="rating-box">
            <header>How was your experience?</header>
            <div class="stars">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
            </div>
            <!-- Comment Box -->
            <div class="comment-group">
            <textarea id="feedback-comment" rows="4" required></textarea>
            <label for="feedback-comment">Write your comments here...</label>
            </div>
            <div class="modal-buttons">
                <button class="submit">Submit</button>
                <button class="cancel">Cancel</button>
            </div>
        </div>
    </div>

</div>

<script>
    const feedbackBtns = document.querySelectorAll(".feedback-btn");
    const modalOverlay = document.querySelector(".modal-overlay");
    const cancelBtn = modalOverlay.querySelector(".cancel");
    const submitBtn = modalOverlay.querySelector(".submit");
    const stars = document.querySelectorAll(".stars i");

    // Open modal
    feedbackBtns.forEach(btn => {
        btn.addEventListener("click", () => {
            modalOverlay.classList.remove("hidden");
            clearStars();
        });
    });

    // Close modal
    cancelBtn.addEventListener("click", () => {
        modalOverlay.classList.add("hidden");
    });

    // Star rating logic
    stars.forEach((star, index1) => {
        star.addEventListener("click", () => {
            stars.forEach((s, index2) => {
                index1 >= index2 ? s.classList.add("active") : s.classList.remove("active");
            });
        });
    });

    // Clear stars
    function clearStars() {
        stars.forEach(star => star.classList.remove("active"));
    }

    // Submit feedback
    submitBtn.addEventListener("click", () => {
        const rating = [...stars].filter(star => star.classList.contains("active")).length;
        alert("You submitted a rating of " + rating + " stars!");
        modalOverlay.classList.add("hidden");
    });

</script>

<script src="script.js"></script>

</body>
</html>
