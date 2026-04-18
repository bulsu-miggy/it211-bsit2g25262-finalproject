<?php
session_start();
if(!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
include('../config/db_connect.php');

if(isset($_GET['id'])) {
    $user_id = mysqli_real_escape_string($conn, $_GET['id']);

    // BABALA: Dahil may orders ang user, kailangan mo munang i-delete 
    // ang references sa ibang tables (Foreign Key constraint)
    // o i-set ang user_id sa orders table as NULL.
    
    // Step 1: I-update ang orders para hindi mag-error ang database (Set user_id to NULL)
    $update_orders = "UPDATE orders SET user_id = NULL WHERE user_id = '$user_id'";
    mysqli_query($conn, $update_orders);

    // Step 2: I-delete na ang user
    $query = "DELETE FROM users WHERE id = '$user_id'";

    if(mysqli_query($conn, $query)) {
        // Kapag successful, babalik sa customers.php na may success message
        header("Location: customers.php?msg=deleted");
    } else {
        // Kapag may error
        echo "Error deleting record: " . mysqli_error($conn);
    }
} else {
    header("Location: customers.php");
}
?>