<?php
session_start();
if(!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
include('../config/db_connect.php');

if(isset($_GET['id'])) {
    $id = $_GET['id'];

    // 1. Kunin muna ang filename ng image para mabura sa folder
    $select_query = mysqli_query($conn, "SELECT image FROM products WHERE id = '$id'");
    $fetch_image = mysqli_fetch_assoc($select_query);
    $image_to_delete = $fetch_image['image'];

    // 2. Burahin ang record sa database
    $delete_query = "DELETE FROM products WHERE id = '$id'";
    
    if(mysqli_query($conn, $delete_query)) {
        // 3. Burahin ang file sa 'uploads' folder kung mayroon man
        if(file_exists('../uploads/' . $image_to_delete)) {
            unlink('../uploads/' . $image_to_delete);
        }
        header("Location: products.php?msg=deleted");
    } else {
        header("Location: products.php?msg=error");
    }
} else {
    header("Location: products.php");
}
?>