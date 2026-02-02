<?php
session_start();

session_destroy();

//direct to Main page
header("Location: index.php");
exit();
?>
