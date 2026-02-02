<?php
session_start();

if (!isset($_SESSION['userID'], $_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

$loggedInUserID = $_SESSION['userID'];
$sessionRole = $_SESSION['role']; // already normalized

// map normalized role → navbar / logic role
$roleMap = [
    'Admin' => 'admin',
    'Event Organizer' => 'organizer',
    'Hospital' => 'hospital',
    'Donor' => 'donor'
];

$role = $roleMap[$sessionRole] ?? 'guest';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Donor Appointments</title>
<style>
/* =======================
   General Styles
======================= */
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
.top-bar h2 {
    color: #f44040;
    margin-bottom: 20px;
}
.appointment-container {
    max-width: 900px;
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}

@media (max-width: 1024px) { 
    .appointment-container { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 700px) { 
    .appointment-container { grid-template-columns: 1fr; }
}
/* ===== Filter Bar ===== */
.filter-bar {
  display: flex;
  gap: 12px;
  margin-bottom: 24px;
}

.filter-bar input,
.filter-bar select {
  padding: 10px 12px;
  border-radius: 6px;
  border: 1px solid #ccc;
  background: #fff;
}

.filter-bar input {
  flex: 1;
}

/* =======================
   Appointment Card
======================= */
.appointment-card {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    cursor: pointer;
    transition: transform 0.2s;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    padding-bottom: 10px;
}

.appointment-card:hover { transform: translateY(-3px); }

.appointment-card img {
    width: 100%;
    height: 150px;
    object-fit: cover;
}

.appointment-card h3 {
    margin: 10px 12px 6px 12px;
    font-size: 16px;
    color: #f44040;
}

.appointment-card p {
    margin: 4px 12px;
    font-size: 14px;
    color: #555;
}

.appointment-card button {
    margin: 6px 12px 0 12px;
    padding: 6px 12px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    font-size: 13px;
    transition: 0.2s;
}

.appointment-card button:hover { opacity: 0.85; }

.appointment-card .cancel-btn { background: #ccc; color: #333; }
.appointment-card .feedback-btn { background: #e74c3c; color: #fff; }

.greyed { filter: grayscale(100%); opacity:0.6; }

.modal {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.4);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal.hidden { display: none; }

.modal-content {
  position: relative;
  background: #fff;
  padding: 25px 50px 35px;
  border-radius: 25px;
  box-shadow: 0 5px 10px rgba(0, 0, 0, 0.05);
  text-align: center;
}

.modal-content header {
  font-size: 22px;
  color: #333;
  font-weight: 500;
  margin-bottom: 20px;
}

#starRating .star {
    color: #e6e6e6;      /* inactive = grey */
    font-size: 35px;
    cursor: pointer;
    transition: color 0.2s ease;
}

/* Selected / clicked stars */
#starRating .star.selected {
    color: #ff9c1a;      /* yellow */
}

/* Hover effect */
#starRating .star:hover,
#starRating .star:hover ~ .star {
    color: #ffc107;      /* lighter yellow */
}

.comment-group {
  position: relative;
  margin-top: 20px;
  margin-bottom: 20px;
  width: 100%;
}

.comment-group textarea {
  width: 100%;
  padding: 16px 12px 12px 12px;
  border-radius: 12px;
  border: 1px solid #ccc;
  outline: none;
  resize: vertical;
  font-family: 'Poppins', sans-serif;
  font-size: 14px;
  background: #fdfdfd;
  transition: border 0.2s, box-shadow 0.2s;
}

.comment-group textarea:focus {
  border-color: #d21919;
  box-shadow: 0 4px 12px rgba(25, 118, 210, 0.2);
}

/* Floating label */
.comment-group label {
  position: absolute;
  top: 16px;
  left: 12px;
  color: #999;
  font-size: 14px;
  pointer-events: none;
  transition: all 0.2s ease;
}

.comment-group textarea:focus + label,
.comment-group textarea:not(:placeholder-shown) + label {
  top: -8px;
  left: 10px;
  font-size: 12px;
  color: #d21919;
  background: #fff;
  padding: 0 4px;
}

.modal-buttons { margin-top: 20px; display:flex; justify-content:center; gap:10px; }
.modal-buttons button {
  padding: 8px 20px;
  border-radius: 6px;
  border: none;
  cursor: pointer;
}

.modal-buttons .submit { background: #d21919; color: #fff; }
.modal-buttons .cancel { background: #ccc; color: #333; }

</style>
</head>
<body>
<?php include "navbar_$role.php"; ?>

<div class="main-container">
    <div class="top-bar">
        <h2>Appointment</h2>
    </div>
    <!-- Filter Bar -->
    <div class="filter-bar">
        <input type="text" placeholder="Search appointments..." />

        <select>
        <option>Filter by Status</option>
        <option>Upcoming</option>
        <option>Completed</option>
        <option>Cancelled</option>
        </select>
    </div>

<div class="appointment-container" id="appointmentContainer">
    <!-- Cards injected by JS -->
</div>

<!-- Event Details Modal -->
<div class="modal hidden" id="appointmentModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Event Title</h3>
            <span class="close-modal" id="closeModal">X</span>
        </div>
        <div class="modal-body">
            <img id="modalImage" src="">
            <p><strong>Date & Time:</strong> <span id="modalDate"></span></p>
            <p><strong>Location:</strong> <span id="modalLocation"></span></p>
            <p><strong>Description:</strong></p>
            <p id="modalDescription"></p>
        </div>
    </div>
</div>

<!-- Feedback Modal -->
<div class="modal hidden" id="feedbackModal">
    <div class="modal-content">
        <header>How was your experience?</header>

        <!-- Star Rating -->
        <div id="starRating">
            <i class="star" data-value="1">☆</i>
            <i class="star" data-value="2">☆</i>
            <i class="star" data-value="3">☆</i>
            <i class="star" data-value="4">☆</i>
            <i class="star" data-value="5">☆</i>
        </div>

        <!-- Comment Box -->
        <div class="comment-group">
            <textarea id="feedbackComment" rows="4" placeholder=" " required></textarea>
            <label for="feedbackComment">Write your comments here...</label>
        </div>

        <!-- Buttons -->
        <div class="modal-buttons">
            <button class="submit" id="submitFeedbackBtn">Submit</button>
            <button class="cancel" id="closeFeedbackModal">Cancel</button>
        </div>
    </div>
</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {

    const container = document.getElementById('appointmentContainer');
    const modal = document.getElementById('appointmentModal');
    const closeModal = document.getElementById('closeModal');

    let currentEventID = null;
    let currentAppointmentID = null;
    let selectedRating = 0;

    
    const feedbackModal = document.getElementById('feedbackModal');
    const feedbackStars = document.querySelectorAll('#starRating .star');
    const feedbackComment = document.getElementById('feedbackComment');
    const submitFeedbackBtn = document.getElementById('submitFeedbackBtn');
    const closeFeedbackModal = document.getElementById('closeFeedbackModal');

    feedbackStars.forEach(star => {
    star.addEventListener('click', function() {
        selectedRating = parseInt(this.dataset.value);
        feedbackStars.forEach(s => {
            s.textContent = parseInt(s.dataset.value) <= selectedRating ? '★' : '☆';
        });
    });
    });
    feedbackStars.forEach(star => {

    // Hover: highlight stars
    star.addEventListener('mouseenter', () => {
        const val = parseInt(star.dataset.value);
        feedbackStars.forEach(s => {
            s.classList.toggle('selected', parseInt(s.dataset.value) <= val);
        });
    });

    // Hover leave: reset to actual rating
    star.addEventListener('mouseleave', () => {
        feedbackStars.forEach(s => {
            s.classList.toggle('selected', parseInt(s.dataset.value) <= selectedRating);
        });
    });

    // Click: set rating
    star.addEventListener('click', () => {
        selectedRating = parseInt(star.dataset.value);
        feedbackStars.forEach(s => {
            s.classList.toggle('selected', parseInt(s.dataset.value) <= selectedRating);
        });
    });
});


    submitFeedbackBtn.addEventListener('click', async function() {
    const comment = feedbackComment.value.trim();
    if(selectedRating === 0 || comment === '') {
        alert('Please give a rating and write a comment.');
        return;
    }

    const form = new FormData();
    form.append('appointmentID', currentAppointmentID);
    form.append('eventID', currentEventID);
    form.append('rating', selectedRating);
    form.append('comment', comment);

    try {
        const res = await fetch('submit_feedback.php', { method:'POST', body: form });
        const data = await res.json();
        if(data.status === 'success'){
            alert('Feedback submitted successfully!');
            feedbackModal.style.display = 'none';
        } else {
            alert(data.message || 'Failed to submit feedback');
        }
    } catch(err) {
        console.error(err);
        alert('Error submitting feedback');
    }
    });

    // Close modals
    closeModal.addEventListener('click', () => modal.style.display = 'none');

    // Load appointments
    async function loadAppointments() {
        try {
            const res = await fetch('donor_appointments.php');
            const data = await res.json();
            if(data.status !== 'success') { alert(data.message || 'Failed'); return; }

            container.innerHTML = '';

            data.appointments.forEach(app => {
                const card = document.createElement('div');
                card.classList.add('appointment-card');
                if(['completed','cancelled','rejected'].includes(app.displayStatus)){
                card.classList.add('greyed');
                }

                const img = document.createElement('img');
                img.src = app.image_url || 'placeholder.jpg';
                card.appendChild(img);

                const title = document.createElement('h3');
                title.textContent = app.eventName;
                card.appendChild(title);

                const date = document.createElement('p');
                date.textContent = 'Date: ' + app.eventDateTime;
                card.appendChild(date);

                const location = document.createElement('p');
                location.textContent = 'Location: ' + (app.location || 'TBD');
                card.appendChild(location);

                const count = document.createElement('p');
                count.textContent = `Booking: ${app.currentBookings}/${app.maxDonors}`;
                card.appendChild(count);

                const status = document.createElement('p');
                status.textContent = 'Status: ' + app.displayStatus;  
                card.appendChild(status);

                /* Cancel button */
                const cancellableStatuses = ['pending', 'approved']; 
                if (cancellableStatuses.includes(app.appointmentStatus)) {
                    const btn = document.createElement('button');
                    btn.textContent = 'Cancel Appointment';
                    btn.addEventListener('click', function(e){
                        e.stopPropagation();
                        cancelAppointment(app.appointmentID);
                    });
                    card.appendChild(btn);
                }

                /* Feedback button – ONLY completed */
                if (app.displayStatus === 'completed') {
                    const feedbackBtn = document.createElement('button');
                    feedbackBtn.textContent = 'Give Feedback';
                    feedbackBtn.style.background = '#e74c3c'; // red

                    // check if alrd gv feedback
                    fetch(`get_feedback.php?appointmentID=${app.appointmentID}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'exists') {
                            // alrd hv feedback -> gray
                            feedbackBtn.style.background = 'gray';
                            feedbackBtn.textContent = 'View Feedback';

                            feedbackBtn.addEventListener('click', (e) => {
                                e.stopPropagation();
                                selectedRating = data.feedback.rating;
                                feedbackComment.value = data.feedback.comment;

                                // update modal stars
                                feedbackStars.forEach(star => {
                                    star.textContent = parseInt(star.dataset.value) <= selectedRating ? '★' : '☆';
                                });

                                // block update button
                                submitFeedbackBtn.disabled = true;
                                submitFeedbackBtn.style.opacity = 0.5;

                                feedbackModal.style.display = 'flex';
                            });
                        } else {
                            // red
                            feedbackBtn.style.background = '#e74c3c';
                            feedbackBtn.textContent = 'Give Feedback';

                            feedbackBtn.addEventListener('click', (e) => {
                                e.stopPropagation();
                                openFeedbackModal(app);
                                
                                submitFeedbackBtn.disabled = false;
                                submitFeedbackBtn.style.opacity = 1;
                            });
                        }
                    })
                    .catch(err => {
                        console.error(err);
                    });

                    card.appendChild(feedbackBtn);
                }
                // Click card to show event details
                card.addEventListener('click', function(){
                    openModal(app);
                });

                container.appendChild(card);
                });
        } catch(err) {
            console.error(err);
            alert('Error loading appointments');
        }
    }

    function openModal(app) {
        document.getElementById('modalImage').src = app.image_url || 'placeholder.jpg';
        document.getElementById('modalTitle').innerText = app.eventName;
        document.getElementById('modalDate').innerText = app.eventDateTime;
        document.getElementById('modalLocation').innerText = app.location || 'TBD';
        document.getElementById('modalDescription').innerText = app.description || 'No description';
        modal.style.display = 'flex';
    }

    function openFeedbackModal(app){
    currentAppointmentID = app.appointmentID;
    currentEventID = app.eventID;
    selectedRating = 0;
    feedbackComment.value = '';

    // initia start
    feedbackStars.forEach(star => star.textContent = '☆');

    feedbackModal.style.display = 'flex';
    }

    // Cancel appointment
    async function cancelAppointment(appointmentID){
        if(!confirm('Cancel this appointment?')) return;
        const form = new FormData();
        form.append('appointmentID', appointmentID);

        try{
            const res = await fetch('cancel_appointment.php', { method:'POST', body: form });
            const data = await res.json();
            if(data.status === 'success'){
                alert('Appointment cancelled successfully');
                loadAppointments();
            } else {
                alert(data.message || 'Failed');
            }
        } catch(err) {
            console.error(err);
            alert('Failed to cancel appointment');
        }
    }
    /*closeFeedbackModal*/

    closeFeedbackModal.addEventListener('click', () => {
    feedbackModal.style.display = 'none';
});

    loadAppointments();
});
</script>

</body>
</html>
