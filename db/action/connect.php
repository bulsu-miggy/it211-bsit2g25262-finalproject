<?php
$con = mysqli_connect("localhost","root",""); 

if(!mysqli_select_db($con, "w71"))
{
  die("connection error");
}
?>