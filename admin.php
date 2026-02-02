<?php
session_start();

$conn = mysqli_connect("localhost", "root", "", "cbdc_system");
if (!$conn) { die("DB error: " . mysqli_connect_error()); }

if (!isset($_SESSION['userID'])) {
    header("Location: loginUI.php");
    exit();
}

$userID = (int)$_SESSION['userID'];
$name = "Admin";

$stmt = mysqli_prepare($conn, "SELECT name FROM user WHERE userID = ?");
mysqli_stmt_bind_param($stmt, "i", $userID);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($res)) {
    $name = $row['name'] ?? $name;
}

$totalDonors = 0;

$stmtDonors = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM user WHERE role = ?");
$role = "Donor";
mysqli_stmt_bind_param($stmtDonors, "s", $role);
mysqli_stmt_execute($stmtDonors);
$resDonors = mysqli_stmt_get_result($stmtDonors);

if ($row = mysqli_fetch_assoc($resDonors)) {
    $totalDonors = (int)($row['total'] ?? 0);
}

$pendingApprovals = 0;

$q = mysqli_query($conn, "SELECT COUNT(*) AS total FROM appointment WHERE status='Pending'");
if ($q) {
    $r = mysqli_fetch_assoc($q);
    $pendingApprovals = (int)($r['total'] ?? 0);
}

$totalEvents = 0;

$stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM event");
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($res)) {
    $totalEvents = $row['total'] ?? 0;
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard</title>
<link rel="stylesheet" href="style.css" />
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
body { background: #fcf4f4; padding: 20px; color: #333; }

/* Layout */
.dashboard {
  display: grid;
  grid-template-columns: 3fr 1.2fr;
  gap: 20px;
  margin-top: 60px; /* below navbar */
  margin-left: 250px; /* sidebar width */
  padding: 20px;
}
.left-section .welcome-box {
    background: #ffffff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    text-align: center;
    margin-bottom: 15px;   /* space below box */
}

.left-section .welcome-box h4 {
    font-size: 1.5rem;
    color: #8a1e1e;
    margin-bottom: 5px;
}

.left-section .welcome-box p {
    font-size: 0.95rem;
    color: #555;
}


/* Left section cards */
.cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px; }
.card { border-radius: 12px; padding: 25px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); transition: transform 0.2s, box-shadow 0.2s; text-align: center; cursor: pointer; }
.card:hover { transform: translateY(-5px); box-shadow: 0 8px 20px rgba(0,0,0,0.12); }
.card-icon {
    font-size: 2rem;
    display: block;
    margin: 0 auto 10px auto;
}
.card h5 { color: #777; font-weight: 500; margin-bottom: 10px; }
.card h3 { font-size: 2.2rem; color: #111; }

/* Card colors */
.card { background: #ffffff; color: #b91c1c; }

/* Events list */
.events-list { background: #fff; border-radius: 12px; padding: 25px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
.events-list h5 { margin-bottom: 15px; color: #111; }
.events-list ul { list-style: none; }
.events-list li { padding: 10px 0; border-bottom: 1px solid #eee; transition: background 0.2s; }
.events-list li:last-child { border-bottom: none; }
.events-list li:hover { background: #fff0f0; }

/* Right sidebar */
.rightbar { display: flex; flex-direction: column; gap: 20px; }

/* Profile card */
.profile-card { background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); text-align: center; }
.profile-card img { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; margin-bottom: 15px; }
.profile-card h5 { margin-bottom: 5px; font-weight: 600; }
.profile-card h6 { margin-bottom: 15px; color: #555; }

/* Mini cards inside profile */
.mini-cards {
    display: flex;
    gap: 10px; /* space between cards */
    justify-content: center; /* center horizontally in sidebar */
}

/* Each mini-card content stacked vertically */
.mini-card {
    background: #fff0f0;
    padding: 12px 10px;
    border-radius: 10px;
    text-align: center;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
    width: 90px; /* fixed width for horizontal row */
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.mini-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.mini-card i {
    font-size: 1.5rem;
    color: #8a1e1e;
    margin-bottom: 5px;
}

.mini-card-label {
    font-size: 0.85rem;
    color: #555;
    margin-bottom: 2px;
}

.mini-card-value {
    font-size: 1rem;
    font-weight: 600;
    color: #111;
}

/* Calendar */
.calendar { background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
.calendar h5 { text-align: center; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; }
.calendar h5 button { background: none; border: none; font-size: 1.2rem; cursor: pointer; color: #8a1e1e; }
.calendar table { width: 100%; border-collapse: collapse; }
.calendar th, .calendar td { padding: 8px; text-align: center; border-radius: 6px; }
.calendar th { color: #555; }
.calendar td { cursor: pointer; transition: background 0.2s; }
.calendar td:hover { background: #fedbdb; }
.calendar td.today { background: #8a1e1e; color: #fff; }
.calendar td.selected { background: #065f46; color: #fff; }

/* Responsive */
@media (max-width: 900px) {
    .dashboard { grid-template-columns: 1fr; }
    .cards { grid-template-columns: 1fr; }
}
</style>
</head>
<body>
    <?php include "navbar_admin.php"; ?>


<div class="dashboard">
    <div class="left-section">
    <!-- Welcome box above the three cards -->
    <div class="welcome-box">
        <h4>Hello, <?php echo htmlspecialchars($name); ?>！</h4>
        <p>Welcome back!</p>
    </div>

    <!-- Three main cards -->
    <div class="cards">
        <div class="card donors">
            <i class='bx bx-user card-icon'></i>
            <h5>Total Donors</h5>
            <h3 id="total-donors"><?php echo $totalDonors; ?></h3>
        </div>
        <div class="card events">
            <i class='bx bx-calendar-event card-icon'></i>
            <h5>Total Events</h5>
            <h3 id="total-events"><?php echo $totalEvents; ?></h3>
        </div>
        <div class="card events">
            <i class='bx bx-run card-icon'></i>
            <h5>Total Active Events</h5>
            <h3 id="active-events">3</h3>
        </div>
    </div>

    <!-- Upcoming events list -->
    <div class="events-list">
        <h5>Upcoming Events</h5>
        <ul id="event-list">
            <li>Blood Donation Camp - 2026-02-10</li>
            <li>Community Health Checkup - 2026-02-15</li>
            <li>University Awareness Program - 2026-02-20</li>
        </ul>
    </div>
</div>

    <!-- Right Sidebar -->
    <div class="rightbar">
        <div class="profile-card">
            <img src="images/profile.png" alt="Profile Picture">
            <h5>Admin</h5>
            <h6></h6>
            <div class="mini-cards">
                <div class="mini-card">
                    <i class='bx bx-user-check'></i>
                    <div class="mini-card-label">Active Users</div>
                    <div class="mini-card-value"><?php echo $totalDonors; ?></div>
                </div>
                <div class="mini-card">
                    <i class='bx bx-hourglass'></i>
                    <div class="mini-card-label">Pending Approvals</div>
                    <div class="mini-card-value"><?php echo $pendingApprovals; ?></div>
                </div>
                <div class="mini-card" onclick="window.location.href='logout.php';" style="cursor:pointer;">
                    <i class='bx bx-log-out'></i>
                    <div class="mini-card-label">Logout</div>
                </div>
            </div>
        </div>

        <div class="calendar">
            <h5>
                <button onclick="prevMonth()">&lt;</button>
                <span id="month-year"></span>
                <button onclick="nextMonth()">&gt;</button>
            </h5>
            <table>
                <thead>
                    <tr>
                        <th>Su</th><th>Mo</th><th>Tu</th><th>We</th>
                        <th>Th</th><th>Fr</th><th>Sa</th>
                    </tr>
                </thead>
                <tbody id="calendar-body"></tbody>
            </table>
        </div>
    </div>
</div>

</div>

<script>
// Animated counters
function animateCounter(id, target) {
    let count = 0;
    const speed = Math.ceil(target / 100);
    const el = document.getElementById(id);
    const interval = setInterval(() => {
        count += speed;
        if(count >= target) { count = target; clearInterval(interval); }
        el.textContent = count;
    }, 10);
}

// Example frontend data
//animateCounter('total-donations', 12);
//animateCounter('events-joined', 3);
//document.getElementById('next-eligible').textContent = '2026-05-15';

// Calendar
let today = new Date();
let currentMonth = today.getMonth();
let currentYear = today.getFullYear();
const monthNames = ["January","February","March","April","May","June","July","August","September","October","November","December"];

function renderCalendar(month, year) {
    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const calendarBody = document.getElementById('calendar-body');
    calendarBody.innerHTML = '';
    document.getElementById('month-year').textContent = `${monthNames[month]} ${year}`;

    let date = 1;
    for(let i=0; i<6; i++) {
        let row = document.createElement('tr');
        for(let j=0; j<7; j++) {
            let cell = document.createElement('td');
            if(i === 0 && j < firstDay) {
                cell.textContent = '';
            } else if(date > daysInMonth) {
                cell.textContent = '';
            } else {
                cell.textContent = date;
                const isToday = date === today.getDate() && month === today.getMonth() && year === today.getFullYear();
                if(isToday) cell.classList.add('today');
                cell.addEventListener('click', () => {
                    document.querySelectorAll('.calendar td').forEach(td => td.classList.remove('selected'));
                    cell.classList.add('selected');
                });
                date++;
            }
            row.appendChild(cell);
        }
        calendarBody.appendChild(row);
    }
}

function prevMonth() {
    currentMonth--;
    if(currentMonth < 0) { currentMonth = 11; currentYear--; }
    renderCalendar(currentMonth, currentYear);
}

function nextMonth() {
    currentMonth++;
    if(currentMonth > 11) { currentMonth = 0; currentYear++; }
    renderCalendar(currentMonth, currentYear);
}

renderCalendar(currentMonth, currentYear);
</script>
<script src="script.js"></script>

</body>
</html>
