<?php
$con = mysqli_connect("localhost","root",""); 

if(!mysqli_select_db($con, "it211_g1g4"))
{
  die("connection error");
}
?>