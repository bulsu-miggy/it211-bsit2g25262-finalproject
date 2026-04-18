<?php
session_start();
if(!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
include('../config/db_connect.php');

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);

    // BABALA: Kapag binura ang category, baka mag-error ang products na naka-link dito.
    // Opsyonal: I-check muna kung may products bago i-delete.
    $delete_query = "DELETE FROM categories WHERE id = '$id'";

    if (mysqli_query($conn, $delete_query)) {
        header("Location: categories.php?msg=deleted");
    } else {
        echo "Error deleting record: " . mysqli_error($conn);
    }
} else {
    header("Location: categories.php");
}
?>