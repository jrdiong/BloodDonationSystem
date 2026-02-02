<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Donor Profile</title>
<link rel="stylesheet" href="style.css" />
<link rel="stylesheet" href="appointment.css" />
<link rel="stylesheet" href="event.css" />
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
    <?php include "navbar_donor.php"; ?>

<div class="main-container">
    <!-- Filter Bar -->
        <div class="filter-bar">
            <input type="text" placeholder="Search events..." />
            <select>
                <option>All Events</option>
                <option>Upcoming</option>
                <option>Past</option>
            </select>
        </div>

        <!-- Event List -->
        <div class="card-list">

            <!-- Upcoming Event -->
            <div class="event-card">
                <div class="date-badge upcoming">
                    <span class="month">FEB</span>
                    <span class="day">10</span>
                    <span class="year">2026</span>
                </div>

                <div class="card-info">
                    <h4>Blood Donation Drive</h4>
                    <p>Hospital ABC</p>
                    <p>10:00 AM – 2:00 PM</p>
                    <span class="status upcoming">Upcoming</span>
                </div>

                <div class="card-actions">
                    <button class="btn primary book-btn">Book Event</button>
                </div>
            </div>

            <!-- Another Event -->
            <div class="event-card">
                <div class="date-badge upcoming">
                    <span class="month">MAR</span>
                    <span class="day">5</span>
                    <span class="year">2026</span>
                </div>

                <div class="card-info">
                    <h4>Health Awareness Camp</h4>
                    <p>Community Center XYZ</p>
                    <p>9:00 AM – 1:00 PM</p>
                    <span class="status upcoming">Upcoming</span>
                </div>

                <div class="card-actions">
                    <button class="btn primary book-btn">Book Event</button>
                </div>
            </div>

        </div>
    </div>
</div>
<!-- Event Confirmation Modal -->
    <div class="modal-overlay hidden">
        <div class="modal-card">
            <div class="modal-header">
                <h3>Confirm Your Booking</h3>
                <i class="fa-solid fa-xmark close-modal"></i>
            </div>

            <div class="modal-body">
                <p><strong>Event:</strong> <span id="modal-event-name"></span></p>
                <p><strong>Date:</strong> <span id="modal-event-date"></span></p>
                <p><strong>Time:</strong> <span id="modal-event-time"></span></p>
                <p><strong>Location:</strong> <span id="modal-event-location"></span></p>
            </div>

            <div class="modal-footer">
                <button class="btn btn-confirm">Confirm</button>
                <button class="btn btn-cancel">Cancel</button>
            </div>
        </div>
    </div>

<script>
    const bookBtns = document.querySelectorAll(".book-btn");
    const modalOverlay = document.querySelector(".modal-overlay");
    const confirmBtn = modalOverlay.querySelector(".btn-confirm");
    const cancelBtn = modalOverlay.querySelector(".btn-cancel");
    const closeIcon = modalOverlay.querySelector(".close-modal");

    bookBtns.forEach(btn => {
        btn.addEventListener("click", () => {
            const card = btn.closest(".event-card");
            document.getElementById("modal-event-name").innerText = card.querySelector("h4").innerText;
            document.getElementById("modal-event-date").innerText =
                card.querySelector(".date-badge .month").innerText + " " +
                card.querySelector(".date-badge .day").innerText + ", " +
                card.querySelector(".date-badge .year").innerText;
            document.getElementById("modal-event-time").innerText =
                card.querySelector(".card-info p:nth-of-type(2)").innerText;
            document.getElementById("modal-event-location").innerText =
                card.querySelector(".card-info p:nth-of-type(1)").innerText;

            modalOverlay.classList.remove("hidden");
            modalOverlay.classList.add("show");
            modalOverlay.currentBtn = btn;
        });
    });

    cancelBtn.addEventListener("click", () => {
        modalOverlay.classList.add("hidden");
        modalOverlay.classList.remove("show");
    });
    closeIcon.addEventListener("click", () => {
        modalOverlay.classList.add("hidden");
        modalOverlay.classList.remove("show");
    });

    confirmBtn.addEventListener("click", () => {
        const btn = modalOverlay.currentBtn;
        btn.classList.add("disabled");
        btn.disabled = true;
        btn.innerText = "Booked";
        alert("You have successfully booked this event!");
        modalOverlay.classList.add("hidden");
        modalOverlay.classList.remove("show");
    });

</script>

<script src="script.js"></script>

</body>
</html>
