<?php
include "db.php";

$email = $_POST['email'];
$password = $_POST['password'];

$sql = "SELECT * FROM users WHERE email='$email'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {

  $row = $result->fetch_assoc();

  if (password_verify($password, $row['password'])) {

    echo json_encode([
      "status" => "success",
      "name" => $row['name'],
      "email" => $row['email']
    ]);

  } else {
    echo json_encode(["status"=>"wrong"]);
  }

} else {
  echo json_encode(["status"=>"not_found"]);
}
?>