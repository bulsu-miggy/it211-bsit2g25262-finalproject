<?php
session_start();

// Simply redirect everyone to home.php. 
// home.php already has the logic to show the Guest or Member header!
header("Location: home.php");
exit();