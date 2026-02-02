<?php
session_start();

session_destroy();

//direct to Main page
header("Location: login.php");
exit();
?>
