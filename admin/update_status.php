<?php
include '../db.php';

if (isset($_POST['order_id']) && isset($_POST['new_status'])) {
    $id = $_POST['order_id'];
    $status = $_POST['new_status'];
    
    $query = "UPDATE orders SET status = '$status' WHERE id = '$id'";
    mysqli_query($conn, $query);
    
    header("Location: orders.php?msg=StatusUpdated");
}
?>