<?php session_start(); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Annyeong Haven | Contact</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        body {
           background-image: url('exo.jpg');
           background-size: cover;
           background-position: center;
           background-attachment: fixed;
           background-repeat: no-repeat;
           margin-top: 0;
           font-family: 'Poppins', sans-serif;
           color: #333;
        }

        .header-section {
            background-color: #13195b;
            color: white;
            padding: 22px 0;
        }

        .header-brand {
            letter-spacing: 2px;
            font-size: 1.6rem;
        }

        .header-icons a {
            color: white;
            font-size: 1.4rem;
            text-decoration: none;
            margin-left: 18px;
            opacity: 0.9;
        }

        .page-nav {
            background-color: #f8f9fa;
            padding: 14px 0;
        }

        .page-nav .nav-link {
            color: #5f6368 !important;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.95rem;
            letter-spacing: 0.5px;
        }

        .page-nav .nav-link.active {
            color: #13195b !important;
        }

        .page-nav .nav-separator {
            color: #ced4da;
        }

        .main-content {
            padding: 50px 0;
        }

        .footer-section {
            background-color: #333333; 
            color: white;
            padding: 60px 0 0 0; 
        }
    </style>
</head>
<body>

<?php include '../header.php'; ?>

    <main class="main-content">
        <div class="container">
            <div style="height: 1500px;">
            </div>
        </div>
    </main>

    <footer class="footer-section pt-5" style="background: linear-gradient(90deg, #000000 0%, #13195b 40%, #3a36c6 100%); color: white;">
      <div class="container pb-5">
        
        <div class="row mb-5">
            <div class="col-12">
                <h1 class="fw-bold display-5">ANNYEONG HAVEN</h1>
            </div>
        </div>

        <div class="row mb-5">
            <div class="col-md-4">
                <h5 class="fw-bold mb-3">Office</h5>
                <p class="mb-1">Malolos City, Bulacan</p>
                <p class="mb-0">annyeonghaven@gmail.com</p>
            </div>
            <div class="col-md-4">
                <h5 class="fw-bold mb-3">Business hours</h5>
                <p class="mb-1">Monday - Friday: 9am - 6pm</p>
                <p class="mb-0">Saturday: 9am - 12pm</p>
            </div>
        </div>

        <div class="row align-items-center py-3 border-top border-bottom border-secondary">
            <div class="col-auto">
                <h5 class="mb-0 fw-bold">Get social</h5>
            </div>
            <div class="col-auto">
                <div class="d-flex gap-3">
                    <a href="#" class="bg-white text-dark rounded-circle d-flex align-items-center justify-content-center text-decoration-none fw-bold" style="width: 35px; height: 35px;">f</a>
                    <a href="#" class="bg-white text-dark rounded-circle d-flex align-items-center justify-content-center text-decoration-none fw-bold" style="width: 35px; height: 35px;">in</a>
                </div>
            </div>
        </div>
        
      </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>