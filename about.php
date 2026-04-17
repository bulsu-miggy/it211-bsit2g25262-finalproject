<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LYNX - About Us</title>
    <link rel="stylesheet" href="css/login.css">
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;700&family=Rubik+Mono+One&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        body {
            background-color: black;
            color: white;
            font-family: 'Rubik', sans-serif;
        }
        .header {
            background: black;
            box-shadow: none;
        }
        .logo {
            color: white;
        }
        .icons .material-symbols-outlined {
            color: white;
        }
        .about-container {
            padding: 80px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .about-headline {
            font-family: 'Rubik Mono One', sans-serif;
            font-size: 6rem;
            font-weight: bold;
            letter-spacing: 3px;
            line-height: 1.1;
            margin: 40px 0;
            text-transform: uppercase;
        }
        .about-body {
            font-size: 1.3rem;
            line-height: 1.8;
            max-width: 800px;
        }
    </style>
</head>
<body>
  <header class="header">
    <a href="index.php" style="text-decoration: none;"><h1 class="logo">LYNX</h1></a>
    <div class="icons">
        <?php if (isset($_SESSION["username"])): ?>
            <a href="profiles.php" title="Profile" style="color: white; text-decoration: none;">
                <span class="material-symbols-outlined">account_circle</span>
            </a>
            <a href="#" class="logout-btn" style="color: white; text-decoration: none;">
                <span class="material-symbols-outlined">logout</span>
            </a>
        <?php else: ?>
            <a href="login.php" style="color: white; text-decoration: none;">
                <span class="material-symbols-outlined">person</span>
            </a>
        <?php endif; ?>
    </div>
  </header>

  <div class="about-container">
    <h2 class="about-headline">THIS IS<br>LYNX</h2>
    <p class="about-body">
        Style is our language, and the street is our stage. We’ve evolved beyond the rack to become a collective for the visionaries who see the world differently. By blending high-utility design with raw creative energy, we provide more than just apparel—we provide a signal to the rest of the world that you’re part of the few who define the "now."
    </p>
  </div>


  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script>
$(document).ready(function() {
    $('.logout-btn').on('click', function(e) {
        e.preventDefault(); 
        
        Swal.fire({
            title: 'Logout of LYNX?',
            text: "Are you sure you want to sign out?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#000000',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, Logout',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'logout.php'; 
            }
        });
    });
});
  </script>
</body>
</html>
