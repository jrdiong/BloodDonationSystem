<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <!-- Boxicons CSS -->
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
    <title>Navigation bar</title>
    <link rel="stylesheet" href="style.css" />
    <style>
      .nav_link.active {
        background: #f44040;
        color: #fff;
        border-radius: 10px;
      }

      .nav_link.active .navlink_icon i {
        color: #fff;
      }
    </style>
  </head>
  <body>
<!-- navbar -->
    <nav class="navbar">
      <div class="logo_item">
        <i class="bx bx-menu" id="sidebarOpen"></i>
        <img src="images/logo.png" alt=""></i>CBDC App
      </div>
      <div class="search_bar">
        <input type="text" placeholder="Search" />
      </div>
      <div class="navbar_content">
        <i class="bi bi-grid"></i>
        <i class='bx bx-sun' id="darkLight"></i>
        <a href="profileUI.php"><img src="images/profile.jpg" alt="" class="profile" /></a>
      </div>
    </nav>
    <!-- sidebar -->
    <nav class="sidebar">
      <div class="menu_content">
        <ul class="menu_items">
          <div class="menu_title"></div>
          <!-- duplicate these li tag if you want to add or remove navlink only -->
          <!-- Start -->
          <li class="item">
            <a href="donor.php" class="nav_link <?= ($currentPage == 'donor.php') ? 'active' : '' ?>">
              <span class="navlink_icon">
                <i class="bx bxs-dashboard"></i>
              </span>
              <span class="navlink">Dashboard</span>
            </a>
          </li>
          <!-- End -->
          <li class="item">
            <a href="events.php" class="nav_link <?= ($currentPage == 'events.php') ? 'active' : '' ?>">
              <span class="navlink_icon">
                <i class="bx bx-calendar-event"></i>
              </span>
              <span class="navlink">Event</span>
            </a>
          </li>
          <li class="item">
            <a href="appointment.php" class="nav_link <?= ($currentPage == 'appointments.php') ? 'active' : '' ?>">
              <span class="navlink_icon">
                <i class="bx bx-calendar-check"></i>
              </span>
              <span class="navlink">Appointment</span>
            </a>
          </li>
          <li class="item">
            <a href="profileUI.php" class="nav_link <?= ($currentPage == 'profileUI.php') ? 'active' : '' ?>">
              <span class="navlink_icon">
                <i class="bx bx-user-circle"></i>
              </span>
              <span class="navlink">Profile</span>
            </a>
          </li>
        </ul>
        <!-- Sidebar Open / Close -->
        <div class="bottom_content">
          <div class="bottom expand_sidebar">
            <span> Expand</span>
            <i class='bx bx-log-in' ></i>
          </div>
          <div class="bottom collapse_sidebar">
            <span> Collapse</span>
            <i class='bx bx-log-out'></i>
          </div>
        </div>
      </div>
    </nav>
            <!-- JavaScript -->
    <script src="script.js"></script>
  </body>
</html>