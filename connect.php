<?php
$con = mysqli_connect("localhost","root",""); 

include 'connection.php';

if(!mysqli_select_db($con, "it211_g2g3"))
{
  die("connection error");
}
?>