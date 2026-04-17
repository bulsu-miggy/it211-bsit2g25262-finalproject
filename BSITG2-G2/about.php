<?php session_start(); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Annyeong Haven | About Us</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(180deg, #a9c9ff 0%, #ffffff 100%);
            min-height: 100vh;
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
        .content-card {
            margin-top: 2.5rem;
            margin-bottom: 2rem;
            line-height: 1.8;
            text-align: justify;
            color: #000;
        }
        .mission-title {
            font-size: 2rem;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<?php include '../header.php'; ?>

    <div class="container content-card">
        
        <div class="text-center mb-3">
            <p class="lead fs-4">
                Welcome to <strong>Annyeong Haven</strong>, a home for K-pop fans who want to celebrate their favorite artists in the brightest way possible. Our store is dedicated to providing high-quality K-pop lightsticks that allow fans to show their support and connect with the global K-pop community.
            </p>
            
            <p class="lead fs-4">
                At <strong>Annyeong Haven</strong>, we believe that a lightstick is more than just merchandise—it is <strong>a symbol of fandom, unity,</strong> and unforgettable concert moments. Whether you're attending a live concert, watching an online performance, or simply collecting official fan items, our goal is to make sure you have the <strong>perfect lightstick to represent your favorite group.</strong>
            </p>

            <p class="lead fs-4">
                We carefully select and offer a variety of authentic and popular K-pop lightsticks from different artists and groups. Our mission is to provide fans with reliable products, excellent service, and a smooth online shopping experience.
            </p>
        </div>

        <hr class="my-5">

        <div class="text-center">
            <h2 class="h1 mb-5 fw-bold">Our Mission</h2>
            <p class="h3">To bring fans closer to their idols by providing trusted K-pop merchandise that makes every fan experience brighter.</p>
        </div>

        <div class="row justify-content-center mt-5">
            <div class="col-md-8 text-center">
                <h3 class="h2 fst-italic mb-4 fw-semibold">Why Choose Annyeong Haven?</h3>
                <ul class="list-styled">
                    <li class="h4 mb-4"> Carefully selected K-pop lightsticks</li>
                    <li class="h4 mb-4"> Fan-focused and reliable service</li>
                    <li class="h4 mb-4"> A safe and convenient online shopping experience</li>
                    <li class="h4 mb-4"> A store created by fans, for fans</li>
                </ul>
            </div>
        </div>

        <div class="text-center mt-5 py-5">
            <h4 class="fst-italic fw-bold">Thank you for visiting Annyeong Haven. We are happy to be part of your fandom journey!</h4>
        </div>        
    </div>

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