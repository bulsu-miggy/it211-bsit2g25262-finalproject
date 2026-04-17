<?php
session_start();
require_once '../auth.php';
requireAdmin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Categories</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body style="background-color: #f8f9fa;">
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">SipFlask Admin</a>
            <div class="collapse navbar-collapse justify-content-end">
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item me-3"><a class="nav-link" href="index.php">Dashboard</a></li>
                    <li class="nav-item"><a class="btn btn-outline-primary" href="../logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <main class="container py-5">
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-body p-5">
                <h1 class="mb-3" style="color: #008080;">Categories</h1>
                <p class="text-muted">This is the admin categories page. Manage product categories here.</p>
                <div class="alert alert-info">This page is a placeholder admin categories page. The original admin page file content was not served by PHP.</div>
            </div>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
