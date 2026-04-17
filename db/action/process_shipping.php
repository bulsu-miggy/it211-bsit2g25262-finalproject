<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Save all form data into a session array
    $_SESSION['checkout_shipping'] = [
        'full_name'   => trim($_POST['full_name'] ?? ''),
        'address'     => trim($_POST['address'] ?? ''),
        'apartment'   => trim($_POST['apartment'] ?? ''),
        'city'        => trim($_POST['city'] ?? ''),
        'postal_code' => trim($_POST['postal_code'] ?? ''),
        'phone'       => trim($_POST['phone'] ?? '')
    ];

    // Redirect to step 2
    header("Location: ../../checkout.php?step=2");
    exit();
}