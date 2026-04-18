<?php
$con = mysqli_connect("localhost","root",""); 

include 'connection.php';

if(!mysqli_select_db($con, "wst"))
{
  die("connection error");
}
?>