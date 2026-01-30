<?php
session_start();

if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

$role = $_SESSION['role'];
?>

<div class="d-flex">

    <!-- Load role navbar/sidebar -->
    <?php include "navbar/navbar_$role.php"; ?>

    <!-- Dashboard Content -->
    <div class="flex-grow-1 p-4">
        <?php include "dashboard/$role.php"; ?>
    </div>

</div>
